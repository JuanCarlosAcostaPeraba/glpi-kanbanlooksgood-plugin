<?php

/**
 * -------------------------------------------------------------------------
 * Kanban Looks Good plugin for GLPI
 * -------------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of KanbanLooksGood.
 *
 * KanbanLooksGood is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * KanbanLooksGood is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with KanbanLooksGood. If not, see <http://www.gnu.org/licenses/>.
 * -------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly");
}

/**
 * Configuration class for Kanban Looks Good plugin
 */
class PluginKanbanlooksgoodConfig extends CommonDBTM
{
    public static $rightname = 'config';

    /**
     * Get the singleton configuration
     *
     * @return array Configuration array with default values
     */
    public static function getConfig()
    {
        global $DB;

        $config = [
            'show_priority' => 1,
            'show_duration' => 1,
            'work_hours_per_day' => 7
        ];

        $iterator = $DB->request([
            'FROM' => 'glpi_plugin_kanbanlooksgood_configs',
            'LIMIT' => 1
        ]);

        if (count($iterator) > 0) {
            $data = $iterator->current();
            $config['show_priority'] = (int)$data['show_priority'];
            $config['show_duration'] = (int)$data['show_duration'];
            $config['work_hours_per_day'] = (int)$data['work_hours_per_day'];
        }

        return $config;
    }

    /**
     * Save configuration
     *
     * @param array $input Configuration to save
     * @return bool Success
     */
    public static function saveConfig($input)
    {
        global $DB;

        // Los valores vienen de Dropdown::showYesNo(), que envía "1" o "0"
        $show_priority = (int)($input['show_priority'] ?? 0);
        $show_duration = (int)($input['show_duration'] ?? 0);
        $work_hours_per_day = (int)($input['work_hours_per_day'] ?? 7);

        // Validar horas por día (entre 1 y 24)
        if ($work_hours_per_day < 1 || $work_hours_per_day > 24) {
            $work_hours_per_day = 7;
        }

        // Debug: Log en archivo (temporal para debugging)
        $debug_msg = date('Y-m-d H:i:s') . " - Guardando config: priority={$show_priority}, duration={$show_duration}, hours={$work_hours_per_day}\n";
        error_log($debug_msg, 3, GLPI_LOG_DIR . '/kanbanlooksgood_debug.log');

        $iterator = $DB->request([
            'FROM' => 'glpi_plugin_kanbanlooksgood_configs',
            'LIMIT' => 1
        ]);

        if (count($iterator) > 0) {
            // Update
            $data = $iterator->current();
            $result = $DB->update(
                'glpi_plugin_kanbanlooksgood_configs',
                [
                    'show_priority' => $show_priority,
                    'show_duration' => $show_duration,
                    'work_hours_per_day' => $work_hours_per_day
                ],
                ['id' => $data['id']]
            );

            error_log(date('Y-m-d H:i:s') . " - UPDATE result: " . ($result ? 'SUCCESS' : 'FAILED') . "\n", 3, GLPI_LOG_DIR . '/kanbanlooksgood_debug.log');
            return $result;
        } else {
            // Insert
            $result = $DB->insert(
                'glpi_plugin_kanbanlooksgood_configs',
                [
                    'show_priority' => $show_priority,
                    'show_duration' => $show_duration,
                    'work_hours_per_day' => $work_hours_per_day
                ]
            );

            error_log(date('Y-m-d H:i:s') . " - INSERT result: " . ($result ? 'SUCCESS' : 'FAILED') . "\n", 3, GLPI_LOG_DIR . '/kanbanlooksgood_debug.log');
            return $result;
        }
    }

    /**
     * Display configuration form
     *
     * @return void
     */
    public static function showConfigForm()
    {
        global $CFG_GLPI;

        if (!Config::canUpdate()) {
            return;
        }

        $config = self::getConfig();

        echo "<div class='center'>";
        echo "<form name='form' method='post' action='" . $CFG_GLPI['root_doc'] . "/plugins/kanbanlooksgood/front/config.form.php'>";
        echo Html::hidden('_glpi_csrf_token', ['value' => Session::getNewCSRFToken()]);

        echo "<table class='tab_cadre_fixe'>";
        echo "<tr class='tab_bg_1'>";
        echo "<th colspan='2'>" . __('Kanban Looks Good - Configuration', 'kanbanlooksgood') . "</th>";
        echo "</tr>";

        // Mostrar badge de prioridad
        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Show Priority Badge', 'kanbanlooksgood') . "</td>";
        echo "<td>";
        Dropdown::showYesNo('show_priority', $config['show_priority']);
        echo "</td>";
        echo "</tr>";

        // Mostrar duración planificada
        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Show Planned Duration', 'kanbanlooksgood') . "</td>";
        echo "<td>";
        Dropdown::showYesNo('show_duration', $config['show_duration']);
        echo "</td>";
        echo "</tr>";

        // Horas por día laboral
        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Work Hours per Day', 'kanbanlooksgood') . "</td>";
        echo "<td>";
        echo "<input type='number' name='work_hours_per_day' value='" . $config['work_hours_per_day'] . "' min='1' max='24' style='width: 80px;' />";
        echo " " . __('hours', 'kanbanlooksgood');
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td colspan='2' class='center'>";
        echo "<input type='submit' name='update_config' value='" . __('Save') . "' class='btn btn-primary'>";
        echo "</td>";
        echo "</tr>";

        echo "</table>";
        Html::closeForm();
        echo "</div>";
    }

    /**
     * Get translated name
     *
     * @param integer $nb Number of items
     * @return string Name
     */
    public static function getTypeName($nb = 0)
    {
        return __('Kanban Looks Good', 'kanbanlooksgood');
    }
}
