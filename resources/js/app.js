import './bootstrap';
import 'bootstrap/dist/js/bootstrap.bundle.min.js';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

document.addEventListener('DOMContentLoaded', () => {
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
            state.minBound = Math.min(state.peek, state.totalScroll);
            state.maxBound = Math.max(state.minBound, state.totalScroll - state.peek);

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

        const clampScroll = (value) => clamp(value, state.minBound, state.maxBound);

        const scrollByStep = (direction) => {
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

        const stopPointerDrag = () => {
            if (!isPointerDragging) {
                return;
            }

            isPointerDragging = false;
            slider.classList.remove('brand-slider--is-dragging');

            if (activePointerId !== null && viewport.hasPointerCapture(activePointerId)) {
                viewport.releasePointerCapture(activePointerId);
            }

            activePointerId = null;

            if (dragMoved) {
                const suppressClick = (event) => {
                    event.stopPropagation();
                    event.preventDefault();
                };

                viewport.addEventListener('click', suppressClick, { capture: true, once: true });
            }

            dragMoved = false;
            updateNavState();
        };

        viewport.addEventListener('pointerdown', (event) => {
            if (event.pointerType === 'mouse' && event.button !== 0) {
                return;
            }

            if (event.target.closest('a, button, input, textarea, select')) {
                stopPointerDrag();
                return;
            }

            event.preventDefault();

            isPointerDragging = true;
            dragStartX = event.clientX;
            dragStartScrollLeft = viewport.scrollLeft;
            activePointerId = event.pointerId;
            dragMoved = false;

            viewport.setPointerCapture(event.pointerId);
            slider.classList.add('brand-slider--is-dragging');
        });

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
        });

        viewport.addEventListener('pointerup', (event) => {
            if (event.pointerId === activePointerId) {
                stopPointerDrag();
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

document.addEventListener('DOMContentLoaded', () => {
    const phoneForm = document.querySelector('[data-phone-form]');

    if (!phoneForm) {
        return;
    }

    const sendBtn = phoneForm.querySelector('[data-phone-send]');
    const phoneInput = phoneForm.querySelector('input[name="phone"]');
    const statusEl = phoneForm.querySelector('[data-phone-status]');
    const sendUrl = phoneForm.dataset.sendUrl;
    const csrfToken = document.head.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const textEmpty = phoneForm.dataset.textEmpty ?? 'Enter phone number before requesting a code.';
    const textSending = phoneForm.dataset.textSending ?? 'Sending...';
    const textSuccess = phoneForm.dataset.textSuccess ?? 'Code sent. Check SMS.';
    const textError = phoneForm.dataset.textError ?? 'Something went wrong. Try again later.';
    const textCooldown = phoneForm.dataset.textCooldown ?? 'Resend in :seconds s';
    const cooldownSeconds = Number(phoneForm.dataset.cooldownSeconds ?? 60);

    if (!sendBtn || !phoneInput || !sendUrl) {
        return;
    }

    const defaultButtonText = sendBtn.textContent.trim();
    let cooldownTimerId = null;
    let cooldownRemaining = 0;

    const isCoolingDown = () => cooldownTimerId !== null;

    const stopCooldown = () => {
        if (cooldownTimerId !== null) {
            window.clearInterval(cooldownTimerId);
            cooldownTimerId = null;
        }
        cooldownRemaining = 0;
        sendBtn.disabled = false;
        sendBtn.textContent = defaultButtonText;
    };

    const startCooldown = () => {
        if (!cooldownSeconds || cooldownSeconds <= 0) {
            return;
        }

        cooldownRemaining = cooldownSeconds;
        sendBtn.disabled = true;

        const renderCountdown = () => {
            const label = textCooldown.replace(':seconds', cooldownRemaining.toString());
            sendBtn.textContent = label;
        };

        renderCountdown();

        cooldownTimerId = window.setInterval(() => {
            cooldownRemaining -= 1;

            if (cooldownRemaining <= 0) {
                stopCooldown();
                return;
            }

            renderCountdown();
        }, 1000);
    };

    const setStatus = (message, isError = false) => {
        if (!statusEl) {
            return;
        }

        statusEl.textContent = message;
        statusEl.classList.toggle('text-danger', isError);
    };

    sendBtn.addEventListener('click', async () => {
        const phone = phoneInput.value.trim();

        if (!phone) {
            setStatus(textEmpty, true);
            phoneInput.focus();
            return;
        }

        sendBtn.disabled = true;
        sendBtn.textContent = textSending;

        try {
            const response = await fetch(sendUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken ?? '',
                },
                body: JSON.stringify({ phone }),
            });

            const payload = await response.json().catch(() => ({}));

            if (!response.ok || !payload.success) {
                throw new Error(payload.message || textError);
            }

            setStatus(payload.message || textSuccess, false);
            startCooldown();
        } catch (error) {
            setStatus(error.message || textError, true);
        } finally {
            if (!isCoolingDown()) {
                sendBtn.disabled = false;
                sendBtn.textContent = defaultButtonText;
            }
        }
    });
});
