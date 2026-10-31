<?php
require_once __DIR__ . '/../include/config.php';
session_start();

// Check if the user is signed in and the username is "chirp"
if (!peep_is_admin()) {
    // If the user is not an admin, show 403 Forbidden
    echo '
    <!DOCTYPE html>
    <html lang="en-US">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="/src/styles/styles.css?v=<?php echo PEEP_ASSET_VERSION; ?>" rel="stylesheet">
        <link href="/src/styles/timeline.css?v=<?php echo PEEP_ASSET_VERSION; ?>" rel="stylesheet">
        <link href="/src/styles/menus.css?v=<?php echo PEEP_ASSET_VERSION; ?>" rel="stylesheet">
        <link href="/src/styles/responsive.css?v=<?php echo PEEP_ASSET_VERSION; ?>" rel="stylesheet">
        <title>403 Forbidden</title>
    </head>
    <body>
        <div id="feed" class="settingsPageContainer">
            <button type="button" id="iconChirp" onclick="playChirpSound()" aria-label="Play the peep sound"><img src="/src/images/icons/chirp.svg" alt=""></button>
            <div class="title">
                <h1 class="selected">403 Forbidden</h1>
            </div>
            <div id="noMoreChirps">
                <p class="subText">Your account is not allowed to perform this action.</p>
                <a class="followButton following tryAgain" href="/">Go back home</a>
            </div>
        </div>
    </body>
    </html>';
    exit;
}

// Connect to the database
$db = peep_db();

// peep admin actions (server-side forms, no JavaScript needed).
$db = peep_db();
if (empty($_SESSION['peep_csrf'])) { $_SESSION['peep_csrf'] = bin2hex(random_bytes(16)); }
$csrf = $_SESSION['peep_csrf'];
$notice = null; $noticeIsError = false; $secretShown = null;

