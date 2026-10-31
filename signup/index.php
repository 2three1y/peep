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
    <title>Get started chirping - Chirp</title>
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
        <div id="feed">
            <button type="button" id="iconChirp" onclick="playChirpSound()" aria-label="Play the peep sound"><img src="/src/images/icons/chirp.svg" alt=""></button>
            <div class="title">
                <h1 class="selected">Sign up</h1>
            </div>
            <div id="signUp">
                <p>Let's make your account. You'll need an invite code from a friend who's already here, or from the admin.</p>
                <form id="signupForm" class="peepForm" method="post" action="/signup/signup.php" novalidate>
                    <fieldset id="inviteCode">
                        <legend>Step 1 of 3: Invite code</legend>
                        <label for="code">Invite code</label>
                        <input type="text" id="code" name="code" autocomplete="off" autocapitalize="characters" spellcheck="false" required>
                        <button type="button" class="followButton" onclick="showNextSection('nameUser')">Next</button>
                    </fieldset>
                    <fieldset id="nameUser">
                        <legend>Step 2 of 3: About you</legend>
                        <label for="name">Display name</label>
                        <input type="text" id="name" name="name" autocomplete="nickname" required>
                        <label for="username">Username (letters, numbers and _ only)</label>
                        <input type="text" id="username" name="username" autocomplete="username" autocapitalize="none" spellcheck="false" pattern="[A-Za-z0-9_]+" required>
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" autocomplete="email" required>
                        <button type="button" class="followButton" onclick="showNextSection('pwordUser')">Next</button>
                    </fieldset>
                    <fieldset id="pwordUser">
                        <legend>Step 3 of 3: Password</legend>
                        <label for="pword">Password</label>
                        <input type="password" id="pword" name="pword" autocomplete="new-password" required>
                        <label for="pwordConfirm">Password again</label>
                        <input type="password" id="pwordConfirm" name="pwordConfirm" autocomplete="new-password" required>
                        <button type="submit" class="followButton">Create account</button>
                    </fieldset>
                </form>
                <p id="signupError" class="peepError" role="alert" hidden></p>
                <p class="subText"><a href="/signin/">Already have an account? Sign in</a></p>
            </div>
        </div>
    </main>
    <aside id="sideBar">


        <div class="peepCredit">
    <p class="subText"><?php echo htmlspecialchars(PEEP_NAME); ?> is a fork of Chirp by <a class="subText" href="https://aridan.net" rel="noopener noreferrer">Adnan Bukvic</a> and the Chirp contributors, MIT license.</p>
    <p class="subText"><a class="subText" href="<?php echo PEEP_SOURCE_URL; ?>" rel="noopener noreferrer">Source code on GitHub</a>. Co-written with Tab. <a class="subText" href="https://2three1y.github.io/">Made by Chaotic Carter</a>.</p>
</div>
    </aside>
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
    <?php include '../include/compose.php'; ?>
    <script>
    function showNextSection(nextSectionId) {
        const sections = ['inviteCode', 'nameUser', 'pwordUser'];
        const current = sections.find(id => document.getElementById(id).style.display !== 'none' && getComputedStyle(document.getElementById(id)).display !== 'none');
        if (current) {
            const empty = Array.from(document.getElementById(current).querySelectorAll('input[required]')).find(i => !i.value.trim());
            if (empty) { showSignupError('Please fill in ' + document.querySelector('label[for="' + empty.id + '"]').textContent + '.'); empty.focus(); return; }
        }
        document.getElementById('signupError').hidden = true;
        sections.forEach(id => { document.getElementById(id).style.display = (id === nextSectionId) ? 'block' : 'none'; });
        const first = document.getElementById(nextSectionId).querySelector('input');
        if (first) first.focus();
    }
    function showSignupError(msg) {
        const el = document.getElementById('signupError');
        el.textContent = msg; el.hidden = false;
    }
    const signupMessages = {
        'Invalid invite code': 'That invite code does not work. Check it, or ask for a new one.',
        'Invite already used': 'That invite code was already used. Ask for a new one.',
        'Username already in use': 'That username is taken. Pick another one.',
        'Passwords do not match': 'The two passwords do not match.',
        'This username is reserved.': 'That username is reserved.',
        'Invite not reserved for this username': 'That invite code is saved for a different username.'
    };
    const goTo = {
        'Invalid invite code': 'inviteCode', 'Invite already used': 'inviteCode', 'Invite not reserved for this username': 'inviteCode',
        'Username already in use': 'nameUser', 'This username is reserved.': 'nameUser', 'Passwords do not match': 'pwordUser'
    };
    document.getElementById('signupForm').addEventListener('submit', function (event) {
        event.preventDefault();
        const form = this;
        fetch(form.action, { method: 'POST', body: new FormData(form) })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    peepAnnounce('Account created. Taking you to sign in.');
                    setTimeout(() => { window.location.href = '/signin/'; }, 900);
                    return;
                }
                const err = data.error || 'Something went wrong.';
                if (goTo[err]) {
                    ['inviteCode', 'nameUser', 'pwordUser'].forEach(id => { document.getElementById(id).style.display = (id === goTo[err]) ? 'block' : 'none'; });
                }
                showSignupError(signupMessages[err] || (err.indexOf('Invalid username') === 0 ? 'Usernames can only use letters, numbers and _.' : err));
                const box = document.getElementById(goTo[err] || 'pwordUser');
                const field = box && box.querySelector('input'); if (field) field.focus();
            })
            .catch(() => showSignupError('Could not reach the server. Try again.'));
    });
    </script>
<div id="peepStatus" class="srOnly" role="status" aria-live="polite" data-flash="<?php echo peep_take_flash(); ?>"></div>
    <script src="/src/scripts/peep.js?v=<?php echo PEEP_ASSET_VERSION; ?>"></script>
</body>

</html>