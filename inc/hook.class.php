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
 * Provides hooks integration with GLPI 11's Kanban system to inject
 * priority and planned duration directly into Kanban card content.
 *
 * @package   KanbanLooksGood
 * @author    Juan Carlos Acosta Perabá
 * @copyright Copyright (C) 2024-2025 by Juan Carlos Acosta Perabá
 * @license   GPLv3+ http://www.gnu.org/licenses/gpl.html
 */
class PluginKanbanlooksgoodHook
{
    /**
     * Get priority color for GLPI 11
     *
     * @param int $priority_value Priority value (1-6)
     * @return string Color hex code or empty string if not found
     */
    private static function getPriorityColor($priority_value)
    {
        // Try session variable
        if (isset($_SESSION['glpipriority_' . $priority_value])) {
            return $_SESSION['glpipriority_' . $priority_value];
        }

        // Try CommonITILObject method
        if (class_exists('CommonITILObject') && method_exists('CommonITILObject', 'getPriorityColor')) {
            return CommonITILObject::getPriorityColor($priority_value);
        }

        // Fallback to default GLPI colors
        $default_colors = [
            1 => '#5cb85c', // Very low - green
            2 => '#5bc0de', // Low - blue
            3 => '#f0ad4e', // Medium - orange
            4 => '#ff9800', // High - darker orange
            5 => '#d9534f', // Very high - red
            6 => '#c9302c', // Major - dark red
        ];

        return $default_colors[$priority_value] ?? '';
    }

    /**
     * Get priority name
     *
     * @param int $priority_value Priority value (1-6)
     * @return string Priority name translated
     */
    private static function getPriorityName($priority_value)
    {
        if (class_exists('CommonITILObject') && method_exists('CommonITILObject', 'getPriorityName')) {
            return CommonITILObject::getPriorityName($priority_value);
        }

        $priorities = [
            1 => __('Very low'),
            2 => __('Low'),
            3 => __('Medium'),
            4 => __('High'),
            5 => __('Very high'),
            6 => __('Major')
        ];

        return $priorities[$priority_value] ?? '';
    }

    /**
     * Format duration in seconds to human-readable format
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

        if ($hoursPerDay === null) {
            $config = PluginKanbanlooksgoodConfig::getConfig();
            $hoursPerDay = $config['work_hours_per_day'];
        }

        $SECONDS_PER_MINUTE = 60;
        $SECONDS_PER_HOUR = 3600;
        $SECONDS_PER_DAY = $hoursPerDay * 3600;

        $days = floor($seconds / $SECONDS_PER_DAY);
        $remainder = $seconds % $SECONDS_PER_DAY;

        $hours = floor($remainder / $SECONDS_PER_HOUR);
        $remainder = $remainder % $SECONDS_PER_HOUR;

        $minutes = floor($remainder / $SECONDS_PER_MINUTE);

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

        if (empty($parts)) {
            return '< 1min';
        }

        return implode(' ', $parts);
    }

    /**
     * Lighten a color for background
     *
     * @param string $color Hex color code
     * @param float $amount Amount to lighten (0.0 to 1.0)
     * @return string RGB color string
     */
    private static function lightenColor($color, $amount = 0.8)
    {
        $color = ltrim($color, '#');

        if (strlen($color) === 3) {
            $color = $color[0] . $color[0] . $color[1] . $color[1] . $color[2] . $color[2];
        }

        $r = hexdec(substr($color, 0, 2));
        $g = hexdec(substr($color, 2, 2));
        $b = hexdec(substr($color, 4, 2));

        $r = round($r + ((255 - $r) * $amount));
        $g = round($g + ((255 - $g) * $amount));
        $b = round($b + ((255 - $b) * $amount));

        return sprintf('rgb(%d, %d, %d)', $r, $g, $b);
    }

    /**
     * Add content to Kanban cards (GLPI 11)
     *
     * This hook is called when building Kanban card content.
     * It injects priority and planned duration HTML directly into the card.
     *
     * @param array $params Hook parameters containing:
     *                      - itemtype: Item type (Project or ProjectTask)
     *                      - items_id: ID of the item
     *
     * @return array Array with 'content' key containing HTML to inject
     */
    public static function addKanbanContent($params = [])
    {
        $itemtype = $params['itemtype'] ?? null;
        $items_id = $params['items_id'] ?? 0;

        // Only process Project and ProjectTask items
        if (!in_array($itemtype, ['Project', 'ProjectTask'], true) || $items_id <= 0) {
            return ['content' => ''];
        }

        // Load plugin configuration
        $config = PluginKanbanlooksgoodConfig::getConfig();

        // If both features are disabled, return empty
        if (!$config['show_priority'] && !$config['show_duration']) {
            return ['content' => ''];
        }

        // Load item
        if ($itemtype === 'Project') {
            $item = new Project();
        } else {
            $item = new ProjectTask();
        }

        if (!$item->getFromDB($items_id)) {
            return ['content' => ''];
        }

        $html = '';
        $priority_color = '';

        // Get priority information (Projects only)
        if ($config['show_priority'] && $itemtype === 'Project') {
            $priority_value = isset($item->fields['priority']) ? (int)$item->fields['priority'] : 0;

            if ($priority_value > 0) {
                $priority_color = self::getPriorityColor($priority_value);
                $priority_name = self::getPriorityName($priority_value);

                if ($priority_color && $priority_name) {
                    // Apply soft background color to card using inline style
                    $bg_color = self::lightenColor($priority_color);
                    $html .= "<style>.kanban-item#{$itemtype}-{$items_id} { background-color: {$bg_color} !important; }</style>";
                    $html .= "<style>.kanban-item#{$itemtype}-{$items_id} .kanban-item-header { background-color: {$priority_color} !important; }</style>";

                    // Add priority badge
                    $html .= "<div class='kanbanlooksgood-metadata'>";
                    $html .= "<div class='kanbanlooksgood-priority'>";
                    $html .= "<div class='priority_block' style='border-color: " . htmlspecialchars($priority_color, ENT_QUOTES, 'UTF-8') . "'>";
                    $html .= "<span style='background: " . htmlspecialchars($priority_color, ENT_QUOTES, 'UTF-8') . "'></span>&nbsp;";
                    $html .= htmlspecialchars($priority_name, ENT_QUOTES, 'UTF-8');
                    $html .= "</div>";
                    $html .= "</div>";
                }
            }
        }

        // Get planned duration
        if ($config['show_duration']) {
            $planned_duration = 0;

            if ($itemtype === 'Project') {
                $planned_duration = ProjectTask::getTotalPlannedDurationForProject($items_id);
            } elseif ($itemtype === 'ProjectTask') {
                $planned_duration = isset($item->fields['planned_duration'])
                    ? (int)$item->fields['planned_duration']
                    : 0;
            }

            if ($planned_duration > 0) {
                $duration_human = self::formatPlannedDuration($planned_duration);

                if ($duration_human) {
                    if (!str_contains($html, 'kanbanlooksgood-metadata')) {
                        $html .= "<div class='kanbanlooksgood-metadata'>";
                    }

                    $html .= "<div class='kanbanlooksgood-duration'>";
                    $html .= "<i class='ti ti-clock'></i>&nbsp;";
                    $html .= htmlspecialchars($duration_human, ENT_QUOTES, 'UTF-8');
                    $html .= "</div>";
                }
            }
        }

        // Close metadata div if opened
        if (str_contains($html, 'kanbanlooksgood-metadata') && !str_contains($html, '</div></div>')) {
            $html .= "</div>"; // Close kanbanlooksgood-metadata
        }

        return ['content' => $html];
    }
}
