<?php

return [
    "mysql" => [// По умолчанию
        "host" => env('DB_HOST'),
        "port" => env('DB_PORT'),
        "database" => env('DB_DATABASE'),
        "username" => env('DB_USERNAME'),
        "password" => env('DB_PASSWORD'),
        
        "ip_save" => true, // Для сохранения IP при контроле активации токена
        "badip_save" => true, // Для сохранения IP в badip при активации токена
    ],
    "mysql_prokeys" => [// Для хранения ключей pro
        "host" => env('DB_HOST'),
        "port" => env('DB_PORT'),
        "database" => env('DB_DATABASE'),
        "username" => env('DB_USERNAME'),
        "password" => env('DB_PASSWORD'),
    ],
    "mysql_google_tokens" => [// Для хранения гугл токена
        "host" => env('DB_HOST_2'),
        "port" => env('DB_PORT_2'),
        "database" => env('DB_DATABASE_2'),
        "username" => env('DB_USERNAME_2'),
        "password" => env('DB_PASSWORD_2'),
    ],
    "mysql_keys" => [// Для хранения ключей
        "host" => env('DB_HOST'),
        "port" => env('DB_PORT'),
        "database" => env('DB_DATABASE'),
        "username" => env('DB_USERNAME'),
        "password" => env('DB_PASSWORD'),
        
        "token_save" => true, // Для сохранения данных токена
    ],
    "mysql_3" => [
        "host" => env('DB_HOST'),
        "port" => env('DB_PORT'),
        "database" => env('DB_DATABASE'),
        "username" => env('DB_USERNAME'),
        "password" => env('DB_PASSWORD'),
        
        "badip_save" => true, // Для сохранения IP в badip при активации токена
        "ip_save" => true, // Для сохранения IP при контроле активации токена
    ],
];
