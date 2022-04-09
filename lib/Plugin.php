<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-04-07 11:26:19
 * @modify date 2022-04-09 09:14:06
 * @license GPLv3
 * @desc [description]
 */

namespace TarsiusGui;

use Zein\Tarsius\Modules\Plugin as ZeinPlugin;

class Plugin extends ZeinPlugin
{
    public function option(string $key)
    {
        return $_POST[$key]??null;
    }
}