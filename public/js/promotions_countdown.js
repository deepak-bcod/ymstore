/**
 * YellowMarkets - Promotional Countdown Engine
 * Automatically ticks countdown timers on all product cards and product detail pages
 */
(function () {
    function padZero(num) {
        return num < 10 ? '0' + num : num;
    }

    function formatRemaining(totalSeconds) {
        if (totalSeconds <= 0) return 'Expired';

        var days = Math.floor(totalSeconds / 86400);
        var hours = Math.floor((totalSeconds % 86400) / 3600);
        var minutes = Math.floor((totalSeconds % 3600) / 60);
        var seconds = totalSeconds % 60;

        var timeStr = padZero(hours) + ':' + padZero(minutes) + ':' + padZero(seconds);
        if (days > 0) {
            return days + (days === 1 ? ' day ' : ' days ') + timeStr;
        }
        return timeStr;
    }

    function updateCountdowns() {
        var now = Math.floor(Date.now() / 1000);
        var timers = document.querySelectorAll('[data-promo-ends-at]');

        for (var i = 0; i < timers.length; i++) {
            var el = timers[i];
            var endsAt = parseInt(el.getAttribute('data-promo-ends-at'), 10);
            if (isNaN(endsAt) || endsAt <= 0) continue;

            var diff = endsAt - now;
            var timeDisplayEl = el.querySelector('.countdown-time') || el;

            if (diff > 0) {
                var prefix = el.getAttribute('data-countdown-prefix') || 'Ends in ';
                timeDisplayEl.innerText = prefix + formatRemaining(diff);
            } else {
                timeDisplayEl.innerText = 'Offer ended';
                // Remove promotional visual badges if offer has expired
                var card = el.closest('.product-item');
                if (card) {
                    var badges = card.querySelectorAll('.promo-badge, .promo-discount-badge, .promo-pill-normal');
                    badges.forEach(function (b) { b.style.display = 'none'; });
                }
            }
        }
    }

    // Run every second
    setInterval(updateCountdowns, 1000);

    // Initial run
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', updateCountdowns);
    } else {
        updateCountdowns();
    }
})();
