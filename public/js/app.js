// Personal Financial Tracker - Main JavaScript

// Toast Notification System
const Toast = {
    show(message, type = 'info', duration = 3000) {
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.textContent = message;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.style.animation = 'slideIn 0.3s ease reverse';
            setTimeout(() => toast.remove(), 300);
        }, duration);
    },
    
    success(message) {
        this.show(message, 'success');
    },
    
    error(message) {
        this.show(message, 'error');
    },
    
    info(message) {
        this.show(message, 'info');
    }
};

// Loading State Manager
const LoadingState = {
    show(element) {
        element.classList.add('loading');
        element.disabled = true;
    },
    
    hide(element) {
        element.classList.remove('loading');
        element.disabled = false;
    }
};

// Form Auto-Save (for drafts)
class AutoSave {
    constructor(formId, storageKey, interval = 30000) {
        this.form = document.getElementById(formId);
        this.storageKey = storageKey;
        this.interval = interval;
        
        if (this.form) {
            this.init();
        }
    }
    
    init() {
        // Load saved data
        this.loadDraft();
        
        // Auto-save on input
        this.form.addEventListener('input', () => {
            clearTimeout(this.saveTimeout);
            this.saveTimeout = setTimeout(() => this.saveDraft(), 2000);
        });
        
        // Clear draft on submit
        this.form.addEventListener('submit', () => this.clearDraft());
        
        // Show restore option if draft exists
        if (this.hasDraft()) {
            this.showRestoreOption();
        }
    }
    
    saveDraft() {
        const formData = new FormData(this.form);
        const data = {};
        
        for (let [key, value] of formData.entries()) {
            if (key !== '_token') {
                data[key] = value;
            }
        }
        
        localStorage.setItem(this.storageKey, JSON.stringify(data));
        Toast.info('Draft saved');
    }
    
    loadDraft() {
        const draft = localStorage.getItem(this.storageKey);
        if (!draft) return;
        
        const data = JSON.parse(draft);
        for (let [key, value] of Object.entries(data)) {
            const input = this.form.querySelector(`[name="${key}"]`);
            if (input) {
                input.value = value;
            }
        }
    }
    
    clearDraft() {
        localStorage.removeItem(this.storageKey);
    }
    
    hasDraft() {
        return localStorage.getItem(this.storageKey) !== null;
    }
    
    showRestoreOption() {
        const banner = document.createElement('div');
        banner.className = 'bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded mb-4';
        banner.innerHTML = `
            <p class="text-sm">
                <strong>Draft found!</strong> 
                <button type="button" class="underline ml-2" onclick="location.reload()">Restore</button>
                <button type="button" class="underline ml-2" onclick="this.closest('div').remove(); localStorage.removeItem('${this.storageKey}')">Discard</button>
            </p>
        `;
        this.form.insertBefore(banner, this.form.firstChild);
    }
}

// Keyboard Shortcuts
document.addEventListener('keydown', (e) => {
    // Ctrl/Cmd + K: Quick search
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        const searchInput = document.querySelector('input[type="search"]');
        if (searchInput) searchInput.focus();
    }
    
    // Ctrl/Cmd + N: New transaction (if on transactions page)
    if ((e.ctrlKey || e.metaKey) && e.key === 'n') {
        if (window.location.pathname.includes('/transactions')) {
            e.preventDefault();
            window.location.href = '/transactions/create';
        }
    }
    
    // Escape: Close modals
    if (e.key === 'Escape') {
        const modals = document.querySelectorAll('.modal');
        modals.forEach(modal => modal.classList.add('hidden'));
    }
});

// Confirm Delete Actions
document.addEventListener('DOMContentLoaded', () => {
    const deleteForms = document.querySelectorAll('form[method="POST"]');
    
    deleteForms.forEach(form => {
        const methodInput = form.querySelector('input[name="_method"]');
        if (methodInput && methodInput.value === 'DELETE') {
            form.addEventListener('submit', (e) => {
                if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
                    e.preventDefault();
                }
            });
        }
    });
});

