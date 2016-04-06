<?php
/**
 * Created by PhpStorm.
 * User: ClownFish
 * Date: 16-4-1
 * Time: 下午5:12
 */


function loadClass($class){

    $dirs = array("lib","include");

    $class = str_replace("\\","/",$class);
    $file = $class.".class.php";

    foreach($dirs as $dir){
        if(file_exists($dir.DS.$file)){
            require $dir.DS.$file;
            break;
        }
    }
}
spl_autoload_register("loadClass");