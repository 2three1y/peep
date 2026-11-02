<?php
require_once __DIR__ . '/../../include/config.php';
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
    <title>Security and Login - Chirp</title>
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
        <div id="feed" class="settingsPageContainer">
            <button type="button" id="iconChirp" onclick="playChirpSound()" aria-label="Play the peep sound"><img src="/src/images/icons/chirp.svg" alt=""></button>
            <div class="title">
                <h1 class="selected">Settings</h1>
                <p class="selcted settingsTab">Security and Login</p>
            </div>
            <div id="settings">

                <div id="settingsExpand">
                    <ul>
                        <li>
                            <a class="settingsMenuLink" href="/settings/account">👤 Account</a>
                        </li>
                        <li>
                            <a class="settingsMenuLink" href="/settings/content-you-see">📝 Content you see</a>
                        </li>
                        <li>
                            <a class="settingsMenuLink" href="/settings/appearance-and-accessibility">🎨 Appearance and
                                accessibility</a>
                        </li>
                        <li  class="activeDesktop">
                            <a class="settingsMenuLink" href="/settings/security-and-login">🔐 Security and Login</a>
                        </li>
                        <li>
                            <a class="settingsMenuLink" href="/settings/privacy-and-safety">👁️ Privacy and Safety</a>
                        </li>
                        <li>
                            <a class="settingsMenuLink" href="/settings/notifications">🔔 Notifications</a>
                        </li>
                        <li>
                        </li>
                    </ul>

                </div>
                <div id="expandedSettings">
                    <section aria-labelledby="h-password" class="peepPanel">
                        <h2 id="h-password">Change your password</h2>
                        <?php if (isset($_SESSION['username'])): ?>
                        <form id="changePasswordForm" class="peepForm" method="post" action="/settings/security-and-login/changePassword.php">
                            <label for="currentPassword">Current password</label>
                            <input type="password" id="currentPassword" name="currentPassword" autocomplete="current-password">
                            <label for="newPassword">New password (at least 8 characters)</label>
                            <input type="password" id="newPassword" name="newPassword" autocomplete="new-password" minlength="8">
                            <label for="confirmPassword">New password again</label>
                            <input type="password" id="confirmPassword" name="confirmPassword" autocomplete="new-password" minlength="8">
                            <button type="submit" class="followButton">Change password</button>
                        </form>
                        <p class="subText">Forgot your password? Ask an admin to reset it for you.</p>
                        <?php else: ?>
                        <p class="subText"><a href="/signin/">Sign in</a> to change your password.</p>
                        <?php endif; ?>
                    </section>
                </div>
            </div>
    </main>
    <footer>
        <div class="mobileMenuFooter">
            <a href="/"><img src="/src/images/icons/house.svg" alt="Home"></a>
            <a href="/discover"><img src="/src/images/icons/search.svg" alt="Discover"></a>
            <a href="/notifications"><img src="/src/images/icons/bell.svg" alt="Notifications"></a>
            <a href="/messages"><img src="/src/images/icons/envelope.svg" alt="Direct Messages"></a>
            <a
                href="<?php echo isset($_SESSION['username']) ? '/user?id=' . htmlspecialchars($_SESSION['username']) : '/signin'; ?>"><img
                    src="/src/images/icons/person.svg" alt="Profile"></a>
        </div>
    </footer>
    <?php include '../../include/compose.php'; ?>
<script>
    (function () {
        var f = document.getElementById('changePasswordForm');
        if (!f) return;
        f.addEventListener('submit', function (e) {
            e.preventDefault();
            fetch(f.action, { method: 'POST', body: new FormData(f) })
                .then(function (r) { return r.json(); })
                .then(function (d) {
                    if (d.success) { f.reset(); peepAnnounce('Password changed'); }
                    else { peepAnnounce(d.error || 'Could not change the password'); }
                })
                .catch(function () { peepAnnounce('Could not reach the server'); });
        });
    })();
    </script>
    <div id="peepStatus" class="srOnly" role="status" aria-live="polite" data-flash="<?php echo peep_take_flash(); ?>"></div>
    <script src="/src/scripts/peep.js?v=<?php echo PEEP_ASSET_VERSION; ?>"></script>
</body>

</html>