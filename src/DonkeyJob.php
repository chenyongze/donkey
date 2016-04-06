<?php
/**
 * Created by PhpStorm.
 * User: ClownFish
 * Date: 16-4-1
 * Time: 下午5:13
 */

date_default_timezone_set('Asia/Shanghai');
define('APP_DEBUG', true);
define('DS', DIRECTORY_SEPARATOR);
define('ROOT_PATH', realpath(dirname(__FILE__)) . DS);

include ROOT_PATH."include".DS."common.php";

$donkey = new \Donkey\Donkey();
