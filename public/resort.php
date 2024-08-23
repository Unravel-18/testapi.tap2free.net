<?php

set_time_limit(60*10);

$db = require(__dir__ . "/../config/db.php");

$config = [
   'db' => [
        'mysql' => [
            'DRIVER' => 'mysql',
            'DB_PERSISTENCY' => true,
            'DB_SERVER' => $db['mysql']['host'],
            'DB_DATABASE' => $db['mysql']['database'],
            'DB_USERNAME' => $db['mysql']['username'],
            'DB_PASSWORD' => $db['mysql']['password'],
            'DB_CHARSET' => 'utf8',
        ],
    ],
];

foreach (DB::GetAll("SELECT `id`, `sort` FROM `servers` ORDER BY `servers`.`sort` ASC") as $key => $row) {
    DB::Execute("UPDATE `servers` SET `sort`=" . ($key + 1) . " WHERE `id` = " . $row['id']);
}

$data = [];

foreach (DB::GetAll("SELECT `id`, `api_id`, `sort` FROM `api_servers` ORDER BY `api_servers`.`sort` ASC") as $key => $row) {
    $data[$row['api_id']][] = $row;
}

foreach ($data as $rows) {
    foreach ($rows as $key => $row) {
        DB::Execute("UPDATE `api_servers` SET `sort`=" . ($key + 1) . " WHERE `id` = " . $row['id']);
    }
}

class DB
{
    const TIMEOUT_CONNECT = 20;
    protected static $last_time = 0;

    protected static $connects_pdo = array();

    protected static $count_query = 0;
    protected static $duration_query = 0;

    public static function GetHandler($connect = null, $is_count = true)
    {
        if (!$connect) {
            $connect = 'mysql';
        }

        if ($is_count) {
            self::$count_query++;
        }

        if (!isset(self::$connects_pdo[$connect]) || (time() - self::$last_time) > self::
            TIMEOUT_CONNECT) {
            $config = $GLOBALS['config']['db'][$connect];

            // Выполняем код, перехватывая потенциальные исключения
            try {
                // Создаем новый экземпляр класса PDO
                switch ($config['DRIVER']) {
                    case 'sqlsrv':
                        self::$connects_pdo[$connect] = new \PDO('sqlsrv:Server=' . $config['DB_SERVER'] .
                            ';Database=' . $config['DB_DATABASE'], $config['DB_USERNAME'], $config['DB_PASSWORD']);
                        // self::$connects_pdo[$connect]->exec("SET character_set_database = " . $config['DB_CHARSET']);
                        // self::$connects_pdo[$connect]->exec("SET NAMES " . $config['DB_CHARSET']);
                        break;
                    case 'sqlite':
                        self::$connects_pdo[$connect] = new \PDO('sqlite:' . $config['DB_DATABASE']);
                        break;
                    case 'mysql':
                        self::$connects_pdo[$connect] = new \PDO('mysql:host=' . $config['DB_SERVER'] .
                            ';dbname=' . $config['DB_DATABASE'] . ';charset=' . $config['DB_CHARSET'], $config['DB_USERNAME'],
                            $config['DB_PASSWORD'], array(\PDO::ATTR_PERSISTENT => $config['DB_PERSISTENCY']));
                        self::$connects_pdo[$connect]->exec("SET NAMES '" . $config['DB_CHARSET'] . "'");
                        break;
                    default:
                        self::$connects_pdo[$connect] = null;

                }

                // Настраиваем PDO на генерацию исключений
                self::$connects_pdo[$connect]->setAttribute(\PDO::ATTR_ERRMODE, \PDO::
                    ERRMODE_EXCEPTION);
            }
            catch (PDOException $e) {
                // Закрываем дескриптор и генерируем ошибку
                self::Close($connect);
                trigger_error($e->getMessage(), E_USER_ERROR);
            }
        }

        self::$last_time = time();

        // Возвращаем дескриптор базы данных
        return self::$connects_pdo[$connect];
    }

    // Очищаем экземпляр класса PDO
    public static function Close($connect = null)
    {
        if (!$connect) {
            $connect = 'mysql';
        }

        self::$connects_pdo[$connect] = null;
    }

    public static function getCountQuery()
    {
        return self::$count_query;
    }

    public static function getDurationQuery()
    {
        return self::$duration_query;
    }

