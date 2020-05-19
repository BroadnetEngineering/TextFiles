<?php
spl_autoload_register(function ($class_name) {
    if(!defined("BASE_PATH")) {
        throw new \Exception("BASE_PATH must be defined for auto loading");
    }
    
    $strFileName = str_replace("\\", "/", $class_name);

    include(BASE_PATH . "/" . $strFileName . '.php');
});
