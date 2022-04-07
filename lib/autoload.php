<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-02-03 14:48:26
 * @modify date 2022-04-07 14:02:30
 * @license GPLv3
 * @desc [description]
 */

spl_autoload_register(function($class) {
    $class = str_replace('TarsiusGui\\', '', $class);
    $paths = explode('\\', $class);
    $fixPath = [];
    foreach ($paths as $index => $path) {
        if ($index === 0)
        {
            $fixPath[] = ucfirst($path);
        }
        else
        {
            $fixPath[] = $path;
        }
    }

    $truePath = __DIR__ . DS . implode(DS, $fixPath) . '.php';

    if (file_exists($truePath))
    {
        include $truePath;
    }
});