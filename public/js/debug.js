/**
 * Kanban Looks Good - Frontend Debug Script
 * 
 * Logs debug information to browser console to help diagnose plugin issues.
 */

(function () {
    'use strict';

    const DEBUG_PREFIX = '[KanbanLooksGood]';
    const STYLES = {
        header: 'color: #4CAF50; font-weight: bold; font-size: 14px;',
        success: 'color: #4CAF50; font-weight: bold;',
        warning: 'color: #FF9800; font-weight: bold;',
        error: 'color: #F44336; font-weight: bold;',
        info: 'color: #2196F3;'
    };

    function log(message, style = '') {
        if (style) {
            console.log(`%c${DEBUG_PREFIX} ${message}`, style);
        } else {
            console.log(`${DEBUG_PREFIX} ${message}`);
        }
    }

    function init() {
        log('Debug script loaded', STYLES.header);
        log(`URL: ${window.location.href}`, STYLES.info);

        // Check if we're on a Kanban page
        const isKanban = detectKanbanPage();
        log(`Is Kanban page: ${isKanban}`, isKanban ? STYLES.success : STYLES.warning);

        if (isKanban) {
            log('Starting Kanban monitoring...', STYLES.info);
            startMonitoring();
        }
    }

    function detectKanbanPage() {
        const url = window.location.href.toLowerCase();
        const hasKanbanInUrl = url.includes('kanban');
        const hasKanbanElement = document.querySelector('.kanban, [data-kanban], .kanban-board, .kanban-container') !== null;
        return hasKanbanInUrl || hasKanbanElement;
    }

    function startMonitoring() {
        // Initial check
        setTimeout(() => {
            checkForInjectedContent();
            checkForCards();
        }, 1000);

        // Monitor for new cards
        observeDOMChanges();

        // Check again after page fully loads
        window.addEventListener('load', () => {
            setTimeout(() => {
                log('Page fully loaded - final check...', STYLES.info);
                checkForInjectedContent();
                checkForCards();
            }, 2000);
        });
    }

    function checkForInjectedContent() {
        log('Checking for injected content...', STYLES.info);

        // Look for our metadata elements
        const selectors = [
            '.kanbanlooksgood-metadata',
            '.klg-metadata',
            '[class*="kanbanlooksgood"]',
            '[data-kanbanlooksgood]'
        ];

        let found = false;
        selectors.forEach(selector => {
            const elements = document.querySelectorAll(selector);
            if (elements.length > 0) {
                found = true;
                log(`✓ Found ${elements.length} element(s) with selector: ${selector}`, STYLES.success);
                elements.forEach((el, idx) => {
                    log(`  Element ${idx + 1}:`, STYLES.info);
                    console.log('    - HTML:', el.outerHTML.substring(0, 200));
                    console.log('    - Text:', el.textContent.substring(0, 100));
                    console.log('    - Parent:', el.parentElement ? el.parentElement.tagName : 'none');
                });
            }
        });

        if (!found) {
            log('✗ No injected content found', STYLES.error);
            log('This suggests the hook is not injecting content', STYLES.warning);
        }

        return found;
    }

    function checkForCards() {
        log('Checking for Kanban cards...', STYLES.info);

        const cardSelectors = [
            '.kanban-card',
            '.kanban-item',
            '[data-item-id]',
            '.card',
            'div[class*="card"]'
        ];

        let totalCards = 0;
        cardSelectors.forEach(selector => {
            const cards = document.querySelectorAll(selector);
            if (cards.length > 0) {
                totalCards += cards.length;
                log(`Found ${cards.length} card(s) with selector: ${selector}`, STYLES.info);

                // Check first few cards for our content
                Array.from(cards).slice(0, 3).forEach((card, idx) => {
                    const hasContent = checkCardContent(card, idx + 1);
                    if (!hasContent && idx === 0) {
                        log(`  Card ${idx + 1} sample HTML (first 300 chars):`, STYLES.info);
                        console.log('    ' + card.innerHTML.substring(0, 300));
                    }
                });
            }
        });

        if (totalCards === 0) {
            log('✗ No Kanban cards found', STYLES.warning);
            log('Available elements:', STYLES.info);
            console.log({
                divs: document.querySelectorAll('div').length,
                elementsWithKanban: document.querySelectorAll('[class*="kanban"]').length,
                elementsWithCard: document.querySelectorAll('[class*="card"]').length
            });
        } else {
            log(`Total cards found: ${totalCards}`, STYLES.success);
        }

        return totalCards;
    }

    function checkCardContent(card, cardNum) {
        const html = card.innerHTML || '';
        const text = card.textContent || '';

        // Look for our markers
        const markers = ['kanbanlooksgood', 'priority', 'planned-duration', 'metadata'];
        const foundMarkers = markers.filter(m =>
            html.toLowerCase().includes(m.toLowerCase()) ||
            text.toLowerCase().includes(m.toLowerCase())
        );

        const hasOurContent = foundMarkers.length > 0;

        if (hasOurContent) {
            log(`  Card ${cardNum}: ✓ Has our content (${foundMarkers.join(', ')})`, STYLES.success);
        } else {
            log(`  Card ${cardNum}: ✗ No our content found`, STYLES.warning);
        }

        return hasOurContent;
    }

    function observeDOMChanges() {
        const observer = new MutationObserver((mutations) => {
            let newCards = false;

            mutations.forEach(mutation => {
                mutation.addedNodes.forEach(node => {
                    if (node.nodeType === 1) { // Element
                        const isCard = node.classList && (
                            node.classList.contains('card') ||
                            node.classList.contains('kanban-card') ||
                            node.querySelector && node.querySelector('.card, .kanban-card')
                        );

                        if (isCard) {
                            newCards = true;
                            log('New card detected!', STYLES.info);
                            setTimeout(() => checkCardContent(node, 'new'), 500);
                        }
                    }
                });
            });

            if (newCards) {
                setTimeout(() => {
                    log('New cards added - rechecking...', STYLES.info);
                    checkForInjectedContent();
                }, 1000);
            }
        });

        observer.observe(document.body || document.documentElement, {
            childList: true,
            subtree: true
        });

        log('DOM observer started', STYLES.info);
    }

    // Check for CSS
    function checkCSS() {
        const stylesheets = Array.from(document.styleSheets);
        const ourCSS = stylesheets.find(sheet => {
            try {
                return sheet.href && sheet.href.includes('kanbanlooksgood');
            } catch (e) {
                return false;
            }
        });

        if (ourCSS) {
            log(`✓ CSS loaded: ${ourCSS.href}`, STYLES.success);
        } else {
            log('✗ CSS not found in loaded stylesheets', STYLES.warning);
        }
    }

    // Export debug functions
    window.KanbanLooksGoodDebug = {
        checkContent: checkForInjectedContent,
        checkCards: checkForCards,
        checkCSS: checkCSS,
        info: function () {
            log('Debug Info:', STYLES.header);
            console.log({
                url: window.location.href,
                kanbanElements: document.querySelectorAll('[class*="kanban"]').length,
                cardElements: document.querySelectorAll('[class*="card"]').length,
                metadataBars: document.querySelectorAll('.kanbanlooksgood-metadata, .klg-metadata').length,
                stylesheets: document.styleSheets.length
            });
        }
    };

    // Start when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Check CSS after a delay
    setTimeout(checkCSS, 500);

})();

