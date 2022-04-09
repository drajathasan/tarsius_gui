<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-04-07 08:52:45
 * @modify date 2022-04-09 10:05:08
 * @license GPLv3
 * @desc [description]
 */

use SLiMS\DB;

class CreateDummy extends \SLiMS\Migration\Migration
{
    public function up()
    {
        DB::getInstance()
                ->query("CREATE TABLE IF NOT EXISTS `dummy_plugin` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `kolom1` varchar(20) COLLATE utf8mb4_bin DEFAULT NULL,
                    `kolom2` varchar(20) COLLATE utf8mb4_bin DEFAULT NULL,
                    `kolom3` varchar(20) COLLATE utf8mb4_bin DEFAULT NULL,
                    PRIMARY KEY (`id`)
                  ) ENGINE=MyISAM;");

        DB::getInstance()
                ->query("INSERT INTO `dummy_plugin` (`kolom1`, `kolom2`, `kolom3`) VALUES ('Test',	'Test',	'Test');");
    }

    public function down()
    {
        
    }
}