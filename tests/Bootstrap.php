<?php
date_default_timezone_set('UTC');
include 'init_autoloader.php';
$loader->add('RindowTest\\Web\\Form\\Mapping\\', __DIR__);

if(!class_exists('PHPUnit\Framework\TestCase')) {
    include __DIR__.'/travis/patch55.php';
}
