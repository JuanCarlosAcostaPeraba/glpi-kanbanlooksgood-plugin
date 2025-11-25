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
 * Hook class for Kanban Looks Good
 */
class PluginKanbanlooksgoodHook
{
    /**
     * Convierte una duración en segundos a formato humano usando jornada laboral configurable
     *
     * @param int $seconds Duración en segundos
     * @param int $hoursPerDay Horas por día laboral (por defecto desde config)
     * @return string Duración formateada (ej: "2d 3h 30min")
     */
    private static function formatPlannedDuration($seconds, $hoursPerDay = null)
    {
        if ($seconds <= 0) {
            return '';
        }

        // Obtener configuración si no se pasa explícitamente
        if ($hoursPerDay === null) {
            $config = PluginKanbanlooksgoodConfig::getConfig();
            $hoursPerDay = $config['work_hours_per_day'];
        }

        // Constantes para jornada laboral configurable
        $SECONDS_PER_MINUTE = 60;
        $SECONDS_PER_HOUR = 3600;
        $SECONDS_PER_DAY = $hoursPerDay * 3600; // Horas configuradas = 1 día laboral

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

        // Si no hay partes (menos de 1 minuto), mostrar "< 1min"
        if (empty($parts)) {
            return '< 1min';
        }

        return implode(' ', $parts);
    }

    /**
     * Hook called when Kanban item metadata is being built
     * Adds priority and planned duration to Project and ProjectTask cards
     *
     * @param array $params Hook parameters:
     *   - itemtype: string (Project or ProjectTask)
     *   - items_id: int
     *   - metadata: array (existing metadata)
     * @return array Modified metadata
     */
    public static function kanbanItemMetadata($params = [])
    {
        $itemtype = $params['itemtype'] ?? null;
        $items_id = $params['items_id'] ?? 0;
        $metadata = $params['metadata'] ?? [];

        // Solo procesar Project y ProjectTask
        if (!in_array($itemtype, ['Project', 'ProjectTask']) || $items_id <= 0) {
            return ['metadata' => $metadata];
        }

        // Obtener configuración del plugin
        $config = PluginKanbanlooksgoodConfig::getConfig();

        // Cargar el item UNA SOLA VEZ para evitar múltiples consultas SQL
        $item = null;
        $needs_item_load = false;

        // Verificar qué necesitamos cargar según configuración
        $needs_priority = $config['show_priority'] && !isset($metadata['priority']) && $itemtype === 'Project';
        $needs_duration = $config['show_duration'] && (!isset($metadata['planned_duration_human']) || empty($metadata['planned_duration_human']));

        if ($needs_priority || ($needs_duration && $itemtype === 'ProjectTask')) {
            $needs_item_load = true;
        }

        // Cargar item solo si es necesario
        if ($needs_item_load) {
            if ($itemtype === 'Project') {
                $item = new Project();
            } else {
                $item = new ProjectTask();
            }
            if (!$item->getFromDB($items_id)) {
                // Si no se puede cargar, retornar metadata sin modificar
                return ['metadata' => $metadata];
            }
        }

        // Procesar PRIORIDAD (solo para Projects)
        if ($needs_priority && $item !== null && $itemtype === 'Project') {
            $priority_value = isset($item->fields['priority'])
                ? (int)$item->fields['priority']
                : 0;

            if ($priority_value > 0) {
                // Color de prioridad configurado en GLPI
                // GLPI guarda estos colores en la sesión como glpipriority_X
                $priority_color = $_SESSION['glpipriority_' . $priority_value] ?? '';
                $priority_name = CommonITILObject::getPriorityName($priority_value);

                if ($priority_color) {
                    $metadata['priority_color'] = $priority_color;

                    // Generar HTML del badge de prioridad (igual que en Search.php)
                    // Formato: <div class='priority_block'><span style='background: color'></span>&nbsp;Nombre</div>
                    $metadata['priority'] = "<div class='priority_block' style='border-color: $priority_color'>" .
                        "<span style='background: $priority_color'></span>&nbsp;" .
                        htmlspecialchars($priority_name) .
                        "</div>";
                } else {
                    // Si no hay color, solo el texto
                    $metadata['priority'] = htmlspecialchars($priority_name);
                }
            }
        }

        // Procesar DURACIÓN PLANIFICADA
        // Usar planned_duration de metadata si ya existe (viene de getDataToDisplayOnKanban)
        if ($needs_duration) {
            if ($itemtype === 'Project') {
                // Para Projects: usar planned_duration de metadata si existe, sino calcularlo
                $planned_duration = $metadata['planned_duration'] ?? null;

                if ($planned_duration === null) {
                    // Solo calcular si no está en metadata (caso raro)
                    $planned_duration = ProjectTask::getTotalPlannedDurationForProject($items_id);
                }

                if ($planned_duration > 0) {
                    // Valor bruto (segundos) para filtros futuros
                    if (!isset($metadata['planned_duration'])) {
                        $metadata['planned_duration'] = $planned_duration;
                    }

                    // Valor formateado para mostrar (con jornada laboral de 7 horas)
                    $metadata['planned_duration_human'] = self::formatPlannedDuration($planned_duration);
                }
            } elseif ($itemtype === 'ProjectTask') {
                // Para ProjectTask: usar planned_duration de metadata si existe, sino del item cargado
                $planned_duration = $metadata['planned_duration'] ?? null;

                if ($planned_duration === null && $item !== null) {
                    $planned_duration = isset($item->fields['planned_duration'])
                        ? (int)$item->fields['planned_duration']
                        : 0;
                }

                if ($planned_duration > 0) {
                    // Valor bruto (segundos) para filtros futuros
                    if (!isset($metadata['planned_duration'])) {
                        $metadata['planned_duration'] = $planned_duration;
                    }

                    // Valor formateado para mostrar (con jornada laboral de 7 horas)
                    $metadata['planned_duration_human'] = self::formatPlannedDuration($planned_duration);
                }
            }
        }

        // Añadir configuración a metadata para uso en JavaScript
        $metadata['_kanbanlooksgood_config'] = [
            'show_priority' => $config['show_priority'],
            'show_duration' => $config['show_duration']
        ];

        return ['metadata' => $metadata];
    }
}
