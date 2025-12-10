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
 * the Free Software Foundation; either version 3 of the License, or
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
 * Configuration management class for Kanban Looks Good plugin
 *
 * Handles plugin configuration storage, retrieval, and form display.
 * Manages settings for priority display, duration display, and work hours
 * per day calculation.
 *
 * @package   KanbanLooksGood
 * @author    Juan Carlos Acosta Perabá
 * @copyright Copyright (C) 2024-2025 by Juan Carlos Acosta Perabá
 * @license   GPLv3+ http://www.gnu.org/licenses/gpl.html
 */
class PluginKanbanlooksgoodConfig extends CommonDBTM
{
    /**
     * Rights management - requires config permission
     *
     * @var string
     */
    public static $rightname = 'config';

    /**
     * Get plugin configuration
     *
     * Retrieves the current plugin configuration from the database.
     * Returns default values if no configuration exists yet.
     *
     * @global DBmysql $DB GLPI database instance
     *
     * @return array Configuration array with keys:
     *               - show_priority: Whether to display priority badge (1|0)
     *               - show_duration: Whether to display planned duration (1|0)
     *               - work_hours_per_day: Hours per work day (1-24)
     */
    public static function getConfig()
    {
        global $DB;

        // Default configuration values
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
     * Save plugin configuration
     *
     * Validates and saves the plugin configuration to the database.
     * Performs input validation and sanitization before saving.
     *
     * @param array $input Configuration data from form submission with keys:
     *                     - show_priority: Display priority badge (1|0)
     *                     - show_duration: Display planned duration (1|0)
     *                     - work_hours_per_day: Hours per work day (1-24)
     *
     * @global DBmysql $DB GLPI database instance
     *
     * @return bool True if save was successful, false otherwise
     */
    public static function saveConfig($input)
    {
        global $DB;

        try {
            // Sanitize and validate input values
            // Values come from Dropdown::showYesNo(), which sends "1" or "0"
            $show_priority = isset($input['show_priority']) ? (int)$input['show_priority'] : 0;
            $show_duration = isset($input['show_duration']) ? (int)$input['show_duration'] : 0;
            $work_hours_per_day = isset($input['work_hours_per_day']) ? (int)$input['work_hours_per_day'] : 7;

            // Validate show_priority (must be 0 or 1)
            $show_priority = ($show_priority === 1) ? 1 : 0;

            // Validate show_duration (must be 0 or 1)
            $show_duration = ($show_duration === 1) ? 1 : 0;

            // Validate work hours per day (must be between 1 and 24)
            if ($work_hours_per_day < 1 || $work_hours_per_day > 24) {
                $work_hours_per_day = 7; // Reset to default if invalid
            }

            // Prepare data for database
            $data = [
                'show_priority' => $show_priority,
                'show_duration' => $show_duration,
                'work_hours_per_day' => $work_hours_per_day
            ];

            $iterator = $DB->request([
                'FROM' => 'glpi_plugin_kanbanlooksgood_configs',
                'LIMIT' => 1
            ]);

            if (count($iterator) > 0) {
                // Update existing configuration
                $config = $iterator->current();
                $result = $DB->update(
                    'glpi_plugin_kanbanlooksgood_configs',
                    $data,
                    ['id' => $config['id']]
                );

                return $result !== false;
            } else {
                // Insert new configuration
                $result = $DB->insert(
                    'glpi_plugin_kanbanlooksgood_configs',
                    $data
                );

                return $result !== false;
            }
        } catch (Exception $e) {
            // Log error if possible
            if (function_exists('Toolbox::logError')) {
                Toolbox::logError($e->getMessage());
            }
            return false;
        }
    }

    /**
     * Display configuration form
     *
     * Renders the plugin configuration form in the GLPI admin interface.
     * Requires UPDATE permission on config to be displayed.
     *
     * @global array $CFG_GLPI GLPI configuration
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

        echo "<table class='tab_cadre_fixe' style='max-width: 800px; margin: 20px auto;'>";

        // Header
        echo "<tr class='tab_bg_1'>";
        echo "<th colspan='2'>";
        echo "<i class='fas fa-cog'></i> ";
        echo __('Kanban Looks Good - Configuration', 'kanbanlooksgood');
        echo "</th>";
        echo "</tr>";

        // Description row
        echo "<tr class='tab_bg_2'>";
        echo "<td colspan='2' style='text-align: center; padding: 15px;'>";
        echo "<p style='margin: 0; color: #666;'>";
        echo __('Configure how priority and duration information is displayed on Project Kanban cards', 'kanbanlooksgood');
        echo "</p>";
        echo "</td>";
        echo "</tr>";

        // Show priority badge option
        echo "<tr class='tab_bg_1'>";
        echo "<td style='width: 60%; padding: 12px;'>";
        echo "<strong>" . __('Show Priority Badge', 'kanbanlooksgood') . "</strong>";
        echo "<br><small style='color: #666;'>";
        echo __('Display the priority badge on Kanban cards using GLPI native colors', 'kanbanlooksgood');
        echo "</small>";
        echo "</td>";
        echo "<td style='padding: 12px;'>";
        Dropdown::showYesNo('show_priority', $config['show_priority']);
        echo "</td>";
        echo "</tr>";

        // Show planned duration option
        echo "<tr class='tab_bg_1'>";
        echo "<td style='padding: 12px;'>";
        echo "<strong>" . __('Show Planned Duration', 'kanbanlooksgood') . "</strong>";
        echo "<br><small style='color: #666;'>";
        echo __('Display the planned duration on Kanban cards in a human-readable format', 'kanbanlooksgood');
        echo "</small>";
        echo "</td>";
        echo "<td style='padding: 12px;'>";
        Dropdown::showYesNo('show_duration', $config['show_duration']);
        echo "</td>";
        echo "</tr>";

        // Work hours per day option
        echo "<tr class='tab_bg_1'>";
        echo "<td style='padding: 12px;'>";
        echo "<strong>" . __('Work Hours per Day', 'kanbanlooksgood') . "</strong>";
        echo "<br><small style='color: #666;'>";
        echo __('Number of work hours per day for duration calculations (1-24)', 'kanbanlooksgood');
        echo "</small>";
        echo "</td>";
        echo "<td style='padding: 12px;'>";
        echo "<input type='number' name='work_hours_per_day' ";
        echo "value='" . htmlspecialchars($config['work_hours_per_day'], ENT_QUOTES, 'UTF-8') . "' ";
        echo "min='1' max='24' required ";
        echo "style='width: 80px; padding: 5px;' /> ";
        echo __('hours', 'kanbanlooksgood');
        echo "</td>";
        echo "</tr>";

        // Submit button
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
     * Get the localized name of the plugin
     *
     * Returns the translated name of the plugin for display in GLPI interface.
     *
     * @param int $nb Number of items (unused, kept for compatibility)
     *
     * @return string Localized plugin name
     */
    public static function getTypeName($nb = 0)
    {
        return __('Kanban Looks Good', 'kanbanlooksgood');
    }
}
