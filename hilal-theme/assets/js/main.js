/**
 * Hilal Theme Main JavaScript
 */

(function() {
    'use strict';

    // Mobile menu toggle
    const menuToggle = document.querySelector('.menu-toggle');
    const navigation = document.querySelector('.main-navigation');

    if (menuToggle && navigation) {
        menuToggle.addEventListener('click', function() {
            navigation.classList.toggle('is-active');
            const expanded = this.getAttribute('aria-expanded') === 'true';
            this.setAttribute('aria-expanded', !expanded);
        });
    }

    // Subscribe form handling
    const subscribeForms = document.querySelectorAll('.subscribe-form');

    subscribeForms.forEach(function(form) {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();

            const emailInput = form.querySelector('input[name="email"]');
            const submitBtn = form.querySelector('button[type="submit"]');
            const email = emailInput.value.trim();

            if (!email) return;

            // Disable form
            emailInput.disabled = true;
            submitBtn.disabled = true;
            submitBtn.textContent = hilalData.strings.loading;

            try {
                const response = await fetch(hilalData.restUrl + 'subscribe', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': hilalData.nonce
                    },
                    body: JSON.stringify({ email: email })
                });

                const data = await response.json();

                if (data.success) {
                    emailInput.value = '';
                    showNotification(data.data.message || data.message, 'success');
                } else {
                    showNotification(data.message || hilalData.strings.error, 'error');
                }
            } catch (error) {
                console.error('Subscribe error:', error);
                showNotification(hilalData.strings.error, 'error');
            }

            // Re-enable form
            emailInput.disabled = false;
            submitBtn.disabled = false;
            submitBtn.textContent = hilalData.language === 'ar' ? 'اشترك' : 'Subscribe';
        });
    });

    // Notification system
    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.textContent = message;

        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 1rem 1.5rem;
            background: ${type === 'success' ? '#00a32a' : type === 'error' ? '#d63638' : '#2271b1'};
            color: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 10000;
            animation: slideIn 0.3s ease;
        `;

        // RTL support
        if (document.body.classList.contains('rtl')) {
            notification.style.right = 'auto';
            notification.style.left = '20px';
        }

        document.body.appendChild(notification);

        setTimeout(function() {
            notification.style.animation = 'slideOut 0.3s ease';
            setTimeout(function() {
                notification.remove();
            }, 300);
        }, 5000);
    }

    // Add animation keyframes
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes slideOut {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
        }
    `;
    document.head.appendChild(style);

    // Language cookie setter
    const langBtns = document.querySelectorAll('.lang-btn');
    langBtns.forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            const url = new URL(this.href);
            const lang = url.searchParams.get('lang');
            if (lang) {
                document.cookie = `hilal_lang=${lang};path=/;max-age=31536000`;
            }
        });
    });

    // Geolocation helper
    window.hilalGetLocation = function() {
        return new Promise(function(resolve, reject) {
            if (!navigator.geolocation) {
                reject(new Error('Geolocation not supported'));
                return;
            }

            navigator.geolocation.getCurrentPosition(
                function(position) {
                    resolve({
                        lat: position.coords.latitude,
                        lng: position.coords.longitude,
                        accuracy: position.coords.accuracy
                    });
                },
                function(error) {
                    let message = hilalData.strings.locationDenied;
                    switch (error.code) {
                        case error.PERMISSION_DENIED:
                            message = 'Location permission denied.';
                            break;
                        case error.POSITION_UNAVAILABLE:
                            message = 'Location information unavailable.';
                            break;
                        case error.TIMEOUT:
                            message = 'Location request timed out.';
                            break;
                    }
                    reject(new Error(message));
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 300000 // 5 minutes cache
                }
            );
        });
    };

    // API helper
    window.hilalAPI = {
        get: async function(endpoint) {
            const response = await fetch(hilalData.restUrl + endpoint, {
                headers: {
                    'X-WP-Nonce': hilalData.nonce
                }
            });
            return response.json();
        },

        post: async function(endpoint, data) {
            const response = await fetch(hilalData.restUrl + endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': hilalData.nonce
                },
                body: JSON.stringify(data)
            });
            return response.json();
        }
    };

    // Expose showNotification globally
    window.hilalShowNotification = showNotification;

    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(function(anchor) {
        anchor.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href === '#') return;

            const target = document.querySelector(href);
            if (target) {
                e.preventDefault();
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Initialize countdown timers
    function initCountdowns() {
        const countdowns = document.querySelectorAll('[data-countdown]');

        countdowns.forEach(function(el) {
            const targetDate = new Date(el.dataset.countdown);

            function updateCountdown() {
                const now = new Date();
                const diff = targetDate - now;

                if (diff <= 0) {
                    el.textContent = hilalData.language === 'ar' ? 'الآن!' : 'Now!';
                    return;
                }

                const days = Math.floor(diff / (1000 * 60 * 60 * 24));
                const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));

                if (hilalData.language === 'ar') {
                    el.textContent = `${days} يوم ${hours} ساعة ${minutes} دقيقة`;
                } else {
                    el.textContent = `${days}d ${hours}h ${minutes}m`;
                }
            }

            updateCountdown();
            setInterval(updateCountdown, 60000); // Update every minute
        });
    }

    initCountdowns();

})();
