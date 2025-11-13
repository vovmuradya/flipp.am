@once
    @push('scripts')
        <script>
            (() => {
                if (window.__auctionCountdownInitialized) {
                    return;
                }
                window.__auctionCountdownInitialized = true;

                const localeStrings = {
                    expired: @json(__('Лот завершён')),
                    prefix: @json(__('Осталось')),
                    dayLabel: @json(__('д')),
                };

                const pad = (value, length = 2) => String(Math.max(0, value)).padStart(length, '0');

                function parseExpires(value) {
                    if (!value) {
                        return null;
                    }
                    const ts = Date.parse(value);
                    return Number.isFinite(ts) ? ts : null;
                }

                function computeParts(diffMillis) {
                    const totalSeconds = Math.floor(diffMillis / 1000);
                    const days = Math.floor(totalSeconds / 86400);
                    const hours = Math.floor((totalSeconds % 86400) / 3600);
                    const minutes = Math.floor((totalSeconds % 3600) / 60);
                    const seconds = totalSeconds % 60;

                    return { totalSeconds, days, hours, minutes, seconds };
                }

                function formatCompact(parts, dayLabel) {
                    if (parts.totalSeconds <= 0) {
                        return null;
                    }

                    if (parts.days > 0) {
                        return `${parts.days}${dayLabel} ${pad(parts.hours)}:${pad(parts.minutes)}:${pad(parts.seconds)}`;
                    }

                    const totalHours = Math.floor(parts.totalSeconds / 3600);
                    return `${pad(totalHours)}:${pad(parts.minutes)}:${pad(parts.seconds)}`;
                }

                function ensureTimestamp(el) {
                    if (el.dataset.countdownTs) {
                        return Number(el.dataset.countdownTs);
                    }
                    const ts = parseExpires(el.dataset.expires);
                    if (!ts) {
                        el.dataset.countdownState = 'invalid';
                        return null;
                    }
                    el.dataset.countdownTs = String(ts);
                    return ts;
                }

                function updateElement(el) {
                    const endTs = ensureTimestamp(el);
                    if (!endTs) {
                        return;
                    }

                    const now = Date.now();
                    const prefix = el.dataset.prefix ?? localeStrings.prefix;
                    const expiredText = el.dataset.expiredText ?? localeStrings.expired;
                    const dayLabel = el.dataset.dayLabel ?? localeStrings.dayLabel;
                    const diff = endTs - now;
                    const textNode = el.querySelector('[data-countdown-text]');
                    const units = {
                        days: el.querySelector('[data-countdown-unit="days"]'),
                        hours: el.querySelector('[data-countdown-unit="hours"]'),
                        minutes: el.querySelector('[data-countdown-unit="minutes"]'),
                        seconds: el.querySelector('[data-countdown-unit="seconds"]'),
                    };
                    const hasUnits = Object.values(units).some(Boolean);

                    if (diff <= 0) {
                        el.dataset.countdownState = 'expired';
                        if (textNode) {
                            textNode.textContent = expiredText;
                        }
                        if (hasUnits) {
                            Object.entries(units).forEach(([unit, node]) => {
                                if (node) {
                                    node.textContent = unit === 'days' ? '00' : '00';
                                }
                            });
                        }
                        return;
                    }

                    const parts = computeParts(diff);
                    el.dataset.countdownState = 'active';

                    if (textNode) {
                        const formatted = formatCompact(parts, dayLabel);
                        textNode.textContent = formatted
                            ? (prefix ? `${prefix}: ${formatted}` : formatted)
                            : expiredText;
                    }

                    if (hasUnits) {
                        if (units.days) {
                            units.days.textContent = pad(parts.days, Math.max(String(parts.days).length, 2));
                        }
                        if (units.hours) {
                            units.hours.textContent = pad(parts.hours);
                        }
                        if (units.minutes) {
                            units.minutes.textContent = pad(parts.minutes);
                        }
                        if (units.seconds) {
                            units.seconds.textContent = pad(parts.seconds);
                        }
                    }
                }

                function tick() {
                    document.querySelectorAll('[data-countdown]').forEach(updateElement);
                }

                document.addEventListener('visibilitychange', () => {
                    if (document.visibilityState === 'visible') {
                        tick();
                    }
                });

                tick();
                window.setInterval(tick, 1000);
            })();
        </script>
    @endpush
@endonce
