// Mobile Menu Functionality for Admin Panel
// This script handles responsive behavior for the admin panel

document.addEventListener('DOMContentLoaded', function() {
    // Make tables responsive
    const tables = document.querySelectorAll('table');
    tables.forEach(table => {
        if (!table.closest('.table-container')) {
            const wrapper = document.createElement('div');
            wrapper.classList.add('responsive-table');
            table.parentNode.insertBefore(wrapper, table);
            wrapper.appendChild(table);
        }
    });

    // Add touch-friendly class to buttons
    const buttons = document.querySelectorAll('.btn, button, .table-actions a');
    buttons.forEach(btn => {
        btn.classList.add('touch-friendly');
    });

    // Improve form usability on mobile
    const formInputs = document.querySelectorAll('input, select, textarea');
    formInputs.forEach(input => {
        input.addEventListener('focus', function() {
            if (window.innerWidth <= 768) {
                this.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
    });

    // Auto-hide mobile menu when orientation changes
    window.addEventListener('orientationchange', function() {
        setTimeout(function() {
            const sidebar = document.querySelector('.sidebar');
            const mobileOverlay = document.getElementById('mobileOverlay');
            if (sidebar && sidebar.classList.contains('mobile-open')) {
                sidebar.classList.remove('mobile-open');
                mobileOverlay.classList.remove('show');
                document.body.style.overflow = '';
            }
        }, 100);
    });

    // Enhance table mobile experience
    if (window.innerWidth <= 768) {
        tables.forEach(table => {
            const headers = table.querySelectorAll('th');
            const rows = table.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const cells = row.querySelectorAll('td');
                cells.forEach((cell, index) => {
                    if (headers[index]) {
                        cell.setAttribute('data-label', headers[index].textContent);
                    }
                });
            });
        });
    }
});
