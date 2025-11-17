import './bootstrap';
import 'bootstrap/dist/js/bootstrap.bundle.min.js';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

const onDocumentReady = (callback) => {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', callback, { once: true });
    } else {
        callback();
    }
};

onDocumentReady(() => {
    const sliders = document.querySelectorAll('[data-slider]');

    const MIN_CARD_WIDTH_DESKTOP = 160;
    const MIN_CARD_WIDTH_MOBILE = 180;
    const MAX_CARD_WIDTH = 360;
    const VISIBLE_FULL_CARDS_DESKTOP = 7;
    const VISIBLE_FULL_CARDS_MOBILE = 3;
    const HALF_CARD_RATIO = 0.5;
    const MOBILE_BREAKPOINT = 768;

    const clamp = (value, min, max) => Math.min(max, Math.max(min, value));

    sliders.forEach((slider) => {
        const viewport = slider.querySelector('[data-slider-viewport]');
        const track = slider.querySelector('[data-slider-track]');
        const prevBtn = slider.querySelector('[data-slider-prev]');
        const nextBtn = slider.querySelector('[data-slider-next]');

        if (!viewport || !track) {
            return;
        }

        const panelSelector = '.brand-slider__panel';
        let panels = Array.from(track.querySelectorAll(panelSelector));

        if (panels.length > 1 && !track.dataset.sliderHasClones) {
            const firstClone = panels[0].cloneNode(true);
            firstClone.dataset.sliderClone = 'repeat-tail';
            firstClone.setAttribute('aria-hidden', 'true');

            const lastClone = panels[panels.length - 1].cloneNode(true);
            lastClone.dataset.sliderClone = 'repeat-head';
            lastClone.setAttribute('aria-hidden', 'true');

            track.insertBefore(lastClone, track.firstChild);
            track.appendChild(firstClone);
            track.dataset.sliderHasClones = 'true';
            panels = Array.from(track.querySelectorAll(panelSelector));
        }

        const realPanels = panels.filter((panel) => !panel.dataset.sliderClone);
        if (!realPanels.length) {
            return;
        }

        const attachPanelLinks = () => {
            realPanels.forEach((panel) => {
                if (panel.dataset.sliderLinkReady === '1') {
                    return;
                }
                panel.dataset.sliderLinkReady = '1';

                panel.addEventListener('click', (event) => {
                    if (event.defaultPrevented || event.target.closest('a, button, input, textarea, select')) {
                        return;
                    }

                    if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || event.button !== 0) {
                        return;
                    }

                    const anchor = panel.querySelector('a[href]');
                    if (anchor) {
                        event.preventDefault();
                        window.location.href = anchor.href;
                    }
                });
            });
        };
        attachPanelLinks();

        const state = {
            initialized: false,
            gap: 0,
            panelWidth: 0,
            peek: 0,
            step: 0,
            totalScroll: 0,
            minBound: 0,
            maxBound: 0,
            visibleCards: VISIBLE_FULL_CARDS_DESKTOP,
        };

        const applyPeekSpacing = () => {
            const peekValue = `${state.peek}px`;
            viewport.style.paddingLeft = peekValue;
            viewport.style.paddingRight = peekValue;
            viewport.style.scrollPaddingLeft = peekValue;
            viewport.style.scrollPaddingRight = peekValue;
            track.style.marginLeft = '0px';
            track.style.marginRight = '0px';
        };

        const measurePanels = () => {
            const viewportWidth = viewport.clientWidth;
            const styles = window.getComputedStyle(track);
            const gap = parseFloat(styles.columnGap || styles.gap || styles.rowGap || '0') || 0;

            state.gap = gap;

            if (viewportWidth <= 0) {
                return;
            }

            const isMobile = viewportWidth < MOBILE_BREAKPOINT;
            const visibleCards = isMobile ? VISIBLE_FULL_CARDS_MOBILE : VISIBLE_FULL_CARDS_DESKTOP;
            const effectiveSlots = visibleCards + HALF_CARD_RATIO * 2;
            const rawWidth = (viewportWidth - gap * Math.max(visibleCards - 1, 0)) / effectiveSlots;
            const minWidth = isMobile ? MIN_CARD_WIDTH_MOBILE : MIN_CARD_WIDTH_DESKTOP;
            const cardWidth = clamp(rawWidth, minWidth, MAX_CARD_WIDTH);

            state.visibleCards = visibleCards;
            state.panelWidth = cardWidth;
            state.step = cardWidth + gap;
            state.peek = cardWidth * HALF_CARD_RATIO;

            slider.style.setProperty('--brand-slider-card-width', `${cardWidth}px`);
            applyPeekSpacing();

            state.totalScroll = Math.max(0, track.scrollWidth - viewport.clientWidth);
            state.minBound = 0;
            state.maxBound = state.totalScroll;

            updateNavState();
        };

        const updateNavState = () => {
            const tolerance = 2;
            const availableScroll = state.maxBound - state.minBound;
            const hasScroll = availableScroll > tolerance;

            if (prevBtn) {
                prevBtn.disabled = !hasScroll || viewport.scrollLeft <= state.minBound + tolerance;
            }

            if (nextBtn) {
                nextBtn.disabled = !hasScroll || viewport.scrollLeft >= state.maxBound - tolerance;
            }
        };

        const realPanWidth = () => state.panelWidth + state.gap;

        const normalizeLoopScroll = (value) => {
            if (!panels.length) {
                return value;
            }
            const totalWidth = track.scrollWidth;
            if (totalWidth <= 0) {
                return value;
            }

            const length = realPanWidth() * realPanels.length;
            if (length <= 0) {
                return value;
            }

            if (value < 0) {
                return value + length;
            }
            if (value > length) {
                return value - length;
            }
            return value;
        };

        const clampScroll = (value) => normalizeLoopScroll(value);

        let momentumId = null;
        let momentumLastTime = null;

        const stopMomentumScroll = () => {
            if (momentumId !== null) {
                cancelAnimationFrame(momentumId);
                momentumId = null;
            }
            momentumLastTime = null;
        };

        const startMomentumScroll = (initialVelocity) => {
            const MIN_VELOCITY = 0.02; // px per ms (~20px/s)
            if (!isFinite(initialVelocity) || Math.abs(initialVelocity) < MIN_VELOCITY) {
                updateNavState();
                return;
            }

            stopMomentumScroll();

            const FRICTION = 0.0012; // higher = faster stop
            const MAX_DURATION = 2000; // ms
            let velocity = initialVelocity;
            let elapsed = 0;

            const stepMomentum = (timestamp) => {
                if (momentumLastTime === null) {
                    momentumLastTime = timestamp;
                    momentumId = requestAnimationFrame(stepMomentum);
                    return;
                }

                const dt = timestamp - momentumLastTime;
                momentumLastTime = timestamp;
                elapsed += dt;

                const delta = velocity * dt;
                const nextScroll = clampScroll(viewport.scrollLeft + delta);
                viewport.scrollLeft = nextScroll;

                const decel = FRICTION * dt * Math.sign(velocity || 1);
                velocity -= decel;

                const velocityMagnitude = Math.abs(velocity);
                const reachedEdge = nextScroll === state.minBound || nextScroll === state.maxBound;
                if (velocityMagnitude < MIN_VELOCITY || elapsed >= MAX_DURATION || reachedEdge) {
                    stopMomentumScroll();
                    updateNavState();
                    return;
                }

                momentumId = requestAnimationFrame(stepMomentum);
            };

            momentumId = requestAnimationFrame(stepMomentum);
        };

        const scrollByStep = (direction) => {
            stopMomentumScroll();
            if (!state.step) {
                measurePanels();
            }

            const rawTarget = viewport.scrollLeft + direction * state.step;
            const target = clampScroll(rawTarget);

            if (Math.abs(target - viewport.scrollLeft) < 1) {
                return;
            }

            viewport.scrollTo({
                left: target,
                behavior: 'smooth',
            });
        };

        prevBtn?.addEventListener('click', (event) => {
            event.preventDefault();
            scrollByStep(-1);
        });

        nextBtn?.addEventListener('click', (event) => {
            event.preventDefault();
            scrollByStep(1);
        });

        let rafId = null;
        const onScroll = () => {
            if (rafId !== null) {
                return;
            }

            rafId = window.requestAnimationFrame(() => {
                rafId = null;
                updateNavState();
            });
        };

        viewport.addEventListener('scroll', onScroll, { passive: true });

        let isPointerDragging = false;
        let dragStartX = 0;
        let dragStartScrollLeft = 0;
        let activePointerId = null;
        let dragMoved = false;
        let pointerSamples = [];

        const recordPointerSample = () => {
            const now = performance.now();
            pointerSamples.push({
                time: now,
                scrollLeft: viewport.scrollLeft,
            });
            if (pointerSamples.length > 5) {
                pointerSamples.shift();
            }
        };

        const computePointerVelocity = () => {
            if (pointerSamples.length < 2) {
                return 0;
            }
            const first = pointerSamples[0];
            const last = pointerSamples[pointerSamples.length - 1];
            const elapsed = last.time - first.time;
            if (elapsed <= 0) {
                return 0;
            }

            return (last.scrollLeft - first.scrollLeft) / elapsed;
        };

        const stopPointerDrag = ({ applyMomentum = false } = {}) => {
            if (!isPointerDragging) {
                return;
            }

            isPointerDragging = false;
            slider.classList.remove('brand-slider--is-dragging');

            if (activePointerId !== null && viewport.hasPointerCapture(activePointerId)) {
                viewport.releasePointerCapture(activePointerId);
            }

            activePointerId = null;

            let momentumVelocity = 0;

            if (dragMoved) {
                const suppressClick = (event) => {
                    event.stopPropagation();
                    event.preventDefault();
                };

                viewport.addEventListener('click', suppressClick, { capture: true, once: true });
                momentumVelocity = computePointerVelocity();
            }

            dragMoved = false;
            pointerSamples = [];
            updateNavState();

            if (applyMomentum && momentumVelocity) {
                startMomentumScroll(momentumVelocity);
            }
        };

        const handlePointerDown = (event) => {
            if (event.pointerType === 'mouse' && event.button !== 0) {
                return;
            }

            const interactiveTarget = event.target.closest('button, input, textarea, select, [data-slider-no-drag]');
            if (interactiveTarget) {
                stopPointerDrag();
                return;
            }

            event.preventDefault();
            stopMomentumScroll();

            isPointerDragging = true;
            dragStartX = event.clientX;
            dragStartScrollLeft = viewport.scrollLeft;
            activePointerId = event.pointerId;
            dragMoved = false;
            pointerSamples = [];
            recordPointerSample();

            viewport.setPointerCapture(event.pointerId);
            slider.classList.add('brand-slider--is-dragging');
        };

        slider.addEventListener('pointerdown', handlePointerDown, { passive: false });
        viewport.addEventListener('pointerdown', handlePointerDown, { passive: false });

        viewport.addEventListener('pointermove', (event) => {
            if (!isPointerDragging || event.pointerId !== activePointerId) {
                return;
            }

            const deltaX = event.clientX - dragStartX;
            if (!dragMoved && Math.abs(deltaX) > 4) {
                dragMoved = true;
            }

            if (!dragMoved) {
                return;
            }

            event.preventDefault();
            viewport.scrollLeft = clampScroll(dragStartScrollLeft - deltaX);
            recordPointerSample();
        });

        viewport.addEventListener('pointerup', (event) => {
            if (event.pointerId === activePointerId) {
                stopPointerDrag({ applyMomentum: dragMoved });
            }
        });

        viewport.addEventListener('pointercancel', (event) => {
            if (event.pointerId === activePointerId) {
                stopPointerDrag();
            }
        });

        viewport.addEventListener('pointerleave', (event) => {
            if (event.pointerType === 'mouse' && !viewport.hasPointerCapture(event.pointerId)) {
                stopPointerDrag();
            }
        });

        const applyInitialOffset = () => {
            if (state.initialized) {
                updateNavState();
                return;
            }

            measurePanels();

            const totalPanels = realPanels.length;
            if (!totalPanels) {
                updateNavState();
                return;
            }

            const centerOffset = Math.max(state.visibleCards - VISIBLE_FULL_CARDS_MOBILE, 0);
            const initialIndex = centerOffset > 0 ? Math.floor(centerOffset / 2) : 0;
            const target = clampScroll(initialIndex * state.step + state.minBound);

            viewport.scrollLeft = target;

            state.initialized = true;
            updateNavState();
        };

        const handleResize = () => {
            measurePanels();
            viewport.scrollLeft = clampScroll(viewport.scrollLeft);
            updateNavState();
        };

        measurePanels();
        window.requestAnimationFrame(applyInitialOffset);
        updateNavState();

        window.addEventListener('resize', () => {
            window.requestAnimationFrame(handleResize);
        });

        window.addEventListener('load', handleResize);
        window.addEventListener('load', attachPanelLinks);
    });

    const MOBILE_FILTER_BREAKPOINT = 768;
    const filterCards = document.querySelectorAll('[data-filter-card]');

    const isMobileViewport = () => window.innerWidth < MOBILE_FILTER_BREAKPOINT;

    filterCards.forEach((card) => {
        if (card.dataset.filterMobileReady === '1') {
            return;
        }

        const toggleArea = card.querySelector('[data-filter-mobile-toggle]');
        const collapsible = card.querySelector('[data-filter-collapsible]');
        const closeBtn = card.querySelector('[data-filter-close-mobile]');

        if (!toggleArea || !collapsible) {
            return;
        }

        card.dataset.filterMobileReady = '1';

        const updateAriaExpanded = () => {
            const expanded = !isMobileViewport() || card.dataset.mobileExpanded === 'true';
            toggleArea.setAttribute('aria-expanded', expanded ? 'true' : 'false');
        };

        const setExpanded = (expanded) => {
            if (expanded) {
                card.dataset.mobileExpanded = 'true';
            } else {
                delete card.dataset.mobileExpanded;
            }
            updateAriaExpanded();
        };

        setExpanded(false);

        const openFilters = () => {
            if (!isMobileViewport()) {
                return;
            }

            if (card.dataset.mobileExpanded === 'true') {
                return;
            }

            setExpanded(true);
        };

        const closeFilters = () => {
            setExpanded(false);
        };

        toggleArea.addEventListener('click', () => {
            openFilters();
        });

        const firstControl = toggleArea.querySelector('input, select, textarea, button');
        if (firstControl) {
            firstControl.addEventListener('focus', () => {
                openFilters();
            });
        }

        closeBtn?.addEventListener('click', (event) => {
            if (!isMobileViewport()) {
                return;
            }

            event.preventDefault();
            closeFilters();
        });

        const handleResize = () => {
            if (!isMobileViewport()) {
                delete card.dataset.mobileExpanded;
            }
            updateAriaExpanded();
        };

        window.addEventListener('resize', handleResize);
        handleResize();
    });
});



