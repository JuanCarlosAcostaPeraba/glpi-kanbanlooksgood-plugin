<?php

/**
 * -------------------------------------------------------------------------
 * Kanban Looks Good plugin for GLPI
 * -------------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of Kanban Looks Good.
 *
 * Kanban Looks Good is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * Kanban Looks Good is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Kanban Looks Good. If not, see <http://www.gnu.org/licenses/>.
 * -------------------------------------------------------------------------
 *
 * @package   KanbanLooksGood
 * @author    Juan Carlos Acosta Perabá
 * @copyright Copyright (C) 2024-2025 by Juan Carlos Acosta Perabá
 * @license   GPLv3+ http://www.gnu.org/licenses/gpl.html
 * @link      https://github.com/JuanCarlosAcostaPeraba/glpi-kanbanlooksgood-plugin
 * -------------------------------------------------------------------------
 */

use Glpi\Plugin\Hooks;

/**
 * Plugin version
 */
define('PLUGIN_KANBANLOOKSGOOD_VERSION', '1.3.0');

/**
 * Minimum GLPI version required (inclusive)
 */
define('PLUGIN_KANBANLOOKSGOOD_MIN_GLPI', '10.0.0');

/**
 * Maximum GLPI version supported (exclusive)
 */
define('PLUGIN_KANBANLOOKSGOOD_MAX_GLPI', '11.0.99');

/**
 * Initialize plugin hooks
 *
 * This function is called by GLPI to register the plugin's hooks and resources.
 * It sets up the Kanban metadata hook, registers JavaScript/CSS files, and
 * configures the plugin's settings page.
 *
 * @global array $PLUGIN_HOOKS GLPI's plugin hooks registry
 *
 * @return void
 */
function plugin_init_kanbanlooksgood()
{
    /**
     * @var array $PLUGIN_HOOKS
     */
    global $PLUGIN_HOOKS;

    // Mark plugin as CSRF compliant
    $PLUGIN_HOOKS['csrf_compliant']['kanbanlooksgood'] = true;

    if (Plugin::isPluginActive('kanbanlooksgood')) {

        // Verify and upgrade database structure if needed
        plugin_kanbanlooksgood_check_and_upgrade();

        // Register Kanban item metadata hook (backend processing)
        $PLUGIN_HOOKS[Hooks::KANBAN_ITEM_METADATA]['kanbanlooksgood'] = [
            'PluginKanbanlooksgoodHook',
            'kanbanItemMetadata'
        ];

        // Register frontend JavaScript files
        $PLUGIN_HOOKS['add_javascript']['kanbanlooksgood'][] = 'js/kanban.js';
        $PLUGIN_HOOKS['add_javascript']['kanbanlooksgood'][] = 'js/config_inject.js';

        // Register frontend CSS files
        $PLUGIN_HOOKS['add_css']['kanbanlooksgood'][] = 'css/kanban.css';

        // Register configuration page
        $PLUGIN_HOOKS['config_page']['kanbanlooksgood'] = 'front/config.form.php';
    }
}

/**
 * Get plugin version information
 *
 * This function returns the plugin's metadata including name, version,
 * author, license, and GLPI version requirements. Called by GLPI to
 * display plugin information in the administration interface.
 *
 * @return array Plugin information array with keys:
 *               - name: Plugin display name
 *               - version: Current plugin version
 *               - author: Plugin author with contact link
 *               - license: Software license
 *               - homepage: Project homepage URL
 *               - requirements: Array of version requirements
 */
function plugin_version_kanbanlooksgood()
{
    return [
        'name'         => 'Kanban Looks Good',
        'version'      => PLUGIN_KANBANLOOKSGOOD_VERSION,
        'author'       => '<a href="mailto:juancarlos.ap.dev@gmail.com">Juan Carlos Acosta Perabá</a>',
        'license'      => 'GPLv3+',
        'homepage'     => 'https://github.com/juancarlosacostaperaba/kanbanlooksgood',
        'requirements' => [
            'glpi' => [
                'min' => PLUGIN_KANBANLOOKSGOOD_MIN_GLPI,
                'max' => PLUGIN_KANBANLOOKSGOOD_MAX_GLPI,
            ]
        ]
    ];
}

