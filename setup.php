<?php

/**
 * -------------------------------------------------------------------------
 * Kanban Looks Good plugin for GLPI
 * -------------------------------------------------------------------------
 */

use Glpi\Plugin\Hooks;

define('PLUGIN_KANBANLOOKSGOOD_VERSION', '1.2.1');

// Minimal GLPI version, inclusive
define("PLUGIN_KANBANLOOKSGOOD_MIN_GLPI", "10.0.0");
// Maximum GLPI version, exclusive
define("PLUGIN_KANBANLOOKSGOOD_MAX_GLPI", "10.0.99");

/**
 * Init hooks of the plugin.
 * REQUIRED
 *
 * @return void
 */
function plugin_init_kanbanlooksgood()
{
    /**
     * @var array $PLUGIN_HOOKS
     */
    global $PLUGIN_HOOKS;

    $PLUGIN_HOOKS['csrf_compliant']['kanbanlooksgood'] = true;

    if (Plugin::isPluginActive('kanbanlooksgood')) {

        // 🔹 Verificar y actualizar estructura de base de datos si es necesario
        plugin_kanbanlooksgood_check_and_upgrade();

        // 🔹 Hook de metadata del Kanban (BACKEND)
        $PLUGIN_HOOKS[Hooks::KANBAN_ITEM_METADATA]['kanbanlooksgood'] = [
            'PluginKanbanlooksgoodHook',
            'kanbanItemMetadata'
        ];

        // 🔹 JS + CSS del plugin (FRONTEND)
        $PLUGIN_HOOKS['add_javascript']['kanbanlooksgood'][] = 'js/kanban.js';
        $PLUGIN_HOOKS['add_css']['kanbanlooksgood'][]        = 'css/kanban.css';

        // 🔹 Menú de configuración
        $PLUGIN_HOOKS['config_page']['kanbanlooksgood'] = 'front/config.form.php';

        // 🔹 Añadir configuración al head para JavaScript
        $PLUGIN_HOOKS['add_javascript']['kanbanlooksgood'][] = 'js/config_inject.js';
    }
}

/**
 * Get the name and the version of the plugin
 * REQUIRED
 *
 * @return array
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
 * This runs on every page load when plugin is active
 *
 * @return void
 */
function plugin_kanbanlooksgood_check_and_upgrade()
{
    global $DB;

    // Verificar si la tabla de configuración existe
    if (!$DB->tableExists('glpi_plugin_kanbanlooksgood_configs')) {
        // Crear tabla de configuración
        $query = "CREATE TABLE IF NOT EXISTS `glpi_plugin_kanbanlooksgood_configs` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `show_priority` tinyint(1) NOT NULL DEFAULT '1',
            `show_duration` tinyint(1) NOT NULL DEFAULT '1',
            `work_hours_per_day` int(11) NOT NULL DEFAULT '7',
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        $DB->query($query);

        // Insertar configuración por defecto
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
 * Check prerequisites before installing
 *
 * @return boolean
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
 * Check configuration before installing
 *
 * @param boolean $verbose
 * @return boolean
 */
function plugin_kanbanlooksgood_check_config($verbose = false)
{
    return true;
}

/**
 * Install hook
 * REQUIRED BY GLPI
 *
 * @return bool
 */
function plugin_kanbanlooksgood_install()
{
    global $DB;

    // Crear tabla de configuración
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

    // Insertar configuración por defecto si no existe
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
 * Uninstall hook
 * REQUIRED BY GLPI
 *
 * @return bool
 */
function plugin_kanbanlooksgood_uninstall()
{
    global $DB;

    // Eliminar tabla de configuración
    $tables = [
        'glpi_plugin_kanbanlooksgood_configs'
    ];

    foreach ($tables as $table) {
        $DB->query("DROP TABLE IF EXISTS `$table`");
    }

    return true;
}
