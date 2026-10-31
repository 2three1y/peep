let loadingChirps = false; // Flag to track if chirps are currently being loaded

function showLoadingSpinner() {
    console.log('Showing loading spinner');
    document.getElementById('noMoreChirps').style.display = 'block';
}

function hideLoadingSpinner() {
    console.log('Hiding loading spinner');
    document.getElementById('noMoreChirps').style.display = 'none';
}

function loadChirps() {
    console.log('loadChirps function called');
    if (loadingChirps) {
        console.log('Chirps are already being loaded, exiting');
        return; // If already loading, exit
    }

    const chirpsContainer = document.getElementById('chirps');
    const offset = parseInt(chirpsContainer.getAttribute('data-offset'));
    console.log(`Current offset: ${offset}`);

    loadingChirps = true; // Set loading flag
    console.log('Setting loadingChirps flag to true');
    showLoadingSpinner(); // Show loading spinner

    setTimeout(() => {
        console.log('Fetching chirps from server');
        fetch(`/fetch_chirps.php?offset=${offset}`)
            .then(response => {
                console.log('Received response from server');
                return response.json();
            })
            .then(chirps => {
                console.log('Successfully parsed chirps:', chirps);
                chirps.forEach(chirp => {
                    console.log(`Processing chirp with ID: ${chirp.id}`);

                    // peep: one accessible <article> per post (src/scripts/peep.js)
                    chirpsContainer.appendChild(peepRenderPost(chirp, 2));
                    console.log(`Appended chirp with ID: ${chirp.id} to the container`);
                });

                chirpsContainer.setAttribute('data-offset', offset + 12); // Correctly increment the offset
                console.log(`Updated offset to: ${offset + 12}`);

                updatePostedDates();
                twemoji.parse(chirpsContainer);
            })
            .catch(error => {
                console.error('Error fetching chirps:', error);
            })
            .finally(() => {
                loadingChirps = false; // Reset loading flag
                console.log('Setting loadingChirps flag to false');
                hideLoadingSpinner(); // Hide loading spinner
            });
    }, 295);
}

// Function to handle button click animation
function handleButtonClick(button) {
    console.log('Button clicked, adding animation');
    button.classList.add('button-clicked'); // Add the animation class
    setTimeout(() => {
        console.log('Removing animation from button');
        button.classList.remove('button-clicked'); // Remove the animation class after 100ms
    }, 100);
}

// Add event listeners to each button
document.querySelectorAll('.reply, .rechirp, .like').forEach(button => {
    button.addEventListener('click', () => {
        console.log('Button event listener triggered');
        handleButtonClick(button); // Call the animation function
    });
});

function legacyUpdateChirpInteraction(chirpId, action, button) {
    console.log(`Updating chirp interaction for chirp ID: ${chirpId}, action: ${action}`);
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
        .then(response => {
            console.log('Received response from interaction update');
            return response.json();
        })
        .then(data => {
            console.log('Interaction update response data:', data);
            if (data.success) {
                const countElement = button.querySelector(`.${action}-count`);
                const currentCount = parseInt(countElement.textContent.trim());

                if (action === 'like') {
                    button.querySelector('img').src = data.like ? '/src/images/icons/liked.svg' :
                        '/src/images/icons/like.svg';
                    button.classList.toggle('liked', data.like);
                    countElement.textContent = data.like_count;
                    button.style.color = data.like ? '#D92D20' : '';
                } else if (action === 'rechirp') {
                    button.querySelector('img').src = data.rechirp ? '/src/images/icons/rechirped.svg' :
                        '/src/images/icons/rechirp.svg';
                    button.classList.toggle('rechirped', data.rechirp); // Toggle 'rechirped' class
                    countElement.textContent = data.rechirp_count;
                    button.style.color = data.rechirp ? '#12B76A' : ''; // Set color based on rechirp status
                }
            } else if (data.error === 'not_signed_in') {
                console.log('User not signed in, redirecting to sign-in page');
                window.location.href = '/signin/';
            }
        })
        .catch(error => {
            console.error('Error updating interaction:', error);
        });
}

loadChirps();

window.addEventListener('scroll', () => {
    console.log('Scroll event detected');
    if (window.innerHeight + window.scrollY >= (document.body.offsetHeight - 120)) {
        console.log('Near bottom of page, loading more chirps');
        loadChirps();
    }
});

setInterval(updatePostedDates, 1000);