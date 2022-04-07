<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-04-07 09:01:46
 * @modify date 2022-04-07 09:19:28
 * @license GPLv3
 * @desc [description]
 */

function tarsiusAutoload()
{
    if (!class_exists('\Zein\Tarsius\Modules\Plugin') && file_exists($inDirAutoload = __DIR__ . '/vendor/autoload.php'))
    {
        // Load composer autoload
        include $inDirAutoload;
    }
    // Include local autoload
    include __DIR__ . '/lib/autoload.php';
}

if (!function_exists('dd'))
{
    function dd($char)
    {
        echo '<pre>';
        var_dump($char);
        echo '</pre>';
        exit;
    }
}