/**
 * -------------------------------------------------------------------------
 * Kanban Looks Good - JavaScript
 * -------------------------------------------------------------------------
 *
 * Añade visualización de Prioridad y Duración Planificada en las tarjetas
 * del Kanban de Proyectos, usando SOLO la metadata que ya trae GLPI.
 * -------------------------------------------------------------------------
 */

(function () {
    'use strict';

    /**
     * Suaviza un color hexadecimal mezclándolo con blanco para crear un tono más tenue.
     *
     * @param {string} color - Color hexadecimal en formato #RGB o #RRGGBB
     * @param {number} [blendRatio=0.8] - Ratio de mezcla con blanco (0.0 a 1.0)
     * @returns {string} Color RGB en formato "rgb(r, g, b)" si el color es válido,
     *                   el color original si no es hex, o cadena vacía si no es string.
     */
    const softenPriorityColor = (color, blendRatio = 0.8) => {
        if (typeof color !== 'string') {
            return '';
        }

        const normalized = color.trim();
        const hexMatch = normalized.match(/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/);

        if (!hexMatch) {
            // Puede ser rgb(), etc. Lo devolvemos tal cual.
            return normalized;
        }

        let hex = hexMatch[1];
        if (hex.length === 3) {
            hex = hex.split('').map((c) => c + c).join('');
        }

        const toInt = (segment) => parseInt(segment, 16, 10);
        const components = [
            toInt(hex.substring(0, 2)),
            toInt(hex.substring(2, 4)),
            toInt(hex.substring(4, 6)),
        ];

        const ratio = Math.min(Math.max(blendRatio, 0), 1);
        const softened = components.map(
            (c) => Math.round(c + ((255 - c) * ratio))
        );

        return `rgb(${softened.join(', ')})`;
    };

    /**
     * Aplica prioridad y duración a una tarjeta concreta, usando los data()
     * que GLPI ya ha rellenado a partir de _metadata.
     *
     * @param {jQuery} $card
     */
    const processKanbanCard = function ($card) {
        if (!$card || !$card.length) {
            return;
        }

        // Evitar reprocesar y duplicar la franja de metadata
        if ($card.data('kanbanlooksgood-processed')) {
            return;
        }

        const cardId = $card.attr('id');
        if (!cardId) {
            return;
        }

        const parts = cardId.split('-', 2);
        if (parts.length !== 2) {
            return;
        }

        const itemtype = parts[0];

        // Solo Projects y Tasks del módulo de proyectos
        if (!['Project', 'ProjectTask'].includes(itemtype)) {
            return;
        }

        // Obtener configuración desde metadata
        const config = $card.data('_kanbanlooksgood_config') || {
            show_priority: 1,
            show_duration: 1
        };

        // Estos valores vienen desde el backend vía _metadata y el core Kanban.js
        const priorityHtml = $card.data('priority') || null;
        const priorityColor = $card.data('priority_color') || null;
        const plannedDurationHuman = $card.data('planned_duration_human') || null;

        const hasPriority = config.show_priority && !!priorityHtml && !!priorityColor;
        const hasDuration = config.show_duration && !!plannedDurationHuman;

        if (!hasPriority && !hasDuration) {
            return;
        }

        // Aplicar fondo suavizado según prioridad
        if (hasPriority && priorityColor) {
            const softened = softenPriorityColor(priorityColor);
            if (softened) {
                $card.css('background-color', softened);
            }

            const $header = $card.find('.kanban-item-header');
            if ($header.length) {
                $header.css('background-color', priorityColor);
            }
        }

        // Insertar franja de metadata antes del contenido
        const $content = $card.find('.kanban-item-content');
        if ($content.length) {
            let metadataHtml = `
                <div class="kanban-item-metadata kanbanlooksgood"
                     style="padding: 0 10px; display:flex; align-items:center; justify-content:space-between; gap:8px;">
            `;

            if (hasPriority) {
                // priorityHtml ya viene preparado desde PHP (badge completo)
                metadataHtml += `<div class="kanban-priority">${priorityHtml}</div>`;
            }

            if (hasDuration) {
                const safeDuration = $('<div>').text(plannedDurationHuman).html();
                metadataHtml += `<div class="kanban-duration" style="font-size:11px; opacity:0.9;">${safeDuration}</div>`;
            }

            metadataHtml += '</div>';

            $content.before(metadataHtml);
        }

        // Marcar como procesada
        $card.data('kanbanlooksgood-processed', true);
    };

    /**
     * Procesa todas las tarjetas de un Kanban (solo si es de tipo Project)
     *
     * @param {jQuery} $kanbanElement
     */
    const processKanban = function ($kanbanElement) {
        if (!$kanbanElement || !$kanbanElement.length) {
            return;
        }

        const kanbanInstance = $kanbanElement.data('js_class');
        if (!kanbanInstance || !kanbanInstance.item) {
            return;
        }

        // Solo Kanban de Proyectos
        if (kanbanInstance.item.itemtype !== 'Project') {
            return;
        }

        // Pequeño timeout para asegurarnos de que las tarjetas ya estén pintadas
        setTimeout(function () {
            $kanbanElement.find('.kanban-item').each(function () {
                processKanbanCard($(this));
            });
        }, 0);
    };

    /**
     * Enganchamos a los eventos del Kanban que lanza el core de GLPI:
     *  - kanban:post_build  → primera construcción
     *  - kanban:refresh     → cuando se recarga (añadir tarjeta, mover, etc.)
     */
    $(document).on('kanban:post_build kanban:refresh', function (e) {
        const $kanbanElement = $(e.target);
        processKanban($kanbanElement);
    });

    /**
     * Por si el Kanban ya está construido cuando se carga este script
     * (raro, pero por si acaso), intentamos procesar lo que haya.
     */
    $(document).ready(function () {
        $('[data-js_class], [id^="kanban"]').each(function () {
            const $k = $(this);
            if ($k.data('js_class')) {
                processKanban($k);
            }
        });
    });

})();
