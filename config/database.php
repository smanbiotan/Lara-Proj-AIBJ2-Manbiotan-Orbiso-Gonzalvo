<?php
return [
'default' => env('DB_CONNECTION', 'mysql'),
'connections' => [
'sqlite' => ['driver'=>'sqlite','url'=>env('DB_URL'),'database'=>env('DB_DATABASE', database_path('database.sqlite')),'prefix'=>'','foreign_key_constraints'=>env('DB_FOREIGN_KEYS', true),'busy_timeout'=>null,'journal_mode'=>null,'synchronous'=>null,'transaction_mode'=>'DEFERRED'],
'mysql' => ['driver'=>'mysql','url'=>env('DB_URL'),'host'=>env('DB_HOST','127.0.0.1'),'port'=>env('DB_PORT','3306'),'database'=>env('DB_DATABASE','blush_bloom_salon'),'username'=>env('DB_USERNAME','root'),'password'=>env('DB_PASSWORD',''),'unix_socket'=>env('DB_SOCKET',''),'charset'=>env('DB_CHARSET','utf8mb4'),'collation'=>env('DB_COLLATION','utf8mb4_unicode_ci'),'prefix'=>'','prefix_indexes'=>true,'strict'=>true,'engine'=>null,'options'=>extension_loaded('pdo_mysql') && env('MYSQL_ATTR_SSL_CA') ? [PDO::MYSQL_ATTR_SSL_CA=>env('MYSQL_ATTR_SSL_CA'),PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT=>(bool) env('MYSQL_SSL_VERIFY', true)] : []],
// PostgreSQL (e.g. Supabase). Use the Supabase "Session pooler" host on port 5432 (IPv4) with sslmode=require.
'pgsql' => ['driver'=>'pgsql','url'=>env('DB_URL'),'host'=>env('DB_HOST','127.0.0.1'),'port'=>env('DB_PORT','5432'),'database'=>env('DB_DATABASE','postgres'),'username'=>env('DB_USERNAME','postgres'),'password'=>env('DB_PASSWORD',''),'charset'=>env('DB_CHARSET','utf8'),'prefix'=>'','prefix_indexes'=>true,'search_path'=>'public','sslmode'=>env('DB_SSLMODE','prefer')],
],
'migrations' => ['table'=>'migrations','update_date_on_publish'=>true],
];
