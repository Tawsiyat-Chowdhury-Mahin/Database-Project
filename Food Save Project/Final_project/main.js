// Main JavaScript for Food Waste Reduction System

document.addEventListener('DOMContentLoaded', function() {
    // Auto-dismiss alerts after 5 seconds
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            if (alert.classList.contains('alert-dismissible')) {
                const closeButton = alert.querySelector('.btn-close');
                if (closeButton) {
                    closeButton.click();
                }
            }
        });
    }, 5000);
    
    // Form validation and loading states
    const forms = document.querySelectorAll('form');
    forms.forEach(function(form) {
        form.addEventListener('submit', function() {
            const submitButton = this.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.disabled = true;
                const originalHTML = submitButton.innerHTML;
                submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...';
                
                // Store original content to restore later
                submitButton.dataset.originalHTML = originalHTML;
            }
        });
    });
    
    // Mark notification as read
    const notificationItems = document.querySelectorAll('.notification-item');
    notificationItems.forEach(function(item) {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            
            const notificationId = this.dataset.id;
            if (notificationId) {
                markNotificationAsRead(notificationId, this);
            }
        });
    });
    
    // Update notification badge count
    updateNotificationBadge();
    
    // Real-time updates for dashboard
    if (window.location.pathname.includes('dashboard.php')) {
        setInterval(updateDashboardStats, 30000); // Update every 30 seconds
    }
    
    // Confirm destructive actions
    const deleteButtons = document.querySelectorAll('.btn-delete, .btn-danger[type="submit"]');
    deleteButtons.forEach(function(button) {
        button.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to perform this action?')) {
                e.preventDefault();
                return false;
            }
        });
    });
    
    // Toggle password visibility
    const togglePasswordButtons = document.querySelectorAll('.toggle-password');
    togglePasswordButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            const passwordInput = this.previousElementSibling;
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                this.innerHTML = '<i class="fas fa-eye-slash"></i>';
            } else {
                passwordInput.type = 'password';
                this.innerHTML = '<i class="fas fa-eye"></i>';
            }
        });
    });
    
    // Auto-format phone numbers
    const phoneInputs = document.querySelectorAll('input[type="tel"]');
    phoneInputs.forEach(function(input) {
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 3 && value.length <= 6) {
                value = value.replace(/(\d{3})(\d+)/, '$1-$2');
            } else if (value.length > 6) {
                value = value.replace(/(\d{3})(\d{3})(\d+)/, '$1-$2-$3');
            }
            e.target.value = value;
        });
    });
});