// AJAX Form Submission
function submitFormAjax(formId, successCallback) {
    const form = document.getElementById(formId);
    if (!form) return;
    
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const submitBtn = form.querySelector('button[type="submit"]');
        LoadingState.show(submitBtn);
        
        try {
            const formData = new FormData(form);
            const response = await fetch(form.action, {
                method: form.method,
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });
            
            const data = await response.json();
            
            if (response.ok) {
                Toast.success(data.message || 'Success!');
                if (successCallback) successCallback(data);
            } else {
                Toast.error(data.message || 'An error occurred');
            }
        } catch (error) {
            Toast.error('Network error. Please try again.');
        } finally {
            LoadingState.hide(submitBtn);
        }
    });
}

// Number Formatting
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount);
}

function formatNumber(number) {
    return new Intl.NumberFormat('en-US').format(number);
}

// Date Formatting
function formatDate(dateString) {
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    }).format(date);
}

// Debounce Function
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

// Live Search
function initLiveSearch(inputId, resultsId, searchUrl) {
    const input = document.getElementById(inputId);
    const results = document.getElementById(resultsId);
    
    if (!input || !results) return;
    
    const search = debounce(async (query) => {
        if (query.length < 2) {
            results.innerHTML = '';
            return;
        }
        
        try {
            const response = await fetch(`${searchUrl}?q=${encodeURIComponent(query)}`);
            const data = await response.json();
            
            results.innerHTML = data.results.map(item => `
                <div class="p-2 hover:bg-gray-100 cursor-pointer">
                    ${item.name}
                </div>
            `).join('');
        } catch (error) {
            console.error('Search error:', error);
        }
    }, 300);
    
    input.addEventListener('input', (e) => search(e.target.value));
}

// Smooth Scroll to Element
function scrollToElement(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        element.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

// Copy to Clipboard
async function copyToClipboard(text) {
    try {
        await navigator.clipboard.writeText(text);
        Toast.success('Copied to clipboard!');
    } catch (error) {
        Toast.error('Failed to copy');
    }
}

// Export Table to CSV
function exportTableToCSV(tableId, filename = 'export.csv') {
    const table = document.getElementById(tableId);
    if (!table) return;
    
    const rows = Array.from(table.querySelectorAll('tr'));
    const csv = rows.map(row => {
        const cells = Array.from(row.querySelectorAll('th, td'));
        return cells.map(cell => `"${cell.textContent.trim()}"`).join(',');
    }).join('\n');
    
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    a.click();
    window.URL.revokeObjectURL(url);
}

// Initialize tooltips
function initTooltips() {
    const tooltips = document.querySelectorAll('[data-tooltip]');
    
    tooltips.forEach(element => {
        const text = element.getAttribute('data-tooltip');
        const tooltip = document.createElement('span');
        tooltip.className = 'tooltiptext';
        tooltip.textContent = text;
        
        element.classList.add('tooltip');
        element.appendChild(tooltip);
    });
}

// Mobile Menu Toggle
function initMobileMenu() {
    const menuButton = document.getElementById('mobile-menu-button');
    const mobileMenu = document.getElementById('mobile-menu');
    
    if (menuButton && mobileMenu) {
        menuButton.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    initTooltips();
    initMobileMenu();
    
    // Show success/error messages from session
    const successMessage = document.querySelector('[data-success-message]');
    if (successMessage) {
        Toast.success(successMessage.textContent);
    }
    
    const errorMessage = document.querySelector('[data-error-message]');
    if (errorMessage) {
        Toast.error(errorMessage.textContent);
    }
});

// Export functions for global use
window.Toast = Toast;
window.LoadingState = LoadingState;
window.AutoSave = AutoSave;
window.formatCurrency = formatCurrency;
window.formatNumber = formatNumber;
window.formatDate = formatDate;
window.copyToClipboard = copyToClipboard;
window.exportTableToCSV = exportTableToCSV;
window.scrollToElement = scrollToElement;
