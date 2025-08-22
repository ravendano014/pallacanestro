// Metro.js - JavaScript framework for Metro UI
class MetroUI {
    constructor() {
        this.init();
    }

    init() {
        this.setupEventListeners();
    }

    setupEventListeners() {
        // Modal functionality
        this.setupModals();
        
        // Form validation
        this.setupFormValidation();
        
        // Confirm dialogs
        this.setupConfirmDialogs();
    }

    // Modal Management
    setupModals() {
        // Open modal
        document.addEventListener('click', (e) => {
            if (e.target.matches('[data-toggle="modal"]')) {
                const targetModal = e.target.getAttribute('data-target');
                this.openModal(targetModal);
            }
        });

        // Close modal
        document.addEventListener('click', (e) => {
            if (e.target.matches('.close') || e.target.matches('.modal')) {
                if (e.target.matches('.modal') && e.target === e.currentTarget) {
                    this.closeModal(e.target);
                } else if (e.target.matches('.close')) {
                    this.closeModal(e.target.closest('.modal'));
                }
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                const openModal = document.querySelector('.modal[style*="block"]');
                if (openModal) {
                    this.closeModal(openModal);
                }
            }
        });
    }

    openModal(selector) {
        const modal = document.querySelector(selector);
        if (modal) {
            modal.style.display = 'block';
            document.body.style.overflow = 'hidden';
            
            // Focus first input
            const firstInput = modal.querySelector('input, textarea, select');
            if (firstInput) {
                setTimeout(() => firstInput.focus(), 100);
            }
        }
    }

    closeModal(modal) {
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
            
            // Reset form if exists
            const form = modal.querySelector('form');
            if (form) {
                form.reset();
                this.clearFormErrors(form);
            }
        }
    }

    // Form Validation
    setupFormValidation() {
        document.addEventListener('submit', (e) => {
            const form = e.target;
            if (form.matches('[data-validate="true"]')) {
                if (!this.validateForm(form)) {
                    e.preventDefault();
                }
            }
        });

        // Real-time validation
        document.addEventListener('blur', (e) => {
            if (e.target.matches('.form-control[required]')) {
                this.validateField(e.target);
            }
        }, true);
    }

    validateForm(form) {
        let isValid = true;
        const requiredFields = form.querySelectorAll('[required]');
        
        requiredFields.forEach(field => {
            if (!this.validateField(field)) {
                isValid = false;
            }
        });

        return isValid;
    }

    validateField(field) {
        const value = field.value.trim();
        let isValid = true;
        let errorMessage = '';

        // Required validation
        if (field.hasAttribute('required') && !value) {
            isValid = false;
            errorMessage = 'Este campo es requerido';
        }

        // Type-specific validation
        if (value && field.type === 'email') {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(value)) {
                isValid = false;
                errorMessage = 'Ingrese un email válido';
            }
        }

        // Custom validation
        if (value && field.hasAttribute('data-min-length')) {
            const minLength = parseInt(field.getAttribute('data-min-length'));
            if (value.length < minLength) {
                isValid = false;
                errorMessage = `Mínimo ${minLength} caracteres requeridos`;
            }
        }

        this.showFieldError(field, isValid ? null : errorMessage);
        return isValid;
    }

    showFieldError(field, errorMessage) {
        // Remove existing error
        const existingError = field.parentNode.querySelector('.field-error');
        if (existingError) {
            existingError.remove();
        }

        field.classList.remove('error');

        if (errorMessage) {
            field.classList.add('error');
            const errorDiv = document.createElement('div');
            errorDiv.className = 'field-error';
            errorDiv.textContent = errorMessage;
            errorDiv.style.cssText = 'color: #d13438; font-size: 12px; margin-top: 5px;';
            field.parentNode.appendChild(errorDiv);
        }
    }

    clearFormErrors(form) {
        const errors = form.querySelectorAll('.field-error');
        errors.forEach(error => error.remove());
        
        const errorFields = form.querySelectorAll('.error');
        errorFields.forEach(field => field.classList.remove('error'));
    }

    // Confirm Dialogs
    setupConfirmDialogs() {
        document.addEventListener('click', (e) => {
            if (e.target.matches('[data-confirm]')) {
                const message = e.target.getAttribute('data-confirm');
                if (!confirm(message)) {
                    e.preventDefault();
                    return false;
                }
            }
        });
    }

    // Alert Management
    showAlert(message, type = 'success', duration = 5000) {
        const alert = document.createElement('div');
        alert.className = `alert alert-${type}`;
        alert.textContent = message;
        alert.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        
        document.body.appendChild(alert);

        // Auto remove
        setTimeout(() => {
            if (alert.parentNode) {
                alert.remove();
            }
        }, duration);

        return alert;
    }

    // AJAX Helper
    ajax(options) {
        const defaults = {
            method: 'GET',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            }
        };

        const config = { ...defaults, ...options };

        return fetch(config.url, {
            method: config.method,
            headers: config.headers,
            body: config.data
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .catch(error => {
            console.error('AJAX Error:', error);
            throw error;
        });
    }

    // Utility Functions
    formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('es-ES');
    }

    formatCurrency(amount) {
        return new Intl.NumberFormat('es-ES', {
            style: 'currency',
            currency: 'EUR'
        }).format(amount);
    }

    debounce(func, wait) {
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
}