// Mark notification as read
function markNotificationAsRead(notificationId, element) {
    fetch(`/food_waste_system_php/api/notifications/mark-read.php?id=${notificationId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove 'New' badge
                const badge = element.querySelector('.badge');
                if (badge) {
                    badge.remove();
                }
                
                // Remove background highlight
                element.classList.remove('bg-light');
                
                // Update notification count
                updateNotificationBadge();
            }
        })
        .catch(error => console.error('Error:', error));
}

// Update notification badge count
function updateNotificationBadge() {
    fetch('/food_waste_system_php/api/notifications/count.php')
        .then(response => response.json())
        .then(data => {
            const badge = document.querySelector('.notification-badge');
            if (data.count > 0) {
                if (badge) {
                    badge.textContent = data.count;
                } else {
                    // Create badge if it doesn't exist
                    const bellIcon = document.querySelector('.fa-bell');
                    if (bellIcon) {
                        const newBadge = document.createElement('span');
                        newBadge.className = 'badge bg-danger notification-badge';
                        newBadge.textContent = data.count;
                        bellIcon.parentNode.appendChild(newBadge);
                    }
                }
            } else if (badge) {
                badge.remove();
            }
        })
        .catch(error => console.error('Error:', error));
}

// Update dashboard stats (AJAX)
function updateDashboardStats() {
    fetch('/food_waste_system_php/api/dashboard/stats.php')
        .then(response => response.json())
        .then(data => {
            // Update stats on the page
            for (const [key, value] of Object.entries(data)) {
                const element = document.getElementById(`stat-${key}`);
                if (element) {
                    element.textContent = value;
                }
            }
        })
        .catch(error => console.error('Error updating stats:', error));
}

// Show toast notification
function showToast(message, type = 'success') {
    const toastContainer = document.getElementById('toast-container') || createToastContainer();
    const toastId = 'toast-' + Date.now();
    
    const icon = type === 'success' ? 'check-circle' : 
                 type === 'error' ? 'exclamation-circle' : 
                 type === 'warning' ? 'exclamation-triangle' : 'info-circle';
    
    const toastHTML = `
        <div id="${toastId}" class="toast align-items-center text-white bg-${type} border-0" 
             role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="fas fa-${icon} me-2"></i>
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;
    
    toastContainer.innerHTML += toastHTML;
    const toastElement = new bootstrap.Toast(document.getElementById(toastId));
    toastElement.show();
    
    // Remove toast after it's hidden
    document.getElementById(toastId).addEventListener('hidden.bs.toast', function() {
        this.remove();
    });
}

function createToastContainer() {
    const container = document.createElement('div');
    container.id = 'toast-container';
    container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
    container.style.zIndex = '1090';
    document.body.appendChild(container);
    return container;
}

// Handle AJAX form submissions
document.addEventListener('submit', function(e) {
    const form = e.target;
    
    // Check if form should be submitted via AJAX
    if (form.classList.contains('ajax-form') || form.hasAttribute('data-ajax')) {
        e.preventDefault();
        
        const submitButton = form.querySelector('button[type="submit"]');
        const originalButtonText = submitButton ? submitButton.innerHTML : null;
        
        if (submitButton) {
            submitButton.disabled = true;
            submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...';
        }
        
        fetch(form.action, {
            method: form.method,
            body: new FormData(form),
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(data.message || 'Action completed successfully', 'success');
                
                if (data.redirect) {
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 1500);
                } else if (data.reload) {
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                }
            } else {
                showToast(data.error || 'An error occurred', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred. Please try again.', 'danger');
        })
        .finally(() => {
            if (submitButton && originalButtonText) {
                submitButton.disabled = false;
                submitButton.innerHTML = originalButtonText;
            }
        });
    }
});

// Auto-update expiry countdown
function updateExpiryCountdown() {
    const expiryElements = document.querySelectorAll('.expiry-countdown');
    expiryElements.forEach(function(element) {
        const expiryDate = new Date(element.dataset.expiry);
        const now = new Date();
        const diffTime = expiryDate - now;
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        
        if (diffDays > 0) {
            element.textContent = `${diffDays} day${diffDays !== 1 ? 's' : ''} left`;
            if (diffDays <= 3) {
                element.classList.add('text-danger', 'fw-bold');
            }
        } else if (diffDays === 0) {
            element.textContent = 'Expires today';
            element.classList.add('text-danger', 'fw-bold');
        } else {
            element.textContent = 'Expired';
            element.classList.add('text-danger', 'fw-bold');
        }
    });
}

// Initialize countdown on pages with expiry dates
if (document.querySelector('.expiry-countdown')) {
    updateExpiryCountdown();
    setInterval(updateExpiryCountdown, 60000); // Update every minute
}

// Search functionality
const searchInputs = document.querySelectorAll('.search-input');
searchInputs.forEach(function(input) {
    input.addEventListener('keyup', function() {
        const searchTerm = this.value.toLowerCase();
        const table = this.closest('.card').querySelector('table');
        
        if (table) {
            const rows = table.querySelectorAll('tbody tr');
            rows.forEach(function(row) {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        }
    });
});

// Sort table columns
const sortableHeaders = document.querySelectorAll('th.sortable');
sortableHeaders.forEach(function(header) {
    header.style.cursor = 'pointer';
    
    header.addEventListener('click', function() {
        const table = this.closest('table');
        const columnIndex = Array.from(this.parentNode.children).indexOf(this);
        const rows = Array.from(table.querySelectorAll('tbody tr'));
        
        const isAscending = !this.classList.contains('asc');
        this.classList.toggle('asc', isAscending);
        this.classList.toggle('desc', !isAscending);
        
        rows.sort(function(a, b) {
            const aText = a.children[columnIndex].textContent.trim();
            const bText = b.children[columnIndex].textContent.trim();
            
            // Try to parse as numbers
            const aNum = parseFloat(aText);
            const bNum = parseFloat(bText);
            
            if (!isNaN(aNum) && !isNaN(bNum)) {
                return isAscending ? aNum - bNum : bNum - aNum;
            }
            
            // Otherwise sort as strings
            return isAscending ? 
                aText.localeCompare(bText) : 
                bText.localeCompare(aText);
        });
        
        // Reorder rows
        const tbody = table.querySelector('tbody');
        rows.forEach(function(row) {
            tbody.appendChild(row);
        });
    });
});