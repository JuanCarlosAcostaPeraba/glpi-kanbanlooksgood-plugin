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
 * Hook class for Kanban Looks Good plugin
 *
 * Provides hooks integration with GLPI's Kanban system to inject
 * priority and planned duration metadata into Kanban cards.
 *
 * @package   KanbanLooksGood
 * @author    Juan Carlos Acosta Perabá
 * @copyright Copyright (C) 2024-2025 by Juan Carlos Acosta Perabá
 * @license   GPLv3+ http://www.gnu.org/licenses/gpl.html
 */
class PluginKanbanlooksgoodHook
{
    /**
     * Format duration in seconds to human-readable format
     *
     * Converts a duration in seconds to a formatted string using configurable
     * work hours per day. The output format is "Xd Yh Zmin" where each part
     * is only shown if greater than zero.
     *
     * Examples:
     * - 28800 seconds (with 8h/day) => "1d"
     * - 10800 seconds (with 8h/day) => "3h"
     * - 27000 seconds (with 7h/day) => "1d 45min"
     *
     * @param int      $seconds     Duration in seconds to format
     * @param int|null $hoursPerDay Hours per work day (null = use config value)
     *
     * @return string Formatted duration string or empty string if <= 0
     */
    private static function formatPlannedDuration($seconds, $hoursPerDay = null)
    {
        if ($seconds <= 0) {
            return '';
        }

        // Get configuration if not explicitly provided
        if ($hoursPerDay === null) {
            $config = PluginKanbanlooksgoodConfig::getConfig();
            $hoursPerDay = $config['work_hours_per_day'];
        }

        // Time conversion constants
        $SECONDS_PER_MINUTE = 60;
        $SECONDS_PER_HOUR = 3600;
        $SECONDS_PER_DAY = $hoursPerDay * 3600; // Configured hours = 1 work day

        // Calculate days, hours, and minutes
        $days = floor($seconds / $SECONDS_PER_DAY);
        $remainder = $seconds % $SECONDS_PER_DAY;

        $hours = floor($remainder / $SECONDS_PER_HOUR);
        $remainder = $remainder % $SECONDS_PER_HOUR;

        $minutes = floor($remainder / $SECONDS_PER_MINUTE);

        // Build formatted parts
        $parts = [];

        if ($days > 0) {
            $parts[] = $days . 'd';
        }

        if ($hours > 0) {
            $parts[] = $hours . 'h';
        }

        if ($minutes > 0) {
            $parts[] = $minutes . 'min';
        }

        // If no parts (less than 1 minute), show "< 1min"
        if (empty($parts)) {
            return '< 1min';
        }

        return implode(' ', $parts);
    }