    // Метод-обертка для PDOStatement::execute()
    public static function Execute($sqlQuery, $params = null, $connect = null)
    {
        if (isAdmin() && config('isDBtesting')) {
            echo $sqlQuery;
        }

        // Пытаемся выполнить SQL-запрос или хранимую процедуру
        try {
            $begin_time = microtime(true);
            // Получаем дескриптор базы данных
            $database_handler = self::GetHandler($connect);
            

            // Подготавливаем запрос к выполнению
            $statement_handler = $database_handler->prepare($sqlQuery);

            // Выполняем запрос
            
            $res = self::PrepareAndExecute($statement_handler, $params);
            
            self::$duration_query = self::$duration_query+(microtime(true) - $begin_time);
            
            return $res;
            //return $statement_handler->execute($params);
        }
        // Генерируем ошибку, если при выполнении SQL-запроса возникло исключение
        catch (PDOException $e) {
            // Закрываем дескриптор базы данных и генерируем ошибку
            self::Close();
            trigger_error($e->getMessage(), E_USER_ERROR);
        }
    }

    // Метод-обертка для PDOStatement::fetchAll(). Извлекает все строки
    public static function GetAll($sqlQuery, $params = null, $fetchStyle = \PDO::
        FETCH_ASSOC, $connect = null)
    {
        if (isAdmin() && config('isDBtesting')) {
            echo $sqlQuery;
        }

        $result = array();

        // Пытаемся выполнить SQL-запрос или хранимую процедуру
        try {
            $begin_time = microtime(true);
            // Получаем дескриптор базы данных
            $database_handler = self::GetHandler($connect);

            // Подготавливаем запрос к выполнению
            $statement_handler = $database_handler->prepare($sqlQuery);

            // Выполняем запрос
            //$statement_handler->execute($params);
            self::PrepareAndExecute($statement_handler, $params);

            // Получаем результат
            
            $result = $statement_handler->fetchAll($fetchStyle);
            
            self::$duration_query = self::$duration_query+(microtime(true) - $begin_time);
        }
        // Генерируем ошибку, если при выполнении SQL-запроса возникло исключение
        catch (PDOException $e) {
            // Закрываем дескриптор базы данных и генерируем ошибку
            self::Close();
            trigger_error($e->getMessage(), E_USER_ERROR);
        }

        // Возвращаем результаты запроса
        return $result;
        //return $this->getCollection( $result );
    }

    // Метод-обертка для PDOStatement::fetch().  Извлечение следующей строки.
    public static function GetRow($sqlQuery, $params = null, $fetchStyle = \PDO::
        FETCH_ASSOC, $connect = null)
    {
        if (isAdmin() && config('isDBtesting')) {
            echo $sqlQuery;
        }

        // Инициализируем возвращаемое значение
        $result = null;

        // Пытаемся выполнить SQL-запрос или хранимую процедуру
        try {
            $begin_time = microtime(true);
            // Получаем дескриптор базы данных
            $database_handler = self::GetHandler($connect);

            // Готовим запрос к выполнению
            $statement_handler = $database_handler->prepare($sqlQuery);

            // Выполняем запрос
            //$statement_handler->execute($params);
            self::PrepareAndExecute($statement_handler, $params);

            // Получаем результат
            
            $result = $statement_handler->fetch($fetchStyle);
            
            self::$duration_query = self::$duration_query+(microtime(true) - $begin_time);
        }
        // Генерируем ошибку, если при выполнении SQL-запроса возникло исключение
        catch (PDOException $e) {
            // Закрываем дескриптор базы данных и генерируем ошибку
            self::Close();
            trigger_error($e->getMessage(), E_USER_ERROR);
        }

        // Возвращаем результаты запроса
        return $result;
    }

    // Возвращает значение первого столбца из строки
    public static function GetOne($sqlQuery, $params = null, $connect = null)
    {
        if (isAdmin() && config('isDBtesting')) {
            echo $sqlQuery;
        }

        // Инициализируем возвращаемое значение
        $result = null;

        // Пытаемся выполнить SQL-запрос или хранимую процедуру
        try {
            $begin_time = microtime(true);
            // Получаем дескриптор базы данных
            $database_handler = self::GetHandler($connect);

            // Готовим запрос к выполнению
            $statement_handler = $database_handler->prepare($sqlQuery);

            // Выполняем запрос
            //$statement_handler->execute($params);
            self::PrepareAndExecute($statement_handler, $params);

            // Получаем результат
            
            $result = $statement_handler->fetch(\PDO::FETCH_NUM);
            
            self::$duration_query = self::$duration_query+(microtime(true) - $begin_time);

            /* Сохраняем первое значение из множества (первый столбец первой строки) в переменной $result */
            $result = $result[0];
        }

        // Генерируем ошибку, если при выполнении SQL-запроса возникло исключение
        catch (PDOException $e) {
            // Закрываем дескриптор базы данных и генерируем ошибку
            self::Close();
            trigger_error($e->getMessage(), E_USER_ERROR);
        }

        // Возвращаем результаты выполнения запроса
        return $result;
    }

