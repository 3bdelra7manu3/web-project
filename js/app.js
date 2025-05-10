// My Health App JavaScript

// Display current date in the dashboard if date element exists
document.addEventListener('DOMContentLoaded', function() {
    // Check for notifications
    checkNotifications();
    
    // Set active nav link
    setActiveNavLink();
});

// Check for upcoming reminders and appointments
function checkNotifications() {
    const today = new Date();
    
    // This would typically be done with AJAX to fetch data from server
    // For now, we're just providing the UI structure
    // In a real-world scenario, you'd implement this with AJAX
}

// Set active navigation link based on current page
function setActiveNavLink() {
    const currentPage = window.location.pathname.split('/').pop();
    const navLinks = document.querySelectorAll('.navbar-nav .nav-link');
    
    navLinks.forEach(link => {
        const href = link.getAttribute('href');
        if (href === currentPage) {
            link.classList.add('active');
        } else if (currentPage === '' && href === 'dashboard.php') {
            link.classList.add('active');
        } else {
            link.classList.remove('active');
        }
    });
}

// Format date for display
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
}
