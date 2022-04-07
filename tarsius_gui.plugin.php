<?php
/**
 * Plugin Name: Tarsius GUI
 * Plugin URI: https://github.com/drajathasan/tarsius-gui
 * Description: Plugin for created SLiMS base plugin powered by zein/tarsius
 * Version: 1.0.0
 * Author: Drajat Hasan
 * Author URI: https://github.com/drajathasan/
 */

// get plugin instance
$plugin = \SLiMS\Plugins::getInstance();

// registering menus or hook
$plugin->registerMenu("system", "Tarsius", __DIR__ . "/pages/index.php");