    // Возвращает значение первого столбца из строки
    public static function LastInsertId($connect = null)
    {
        // Инициализируем возвращаемое значение
        $result = null;

        // Пытаемся выполнить SQL-запрос или хранимую процедуру
        try {
            $begin_time = microtime(true);
            // Получаем дескриптор базы данных
            $database_handler = self::GetHandler($connect, false);

            
            $result = $database_handler->lastInsertId();
            
            self::$duration_query = self::$duration_query+(microtime(true) - $begin_time);
        }

        // Генерируем ошибку, если при выполнении SQL-запроса возникло исключение
        catch (PDOException $e) {
            // Закрываем дескриптор базы данных и генерируем ошибку
            self::Close();
            trigger_error($e->getMessage(), E_USER_ERROR);
        }

        // Возвращаем результаты выполнения запроса
        return $result;
    }

    private static function PrepareAndExecute($sth, &$params)
    {
        if (is_array($params))
            foreach ($params as $key => $value) {
                switch (gettype($value)) {
                    case 'boolean';
                        $sth->bindValue($key, $value, \PDO::PARAM_BOOL);
                        break;
                    case 'integer';
                        $sth->bindValue($key, $value, \PDO::PARAM_INT);
                        break;
                    case 'double';
                        $sth->bindValue($key, $value, \PDO::PARAM_STR);
                        break;
                    case 'string';
                        $sth->bindValue($key, $value, \PDO::PARAM_STR);
                        break;
                    case 'array';
                        break;
                    case 'object';
                        break;
                    case 'resource';
                        break;
                    case 'NULL';
                        $sth->bindValue($key, $value, \PDO::PARAM_NULL);
                        break;
                    case 'unknown type';
                        break;
                }
            }

        return $sth->execute();
    }
}

function config($key, $default = null)
{
    return value_get($GLOBALS['config'], $key, $default);
}

function isAdmin()
{
    return value_get($GLOBALS['config'], 'is_admin');
}

function object_get($object, $keys, $default = null)
{
    if (is_null($keys)) {
        return $object;
    }

    if (!is_array($keys)) {
        $keys = explode('.', $keys);
    }

    foreach ($keys as $segment) {
        if (isset($object->{$segment})) {
            $object = $object->{$segment};
        } else {
            return $default;
        }
    }

    return $object;
}

function array_get($array, $keys, $default = null)
{
    if (is_null($keys)) {
        return $array;
    }

    if (!is_array($keys)) {
        $keys = explode('.', $keys);
    }

    foreach ($keys as $segment) {
        if (isset($array[$segment])) {
            $array = $array[$segment];
        } else {
            return $default;
        }
    }

    return $array;
}

