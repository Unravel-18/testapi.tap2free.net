<?php

return [
    "mysql" => [// По умолчанию
        "host" => "127.0.0.1",
        "port" => "3306",
        "database" => "testapi",
        "username" => "testapi",
        "password" => "uI8eR1pK6q",
        
        "ip_save" => true, // Для сохранения IP при контроле активации токена
        "badip_save" => true, // Для сохранения IP в badip при активации токена
    ],
    "mysql_prokeys" => [// Для хранения ключей pro
        "host" => "127.0.0.1",
        "port" => "3306",
        "database" => "testapi",
        "username" => "testapi",
        "password" => "uI8eR1pK6q",
    ],
    "mysql_google_tokens" => [// Для хранения гугл токена
        "host" => "127.0.0.1",
        "port" => "3306",
        "database" => "testapitoken",
        "username" => "testapitoken",
        "password" => "zJ1oY0nE4v",
    ],
    "mysql_keys" => [// Для хранения ключей
        "host" => "127.0.0.1",
        "port" => "3306",
        "database" => "testapi",
        "username" => "testapi",
        "password" => "uI8eR1pK6q",
        
        "token_save" => true, // Для сохранения данных токена
    ],
    "mysql_3" => [
        "host" => "127.0.0.1",
        "port" => "3306",
        "database" => "testapi",
        "username" => "testapi",
        "password" => "uI8eR1pK6q",
        
        "badip_save" => true, // Для сохранения IP в badip при активации токена
        "ip_save" => true, // Для сохранения IP при контроле активации токена
    ],
];
