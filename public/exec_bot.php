<?php

ini_set('error_reporting', E_ALL);// E_ALL E_ERROR
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

set_time_limit(60*10);

$begin_time = microtime(true);
$count_success = 0;
$count_error = 0;

$config = [
   'connect_count' => 5, // Количество потоков
   'connect_timeout' => 3, // Таймаут запроса. Секунд
   
   'db' => [
        'mysql' => [
            'DRIVER' => 'mysql',
            'DB_PERSISTENCY' => true,
            'DB_SERVER' => '127.0.0.1',
            'DB_DATABASE' => 'testapi',
            'DB_USERNAME' => 'testapi',
            'DB_PASSWORD' => 'uI8eR1pK6q',
            'DB_CHARSET' => 'utf8',
        ],
    ],
    
    'TELGRAM_BOT' => '1605406681:AAESEJZbyugRhbpKc6UE3sOj3ldfwY0Goho',
    'TELGRAM_CHAT' => '-1001355176511',
    
    'dir_badservers' => __dir__ . '/../storage/badservers',
    
    'token' => str_rand(20),
];

date_default_timezone_set('Europe/Kiev');

foreach (scandir(config('dir_badservers')) as $key => $value) {
    if (!in_array($value,array(".",".."))) {
        if (is_file(config('dir_badservers') . '/' . $value)) {
            $post_fields = array(
                'chat_id' => config('TELGRAM_CHAT'),
                'parse_mode' => 'html',
                'text' => file_get_contents(config('dir_badservers') . '/' . $value),
            );

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type:multipart/form-data"));
            curl_setopt($ch, CURLOPT_URL, "https://api.telegram.org/bot" . config('TELGRAM_BOT') . "/sendMessage");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);

            curl_exec($ch);
    
            curl_close($ch);
            
            unlink(config('dir_badservers') . '/' . $value);
        }
    }
}

$time_connection_error = intval(trim(DB::GetOne('select `value` from `settings` where `key` = ?', [1 => 'time_connection_error']), '"'));

if (!$time_connection_error) {
    $time_connection_error = 5;
}

$ltime = null;

if (file_exists(__dir__ . "/ltime.txt")) {
    $ltime = file_get_contents(__dir__ . "/ltime.txt");
}

if (!$ltime || $ltime < time() - 60 * $time_connection_error) {
    //file_put_contents(__dir__ . "/ltime.txt", time());
    
    DB::Execute(
        "DELETE FROM `connection_errors` WHERE `error_at` < ?",
        [
            1 => date('Y-m-d H:i:s', time() - 3600 * 24)
        ]
    );

    $n_connection_errors = [];

    foreach (DB::GetAll("SELECT 
            `connection_errors`.`ip`, 
            COUNT(`connection_errors`.`ip`) AS count, 
            GROUP_CONCAT(DISTINCT `connection_errors`.`country` SEPARATOR ',') AS country, 
            SUM(`connection_errors`.`count_errors`) AS count_errors 
        FROM `connection_errors` 
        WHERE `connection_errors`.`notification_at` is null 
          AND `connection_errors`.`error_at` >= ? 
        GROUP BY `connection_errors`.`ip` 
        ORDER BY `count_errors` DESC 
        LIMIT 2000", [
           1 => date('Y-m-d H:i:s', time() - $time_connection_error * 60),
        ]) as $row) {
        if ($row['count_errors'] >= 10) {
            $n_connection_errors[] = '`' . $row['ip'] . '` ' . $row['country'] . ' ' . $row['count_errors'];
        }
    }

    DB::Execute("UPDATE `connection_errors` SET `connection_errors`.`notification_at` = ? 
        WHERE `connection_errors`.`notification_at` is null 
          AND `connection_errors`.`error_at` >= ?", [
           1 => date('Y-m-d H:i:s', time()),
           2 => date('Y-m-d H:i:s', time() - $time_connection_error * 60),
    ]);

    if (!empty($n_connection_errors)) {
        $post_fields = array(
            'chat_id' => config('TELGRAM_CHAT'),
            'parse_mode' => 'MarkDown',
            'text' => "Connection error: \r\n" . implode(" \r\n", $n_connection_errors),
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type:multipart/form-data"));
        curl_setopt($ch, CURLOPT_URL, "https://api.telegram.org/bot" . config('TELGRAM_BOT') . "/sendMessage");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);

        $response = curl_exec($ch);
        curl_close($ch);
    
        //print_r(json_decode($response));
    }
}

