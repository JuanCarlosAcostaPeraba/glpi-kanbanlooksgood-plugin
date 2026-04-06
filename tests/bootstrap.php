<?php

/**
 * Bootstrap file for KanbanLooksGood plugin tests
 *
 * Provides minimal stubs for GLPI classes and functions so that
 * pure logic in the plugin can be tested without a full GLPI instance.
 */

// Define GLPI_ROOT to satisfy the guard in plugin files
if (!defined('GLPI_ROOT')) {
    define('GLPI_ROOT', __DIR__ . '/..');
}

// Stub GLPI translation function
if (!function_exists('__')) {
    function __($string, $domain = 'glpi')
    {
        return $string;
    }
}

// Stub CommonDBTM base class
if (!class_exists('CommonDBTM')) {
    class CommonDBTM
    {
        public static $rightname = '';
    }
}

// Stub CommonITILObject for priority methods
if (!class_exists('CommonITILObject')) {
    class CommonITILObject
    {
        public static function getPriorityColor($priority)
        {
            $colors = [
                1 => '#5cb85c',
                2 => '#5bc0de',
                3 => '#f0ad4e',
                4 => '#ff9800',
                5 => '#d9534f',
                6 => '#c9302c',
            ];
            return $colors[$priority] ?? '';
        }

        public static function getPriorityName($priority)
        {
            $names = [
                1 => 'Very low',
                2 => 'Low',
                3 => 'Medium',
                4 => 'High',
                5 => 'Very high',
                6 => 'Major',
            ];
            return $names[$priority] ?? '';
        }
    }
}

// Stub PluginKanbanlooksgoodConfig for formatPlannedDuration fallback
if (!class_exists('PluginKanbanlooksgoodConfig')) {
    require_once __DIR__ . '/../inc/config.class.php';
}

// Load the hook class
require_once __DIR__ . '/../inc/hook.class.php';
