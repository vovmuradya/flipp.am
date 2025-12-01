import './bootstrap';
import 'bootstrap/dist/js/bootstrap.bundle.min.js';
import './favorites';
import Splide from '@splidejs/splide';
import '@splidejs/splide/css';

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
    const sliders = document.querySelectorAll('[data-slider="auction"]');

    sliders.forEach((slider) => {
        const slides = slider.querySelectorAll('.brand-slider__panel');
        if (slides.length < 2) {
            return;
        }

        const splide = new Splide(slider, {
            type: 'loop',
            drag: 'free',
            snap: true,
            autoWidth: true,
            arrows: true,
            pagination: false,
            speed: 520,
            gap: 'clamp(18px, 2vw, 28px)',
            easing: 'cubic-bezier(0.4, 0.0, 0.2, 1)',
            autoplay: true,
            interval: 3200,
            pauseOnHover: true,
            pauseOnFocus: true,
            keyboard: 'global',
        });

        splide.mount();
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
