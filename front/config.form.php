<?php

/**
 * -------------------------------------------------------------------------
 * Kanban Looks Good plugin for GLPI
 * Configuration form handler
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

// GLPI 10.x requires manual include, GLPI 11.x auto-loads
if (!defined('GLPI_ROOT')) {
    include('../../../inc/includes.php');
}

// Verify user has permission to update configuration
Session::checkRight('config', UPDATE);

// Handle configuration update submission
if (isset($_POST['update_config'])) {
    $result = PluginKanbanlooksgoodConfig::saveConfig($_POST);

    if ($result) {
        Session::addMessageAfterRedirect(
            __('Configuration saved successfully', 'kanbanlooksgood'),
            false,
            INFO
        );
    } else {
        Session::addMessageAfterRedirect(
            __('Error saving configuration', 'kanbanlooksgood'),
            false,
            ERROR
        );
    }

    Html::back();
} else {
    // Display configuration form
    // GLPI 11 compatible header
    if (version_compare(GLPI_VERSION, '11.0', '>=')) {
        Html::header(
            __('Kanban Looks Good', 'kanbanlooksgood'),
            $_SERVER['PHP_SELF'],
            'config',
            'config',
            'plugins'
        );
    } else {
        Html::header(
            __('Kanban Looks Good', 'kanbanlooksgood'),
            $_SERVER['PHP_SELF'],
            'config',
            'plugins'
        );
    }

    PluginKanbanlooksgoodConfig::showConfigForm();

    Html::footer();
}
