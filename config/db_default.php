<?php

$dbOptions = [
  'driver'   => 'pdo_mysql',
  'dbname' => 'dbname',       // The name of the database to connect to.
  'host' => 'localhost',      // The host of the database to connect to. Defaults to localhost.
  'user' => 'user',           // The user of the database to connect to. Defaults to root.
  'password' => 'password',   // The password of the database to connect to.
  'charset' => 'UTF8'        // Specifies the charset used when connecting to the database.
];

return $dbOptions;