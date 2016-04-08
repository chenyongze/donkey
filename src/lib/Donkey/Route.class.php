<?php
/**
 * Created by PhpStorm.
 * User: ClownFish
 * Date: 16-4-7
 * Time: 下午3:56
 */

namespace Donkey;


class Route
{

    public function run($request)
    {
        $path_info = $request->server["path_info"];
        $path_info = trim($path_info,"/");
        $params = explode("/",$path_info);


        print_r($request);
    }

}