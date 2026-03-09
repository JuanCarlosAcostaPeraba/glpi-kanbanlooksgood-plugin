/**
 * Kanban Looks Good - Automatic Filtering
 * 
 * Automatically applies the "type:Project" filter to the Project Kanban
 * when no other filters are present.
 */
(function () {
    console.log('KanbanLooksGood: Script initialized (v3 - Vue Component Support)');

    const init = () => {
        // Detect GLPI 11 Kanban URLs
        const url = window.location.href;
        const isDirectKanban = url.includes('kanban.php');
        const isProjectFormKanban = url.includes('project.form.php') && url.includes('showglobalkanban=1');

        if (!isDirectKanban && !isProjectFormKanban) {
            return;
        }

        // Determine itemtype
        const urlParams = new URLSearchParams(window.location.search);
        let itemtype = urlParams.get('itemtype');
        if (!itemtype && isProjectFormKanban) {
            itemtype = 'Project';
        }

        if (itemtype !== 'Project') {
            return;
        }

        let filterApplied = false;

        const applyDefaultFilter = () => {
            if (filterApplied) return true;

            // GLPI 11 Kanban uses a custom "search-input" component (Vue)
            // It's a div with contenteditable spans, not a standard input
            const searchContainer = document.querySelector('.search-input.form-control');
            const editableSpan = document.querySelector('.search-input-tag-input[contenteditable="true"]');

            if (searchContainer && editableSpan) {
                // Check if any tags are already present
                const existingTags = searchContainer.querySelectorAll('.search-input-tag');

                if (existingTags.length === 0 && editableSpan.textContent.trim() === '') {
                    console.log('KanbanLooksGood: Applying default "type:Project" filter to Vue component');

                    // To trigger the Vue filter, we need to:
                    // 1. Put the text in the editable span
                    // 2. Trigger the input/keyup events so the Vue component processes it
                    editableSpan.focus();
                    editableSpan.textContent = 'type:Project';

                    // Dispatch events
                    const events = ['input', 'keydown', 'keyup', 'change'];
                    events.forEach(type => {
                        editableSpan.dispatchEvent(new Event(type, { bubbles: true }));
                    });

                    // Trigger Enter to confirm the tag
                    editableSpan.dispatchEvent(new KeyboardEvent('keydown', {
                        key: 'Enter',
                        code: 'Enter',
                        keyCode: 13,
                        which: 13,
                        bubbles: true
                    }));

                    // Blur the element to close the suggestion dropdown
                    setTimeout(() => {
                        editableSpan.blur();
                        // Also click outside if necessary
                        document.body.click();
                    }, 100);

                    filterApplied = true;
                    return true;
                } else {
                    console.log('KanbanLooksGood: Filter or text already present, skipping');
                    filterApplied = true;
                    return true;
                }
            }
            return false;
        };

        // Observe for Vue rendering
        const observer = new MutationObserver((mutations, obs) => {
            if (applyDefaultFilter()) {
                obs.disconnect();
            }
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });

        // Fallbacks
        setTimeout(applyDefaultFilter, 1500);
        setTimeout(applyDefaultFilter, 4000);
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
