<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-04-07 08:52:45
 * @modify date 2022-04-07 12:13:08
 * @license GPLv3
 * @desc [description]
 */

use SLiMS\DB;

class CreateTable extends \SLiMS\Migration\Migration
{
    public function up()
    {
        DB::getInstance()
                ->query("CREATE TABLE IF NOT EXISTS `tarsius` (
                    `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    `name` varchar(100) NULL,
                    `type` varchar(10) NULL,
                    `attribute` json NULL,
                    `created_at` datetime NULL,
                    `updated_at` datetime NULL
                  ) ENGINE='MyISAM';");
    }

    public function down()
    {
        
    }
}