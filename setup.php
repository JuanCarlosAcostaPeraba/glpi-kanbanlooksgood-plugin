<?php

/**
 * -------------------------------------------------------------------------
 * Kanban Looks Good plugin for GLPI
 * -------------------------------------------------------------------------
 */

use Glpi\Plugin\Hooks;

define('PLUGIN_KANBANLOOKSGOOD_VERSION', '1.0.0');

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

        // 🔹 Hook de metadata del Kanban (BACKEND)
        $PLUGIN_HOOKS[Hooks::KANBAN_ITEM_METADATA]['kanbanlooksgood'] = [
            'PluginKanbanlooksgoodHook',
            'kanbanItemMetadata'
        ];

        // 🔹 JS + CSS del plugin (FRONTEND)
        $PLUGIN_HOOKS['add_javascript']['kanbanlooksgood'][] = 'js/kanban.js';
        $PLUGIN_HOOKS['add_css']['kanbanlooksgood'][]        = 'css/kanban.css';
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
        'license'      => 'GPLv2+',
        'homepage'     => 'https://github.com/JuanCarlosAcostaPeraba/kanbanlooksgood',
        'requirements' => [
            'glpi' => [
                'min' => PLUGIN_KANBANLOOKSGOOD_MIN_GLPI,
                'max' => PLUGIN_KANBANLOOKSGOOD_MAX_GLPI,
            ]
        ]
    ];
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
    return true;
}
