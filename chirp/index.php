<?php
require_once __DIR__ . '/../include/config.php';
session_start();
try {
    // Connect to the SQLite database
    $db = peep_db();
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    include 'fetch_post.php';
    
} catch (PDOException $e) {
    die('Connection failed: ' . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en-US">

<head>
    <title><?php echo isset($title) ? $title : 'Chirp'; ?></title>
    <meta charset="UTF-8">

    <meta name="mobile-web-app-capable" content="yes">


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

<body>

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
                        href="<?php echo isset($_SESSION['username']) ? '/user?id=' . htmlspecialchars($_SESSION['username']) : '/signin'; ?>">
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
        <div id="feed">
            <div id="timelineSelect" class="extraBlur">
            <button type="button" id="iconChirp" onclick="playChirpSound()" aria-label="Play the peep sound"><img src="/src/images/icons/chirp.svg" alt=""></button>
            <button type="button" id="back" class="selected" onclick="back()" aria-label="Back"><svg class="peepIcon" aria-hidden="true" focusable="false" viewBox="0 0 24 24" width="1em" height="1em"><path fill="currentColor" d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/></svg> Chirp</button>
            </div>
                
                </div>
                <?php if (!$post || empty($postId)) : ?>
                <!-- If post is not found or no ID provided, show this -->
                <div id="notFound">
                    <h1 class="notFoundTitle">Chirp not found</h1>
                    <p class="subText">That chirp does not exist. <br>It was most likely deleted, or it never existed in
                        the first place.</p>
                </div>
                <?php else : ?>
                <!-- Display the fetched post -->
                <div id="context" data-offset="0">
                    <!-- Chirps will be loaded here -->
                </div>
                <div id="chirps">
                    <h1 class="srOnly">Post by <?php echo isset($name) ? $name : 'Guest'; ?></h1>
                    <article class="chirpThread" id="<?php echo $postId; ?>" aria-labelledby="peep-main-author">
                        <div class="chirpInfo">
                            <div>
                                <img class="userPic"
                                    src="<?php echo isset($profilePic) ?$profilePic : '/src/images/users/guest/user.svg'; ?>"
                                    alt="<?php echo isset($user) ? htmlspecialchars($user) : 'Guest'; ?>">
                                <div>
                                    <h2 class="postAuthor" id="peep-main-author"><?php echo isset($name) ? $name : 'Guest'; ?></h2>
                                    <a href="/user?id=<?php echo isset($user) ? htmlspecialchars($user) : 'guest'; ?>" class="subText usernameLink">@<?php echo isset($user) ? htmlspecialchars($user) : 'guest'; ?>
                                    </a>
                                </div>
                            </div>
                            <?php include '../include/moreOptions.php';?>
                        </div>
                        <!-- Display chirp content with line breaks -->
                        <pre><?php echo $status; ?></pre>

                        <div class="chirpInteractThread">
                                                        <p class="subText postedDate">Posted on:
                                <script>
                                const options = {
                                    year: 'numeric',
                                    month: '2-digit',
                                    day: '2-digit',
                                    hour: '2-digit',
                                    minute: '2-digit'
                                };
                                document.write(new Date("<?php echo $timestamp ?>").toLocaleString([], options));
                                </script> via Chirp for Web
                            </p>
                            <div>
                                <button type="button" class="reply" onClick="openReplyModal()">
                                    <img alt="Reply" src="/src/images/icons/reply.svg"><br>
                                    <?php echo ($reply_count == 1) ? '1 <span class="interactWithPostLabel">reply</span>' : $reply_count . ' <span class="interactWithPostLabel">replies</span>'; ?>
                                </button>

                                <?php if ($rechirped): ?>
                                <button type="button" class="rechirp rechirped">
                                    <img alt="Rechirped" src="/src/images/icons/rechirped.svg"><br>
                                    <?php echo ($rechirp_count == 1) ? '1 <span class="interactWithPostLabel">rechirp</span>' : $rechirp_count . ' <span class="interactWithPostLabel">rechirps</span>'; ?>
                                </button>
                                <?php else: ?>
                                <button type="button" class="rechirp">
                                    <img alt="Rechirp" src="/src/images/icons/rechirp.svg"><br>
                                    <?php echo ($rechirp_count == 1) ? '1 <span class="interactWithPostLabel">rechirp</span>' : $rechirp_count . ' <span class="interactWithPostLabel">rechirps</span>'; ?>
                                </button>
                                <?php endif; ?>

                                <button type="button" class="like<?php echo $liked ? ' liked' : ''; ?>" id="mainLikeButton" aria-pressed="<?php echo $liked ? 'true' : 'false'; ?>"
                                    onclick="updateChirpInteraction(<?php echo (int)$postId; ?>, 'like', this)">
                                    <img alt="" src="/src/images/icons/<?php echo $liked ? 'liked' : 'like'; ?>.svg"><br>
                                    <span class="like-count"><?php echo (int)$like_count; ?></span> <span class="interactWithPostLabel"><?php echo ($like_count == 1) ? 'like' : 'likes'; ?></span>
                                </button>
                            </div>

                        </div>
                            <button onclick="openReplyModal()" class="replyToButtonMenu">
                              Reply to @<?php echo isset($user) ? htmlspecialchars($user) : 'guest'; ?>...</button>
                    </article>
                </div>
                <h2 class="srOnly">Replies</h2>
                <div id="replies" data-offset="0">
                    <!-- Chirps will be loaded here -->
                </div>
                <div id="noMoreChirps" style="display: none;">
                    <div class="lds-ring">
                        <div></div>
                        <div></div>
                        <div></div>
                        <div></div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </main>

        <aside id="sideBar">
            <?php include '../include/sideBar.php';?>
        </aside>
        <footer>
            <div class="mobileCompose">
                <?php if (isset($_SESSION['username'])): ?>
                <button class="newchirpmobile" onclick="openNewChirpModal()">Chirp</button>
                <?php endif; ?>
            </div>
            <div>
                <a href="/"><img src="/src/images/icons/house.svg" alt="Home"></a>
                <a href="/discover"><img src="/src/images/icons/search.svg" alt="Discover"></a>
                <a href="/notifications"><img src="/src/images/icons/bell.svg" alt="Notifications"></a>
                <a href="/messages"><img src="/src/images/icons/envelope.svg" alt="Direct Messages"></a>
                <a
                    href="<?php echo isset($_SESSION['username']) ? '/user?id=' . htmlspecialchars($_SESSION['username']) : '/signin'; ?>"><img
                        src="/src/images/icons/person.svg" alt="Profile"></a>
            </div>
        </footer>
        <?php include '../include/compose.php'; ?>
        <?php include '../include/reply.php'; ?>
        <div id="likeModal" class="interaction-modal" style="display: none;">
            <ul class="interaction-modal-content">
                <li><button onclick="showLikes()"><img src="/src/images/icons/stats.svg" class="emoji" alt="">View
                        likes</button></li>
                <li>
                    <button type="button" class="like"
                        onclick="updateChirpInteraction(<?php echo $postId; ?>, 'like', this)">
                        <img class="emoji" alt="Like" src="/src/images/icons/<?php echo $liked ? 'liked' : 'like'; ?>.svg">
                        <span class="like-button-text"><?php echo $liked ? 'Undo like' : 'Like'; ?></span>
                    </button>
                </li>
            </ul>
        </div>

        <div id="rechirpModal" class="interaction-modal" style="display: none;">
            <ul class="interaction-modal-content">
                <li><button onclick="showRechirps()"><img src="/src/images/icons/stats.svg" class="emoji" alt="">View
                        rechirps</button></li>
                <li><button onclick="quoteChirp()"><img src="/src/images/icons/write.svg" class="emoji" alt="">Quote</button>
                </li>
                <li>
                    <button type="button" class="rechirp"
                        onclick="updateChirpInteraction(<?php echo $postId; ?>, 'rechirp', this)">
                        <img class="emoji" alt="Rechirp"
                            src="/src/images/icons/<?php echo $rechirped ? 'rechirped' : 'rechirp'; ?>.svg">
                        <span class="rechirp-button-text"><?php echo $rechirped ? 'Undo rechirp' : 'Rechirp'; ?></span>
                    </button>
                </li>
            </ul>
        </div>
        <script>
        let loadingChirps = false; // Flag to track if chirps are currently being loaded

        function showLoadingSpinner() {
            document.getElementById('noMoreChirps').style.display = 'block';
        }

        function hideLoadingSpinner() {
            document.getElementById('noMoreChirps').style.display = 'none';
        }

        function loadChirps() {
            if (loadingChirps) return; // If already loading, exit

            const chirpsContainer = document.getElementById('replies');
            const offset = parseInt(chirpsContainer.getAttribute('data-offset'));

            loadingChirps = true; // Set loading flag
            showLoadingSpinner(); // Show loading spinner

            setTimeout(() => {
                fetch(`/chirp/fetch_replies.php?offset=${offset}&for=<?php echo $postId; ?>`)
                .then(response => response.json())
            .then(chirps => {
                chirps.forEach(chirp => {
                    

                    // peep: one accessible <article> per post (src/scripts/peep.js)
                    chirpsContainer.appendChild(peepRenderPost(chirp, 3));
                    
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
                        const imgElement = button.querySelector('img');

                        if (action === 'like') {
                            imgElement.src = data.like ? '/src/images/icons/liked.svg' :
                                '/src/images/icons/like.svg';
                            countElement.textContent = data.like_count;
                        } else if (action === 'rechirp') {
                            imgElement.src = data.rechirp ? '/src/images/icons/rechirped.svg' :
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


        document.addEventListener("DOMContentLoaded", function() {
            const likeModal = document.getElementById("likeModal");
            const rechirpModal = document.getElementById("rechirpModal");
            const likeButton = document.getElementById("likeButton");
            const rechirpButton = document.getElementById("rechirpButton");

            function openModal(modalId) {
                document.getElementById(modalId).style.display = "block";
            }


            window.onclick = function(event) {
                if (event.target == likeModal) {
                    likeModal.style.display = "none";
                } else if (event.target == rechirpModal) {
                    rechirpModal.style.display = "none";
                }
            }

            // peep: the like button toggles directly (aria-pressed); no extra menu
            var mainLike = document.getElementById('mainLikeButton');
            if (mainLike && window.peepSetToggle) peepSetToggle(mainLike, 'like', mainLike.getAttribute('aria-pressed') === 'true', mainLike.querySelector('.like-count').textContent);

            document.querySelector(".rechirp").addEventListener("click", function() {
                openModal("rechirpModal");
            });

        });

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