function value_get($value, $keys, $default = null)
{
    if (is_null($keys)) {
        return $value;
    }

    if (!is_array($keys)) {
        $keys = explode('.', $keys);
    }

    foreach ($keys as $segment) {
        if ($pos = stripos($segment, '(')) {
            $method = substr($segment, 0, $pos);
            $args = array_map(function ($item)
            {
                return trim($item); }
            , explode(',', trim(rtrim(substr($segment, $pos + 1), ')'))));

            /*
            print_r($value);echo "\n";
            print_r($segment);echo "\n";
            print_r($method);echo "\n";
            print_r($args);echo "\n";
            
            exit();
            */

            if (is_object($value)) {
                switch (count($args)) {
                    case 1:
                        $value = $value->{$method}($args[0]);
                        break;
                    case 2:
                        $value = $value->{$method}($args[0], $args[1]);
                        break;
                    case 3:
                        $value = $value->{$method}($args[0], $args[1], $args[2]);
                        break;
                    case 4:
                        $value = $value->{$method}($args[0], $args[1], $args[2], $args[3]);
                        break;
                    case 5:
                        $value = $value->{$method}($args[0], $args[1], $args[2], $args[3], $args[4]);
                        break;
                    case 6:
                        $value = $value->{$method}($args[0], $args[1], $args[2], $args[3], $args[4], $args[5]);
                        break;
                    case 7:
                        $value = $value->{$method}($args[0], $args[1], $args[2], $args[3], $args[4], $args[5],
                            $args[6]);
                        break;
                    case 8:
                        $value = $value->{$method}($args[0], $args[1], $args[2], $args[3], $args[4], $args[5],
                            $args[6], $args[7]);
                        break;
                    case 9:
                        $value = $value->{$method}($args[0], $args[1], $args[2], $args[3], $args[4], $args[5],
                            $args[6], $args[7], $args[8]);
                        break;
                    case 10:
                        $value = $value->{$method}($args[0], $args[1], $args[2], $args[3], $args[4], $args[5],
                            $args[6], $args[7], $args[8], $args[9]);
                        break;
                    default:
                        $value = $value->{$method}();
                        break;
                }
            } elseif (is_array($value)) {
                switch (count($args)) {
                    case 1:
                        $value = $value[$method]($args[0]);
                        break;
                    case 2:
                        $value = $value[$method]($args[0], $args[1]);
                        break;
                    case 3:
                        $value = $value[$method]($args[0], $args[1], $args[2]);
                        break;
                    case 4:
                        $value = $value[$method]($args[0], $args[1], $args[2], $args[3]);
                        break;
                    case 5:
                        $value = $value[$method]($args[0], $args[1], $args[2], $args[3], $args[4]);
                        break;
                    case 6:
                        $value = $value[$method]($args[0], $args[1], $args[2], $args[3], $args[4], $args[5]);
                        break;
                    case 7:
                        $value = $value[$method]($args[0], $args[1], $args[2], $args[3], $args[4], $args[5],
                            $args[6]);
                        break;
                    case 8:
                        $value = $value[$method]($args[0], $args[1], $args[2], $args[3], $args[4], $args[5],
                            $args[6], $args[7]);
                        break;
                    case 9:
                        $value = $value[$method]($args[0], $args[1], $args[2], $args[3], $args[4], $args[5],
                            $args[6], $args[7], $args[8]);
                        break;
                    case 10:
                        $value = $value[$method]($args[0], $args[1], $args[2], $args[3], $args[4], $args[5],
                            $args[6], $args[7], $args[8], $args[9]);
                        break;
                    default:
                        $value = $value[$method]();
                        break;
                }
            } else {
                return $default;
            }
        } else {
            if (is_object($value) && isset($value->{$segment})) {
                $value = $value->{$segment};
            } elseif (is_array($value) && isset($value[$segment])) {
                $value = $value[$segment];
            } else {
                return $default;
            }
        }
    }

    return $value;
}

function value_set(&$value, $keys, $var, $recurs = true)
{
    if (is_null($keys)) {
        return $value = $var;
    }

    if (!is_array($keys)) {
        $keys = explode('.', $keys);
    }

    while ($count = count($keys) > 1) {
        $key = array_shift($keys);

        if (is_object($value)) {
            if ($recurs) {
                if (!isset($value->{$key})) {
                    $value->{$key} = new \Sirius\Base\BaseObj;
                }

                if (!is_object($value->{$key})) {
                    $value->{$key} = new \Sirius\Base\BaseObj([$value->{$key}]);
                }
            } else {
                if (!isset($value->{$key}) || !is_object($value->{$key})) {
                    return false;
                }
            }

            $value = $value->{$key};
        } elseif (is_array($value)) {
            if ($recurs) {
                if (!isset($value[$key])) {
                    $value[$key] = [];
                }

                if (!is_array($value[$key])) {
                    $value[$key] = [$value[$key]];
                }
            } else {
                if (!isset($value[$key]) || !is_array($value[$key])) {
                    return false;
                }
            }

            $value = &$value[$key];
        } else {
            return false;
        }
    }

    if (!is_object($value) && !is_array($value)) {
        return false;
    }

    $key = array_shift($keys);

    if (is_object($value)) {
        $value->{$key} = $var;
    } elseif (is_array($value)) {
        $array[$key] = $value;
    }

    return true;
}

function object_set(&$object, $keys, $value, $recurs = true)
{
    if (is_null($keys)) {
        return $object = $value;
    }

    if (!is_array($keys)) {
        $keys = explode('.', $keys);
    }

    while ($count = count($keys) > 1) {
        $key = array_shift($keys);

        if ($recurs) {
            if (!isset($object->{$key})) {
                $object->{$key} = new \Sirius\Base\BaseObj;
            }

            if (!is_object($object->{$key})) {
                $object->{$key} = new \Sirius\Base\BaseObj([$object->{$key}]);
            }
        } else {
            if (!isset($object->{$key}) || !is_object($object->{$key})) {
                return false;
            }
        }

        $object = $object->{$key};
    }

    if (!is_object($object)) {
        return false;
    }

    $key = array_shift($keys);

    $object->{$key} = $value;

    return true;
}