function peep_invite_code(int $length = 8): string {
    $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // no 0/O/1/I, easier to read out loud
    $s = '';
    for ($i = 0; $i < $length; $i++) { $s .= $chars[random_int(0, strlen($chars) - 1)]; }
    return $s;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($csrf, $_POST['csrf'] ?? '')) {
        $notice = 'That form expired. Please try again.'; $noticeIsError = true;
    } elseif (($_POST['action'] ?? '') === 'create_invite') {
        $code = peep_invite_code();
        $stmt = $db->prepare('INSERT INTO invites (invite) VALUES (:invite)');
        $stmt->execute(['invite' => $code]);
        $notice = 'New invite code created.'; $secretShown = ['Invite code', $code];
    } elseif (($_POST['action'] ?? '') === 'reset_password') {
        $who = trim($_POST['resetUsername'] ?? '');
        $stmt = $db->prepare('SELECT id, username FROM users WHERE LOWER(username) = LOWER(:u)');
        $stmt->execute(['u' => $who]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user) {
            $notice = 'No user called "' . $who . '".'; $noticeIsError = true;
        } else {
            $temp = peep_invite_code(4) . '-' . peep_invite_code(4) . '-' . peep_invite_code(4);
            $stmt = $db->prepare('UPDATE users SET password_hash = :h WHERE id = :id');
            $stmt->execute(['h' => password_hash($temp, PASSWORD_DEFAULT), 'id' => $user['id']]);
            $notice = 'Password reset for @' . $user['username'] . '. Give them this temporary password; they can change it in Settings, Security and login.';
            $secretShown = ['Temporary password for @' . $user['username'], $temp];
        }
    } elseif (($_POST['action'] ?? '') === 'download_backup') {
        // A copy of the whole database (users, posts, likes). Keep it private: it has emails and password hashes.
        $tmp = sys_get_temp_dir() . '/peep-backup-' . bin2hex(random_bytes(6)) . '.db';
        $db->exec('VACUUM INTO ' . $db->quote($tmp));
        while (ob_get_level()) { ob_end_clean(); }
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="peep-backup-' . date('Y-m-d') . '.db"');
        header('Content-Length: ' . filesize($tmp));
        readfile($tmp); unlink($tmp);
        exit;
    } elseif (($_POST['action'] ?? '') === 'delete_invite') {
        $st = $db->prepare('DELETE FROM invites WHERE invite = :c AND UPPER(invite) NOT IN (SELECT UPPER(usedInvite) FROM users)');
        $st->execute(['c' => $_POST['invite'] ?? '']);
        $notice = $st->rowCount() ? 'Invite code deleted.' : 'That code is in use or already gone.';
        $noticeIsError = !$st->rowCount();
    } elseif (($_POST['action'] ?? '') === 'delete_user') {
        $who = trim($_POST['deleteUsername'] ?? '');
        $st = $db->prepare('SELECT id, username, is_admin FROM users WHERE LOWER(username) = LOWER(:u)');
        $st->execute(['u' => $who]);
        $victim = $st->fetch(PDO::FETCH_ASSOC);
        if (!$victim) {
            $notice = 'No user called "' . $who . '".'; $noticeIsError = true;
        } elseif ((int)$victim['is_admin'] === 1) {
            $notice = 'Admins cannot be deleted here.'; $noticeIsError = true;
        } elseif (($_POST['confirmDelete'] ?? '') !== $victim['username']) {
            $notice = 'To delete @' . $victim['username'] . ', type their username again in the confirm box.'; $noticeIsError = true;
        } else {
            $id = (int)$victim['id'];
            $db->beginTransaction();
            $mine = 'SELECT id FROM chirps WHERE user = ' . $id;
            $db->exec("DELETE FROM likes WHERE user_id = $id OR chirp_id IN ($mine)");
            $db->exec("DELETE FROM rechirps WHERE user_id = $id OR chirp_id IN ($mine)");
            $db->exec("DELETE FROM chirps WHERE parent IN ($mine)");
            $db->exec("DELETE FROM chirps WHERE user = $id");
            $db->exec("DELETE FROM following WHERE follower_id = $id OR following_id = $id");
            $m = $db->prepare('DELETE FROM messages WHERE "from" = :u OR "to" = :u');
            $m->execute(['u' => $victim['username']]);
            // retire their invite code too, so it cannot be used again
            $db->exec("DELETE FROM invites WHERE UPPER(invite) = (SELECT UPPER(usedInvite) FROM users WHERE id = $id)");
            $db->exec("DELETE FROM users WHERE id = $id");
            $db->commit();
            $notice = 'Deleted @' . $victim['username'] . ' and everything they posted.';
        }
    } elseif (($_POST['action'] ?? '') === 'migrate_user') {
        $from = trim($_POST['migrateFrom'] ?? ''); $to = trim($_POST['migrateTo'] ?? '');
        if (!preg_match('/^[A-Za-z0-9_]+$/', $to)) {
            $notice = 'New username can only use letters, numbers and underscores.'; $noticeIsError = true;
        } else {
            $stmt = $db->prepare('UPDATE users SET username = :to WHERE LOWER(username) = LOWER(:from)');
            $stmt->execute(['to' => $to, 'from' => $from]);
            if ($stmt->rowCount()) { $notice = 'Moved @' . $from . ' to @' . $to . '.'; }
            else { $notice = 'No user called "' . $from . '".'; $noticeIsError = true; }
        }
    }
}
$invites = $db->query('SELECT i.invite, u.username FROM invites i LEFT JOIN users u ON UPPER(u.usedInvite) = UPPER(i.invite) ORDER BY i.id DESC LIMIT 100')->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="en-US">

