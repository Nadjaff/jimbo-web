<?php
require 'config.php';
return [
  'paths' => [
    'migrations' => 'migrations'
  ],
  'migration_base_class' => '\Api\Migration\Migration',
  'environments' => [
    'default_migration_table' => 'phinxlog',
    'default_database' => 'jimbo',
    'jimbo' => [
      'adapter' => 'mysql',
      'host' => DB_HOST,
      'name' => DB_NAME,
      'user' => DB_USER,
      'pass' => DB_PASSWORD,
      'port' => DB_PORT
    ]
  ]
];