$not_available_minutes = trim(DB::GetOne('select `value` from `settings` where `key` = ?', [1 => 'minute_not_available']), '"');

if ($not_available_minutes) {
    $not_available_minutes = intval(json_decode($not_available_minutes));
} else {
    $not_available_minutes = 180;
}

foreach (DB::GetAll('select distinct `servers`.`id`, `servers`.* 
                     from `servers` 
                     where `servers`.`status` = 1 
                       and (
                           (`servers`.`count_connections` = \'9999999\' or `servers`.`not_available_at` > 1) 
                        or (`servers`.`count_connections_ss` = \'9999999\' or `servers`.`not_available_ss_at` > 1)
                       )'
) as $key => $server) {
    $is_send_not_available = false;
    $is_send_not_available_ss = false;

    $not_available = 0;
    $not_available_ss = 0;

    if ($server['count_connections'] == '9999999') {
        if (!$server['not_available_at']) {
            $server['not_available_at'] = date('Y-m-d H:i:s');
        }

        if ($server['not_available_at'] && date_create($server['not_available_at'])->getTimestamp() <
            time() - intval($not_available_minutes) * 60) {
            $not_available = round((time() - date_create($server['not_available_at'])->
                getTimestamp()) / 60);
            $server['not_available_at'] = date('Y-m-d H:i:s');
            $is_send_not_available = true;
        }
    } elseif ($server['not_available_at']) {
        $server['not_available_at'] = null;
    }

    if ($server['count_url_ss']) {
        if ($server['count_connections_ss'] == '9999999') {
            if (!$server['not_available_ss_at']) {
                $server['not_available_ss_at'] = date('Y-m-d H:i:s');
            }

            if ($server['not_available_ss_at'] && date_create($server['not_available_ss_at'])->
                getTimestamp() < time() - intval($not_available_minutes) * 60) {
                $not_available_ss = round((time() - date_create($server['not_available_ss_at'])->
                    getTimestamp()) / 60);
                $server['not_available_ss_at'] = date('Y-m-d H:i:s');
                $is_send_not_available_ss = true;
            }
        } elseif ($server['not_available_ss_at']) {
            $server['not_available_ss_at'] = null;
        }
    }

    if ($is_send_not_available) {
        $post_fields = array(
            'chat_id' => config('TELGRAM_CHAT'),
            'parse_mode' => 'html',
            'text' => $server['ip'] . ($server['name'] ? (' (' . $server['name'] . ')') : '') .
                ' VPN не доступен в течении ' . $not_available . ' минут.',
            );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type:multipart/form-data"));
        curl_setopt($ch, CURLOPT_URL, "https://api.telegram.org/bot" . config('TELGRAM_BOT') .
            "/sendMessage");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);

        curl_exec($ch);

        curl_close($ch);
    }

    if ($is_send_not_available_ss) {
        $post_fields = array(
            'chat_id' => config('TELGRAM_CHAT'),
            'parse_mode' => 'html',
            'text' => $server['ip'] . ($server['name'] ? (' (' . $server['name'] . ')') : '') .
                ' SS не доступен в течении ' . $not_available_ss . ' минут.',
            );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type:multipart/form-data"));
        curl_setopt($ch, CURLOPT_URL, "https://api.telegram.org/bot" . config('TELGRAM_BOT') .
            "/sendMessage");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);

        curl_exec($ch);

        curl_close($ch);
    }
    
    DB::Execute(
        "UPDATE `servers` SET `not_available_at`= ?, `not_available_ss_at` = ? WHERE `id` = ?",
        [
            1 => $server['not_available_at'],
            2 => $server['not_available_ss_at'],
            3 => $server['id'],
        ]
    );
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

class CurlExec
{
    // Стек ссылок
    public $UrlStack = array();

    private $key_stack = 0;

    // Опции по умочанию
    public $OptionsDefault = array(
        CURLOPT_HEADER => 0,
        CURLOPT_RETURNTRANSFER => 1,
        );

    private $_MaxConnect = 3;
    private $_i = 0;
    private $_active = null;
    private $_status = false;
    private $_mh = null;
    private $_mrc = null;
    private $_ch = null;
    private $_CallbackFunction = null;
    private $_HandleStack = array();
    private $_Handle = null;

    private $_microtime_delay;

    private $proxy = [];
    private $i_proxy = 0;
    private $count_proxy = 0;

    private static $is_php_7 = null;

    private $objParseSource = null;

    public function __construct($options_default = null, $function_default = null, $microtime_delay = null,
        $proxy = null)
    {
        if ($options_default && is_array($options_default)) {
            $this->OptionsDefault = $options_default;
        }

        if ($function_default && function_exists($function_default)) {
            $this->_CallbackFunction = $function_default;
        }

        if (!empty($microtime_delay) && $microtime_delay > 0) {
            $this->_microtime_delay = intval($microtime_delay);
        }

        if ($proxy && is_array($proxy)) {
            foreach ($proxy as $key => $value) {
                $this->addProxy($value);
            }
        }

        $this->count_proxy = count($this->proxy);

        if ($this->count_proxy) {
            shuffle($this->proxy);
        }

        if (version_compare(PHP_VERSION, '7.0.0') >= 0) {
            self::$is_php_7 = true;
        } else {
            self::$is_php_7 = false;
        }
    }

    public function setObjParseSource($objParseSource)
    {
        if (is_object($objParseSource) || is_null($objParseSource)) {
            $this->objParseSource = $objParseSource;
        }
    }

    public function addProxy($proxy)
    {
        if (is_array($proxy)) {
            if (isset($proxy['proxy'])) {
                if (preg_match("#\d{1,3}\.\d{1,3}\.\d{1,3}.\d{1,3}(:\d{1,8})?#", $proxy['proxy'])) {
                    $this->proxy[] = $proxy;
                }
            }
        } else {
            if (preg_match("#\d{1,3}\.\d{1,3}\.\d{1,3}.\d{1,3}(:\d{1,8})?#", $proxy)) {
                $this->proxy[] = ['proxy' => trim($proxy)];
            }
        }
    }

    public function AddUrls($urls = array())
    {
        if (is_array($urls) && !isset($urls['url'])) {
            foreach ($urls as $url) {
                self::AddUrl($url);
            }
        } else {
            self::AddUrl($urls);
        }
    }

    // Добавляем url в стек.
    public function AddUrl($url)
    {
        if (is_array($url)) {
            if (isset($url['url']) && self::is_url($url['url'])) {
                if (!isset($url['options']) || !is_array($url['options'])) {
                    $url['options'] = null;
                }

                if (!(isset($url['function']) && is_string($url['function']) && function_exists
                    ($url['function'])) && !(isset($url['function']) && is_array($url['function']) &&
                    isset($url['function'][0]) && isset($url['function'][1]) && method_exists(($url['function'][0] ==
                    '$this' && $this->objParseSource ? $this->objParseSource : $url['function'][0]),
                    $url['function'][1]))) {
                    $url['function'] = null;
                }

                $this->UrlStack[$this->key_stack++] = $url;
            }
        } else {
            if (self::is_url($url)) {
                $this->UrlStack[$this->key_stack++] = array(
                    'url' => $url,
                    'options' => null,
                    'function' => null);
            }
        }
    }

    // Валидация url
    public function is_url($url)
    {
        return true;

        $chars = "a-zA-Z0-9АаБбВвГгҐґДдЕеЄєЭэЖжЗзИиІіЇїЙйКкЛлМмНнОоПпРрСсТтУуФфХхЦцЧчШшЮюЯяЬьЪъёЫы";

        if (preg_match("#((http|https):\/\/|www\.)([" . $chars . "][" . $chars .
            "_-]*(?:.[" . $chars . "][" . $chars . "@\#$%&*().:;_-]*\/{0,1})+):?(d+)?\/?#Diu",
            $url)) {
            return true;
        } else {
            return false;
        }
    }

    // Запрашиваем все страницы паралельными потоками.
    public function ExecuteMulti($max_connect = null)
    {
        if ($max_connect) {
            $this->_MaxConnect = intval($max_connect);
        }

        if (!$this->_mh && !$this->_active && !$this->_ch && !$this->_status) {
            // 1. множественный обработчик
            $this->_mh = curl_multi_init();

            // 2. добавляем множество URL
            $this->fillMultiStack();

            // 3. инициализация выполнения
            $this->MultiExec();

            // 4. основной цикл
            while ($this->_active && $this->_mrc == CURLM_OK) {
                curl_multi_select($this->_mh);

                // 5. если всё прошло успешно
                if (true) { // curl_multi_select($mh) != -1 Не работает на некоторых версия php. Всегда возвращает -1

                    // 6. делаем дело
                    $this->MultiExec();

                    // 7. если есть инфа?
                    if ($mhinfo = curl_multi_info_read($this->_mh)) {
                        // это значит, что запрос завершился

                        // 8. извлекаем инфу
                        $chinfo = curl_getinfo($mhinfo['handle']);

                        $chdata = curl_multi_getcontent($mhinfo['handle']); // get results

                        $function = null;

                        $url = array();

                        $keyHandleStack = null;

                        foreach ($this->_HandleStack as $keyHandle => $valueHandle) {
                            if ($valueHandle['ch'] === $mhinfo['handle']) {
                                $keyHandleStack = $keyHandle;
                            }
                        }

                        if (!is_null($keyHandleStack)) {
                            $key = $this->_HandleStack[$keyHandleStack]['i'];

                            if (isset($this->UrlStack[$key])) {
                                $url = $this->UrlStack[$key];
                                if ($this->UrlStack[$key]['function']) {
                                    $function = $this->UrlStack[$key]['function'];
                                }

                                unset($this->UrlStack[$key]);
                            } else {
                                $url = array();
                            }

                            unset($this->_HandleStack[$keyHandleStack]);
                        }

                        if (!$function && $this->_CallbackFunction) {
                            $function = $this->_CallbackFunction;
                        }

                        if ($function && is_string($function) && function_exists($function)) {
                            $function(array_replace($url, array('info' => $chinfo, 'data' => $chdata)));
                        } elseif (is_array($function)) {
                            if ($function[0] == '$this') {
                                $this->objParseSource->{$function[1]}(array_replace($url, array('info' => $chinfo,
                                        'data' => $chdata)));
                            } else {
                                if (self::$is_php_7) {
                                    $callback = $function[0] . '::' . $function[1];
                                    $callback(array_replace($url, array('info' => $chinfo, 'data' => $chdata)));
                                } else {
                                    $function[0]::$function[1](array_replace($url, array('info' => $chinfo, 'data' =>
                                            $chdata)));
                                }
                            }
                        }

                        // 12. чистим за собой
                        curl_multi_remove_handle($this->_mh, $mhinfo['handle']); // в случае зацикливания, закомментируйте данный вызов
                        curl_close($mhinfo['handle']);

                        // 13. добавляем новый url и продолжаем работу
                        if ($this->fillMultiStack() > 0) {
                            $this->MultiExec();
                        }
                    }
                }
            }

            // 14. завершение
            self::StopMultiCurl();
        }
    }

    private function fillMultiStack()
    {
        $count = 0;

        for ($i = 0; $i < $this->_MaxConnect; $i++) {
            if (isRun() && count($this->_HandleStack) < $this->_MaxConnect) {
                if ($this->AddUrlToMultiHandle()) {
                    $count++;
                }
            } else {
                break;
            }
        }

        return $count;
    }

    // Запуск дескрипторов стека
    private function MultiExec()
    {
        if ($this->_mh) {
            do {
                $this->_mrc = curl_multi_exec($this->_mh, $this->_active);
            } while ($this->_mrc == CURLM_CALL_MULTI_PERFORM);
        } else {
            self::StopMultiCurl();
        }

    }

    // Добавляем ссылку на выполнение
    private function AddUrlToMultiHandle()
    {
        // если у нас есть ещё url, которые нужно достать
        if ($this->_mh && isset($this->UrlStack[$this->_i]) && isset($this->UrlStack[$this->
            _i]['url'])) {
            // новый curl обработчик
            $ch = curl_init();

            $this->_HandleStack[] = array('i' => $this->_i, 'ch' => $ch);

            $options = null;

            if (isset($this->UrlStack[$this->_i]['options'])) {
                $options = array_replace($this->OptionsDefault, $this->UrlStack[$this->_i]['options']);
            } else {
                $options = $this->OptionsDefault;
            }

            if ($this->count_proxy) {
                if ($this->i_proxy >= $this->count_proxy) {
                    $this->i_proxy = 0;
                }

                if (isset($this->proxy[$this->i_proxy])) {
                    $options[CURLOPT_PROXY] = $this->proxy[$this->i_proxy]['proxy'];

                    if (isset($this->proxy[$this->i_proxy]['userpwd'])) {
                        $options[CURLOPT_PROXYUSERPWD] = $this->proxy[$this->i_proxy]['userpwd'];
                    }

                    if (isset($this->proxy[$this->i_proxy]['useragent'])) {
                        $options[CURLOPT_USERAGENT] = $this->proxy[$this->i_proxy]['useragent'];
                    }

                    if (isset($this->proxy[$this->i_proxy]['type'])) {
                        switch ($this->proxy[$this->i_proxy]['type']) {
                            case 'SOCKS5':
                                $options[CURLOPT_PROXYTYPE] = CURLPROXY_SOCKS5;
                                break;
                            case 'IPv6':
                                break;
                        }
                    }
                }

                $this->i_proxy++;
            }

            $options[CURLOPT_URL] = $this->UrlStack[$this->_i]['url'];
            $options[CURLOPT_RETURNTRANSFER] = 1;

            curl_setopt_array($ch, $options);

            curl_multi_add_handle($this->_mh, $ch);

            // переходим на следующий url
            $this->_i++;

            return true;
        } else {
            // добавление новых URL завершено
            return false;
        }
    }

    // Очищаем стек
    public function ClearStack()
    {
        $this->UrlStack = array();
        $this->key_stack = 0;
        $this->_HandleStack = array();
        $this->_i = 0;
        $this->_active = null;
        $this->_mh = null;
        $this->_mrc;

        $this->_ch = null;
        $this->_Handle = null;
        $this->_status = false;
    }

    // Закрывает набор cURL дескрипторов
    public function StopMultiCurl()
    {
        if ($this->_mh) {
            foreach ($this->_HandleStack as $key => $value) {
                curl_multi_remove_handle($this->_mh, $value['ch']); // в случае зацикливания, закомментируйте данный вызов
                curl_close($value['ch']);
            }

            curl_multi_close($this->_mh);
        }

        $this->_active = false;
        $this->_mh = null;
        $this->_mrc = null;
        $this->ClearStack();
    }

    // Запрашиваем страницы последовательно
    public function Execute($count_connect = 1)
    {
        if ($count_connect > 1) {
            $this->ExecuteMulti($count_connect);
            return;
        }

        if (!$this->_ch && !$this->_status && !$this->_mh && !$this->_active) {
            $this->_status = true;

            while (isRun() && $this->_status && isset($this->UrlStack[$this->_i]) && isset($this->
                UrlStack[$this->_i]['url'])) {
                if ($this->_status) {
                    if ($this->_microtime_delay) {
                        usleep($this->_microtime_delay);
                    }

                    $this->_ch = curl_init();

                    $this->_Handle = array('i' => $this->_i, 'ch' => $this->_ch);

                    $options = null;

                    if (isset($this->UrlStack[$this->_i]['options'])) {
                        $options = array_replace($this->OptionsDefault, $this->UrlStack[$this->_i]['options']);
                    } else {
                        $options = $this->OptionsDefault;
                    }

                    if ($this->count_proxy) {
                        if ($this->i_proxy >= $this->count_proxy) {
                            $this->i_proxy = 0;
                        }

                        if (isset($this->proxy[$this->i_proxy])) {
                            $options[CURLOPT_PROXY] = $this->proxy[$this->i_proxy]['proxy'];

                            if (isset($this->proxy[$this->i_proxy]['userpwd'])) {
                                $options[CURLOPT_PROXYUSERPWD] = $this->proxy[$this->i_proxy]['userpwd'];
                            }

                            if (isset($this->proxy[$this->i_proxy]['useragent'])) {
                                $options[CURLOPT_USERAGENT] = $this->proxy[$this->i_proxy]['useragent'];
                            }

                            if (isset($this->proxy[$this->i_proxy]['type'])) {
                                switch ($this->proxy[$this->i_proxy]['type']) {
                                    case 'SOCKS5':
                                        $options[CURLOPT_PROXYTYPE] = CURLPROXY_SOCKS5;
                                        break;
                                    case 'IPv6':
                                        break;
                                }
                            }
                        }

                        $this->i_proxy++;
                    }

                    $options[CURLOPT_URL] = $this->UrlStack[$this->_i]['url'];
                    $options[CURLOPT_RETURNTRANSFER] = 1;

                    curl_setopt_array($this->_ch, $options);

                    curl_exec($this->_ch);

                    $chinfo = curl_getinfo($this->_ch);

                    $chdata = curl_multi_getcontent($this->_ch); // get results

                    $function = null;

                    if ($this->UrlStack[$this->_i]['function']) {
                        $function = $this->UrlStack[$this->_i]['function'];
                    }

                    if (!$function && $this->_CallbackFunction) {
                        $function = $this->_CallbackFunction;
                    }

                    if ($function && is_string($function) && function_exists($function)) {
                        $function(array_replace($this->UrlStack[$this->_i], array('info' => $chinfo,
                                'data' => $chdata)));
                    } elseif (is_array($function)) {
                        if ($function[0] == '$this') {
                            $this->objParseSource->{$function[1]}(array_replace($this->UrlStack[$this->_i],
                                array('info' => $chinfo, 'data' => $chdata)));
                        } else {
                            if (self::$is_php_7) {
                                $callback = $function[0] . '::' . $function[1];
                                $callback(array_replace($this->UrlStack[$this->_i], array('info' => $chinfo,
                                        'data' => $chdata)));
                            } else {
                                $function[0]::$function[1](array_replace($this->UrlStack[$this->_i], array('info' =>
                                        $chinfo, 'data' => $chdata)));
                            }
                        }
                    }

                    curl_close($this->_ch);

                    unset($this->UrlStack[$this->_i]);

                    $this->_ch = null;

                    $this->_Handle = null;

                    $this->_i++;
                } else {
                    break;
                }
            }

            self::StopCurl();
        }
    }

    // Останавливаем выполнение curl
    public function StopCurl()
    {
        if ($this->_ch) {
            curl_close($this->_ch);
        }

        $this->_ch = null;
        $this->_Handle = null;
        $this->_status = false;
        $this->ClearStack();
    }

    // Останавливаем выполнение
    public function Stop()
    {
        $this->StopCurl();
        $this->StopMultiCurl();
        $this->ClearStack();
    }

    // Выполняется ли пороцесс
    public function isActive()
    {
        if ($this->_active) {
            return true;
        }

        if ($this->_status) {
            return true;
        }

        return false;
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
