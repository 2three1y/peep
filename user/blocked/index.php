<?php
require_once __DIR__ . '/../../include/config.php';
session_start();

// Check if 'id' parameter is provided in the URL
$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_SPECIAL_CHARS);
if (!$id) {
    $userNotFound = true;
    $invalidId = true;
} else {
    $invalidId = false;
    try {
        $db = peep_db();
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Enable error handling
    } catch(PDOException $e) {
        echo "Database connection failed: " . $e->getMessage();
        exit;
    }

    // Fetch user details
    $stmt = $db->prepare('SELECT * FROM users WHERE LOWER(username) = LOWER(:username)');
    $stmt->bindParam(':username', $id);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $userNotFound = true;
    } else {
        $userNotFound = false;
        $pageTitle = htmlspecialchars($user['name']) . ' (@' . htmlspecialchars($user['username']) . ') blocked you - Chirp';
        $isUserProfile = isset($_SESSION['username']) && strtolower($_SESSION['username']) === strtolower($user['username']);
        $user['is_verified'] = strtolower($user['isVerified']) === 'yes';

        // Fetch follower and following counts
        $followerStmt = $db->prepare('SELECT COUNT(*) FROM following WHERE following_id = :userId');
        $followerStmt->bindParam(':userId', $user['id']);
        $followerStmt->execute();
        $user['followers'] = $followerStmt->fetchColumn();

        $followingStmt = $db->prepare('SELECT COUNT(*) FROM following WHERE follower_id = :userId');
        $followingStmt->bindParam(':userId', $user['id']);
        $followingStmt->execute();
        $user['following'] = $followingStmt->fetchColumn();

        // Check if the signed-in user is following this user
        if (isset($_SESSION['user_id'])) {
            $checkFollowStmt = $db->prepare('SELECT 1 FROM following WHERE follower_id = :followerId AND following_id = :followingId');
            $checkFollowStmt->bindParam(':followerId', $_SESSION['user_id']);
            $checkFollowStmt->bindParam(':followingId', $user['id']);
            $checkFollowStmt->execute();
            $isFollowing = $checkFollowStmt->fetchColumn();
        } else {
            $isFollowing = false;
        }
    }

    $db = null;
}
?>

<!DOCTYPE html>
<html lang="en-US">

<head>
    <meta charset="UTF-8">

    <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#0000">
    <link href="/src/styles/styles.css?v=<?php echo PEEP_ASSET_VERSION; ?>" rel="stylesheet">
    <link href="/src/styles/timeline.css?v=<?php echo PEEP_ASSET_VERSION; ?>" rel="stylesheet">
    <link href="/src/styles/menus.css?v=<?php echo PEEP_ASSET_VERSION; ?>" rel="stylesheet">
    <link href="/src/styles/responsive.css?v=<?php echo PEEP_ASSET_VERSION; ?>" rel="stylesheet">
    <script src="/src/scripts/general.js?v=<?php echo PEEP_ASSET_VERSION; ?>"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="manifest" href="/site.webmanifest">
    <title><?php echo $pageTitle; ?></title>
</head>

