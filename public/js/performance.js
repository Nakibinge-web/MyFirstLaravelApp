/**
 * Performance Optimization Scripts
 * Handles lazy loading, image optimization, and performance monitoring
 */

// Lazy load images
document.addEventListener('DOMContentLoaded', function() {
    // Lazy loading for images
    if ('loading' in HTMLImageElement.prototype) {
        // Browser supports native lazy loading
        const images = document.querySelectorAll('img[data-src]');
        images.forEach(img => {
            img.src = img.dataset.src;
            img.removeAttribute('data-src');
        });
    } else {
        // Fallback for browsers that don't support lazy loading
        const script = document.createElement('script');
        script.src = 'https://cdnjs.cloudflare.com/ajax/libs/lazysizes/5.3.2/lazysizes.min.js';
        document.body.appendChild(script);
    }

    // Defer non-critical CSS
    const deferredStyles = document.querySelectorAll('link[data-defer]');
    deferredStyles.forEach(link => {
        link.rel = 'stylesheet';
        link.removeAttribute('data-defer');
    });

    // Preload critical resources
    preloadCriticalResources();

    // Monitor performance
    if (window.performance && window.performance.timing) {
        monitorPagePerformance();
    }
});

/**
 * Preload critical resources
 */
function preloadCriticalResources() {
    const criticalResources = [
        { href: '/css/app.css', as: 'style' },
        { href: '/js/app.js', as: 'script' },
    ];

    criticalResources.forEach(resource => {
        const link = document.createElement('link');
        link.rel = 'preload';
        link.href = resource.href;
        link.as = resource.as;
        document.head.appendChild(link);
    });
}

/**
 * Monitor page performance
 */
function monitorPagePerformance() {
    window.addEventListener('load', function() {
        setTimeout(function() {
            const perfData = window.performance.timing;
            const pageLoadTime = perfData.loadEventEnd - perfData.navigationStart;
            const connectTime = perfData.responseEnd - perfData.requestStart;
            const renderTime = perfData.domComplete - perfData.domLoading;

            // Log performance metrics (in production, send to analytics)
            if (pageLoadTime > 3000) {
                console.warn('Slow page load detected:', {
                    pageLoadTime: pageLoadTime + 'ms',
                    connectTime: connectTime + 'ms',
                    renderTime: renderTime + 'ms'
                });
            }

            // Store metrics for debugging
            window.performanceMetrics = {
                pageLoadTime,
                connectTime,
                renderTime,
                timestamp: new Date().toISOString()
            };
        }, 0);
    });
}

/**
 * Debounce function for performance optimization
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Throttle function for performance optimization
 */
function throttle(func, limit) {
    let inThrottle;
    return function(...args) {
        if (!inThrottle) {
            func.apply(this, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

/**
 * Optimize AJAX requests with caching
 */
const ajaxCache = new Map();

function cachedAjaxRequest(url, options = {}) {
    const cacheKey = url + JSON.stringify(options);
    const cacheDuration = options.cacheDuration || 5 * 60 * 1000; // 5 minutes default

    // Check cache
    if (ajaxCache.has(cacheKey)) {
        const cached = ajaxCache.get(cacheKey);
        if (Date.now() - cached.timestamp < cacheDuration) {
            return Promise.resolve(cached.data);
        }
    }

    // Make request
    return fetch(url, options)
        .then(response => response.json())
        .then(data => {
            // Store in cache
            ajaxCache.set(cacheKey, {
                data: data,
                timestamp: Date.now()
            });
            return data;
        });
}

/**
 * Clear AJAX cache
 */
function clearAjaxCache() {
    ajaxCache.clear();
}

/**
 * Prefetch links on hover
 */
document.addEventListener('DOMContentLoaded', function() {
    const prefetchLinks = document.querySelectorAll('a[data-prefetch]');
    
    prefetchLinks.forEach(link => {
        link.addEventListener('mouseenter', function() {
            const url = this.href;
            if (url && !ajaxCache.has(url)) {
                // Prefetch the page
                fetch(url, { method: 'GET' })
                    .then(response => response.text())
                    .catch(() => {}); // Silently fail
            }
        });
    });
});

/**
 * Service Worker registration for offline support (optional)
 */
if ('serviceWorker' in navigator && window.location.protocol === 'https:') {
    window.addEventListener('load', function() {
        navigator.serviceWorker.register('/sw.js')
            .then(registration => {
                console.log('ServiceWorker registered:', registration);
            })
            .catch(error => {
                console.log('ServiceWorker registration failed:', error);
            });
    });
}

// Export functions for use in other scripts
window.performanceUtils = {
    debounce,
    throttle,
    cachedAjaxRequest,
    clearAjaxCache
};
