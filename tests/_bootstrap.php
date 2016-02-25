<?php
// This is global bootstrap for autoloading

// load configs from env file
Dotenv::load(__DIR__ . '/..');

// register mocker
$kernel = \AspectMock\Kernel::getInstance();
$kernel->init([
    'debug' => true,
    'includePaths' => [
        __DIR__.'/../src',
    ],
]);