// Initialize Metro UI when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.Metro = new MetroUI();
});

// Teams CRUD specific functions
class TeamsCRUD {
    constructor() {
        this.initTeamsFunctions();
    }

    initTeamsFunctions() {
        this.setupEditTeam();
        this.setupDeleteTeam();
        this.setupFormSubmission();
    }

    setupEditTeam() {
        document.addEventListener('click', (e) => {
            if (e.target.matches('.edit-team')) {
                e.preventDefault();
                const teamId = e.target.getAttribute('data-id');
                this.loadTeamData(teamId);
            }
        });
    }

    setupDeleteTeam() {
        document.addEventListener('click', (e) => {
            if (e.target.matches('.delete-team')) {
                e.preventDefault();
                const teamId = e.target.getAttribute('data-id');
                const teamName = e.target.getAttribute('data-name');
                
                if (confirm(`¿Está seguro que desea eliminar el equipo "${teamName}"?`)) {
                    this.deleteTeam(teamId);
                }
            }
        });
    }

    setupFormSubmission() {
        const teamForm = document.getElementById('teamForm');
        if (teamForm) {
            teamForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.saveTeam();
            });
        }
    }

    loadTeamData(teamId) {
        fetch(`teams_crud.php?action=get&id=${teamId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.populateForm(data.team);
                    Metro.openModal('#teamModal');
                } else {
                    Metro.showAlert('Error al cargar los datos del equipo', 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Metro.showAlert('Error al cargar los datos del equipo', 'danger');
            });
    }

    populateForm(team) {
        document.getElementById('teamId').value = team.id || '';
        document.getElementById('teamName').value = team.name || '';
        document.getElementById('teamCity').value = team.city || '';
        document.getElementById('teamCoach').value = team.coach || '';
        document.getElementById('teamFoundedYear').value = team.founded_year || '';
    }

    saveTeam() {
        const form = document.getElementById('teamForm');
        const formData = new FormData(form);
        
        fetch('teams_crud.php?action=save', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Metro.showAlert(data.message, 'success');
                Metro.closeModal(document.getElementById('teamModal'));
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                Metro.showAlert(data.message, 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Metro.showAlert('Error al guardar el equipo', 'danger');
        });
    }

    deleteTeam(teamId) {
        fetch(`teams_crud.php?action=delete&id=${teamId}`, {
            method: 'DELETE'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Metro.showAlert(data.message, 'success');
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                Metro.showAlert(data.message, 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Metro.showAlert('Error al eliminar el equipo', 'danger');
        });
    }
}

// Initialize Teams CRUD when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.TeamsCRUD = new TeamsCRUD();
});
