let loadingChirps = false; // Flag to track if chirps are currently being loaded

function showLoadingSpinner() {
    document.getElementById('noMoreChirps').style.display = 'block';
}

function hideLoadingSpinner() {
    document.getElementById('noMoreChirps').style.display = 'none';
}

function loadChirps() {
    const userId = document.getElementById('posts').getAttribute('data-user-id');
    if (loadingChirps) return; // If already loading, exit

    const chirpsContainer = document.getElementById('posts');
    const offset = parseInt(chirpsContainer.getAttribute('data-offset'));

    loadingChirps = true; // Set loading flag
    showLoadingSpinner(); // Show loading spinner

    setTimeout(() => {
        fetch(`/user/replies/fetch_replies.php?offset=${offset}&user=${userId}`)
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
    button.classList.add('button-clicked'); // Add the animation class
    setTimeout(() => {
        button.classList.remove('button-clicked'); // Remove the animation class after 100ms
    }, 100);
}

// Add event listeners to each button
document.querySelectorAll('.reply, .rechirp, .like').forEach(button => {
    button.addEventListener('click', () => {
        handleButtonClick(button); // Call the animation function
    });
});


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


loadChirps();

window.addEventListener('scroll', () => {
    if (window.innerHeight + window.scrollY >= document.body.offsetHeight) {
        loadChirps();
    }
});

setInterval(updatePostedDates, 1000);