/**
 * Check and upgrade database structure if needed
 *
 * This function is called on every page load when the plugin is active.
 * It ensures the configuration table exists and has the correct structure,
 * creating it with default values if necessary.
 *
 * @global DBmysql $DB GLPI database instance
 *
 * @return void
 */
function plugin_kanbanlooksgood_check_and_upgrade()
{
    global $DB;

    // Check if configuration table exists
    if (!$DB->tableExists('glpi_plugin_kanbanlooksgood_configs')) {
        // Create configuration table
        $query = "CREATE TABLE IF NOT EXISTS `glpi_plugin_kanbanlooksgood_configs` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `show_priority` tinyint(1) NOT NULL DEFAULT '1',
            `show_duration` tinyint(1) NOT NULL DEFAULT '1',
            `work_hours_per_day` int(11) NOT NULL DEFAULT '7',
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        $DB->query($query);

        // Insert default configuration
        $DB->insert(
            'glpi_plugin_kanbanlooksgood_configs',
            [
                'show_priority' => 1,
                'show_duration' => 1,
                'work_hours_per_day' => 7
            ]
        );
    }
}

/**
 * Check prerequisites before installing the plugin
 *
 * Verifies that the GLPI version meets the minimum requirements
 * before allowing plugin installation.
 *
 * @return bool True if prerequisites are met, false otherwise
 */
function plugin_kanbanlooksgood_check_prerequisites()
{
    if (version_compare(GLPI_VERSION, PLUGIN_KANBANLOOKSGOOD_MIN_GLPI, 'lt')) {
        echo __('This plugin requires GLPI >= ') . PLUGIN_KANBANLOOKSGOOD_MIN_GLPI;
        return false;
    }
    return true;
}

/**
 * Check configuration process status
 *
 * This function is called during plugin configuration to verify
 * that the configuration process was successful. Currently returns
 * true as no special configuration checks are needed.
 *
 * @param bool $verbose Whether to output verbose messages
 *
 * @return bool True if configuration is valid, false otherwise
 */
function plugin_kanbanlooksgood_check_config($verbose = false)
{
    return true;
}

/**
 * Plugin installation process
 *
 * Creates the necessary database tables and inserts default configuration
 * values when the plugin is installed for the first time.
 *
 * @global DBmysql $DB GLPI database instance
 *
 * @return bool True if installation succeeded, false otherwise
 */
function plugin_kanbanlooksgood_install()
{
    global $DB;

    // Create configuration table
    $query = "CREATE TABLE IF NOT EXISTS `glpi_plugin_kanbanlooksgood_configs` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `show_priority` tinyint(1) NOT NULL DEFAULT '1',
        `show_duration` tinyint(1) NOT NULL DEFAULT '1',
        `work_hours_per_day` int(11) NOT NULL DEFAULT '7',
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (!$DB->query($query)) {
        return false;
    }

    // Insert default configuration if none exists
    $iterator = $DB->request([
        'FROM' => 'glpi_plugin_kanbanlooksgood_configs',
        'LIMIT' => 1
    ]);

    if (count($iterator) === 0) {
        $DB->insert(
            'glpi_plugin_kanbanlooksgood_configs',
            [
                'show_priority' => 1,
                'show_duration' => 1,
                'work_hours_per_day' => 7
            ]
        );
    }

    return true;
}

/**
 * Plugin uninstallation process
 *
 * Removes all database tables created by the plugin, cleaning up
 * all plugin data from the GLPI database.
 *
 * @global DBmysql $DB GLPI database instance
 *
 * @return bool True if uninstallation succeeded, false otherwise
 */
function plugin_kanbanlooksgood_uninstall()
{
    global $DB;

    // Remove all plugin tables
    $tables = [
        'glpi_plugin_kanbanlooksgood_configs'
    ];

    foreach ($tables as $table) {
        $DB->query("DROP TABLE IF EXISTS `$table`");
    }

    return true;
}
