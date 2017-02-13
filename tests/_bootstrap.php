<?php
// This is global bootstrap for autoloading

// register mocker
$kernel = \AspectMock\Kernel::getInstance();
$kernel->init([
    'debug' => true,
    'includePaths' => [
        __DIR__.'/../src',
    ],
]);