const initCardMediaPreview = () => {
    const mediaBlocks = document.querySelectorAll('.brand-listing-card__media[data-photo-sources]');

    mediaBlocks.forEach((media) => {
        let sources = [];
        try {
            sources = JSON.parse(media.getAttribute('data-photo-sources') || '[]');
        } catch (error) {
            sources = [];
        }

        sources = Array.isArray(sources) ? sources.filter((src) => typeof src === 'string' && src.length > 0) : [];
        if (sources.length < 2) {
            return;
        }

        const img = media.querySelector('img');
        if (!img) {
            return;
        }

        img.dataset.currentIndex = '0';

        const setImageByIndex = (index) => {
            const normalized = Math.max(0, Math.min(sources.length - 1, index));
            if (img.dataset.currentIndex === String(normalized)) {
                return;
            }
            img.dataset.currentIndex = String(normalized);
            img.src = sources[normalized];
        };

        const computeIndexFromClientX = (clientX) => {
            const rect = media.getBoundingClientRect();
            const relative = (clientX - rect.left) / rect.width;
            const clamped = Math.max(0, Math.min(0.999, relative));
            return Math.floor(clamped * sources.length);
        };

        media.addEventListener('mousemove', (event) => {
            if (event.buttons > 0) {
                return;
            }
            const targetIndex = computeIndexFromClientX(event.clientX);
            setImageByIndex(targetIndex);
        });

        media.addEventListener('touchmove', (event) => {
            const touch = event.touches[0];
            if (!touch) {
                return;
            }
            const targetIndex = computeIndexFromClientX(touch.clientX);
            setImageByIndex(targetIndex);
        }, { passive: true });

        const resetPreview = () => setImageByIndex(0);
        media.addEventListener('mouseleave', resetPreview);
        media.addEventListener('touchend', resetPreview);
        media.addEventListener('touchcancel', resetPreview);
    });
};

