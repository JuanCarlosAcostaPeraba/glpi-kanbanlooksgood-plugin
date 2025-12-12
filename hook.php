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

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly");
}

// Ensure install/uninstall functions are available when GLPI triggers actions.
// GLPI may include hook.php without loading setup.php in some flows.
include_once __DIR__ . '/setup.php';
