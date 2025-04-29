document.addEventListener('DOMContentLoaded', () => {

    // Dark Mode Toggle
    const toggleButton = document.getElementById('dark-mode-toggle');
    const body = document.body;

    // Apply saved theme on load
    const savedTheme = getCookie('theme');
    if (savedTheme === 'dark') {
        body.classList.add('dark-mode');
    } else {
        body.classList.remove('dark-mode'); // Ensure light mode if no cookie or light
    }


    if (toggleButton) {
        toggleButton.addEventListener('click', () => {
            body.classList.toggle('dark-mode');

            // Save preference in a cookie
            if (body.classList.contains('dark-mode')) {
                setCookie('theme', 'dark', 365); // Cookie expires in 365 days
            } else {
                setCookie('theme', 'light', 365);
            }
        });
    }

    // Add other global JS logic here, e.g., form validation helpers

});

// --- Cookie Helper Functions ---
function setCookie(name, value, days) {
    var expires = "";
    if (days) {
        var date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        expires = "; expires=" + date.toUTCString();
    }
    document.cookie = name + "=" + (value || "") + expires + "; path=/; SameSite=Lax"; // Added SameSite
}

function getCookie(name) {
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
    }
    return null;
}

// --- Optional: AJAX Example for loading recommendations ---
function loadRecommendationsAJAX() {
    const listElement = document.getElementById('recommendation-list');
    if (!listElement) return;

    listElement.innerHTML = '<p>Loading recommendations...</p>';

    fetch('get_recommendations_ajax.php') // Path to your PHP AJAX handler
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            listElement.innerHTML = ''; // Clear loading message

            if (data.error) {
                 listElement.innerHTML = `<p class="message error">Error: ${escapeHTML(data.error)}</p>`;
            } else if (data.recommendations && data.recommendations.length > 0) {
                data.recommendations.forEach(internship => {
                    // Use the same card structure as in dashboard.php
                    const card = document.createElement('div');
                    card.className = 'internship-card';
                    card.innerHTML = `
                        <h3><a href="internship_detail.php?id=${internship.internship_id}">${escapeHTML(internship.title)}</a></h3>
                        <p><span class="company">${escapeHTML(internship.company_name)}</span></p>
                        <p><span class="location">${escapeHTML(internship.location)}</span></p>
                        <p>Match Score: <span class="score">${internship.score.toFixed(1)}</span></p>
                        <a href="internship_detail.php?id=${internship.internship_id}" class="details-link">View Details</a>
                    `;
                    listElement.appendChild(card);
                });
            } else {
                listElement.innerHTML = '<p>No recommendations found at this time.</p>';
            }
        })
        .catch(error => {
            console.error('Error fetching recommendations via AJAX:', error);
            listElement.innerHTML = '<p class="message error">Could not load recommendations. Please try again later.</p>';
        });
}

// Helper to prevent XSS in JS rendering
function escapeHTML(str) {
    if (str === null || str === undefined) return '';
    var p = document.createElement('p');
    p.appendChild(document.createTextNode(str));
    return p.innerHTML;
}


// If you want to use AJAX loading on the dashboard uncomment the next line
// document.addEventListener('DOMContentLoaded', loadRecommendationsAJAX);