const initOffcanvasGestures = () => {
    const canvases = document.querySelectorAll('.mobile-offcanvas');

    canvases.forEach((canvas) => {
        const isEnd = canvas.classList.contains('offcanvas-end');
        const isStart = canvas.classList.contains('offcanvas-start');
        if (!isEnd && !isStart) {
            return;
        }

        let startX = null;
        const threshold = 60;

        canvas.addEventListener('touchstart', (event) => {
            const touch = event.touches[0];
            if (!touch) {
                return;
            }
            startX = touch.clientX;
        }, { passive: true });

        canvas.addEventListener('touchmove', (event) => {
            if (startX === null) {
                return;
            }
            const touch = event.touches[0];
            if (!touch) {
                return;
            }
            const deltaX = touch.clientX - startX;
            const shouldClose = (isEnd && deltaX > threshold) || (isStart && deltaX < -threshold);

            if (shouldClose) {
                const instance = window.bootstrap ? window.bootstrap.Offcanvas.getInstance(canvas) : null;
                if (instance) {
                    instance.hide();
                }
                startX = null;
            }
        }, { passive: true });

        const reset = () => {
            startX = null;
        };

        canvas.addEventListener('touchend', reset);
        canvas.addEventListener('touchcancel', reset);
    });
};

onDocumentReady(() => {
    initCardMediaPreview();
    initOffcanvasGestures();
});
