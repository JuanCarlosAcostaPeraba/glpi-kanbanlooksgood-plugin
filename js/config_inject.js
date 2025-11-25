/**
 * -------------------------------------------------------------------------
 * Kanban Looks Good - Config Injection
 * -------------------------------------------------------------------------
 *
 * Inyecta la configuración del plugin en una variable global para que
 * kanban.js pueda acceder a ella.
 * -------------------------------------------------------------------------
 */

// Inicializar objeto global de configuración
window.KanbanLooksGoodConfig = window.KanbanLooksGoodConfig || {
    show_priority: 1,
    show_duration: 1
};