<head>
    <meta charset="UTF-8">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

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
    <title>Admin panel - Chirp</title>
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
        <?php if (!peep_is_admin()) : ?>
        <div id="feed" class="settingsPageContainer">
            <div class="title">
                <h1 class="selected">403 Forbidden</h1>
            </div>
            <div id="noMoreChirps">
                <p class="subText">Your account is not allowed to perform this action.</p>
                <a class="followButton following tryAgain" href="/">Go back home</a>
            </div>
        </div>
        <?php else : ?>
        <div id="feed" class="settingsPageContainer peepAdmin">
            <div class="title">
                <h1 class="selected">Admin panel</h1>
            </div>
            <?php if ($notice): ?>
            <div class="peepNotice<?php echo $noticeIsError ? ' peepError' : ''; ?>" role="alert">
                <p><?php echo htmlspecialchars($notice); ?></p>
                <?php if ($secretShown): ?>
                <p><?php echo htmlspecialchars($secretShown[0]); ?>: <strong class="peepCode"><?php echo htmlspecialchars($secretShown[1]); ?></strong></p>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <section aria-labelledby="h-invites">
                <h2 id="h-invites">Invite codes</h2>
                <p class="subText">Signup is invite-only. Each code works for one new account. Send a code to a friend you know.</p>
                <form method="post">
                    <input type="hidden" name="csrf" value="<?php echo $csrf; ?>">
                    <input type="hidden" name="action" value="create_invite">
                    <button type="submit" class="followButton">Make a new invite code</button>
                </form>
                <h3>Recent codes</h3>
                <?php if ($invites): ?>
                <ul class="peepList">
                    <?php foreach ($invites as $inv): ?>
                    <li><span class="peepCode"><?php echo htmlspecialchars($inv['invite']); ?></span>:
                        <?php echo $inv['username'] ? 'used by @' . htmlspecialchars($inv['username']) : 'not used yet'; ?>
                        <?php if (!$inv['username']): ?>
                        <form method="post" class="peepInline">
                            <input type="hidden" name="csrf" value="<?php echo $csrf; ?>">
                            <input type="hidden" name="action" value="delete_invite">
                            <input type="hidden" name="invite" value="<?php echo htmlspecialchars($inv['invite']); ?>">
                            <button type="submit" class="underlinedButton" aria-label="Delete code <?php echo htmlspecialchars(implode(' ', str_split($inv['invite']))); ?>">Delete</button>
                        </form>
                        <?php endif; ?></li>
                    <?php endforeach; ?>
                </ul>
                <?php else: ?>
                <p class="subText">No invite codes yet.</p>
                <?php endif; ?>
            </section>

            <section aria-labelledby="h-reset">
                <h2 id="h-reset">Reset someone's password</h2>
                <p class="subText">Makes a new temporary password and shows it here once. Only admins can do this.</p>
                <form method="post" class="peepForm">
                    <input type="hidden" name="csrf" value="<?php echo $csrf; ?>">
                    <input type="hidden" name="action" value="reset_password">
                    <label for="resetUsername">Username</label>
                    <input type="text" id="resetUsername" name="resetUsername" autocomplete="off" autocapitalize="none" required>
                    <button type="submit" class="followButton">Reset password</button>
                </form>
            </section>

            <section aria-labelledby="h-delete">
                <h2 id="h-delete">Remove someone</h2>
                <p class="subText">Deletes the account, their posts, replies to their posts, their likes and follows. This cannot be undone.</p>
                <form method="post" class="peepForm">
                    <input type="hidden" name="csrf" value="<?php echo $csrf; ?>">
                    <input type="hidden" name="action" value="delete_user">
                    <label for="deleteUsername">Username</label>
                    <input type="text" id="deleteUsername" name="deleteUsername" autocomplete="off" autocapitalize="none" required>
                    <label for="confirmDelete">Type the username again to confirm</label>
                    <input type="text" id="confirmDelete" name="confirmDelete" autocomplete="off" autocapitalize="none" required>
                    <button type="submit" class="followButton">Delete account</button>
                </form>
            </section>

            <section aria-labelledby="h-backup">
                <h2 id="h-backup">Backup</h2>
                <p class="subText">Download a copy of everything on this site. Keep it private: it has emails and password hashes.</p>
                <form method="post">
                    <input type="hidden" name="csrf" value="<?php echo $csrf; ?>">
                    <input type="hidden" name="action" value="download_backup">
                    <button type="submit" class="followButton">Download backup</button>
                </form>
            </section>

            <section aria-labelledby="h-migrate">
                <h2 id="h-migrate">Change someone's username</h2>
                <form method="post" class="peepForm">
                    <input type="hidden" name="csrf" value="<?php echo $csrf; ?>">
                    <input type="hidden" name="action" value="migrate_user">
                    <label for="migrateFrom">Current username</label>
                    <input type="text" id="migrateFrom" name="migrateFrom" autocomplete="off" autocapitalize="none" required>
                    <label for="migrateTo">New username</label>
                    <input type="text" id="migrateTo" name="migrateTo" autocomplete="off" autocapitalize="none" required>
                    <button type="submit" class="followButton">Change username</button>
                </form>
            </section>
        </div>
        <?php endif; ?>
    </main>
    <footer>
        <div class="mobileMenuFooter">
            <a href="/"><img src="/src/images/icons/house.svg" alt="Home"></a>
            <a href="/discover"><img src="/src/images/icons/search.svg" alt="Discover"></a>
            <a href="/notifications"><img src="/src/images/icons/bell.svg" alt="Notifications"></a>
            <a href="/messages"><img src="/src/images/icons/envelope.svg" alt="Direct Messages"></a>
            <a href="<?php echo isset($_SESSION['username']) ? '/user?id=' . htmlspecialchars($_SESSION['username']) : '/signin'; ?>"><img src="/src/images/icons/person.svg" alt="Profile"></a>
        </div>
    </footer>
    <div id="peepStatus" class="srOnly" role="status" aria-live="polite" data-flash="<?php echo peep_take_flash(); ?>"></div>
    <script src="/src/scripts/peep.js?v=<?php echo PEEP_ASSET_VERSION; ?>"></script>
</body>

</html>