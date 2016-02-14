<?php

require "app/autoloader.php";

$loader = new Autoloader();
$loader->addNamespace('app', __DIR__ . '/app');
$loader->register();