function array_set(&$array, $keys, $value, $recurs = true)
{
    if (is_null($keys)) {
        return $array = $value;
    }

    if (!is_array($keys)) {
        $keys = explode('.', $keys);
    }

    while ($count = count($keys) > 1) {
        $key = array_shift($keys);

        if ($recurs) {
            if (!isset($array[$key])) {
                $array[$key] = [];
            }

            if (!is_array($array[$key])) {
                $array[$key] = [$array[$key]];
            }
        } else {
            if (!isset($array[$key]) || !is_array($array[$key])) {
                return false;
            }
        }

        $array = &$array[$key];
    }

    if (!is_array($array)) {
        return false;
    }

    $key = array_shift($keys);

    $array[$key] = $value;

    return true;
}

function array_replaces(&$array, $keys, $value, $recurs = true)
{
    if (is_null($keys)) {
        return $array = $value;
    }

    if (!is_array($keys)) {
        $keys = explode('.', $keys);
    }

    while ($count = count($keys) > 1) {
        $key = array_shift($keys);

        if ($recurs) {
            if (!isset($array[$key])) {
                $array[$key] = [];
            }

            if (!is_array($array[$key])) {
                $array[$key] = [$array[$key]];
            }
        } else {
            if (!isset($array[$key]) || !is_array($array[$key])) {
                return false;
            }
        }

        $array = &$array[$key];
    }

    if (!is_array($array)) {
        return false;
    }

    $key = array_shift($keys);

    if (isset($array[$key])) {
        $array[$key] = $value;

        return true;
    }

    return false;
}

function array_append(&$array, $keys, $value, $recurs = true)
{
    if (is_null($keys)) {
        return $array = $value;
    }

    if (!is_array($keys)) {
        $keys = explode('.', $keys);
    }

    while ($count = count($keys) > 0) {
        $key = array_shift($keys);

        if ($recurs) {
            if (!isset($array[$key])) {
                $array[$key] = [];
            }

            if (!is_array($array[$key])) {
                $array[$key] = [$array[$key]];
            }
        } else {
            if (!isset($array[$key]) || !is_array($array[$key])) {
                return false;
            }
        }

        $array = &$array[$key];
    }

    if (!is_array($array)) {
        return false;
    }

    $array = array_merge($array, [$value]);

    return true;
}

function array_prepend(&$array, $keys, $value, $recurs = true)
{
    if (is_null($keys)) {
        return $array = $value;
    }

    if (!is_array($keys)) {
        $keys = explode('.', $keys);
    }

    while ($count = count($keys) > 0) {
        $key = array_shift($keys);

        if ($recurs) {
            if (!isset($array[$key])) {
                $array[$key] = [];
            }

            if (!is_array($array[$key])) {
                $array[$key] = [$array[$key]];
            }
        } else {
            if (!isset($array[$key]) || !is_array($array[$key])) {
                return false;
            }
        }

        $array = &$array[$key];
    }

    if (!is_array($array)) {
        return false;
    }

    $array = array_merge([$value], $array);

    return true;
}

function array_exists(&$array, $keys)
{
    if (!is_array($keys)) {
        $keys = explode('.', $keys);
    }

    if (empty($keys)) {
        return false;
    }

    while (count($keys) > 1) {
        $key = array_shift($keys);

        if (!isset($array[$key]) || !is_array($array[$key])) {
            return false;
        }

        $array = &$array[$key];
    }

    $key = array_shift($keys);

    if (!isset($array[$key])) {
        return false;
    }

    return true;
}

function array_forget(&$array, $keys)
{
    $original = &$array;

    $keys = (array )$keys;

    if (count($keys) === 0) {
        return;
    }

    foreach ($keys as $key) {
        // if the exact key exists in the top-level, remove it
        if (isset($array[$key])) {
            unset($array[$key]);

            continue;
        }

        $parts = explode('.', $key);

        // clean up before each pass
        $array = &$original;

        while (count($parts) > 1) {
            $part = array_shift($parts);

            if (isset($array[$part]) && is_array($array[$part])) {
                $array = &$array[$part];
            } else {
                continue 2;
            }
        }

        unset($array[array_shift($parts)]);
    }
}

function array_pull(&$array, $key, $default = null)
{
    $value = array_get($array, $key, $default);

    array_forget($array, $key);

    return $value;
}

function delFolder($dir)
{
    if (is_dir($dir)) {
        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $file) {
            (is_dir($dir . DIRECTORY_SEPARATOR . $file)) ? delFolder($dir .
                DIRECTORY_SEPARATOR . $file) : unlink($dir . DIRECTORY_SEPARATOR . $file);
        }
        return rmdir($dir);
    }
}

function str_rand($length = 10)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function isRun()
{
    if ($GLOBALS['config']['token'] == file_get_contents(__dir__ . '/tmp.token.txt')) {
        return true;
    }
    
    return false;
}

