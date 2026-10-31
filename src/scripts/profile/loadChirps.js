let loadingChirps = false; // Flag to track if chirps are currently being loaded

// Show loading spinner
function showLoadingSpinner() {
    document.getElementById('noMoreChirps').style.display = 'block';
}

// Hide loading spinner
function hideLoadingSpinner() {
    document.getElementById('noMoreChirps').style.display = 'none';
}

// Load chirps from the server
function loadChirps() {
    const userId = document.getElementById('posts').getAttribute('data-user-id');
    if (loadingChirps || !userId) return; // If already loading or no userId, exit

    const chirpsContainer = document.getElementById('posts');
    const offset = parseInt(chirpsContainer.getAttribute('data-offset'));

    loadingChirps = true; // Set loading flag
    showLoadingSpinner(); // Show loading spinner

    setTimeout(() => {
        // Fetch chirps with the dynamic user ID
        fetch(`/user/fetch_chirps.php?offset=${offset}&user=${userId}`)
            .then(response => response.json())
            .then(chirps => {
                chirps.forEach(chirp => {
                    // peep: one accessible <article> per post (src/scripts/peep.js)
                    chirpsContainer.appendChild(peepRenderPost(chirp, 2));

                });

                chirpsContainer.setAttribute('data-offset', offset + 12); // Correctly increment the offset

                updatePostedDates();
                twemoji.parse(chirpsContainer);
            })
            .catch(error => {
                console.error('Error fetching chirps:', error);
            })
            .finally(() => {
                loadingChirps = false; // Reset loading flag
                hideLoadingSpinner(); // Hide loading spinner
            });
    }, 295);
}

// Function to handle button click animation
function handleButtonClick(button) {
    button.classList.add('button-clicked');
    setTimeout(() => {
        button.classList.remove('button-clicked');
    }, 100);
}

// Add event listeners to each button
document.querySelectorAll('.reply, .rechirp, .like').forEach(button => {
    button.addEventListener('click', () => {
        handleButtonClick(button); // Call the animation function
    });
});

// Update chirp interaction (like/rechirp)
function legacyUpdateChirpInteraction(chirpId, action, button) {
    fetch(`/interact_chirp.php`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            chirpId,
            action
        })
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const countElement = button.querySelector(`.${action}-count`);
                const currentCount = parseInt(countElement.textContent.trim());

                if (action === 'like') {
                    button.querySelector('img').src = data.like ? '/src/images/icons/liked.svg' :
                        '/src/images/icons/like.svg';
                    countElement.textContent = data.like_count;
                } else if (action === 'rechirp') {
                    button.querySelector('img').src = data.rechirp ? '/src/images/icons/rechirped.svg' :
                        '/src/images/icons/rechirp.svg';
                    countElement.textContent = data.rechirp_count;
                }
            } else if (data.error === 'not_signed_in') {
                window.location.href = '/signin/';
            }
        })
        .catch(error => {
            console.error('Error updating interaction:', error);
        });
}

// Initial call to load chirps
loadChirps();

// Infinite scroll handler
window.addEventListener('scroll', () => {
    if (window.innerHeight + window.scrollY >= document.body.offsetHeight - 200) {
        loadChirps();
    }
});

// Periodically update posted dates
setInterval(updatePostedDates, 1000);
