// Form Validation Helper
document.addEventListener('DOMContentLoaded', function() {
    // Add real-time validation to all forms with data-validate attribute
    const forms = document.querySelectorAll('form[data-validate]');
    
    forms.forEach(form => {
        const inputs = form.querySelectorAll('input[required], textarea[required], select[required]');
        
        inputs.forEach(input => {
            // Validate on blur
            input.addEventListener('blur', function() {
                validateField(this);
            });
            
            // Clear error on input
            input.addEventListener('input', function() {
                clearFieldError(this);
            });
        });
        
        // Validate on submit
        form.addEventListener('submit', function(e) {
            let isValid = true;
            
            inputs.forEach(input => {
                if (!validateField(input)) {
                    isValid = false;
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                showNotification('Please fix the errors before submitting', 'error');
            }
        });
    });
});

function validateField(field) {
    const value = field.value.trim();
    const type = field.type;
    const name = field.name;
    
    // Clear previous error
    clearFieldError(field);
    
    // Required validation
    if (field.hasAttribute('required') && !value) {
        showFieldError(field, 'This field is required');
        return false;
    }
    
    // Email validation
    if (type === 'email' && value) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(value)) {
            showFieldError(field, 'Please enter a valid email address');
            return false;
        }
    }
    
    // Number validation
    if (type === 'number' && value) {
        const num = parseFloat(value);
        const min = field.getAttribute('min');
        const max = field.getAttribute('max');
        
        if (isNaN(num)) {
            showFieldError(field, 'Please enter a valid number');
            return false;
        }
        
        if (min !== null && num < parseFloat(min)) {
            showFieldError(field, `Value must be at least ${min}`);
            return false;
        }
        
        if (max !== null && num > parseFloat(max)) {
            showFieldError(field, `Value must be at most ${max}`);
            return false;
        }
    }
    
    // Date validation
    if (type === 'date' && value) {
        const date = new Date(value);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        if (field.hasAttribute('max') && field.getAttribute('max') === new Date().toISOString().split('T')[0]) {
            if (date > today) {
                showFieldError(field, 'Date cannot be in the future');
                return false;
            }
        }
        
        if (field.hasAttribute('min') && field.getAttribute('min') === new Date().toISOString().split('T')[0]) {
            if (date < today) {
                showFieldError(field, 'Date cannot be in the past');
                return false;
            }
        }
    }
    
    // Password confirmation
    if (name === 'password_confirmation') {
        const password = document.querySelector('input[name="password"]');
        if (password && value !== password.value) {
            showFieldError(field, 'Passwords do not match');
            return false;
        }
    }
    
    // Amount validation (for financial inputs)
    if (name === 'amount' && value) {
        const amount = parseFloat(value);
        if (amount <= 0) {
            showFieldError(field, 'Amount must be greater than 0');
            return false;
        }
    }
    
    return true;
}

function showFieldError(field, message) {
    field.classList.add('border-red-500');
    
    // Create or update error message
    let errorDiv = field.parentElement.querySelector('.field-error');
    if (!errorDiv) {
        errorDiv = document.createElement('p');
        errorDiv.className = 'field-error text-red-500 text-xs italic mt-1';
        field.parentElement.appendChild(errorDiv);
    }
    errorDiv.textContent = message;
}

function clearFieldError(field) {
    field.classList.remove('border-red-500');
    const errorDiv = field.parentElement.querySelector('.field-error');
    if (errorDiv) {
        errorDiv.remove();
    }
}

function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 ${
        type === 'success' ? 'bg-green-500' : 'bg-red-500'
    } text-white`;
    notification.textContent = message;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Sanitize input to prevent XSS
function sanitizeInput(input) {
    const div = document.createElement('div');
    div.textContent = input;
    return div.innerHTML;
}
