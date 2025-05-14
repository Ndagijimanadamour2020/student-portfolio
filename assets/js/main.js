// Document ready
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Handle form submissions with confirmation
    const forms = document.querySelectorAll('form[data-confirm]');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!confirm(this.getAttribute('data-confirm'))) {
                e.preventDefault();
            }
        });
    });
    
    // Dynamic field toggling
    document.querySelectorAll('[data-toggle-fields]').forEach(toggle => {
        const target = toggle.getAttribute('data-toggle-fields');
        const fields = document.querySelectorAll(target);
        
        toggle.addEventListener('change', function() {
            const show = this.checked || (this.value && this.value !== 'no');
            fields.forEach(field => {
                field.style.display = show ? 'block' : 'none';
                if (field.required) {
                    field.required = show;
                }
            });
        });
        
        // Trigger change event on load
        toggle.dispatchEvent(new Event('change'));
    });
    
    // Calculate percentage when marks are entered
    document.querySelectorAll('.marks-input').forEach(input => {
        input.addEventListener('input', function() {
            const row = this.closest('tr');
            const obtained = parseFloat(row.querySelector('.obtained-marks').value) || 0;
            const max = parseFloat(row.querySelector('.max-marks').value) || 1;
            const percentage = (obtained / max) * 100;
            row.querySelector('.percentage').textContent = percentage.toFixed(2) + '%';
        });
    });
});