    /**
     * Kanban item metadata hook
     *
     * This hook is called by GLPI when building Kanban card metadata.
     * It adds priority information (badge and color) and planned duration
     * in a human-readable format to Project and ProjectTask cards.
     *
     * The hook:
     * 1. Validates input parameters
     * 2. Loads plugin configuration
     * 3. Adds priority data if enabled and applicable
     * 4. Adds duration data if enabled and applicable
     * 5. Returns modified metadata for frontend rendering
     *
     * @param array $params Hook parameters containing:
     *                      - itemtype: Item type (Project or ProjectTask)
     *                      - items_id: ID of the item
     *                      - metadata: Existing metadata array
     *
     * @return array Array containing modified metadata with keys:
     *               - priority: HTML badge for priority display
     *               - priority_color: Hex color code for priority
     *               - planned_duration: Duration in seconds (raw value)
     *               - planned_duration_human: Formatted duration string
     *               - _kanbanlooksgood_config: Plugin configuration
     */
    public static function kanbanItemMetadata($params = [])
    {
        // Extract and validate parameters
        $itemtype = $params['itemtype'] ?? null;
        $items_id = $params['items_id'] ?? 0;
        $metadata = $params['metadata'] ?? [];

        // Only process Project and ProjectTask items
        if (!in_array($itemtype, ['Project', 'ProjectTask'], true) || $items_id <= 0) {
            return ['metadata' => $metadata];
        }

        // Load plugin configuration
        $config = PluginKanbanlooksgoodConfig::getConfig();

        // Optimize database queries: load item only once if needed
        $item = null;
        $needs_item_load = false;

        // Determine what needs to be loaded based on configuration
        $needs_priority = $config['show_priority'] && !isset($metadata['priority']) && $itemtype === 'Project';
        $needs_duration = $config['show_duration'] && (!isset($metadata['planned_duration_human']) || empty($metadata['planned_duration_human']));

        if ($needs_priority || ($needs_duration && $itemtype === 'ProjectTask')) {
            $needs_item_load = true;
        }

        // Load item from database only if necessary
        if ($needs_item_load) {
            if ($itemtype === 'Project') {
                $item = new Project();
            } else {
                $item = new ProjectTask();
            }
            if (!$item->getFromDB($items_id)) {
                // If item cannot be loaded, return unmodified metadata
                return ['metadata' => $metadata];
            }
        }

        // Process PRIORITY (Projects only)
        if ($needs_priority && $item !== null && $itemtype === 'Project') {
            $priority_value = isset($item->fields['priority'])
                ? (int)$item->fields['priority']
                : 0;

            if ($priority_value > 0) {
                // Get priority color from GLPI configuration
                // GLPI 10.x stores these colors in session as glpipriority_X
                // GLPI 11.x may use a different approach
                $priority_color = '';
                if (isset($_SESSION['glpipriority_' . $priority_value])) {
                    $priority_color = $_SESSION['glpipriority_' . $priority_value];
                }

                // Get priority name - compatible with both versions
                $priority_name = '';
                if (class_exists('CommonITILObject')) {
                    $priority_name = CommonITILObject::getPriorityName($priority_value);
                } else {
                    // Fallback for GLPI 11 if CommonITILObject doesn't exist
                    $priorities = [
                        1 => __('Very low'),
                        2 => __('Low'),
                        3 => __('Medium'),
                        4 => __('High'),
                        5 => __('Very high'),
                        6 => __('Major')
                    ];
                    $priority_name = $priorities[$priority_value] ?? '';
                }

                if ($priority_color) {
                    $metadata['priority_color'] = $priority_color;

                    // Generate priority badge HTML (same as in Search.php)
                    // Format: <div class='priority_block'><span style='background: color'></span>&nbsp;Name</div>
                    $metadata['priority'] = "<div class='priority_block' style='border-color: " . htmlspecialchars($priority_color, ENT_QUOTES, 'UTF-8') . "'>" .
                        "<span style='background: " . htmlspecialchars($priority_color, ENT_QUOTES, 'UTF-8') . "'></span>&nbsp;" .
                        htmlspecialchars($priority_name, ENT_QUOTES, 'UTF-8') .
                        "</div>";
                } else {
                    // If no color available, show text only
                    $metadata['priority'] = htmlspecialchars($priority_name, ENT_QUOTES, 'UTF-8');
                }
            }
        }

        // Process PLANNED DURATION
        // Use planned_duration from metadata if already available (from getDataToDisplayOnKanban)
        if ($needs_duration) {
            if ($itemtype === 'Project') {
                // For Projects: use metadata planned_duration if exists, otherwise calculate
                $planned_duration = $metadata['planned_duration'] ?? null;

                if ($planned_duration === null) {
                    // Only calculate if not in metadata (rare case)
                    $planned_duration = ProjectTask::getTotalPlannedDurationForProject($items_id);
                }

                if ($planned_duration > 0) {
                    // Raw value (seconds) for potential future filtering
                    if (!isset($metadata['planned_duration'])) {
                        $metadata['planned_duration'] = $planned_duration;
                    }

                    // Formatted value for display
                    $metadata['planned_duration_human'] = self::formatPlannedDuration($planned_duration);
                }
            } elseif ($itemtype === 'ProjectTask') {
                // For ProjectTask: use metadata planned_duration if exists, otherwise from loaded item
                $planned_duration = $metadata['planned_duration'] ?? null;

                if ($planned_duration === null && $item !== null) {
                    $planned_duration = isset($item->fields['planned_duration'])
                        ? (int)$item->fields['planned_duration']
                        : 0;
                }

                if ($planned_duration > 0) {
                    // Raw value (seconds) for potential future filtering
                    if (!isset($metadata['planned_duration'])) {
                        $metadata['planned_duration'] = $planned_duration;
                    }

                    // Formatted value for display
                    $metadata['planned_duration_human'] = self::formatPlannedDuration($planned_duration);
                }
            }
        }

        // Add plugin configuration to metadata for JavaScript usage
        $metadata['_kanbanlooksgood_config'] = [
            'show_priority' => $config['show_priority'],
            'show_duration' => $config['show_duration']
        ];

        return ['metadata' => $metadata];
    }
}
