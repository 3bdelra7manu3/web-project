/* ================================================
   MY HEALTH APP JAVASCRIPT
   ================================================
   This file contains all the JavaScript functions for our health app.
   It helps highlight the active menu item and format dates.
*/

// This runs when the page finishes loading
// The DOMContentLoaded event fires when the HTML is fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Call our function to highlight the current page in the menu
    setActiveNavLink();
    
    // We could add more initialization code here in the future
    console.log("Page loaded and ready!");
});

/**
 * This function highlights the current page in the navigation menu
 * It works by comparing the current page URL with the href of each nav link
 */
function setActiveNavLink() {
    // Get the current page filename from the URL
    // Example: if we're on http://localhost/health/dashboard.php
    // This will extract just "dashboard.php"
    var currentPage = window.location.pathname.split('/').pop();
    console.log("Current page is: " + currentPage);
    
    // Find all the navigation links in our menu
    var navLinks = document.querySelectorAll('.navbar-nav .nav-link');
    
    // Loop through each link and check if it matches the current page
    for (var i = 0; i < navLinks.length; i++) {
        var link = navLinks[i];
        var href = link.getAttribute('href');
        
        // If the href matches the current page, add the "active" class to highlight it
        if (href === currentPage) {
            link.classList.add('active');
        } 
        // Special case for the homepage (dashboard)
        else if (currentPage === '' && href === 'dashboard.php') {
            link.classList.add('active');
        } 
        // Otherwise, make sure the link is not highlighted
        else {
            link.classList.remove('active');
        }
    }
}

/**
 * Format a date string to make it more user-friendly
 * @param {string} dateString - A date string from the database
 * @return {string} A formatted date like "1/1/2025 12:00 PM"
 */
function formatDate(dateString) {
    // Create a JavaScript Date object from our string
    var date = new Date(dateString);
    
    // Format the date as a string
    // toLocaleDateString gives us the date portion (e.g., "1/1/2025")
    // toLocaleTimeString gives us the time portion (e.g., "12:00 PM")
    var formattedDate = date.toLocaleDateString();
    var formattedTime = date.toLocaleTimeString([], { 
        hour: '2-digit', 
        minute: '2-digit' 
    });
    
    // Combine date and time with a space between them
    return formattedDate + ' ' + formattedTime;
}
