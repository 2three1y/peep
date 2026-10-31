<?php
require_once __DIR__ . '/../include/config.php';
session_start();
?>

<!DOCTYPE html>
<html lang="en-US">

<head>
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
    <title>Notifications - Chirp</title>
</head>

<body>
    <header>
        <div id="desktopMenu">
            <nav>
                <button type="button" class="logoButton" onclick="playChirpSound()" aria-label="Play the peep sound"><img src="/src/images/icons/chirp.svg" alt=""></button>
                <a href="/"><img src="/src/images/icons/house.svg" alt=""> Home</a>
                <a href="/discover"><img src="/src/images/icons/search.svg" alt=""> Discover</a>
                <?php if (isset($_SESSION['username'])): ?>
                <a href="/notifications" class="activeDesktop"><img src="/src/images/icons/bell.svg" alt=""> Notifications</a>
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
        <h1 class="srOnly">Notifications</h1>
        <div id="feed">
            <div id="timelineSelect">
            <button type="button" id="iconChirp" onclick="playChirpSound()" aria-label="Play the peep sound"><img src="/src/images/icons/chirp.svg" alt=""></button>
                <div>
                    <a id="forYou" class="selcted" href="/notifications">All</a>
                    <a id="following">Mentions</a>
                </div>
            </div>
         <div id="chirps" data-offset="0">
                <div class="chirp">           <!--
                    <a class="chirpClicker" href="/chirp?id=${chirp.id}">
                        <div class="chirpInfo">
                            <div>
                                <img class="userPic"
                                    src="/src/images/users/chirp/user.svg"
                                    alt="${chirp.name ? chirp.name : 'Guest'}">
                                    <img class="iconPic"
                                    src="/src/images/icons/rechirped.svg"
                                    alt="${chirp.name ? chirp.name : 'Guest'}">
                                <div>
                                    <p>Chirp <img class="verified" src="/src/images/icons/verified.svg"
                                            alt="Verified"> 
                                    </p>
                                    <p class="subText">rechirped your chirp</p>
                                </div>
                            </div>
                            <div class="timestampTimeline">
                                <p class="subText postedDate" data-timestamp="${chirp.timestamp}"></p>
                            </div>
                        </div>
                        <pre>${chirp.chirp}</pre>
                    </a>
                </div>
                <div class="chirp">
                    <a class="chirpClicker" href="/chirp?id=${chirp.id}">
                        <div class="chirpInfo">
                            <div>
                                <img class="userPic"
                                    src="/src/images/users/chirp/user.svg"
                                    alt="${chirp.name ? chirp.name : 'Guest'}">
                                    <img class="iconPic"
                                    src="/src/images/icons/liked.svg"
                                    alt="${chirp.name ? chirp.name : 'Guest'}">
                                <div>
                                    <p>Chirp <img class="verified" src="/src/images/icons/verified.svg"
                                            alt="Verified"> 
                                    </p>
                                    <p class="subText">liked your chirp</p>
                                </div>
                            </div>
                            <div class="timestampTimeline">
                                <p class="subText postedDate" data-timestamp="${chirp.timestamp}"></p>
                            </div>
                        </div>
                        <pre>${chirp.chirp}</pre>
                    </a>
                </div>
                <div class="chirp">
                <a class="chirpClicker" href="/chirp?id=${chirp.id}">
                            <div class="chirpInfo">
                                <div>
                                    <img class="userPic"
                                        src="/src/images/users/chirp/user.svg"
                                        alt="${chirp.name ? chirp.name : 'Guest'}">
                                    <div>
                                        <p>Chirp <img class="verified" src="/src/images/icons/verified.svg" alt="Verified">
                                        </p>
                                        <p class="subText">@chirp - replying to you</p>
                                    </div>
                                </div>
                                <div class="timestampTimeline">
                                    <p class="subText postedDate" data-timestamp="${chirp.timestamp}"></p>
                                </div>
                            </div>
                            <pre>bla bla reply test</pre>
                        </a>
                        <div class="chirpInteract">
                              <button type="button" class="reply" onclick="location.href='/chirp/?id=${chirp.id}'"><img alt="Reply" src="/src/images/icons/reply.svg"> <span class="reply-count">${chirp.reply_count}</span></button>
                            <a href="/chirp?id=${chirp.id}"></a>
                               <button type="button" class="rechirp" onclick="updateChirpInteraction(${chirp.id}, 'rechirp', this)"><img alt="Rechirp" src="/src/images/icons/${chirp.rechirped_by_current_user ? 'rechirped' : 'rechirp'}.svg"> <span class="rechirp-count">${chirp.rechirp_count}</span></button>
                            <a href="/chirp?id=${chirp.id}"></a>
                                 <button type="button" class="like" onclick="updateChirpInteraction(${chirp.id}, 'like', this)"><img alt="Like" src="/src/images/icons/${chirp.liked_by_current_user ? 'liked' : 'like'}.svg"> <span class="like-count">${chirp.like_count}</span></button>
                        </div>
                </div> -->
            </div>
            <div id="noMoreChirps">
                <p class="subText">Something went wrong.</p>
                <button class="followButton following tryAgain">↻ Try again</button>
            </div>
        </div>
    </main>
    <aside id="sideBar">
        <?php include '../include/sideBar.php';?>
    </aside>
    <footer>
        <div class="mobileMenuFooter">
            <a href="/"><img src="/src/images/icons/house.svg" alt="Home"></a>
            <a href="/discover"><img src="/src/images/icons/search.svg" alt="Discover"></a>
            <a href="/notifications" class="active"><img src="/src/images/icons/bell.svg" alt="Notifications"></a>
            <a href="/messages"><img src="/src/images/icons/envelope.svg" alt="Direct Messages"></a>
            <a
                href="<?php echo isset($_SESSION['username']) ? '/user?id=' . htmlspecialchars($_SESSION['username']) : '/signin'; ?>"><img
                    src="/src/images/icons/person.svg" alt="Profile"></a>
        </div>
    </footer>
    <?php include '../include/compose.php'; ?>
<div id="peepStatus" class="srOnly" role="status" aria-live="polite" data-flash="<?php echo peep_take_flash(); ?>"></div>
    <script src="/src/scripts/peep.js?v=<?php echo PEEP_ASSET_VERSION; ?>"></script>
</body>

</html>