<body>
    <header>
        <div id="desktopMenu">
            <nav>
                <button type="button" class="logoButton" onclick="playChirpSound()" aria-label="Play the peep sound"><img src="/src/images/icons/chirp.svg" alt=""></button>
                <a href="/"><img src="/src/images/icons/house.svg" alt=""> Home</a>
                <a href="/discover"><img src="/src/images/icons/search.svg" alt=""> Discover</a>
                <?php if (isset($_SESSION['username'])): ?>
                <a href="/notifications"><img src="/src/images/icons/bell.svg" alt=""> Notifications</a>
                <a href="/messages"><img src="/src/images/icons/envelope.svg" alt=""> Direct Messages</a>
                <a
                    href="<?php echo isset($_SESSION['username']) ? '/user?id=' . htmlspecialchars($_SESSION['username']) : '/signin'; ?>" class="activeDesktop">
                    <img src="/src/images/icons/person.svg" alt=""> Profile
                </a>
                    <button class="newchirp" onclick="openNewChirpModal()">Chirp</button>
                <?php endif; ?>
            </nav>
            <div id="menuSettings">
                <?php if (peep_is_admin()): ?>
                <a href="/admin">🛡️ Admin panel</a>
                <?php endif; ?>
                <a href="/settings/account">⚙️ Settings</a>
                <?php if (isset($_SESSION['username'])): ?>
                <a href="/signout.php">🚪 Sign out</a>
                <?php else: ?>
                <a href="/signin/">🚪 Sign in</a>
                <?php endif; ?>
            </div>
            <button id="settingsButtonWrapper" type="button" onclick="showMenuSettings()">
                <img class="userPic"
                    src="<?php echo isset($_SESSION['profile_pic']) ? htmlspecialchars($_SESSION['profile_pic']) : '/src/images/users/guest/user.svg'; ?>"
                    alt="<?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'guest'; ?>">
                <div>
                    <p class="usernameMenu">
                        <?php echo isset($_SESSION['name']) ? htmlspecialchars($_SESSION['name']) : 'Guest'; ?>
                        <?php if (isset($_SESSION['is_verified']) && $_SESSION['is_verified']): ?>
                        <img class="emoji" src="/src/images/icons/verified.svg" alt="Verified">
                        <?php endif; ?>
                    </p>
                    <p class="subText">
                        @<?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'guest'; ?>
                    </p>
                </div>
                <p class="settingsButton">⚙️</p>
            </button>
        </div>
    </header>
    <main>
        <h1 class="srOnly"><?php echo preg_replace('/\s*-\s*Chirp$/', '', $pageTitle); ?></h1>
        <div id="feed">
            <div id="timelineSelect">
            <button type="button" id="iconChirp" onclick="playChirpSound()" aria-label="Play the peep sound"><img src="/src/images/icons/chirp.svg" alt=""></button>
                <button id="back" class="selcted" onclick="back()"><svg class="peepIcon" aria-hidden="true" focusable="false" viewBox="0 0 24 24" width="1em" height="1em"><path fill="currentColor" d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/></svg> Back </button>
            </div>
            <?php if ($userNotFound): ?>
            <!-- If post is not found or no ID provided, show this -->
            <div id="notFound">
                <p>This account doesn't exist</p>
                <p class="subText">Try searching for something else</p>
            </div>
            <?php else : ?>
            <div id="chirps">
                <img class="userBanner"
                    src="<?php echo isset($user['profilePic']) ? htmlspecialchars($user['userBanner']) : '/src/images/users/chirp/banner.png'; ?>">
                <div class="account">
                    <div class="accountInfo">
                        <div>
                            <img class="userPic"
                                src="<?php echo isset($user['profilePic']) ? htmlspecialchars($user['profilePic']) : '/src/images/users/guest/user.svg'; ?>"
                                alt="">
                            <div>
                            <p>
                <?php echo htmlspecialchars($user['name']); ?>
                <?php if ($user['is_verified']): ?>
                <img class="verified" src="/src/images/icons/verified.svg" alt="Verified">
                <?php endif; ?>
            </p>
                                <p class="subText">@<?php echo htmlspecialchars($user['username']); ?></p>
                            </div>
                        </div>
                        <div class="timestampTimeline">
                        </div>
                    </div>
                </div>
            </div>
            <div id="posts" data-offset="0">
                <!-- Chirps will be loaded here -->
                 <div class="noFoundUserTab">
                <p>You're blocked.</p>
                <p class="subText">You can't follow or see @<?php echo htmlspecialchars($user['username']); ?>'s chirps.</p>
                            </div>
            </div>
            <!--<div id="noMoreChirps">
                <div class="lds-ring">
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                </div>
            </div>-->
            <?php endif; ?>
        </div>
    </main>
    <aside id="sideBar">
        <?php include '../../include/sideBar.php';?>
    </aside>
    <footer>
        <div class="mobileCompose">
                <?php if (isset($_SESSION['username'])): ?>
            <a class="chirpMoile" href="/compose">Chirp</a>
                <?php endif; ?>
        </div>
        <div class="mobileMenuFooter">
            <a href="/"><img src="/src/images/icons/house.svg" alt="Home"></a>
            <a href="/discover"><img src="/src/images/icons/search.svg" alt="Discover"></a>
            <a href="/notifications"><img src="/src/images/icons/bell.svg" alt="Notifications"></a>
            <a href="/messages"><img src="/src/images/icons/envelope.svg" alt="Direct Messages"></a>
            <a
                href="<?php echo isset($_SESSION['username']) ? '/user?id=' . htmlspecialchars($_SESSION['username']) : '/signin'; ?>"class="active"><img
                    src="/src/images/icons/person.svg" alt="Profile"></a>
        </div>
    </footer>
    <script>
    let loadingChirps = false; // Flag to track if chirps are currently being loaded

    function updatePostedDates() {
        const chirps = document.querySelectorAll('.chirp .postedDate');
        chirps.forEach(function(chirp) {
            const timestamp = chirp.getAttribute('data-timestamp');
            const postDate = new Date(parseInt(timestamp) * 1000);
            const now = new Date();
            const diffInMilliseconds = now - postDate;
            const diffInSeconds = Math.floor(diffInMilliseconds / 1000);
            const diffInMinutes = Math.floor(diffInSeconds / 60);
            const diffInHours = Math.floor(diffInMinutes / 60);
            const diffInDays = Math.floor(diffInHours / 24);

            let relativeTime;

            if (diffInSeconds < 60) {
                relativeTime = diffInSeconds + "s ago";
            } else if (diffInMinutes < 60) {
                relativeTime = diffInMinutes + "m ago";
            } else if (diffInHours < 24) {
                relativeTime = diffInHours + "h ago";
            } else if (diffInDays < 7) {
                relativeTime = diffInDays + "d ago";
            } else {
                const options = {
                    year: 'numeric',
                    month: '2-digit',
                    day: '2-digit',
                    hour: '2-digit',
                    minute: '2-digit'
                };
                relativeTime = postDate.toLocaleString([], options);
            }

            chirp.textContent = relativeTime;
        });
    }

    function showLoadingSpinner() {
        document.getElementById('noMoreChirps').style.display = 'block';
    }

    function hideLoadingSpinner() {
        document.getElementById('noMoreChirps').style.display = 'none';
    }

    function loadChirps() {
        if (loadingChirps) return; // If already loading, exit

        const chirpsContainer = document.getElementById('posts');
        const offset = parseInt(chirpsContainer.getAttribute('data-offset'));

        loadingChirps = true; // Set loading flag
        showLoadingSpinner(); // Show loading spinner

        setTimeout(() => {
            fetch(`/user/media/fetch_media.php?offset=${offset}&user=<?php echo $user['id']; ?>`)
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
    }, 250);
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
    function toggleFollow(userId) {
        const followButton = document.getElementById('followProfileButton');
        const followingButton = document.getElementById('followingProfileButton');

        // Determine action based on current button state
        const action = followButton.style.display === 'none' ? 'unfollow' : 'follow';

        fetch('/interact_user.php', { // Ensure the path is correct
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    userId,
                    action
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok ' + response.statusText);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    if (action === 'follow') {
                        followButton.style.display = 'none'; // Hide Follow button
                        followingButton.style.display = ''; // Show Following button
                    } else if (action === 'unfollow') {
                        followButton.style.display = ''; // Show Follow button
                        followingButton.style.display = 'none'; // Hide Following button
                    }
                } else if (data.error === 'not_signed_in') {
                    window.location.href = '/signin/';
                } else {
                    console.error('Error:', data.message); // Log server errors
                }
            })
            .catch(error => {
                console.error('Fetch error:', error); // Log fetch errors
            });
    }


    <?php
if (isset($_SESSION['error_message'])) {
    echo 'console.error(' . json_encode($_SESSION['error_message']) . ');';
    unset($_SESSION['error_message']); // Clear the error message after displaying it
}
?>
    </script>

<div id="peepStatus" class="srOnly" role="status" aria-live="polite" data-flash="<?php echo peep_take_flash(); ?>"></div>
    <script src="/src/scripts/peep.js?v=<?php echo PEEP_ASSET_VERSION; ?>"></script>
</body>

</html>