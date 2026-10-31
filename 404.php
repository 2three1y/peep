<?php require_once __DIR__ . '/include/config.php'; ?>
<!DOCTYPE html>
<html lang="en-US">
<head>
  <meta charset="UTF-8">
  <meta name="theme-color" content="#00001"/>
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
  <title>404 - Chirp</title>
</head>
<body>
    <div id="menuSettings">
        <a href="settings/account">⚙️ Settings</a>
        <a>👪 Add another account</a>
        <a>🚪 Sign in</a>
    </div>
    <header>
        <div id="desktopMenu">
            <nav>
         <button type="button" class="logoButton" onclick="playChirpSound()" aria-label="Play the peep sound"><img src="/src/images/icons/chirp.svg" alt=""></button>
                <a href="/"><img src="/src/images/icons/house.svg" alt=""> Home</a>
                <a href="/discover"><img src="/src/images/icons/search.svg" alt=""> Discover</a>
                <a href="/notifications"><img src="/src/images/icons/bell.svg" alt=""> Notifications</a>
                <a href="/messages"><img src="/src/images/icons/envelope.svg" alt=""> Direct Messages</a>
                <a
                href="<?php echo isset($_SESSION['username']) ? '/user?id=' . htmlspecialchars($_SESSION['username']) : '/signin'; ?>"><img
                    src="/src/images/icons/person.svg" alt=""> Profile</a>
                <?php if (isset($_SESSION['username'])): ?>
                    <button class="newchirp" onclick="openNewChirpModal()">Chirp</button>
                <?php endif; ?>
            </nav>
            <button id="settingsButtonWrapper" type="button" onclick=showMenuSettings()>
                <img class="userPic" src="/src/images/users/guest/user.svg" alt="aridan">
                <div>
                    <p>Guest</p>
                    <p class="subText">@guest</p>
                </div>
                <p class="settingsButton">⚙️</p>
            </button>
        </div>
    </header>
    <main>
        <h1 class="srOnly">Page not found</h1>
        <div id="feed">
            <button type="button" id="iconChirp" onclick="playChirpSound()" aria-label="Play the peep sound"><img src="/src/images/icons/chirp.svg" alt=""></button>
            <div id="notFound">
                <p>404 / Not Found</p>
                <p class="subText">Chirpie has searched everywhere but can't find what you were looking for.<br>Maybe it flew away?</p>
            </div>
        </div>
    </main>
    <aside id="sideBar">


        </div>
        <div class="peepCredit">
    <p class="subText"><?php echo htmlspecialchars(PEEP_NAME); ?> is a fork of Chirp by <a class="subText" href="https://aridan.net" rel="noopener noreferrer">Adnan Bukvic</a> and the Chirp contributors, MIT license.</p>
    <p class="subText"><a class="subText" href="<?php echo PEEP_SOURCE_URL; ?>" rel="noopener noreferrer">Source code on GitHub</a>. Co-written with Tab. <a class="subText" href="https://2three1y.github.io/">Made by Chaotic Carter</a>.</p>
</div>
    </aside>
    <footer>
    </footer>
<div id="peepStatus" class="srOnly" role="status" aria-live="polite" data-flash="<?php echo peep_take_flash(); ?>"></div>
    <script src="/src/scripts/peep.js?v=<?php echo PEEP_ASSET_VERSION; ?>"></script>
</body>
</html>
