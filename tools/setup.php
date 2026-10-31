<?php
/*
 * peep setup — run once from a terminal (never from the web):
 *
 *   php tools/setup.php --admin=yourname
 *
 * It will:
 *   1. create the database (from chirp.db.sample) at PEEP_DB, or ../chirp.db next to the site folder
 *   2. add the is_admin column peep uses
 *   3. create the admin account with a random password (printed ONCE)
 *   4. create invite codes (printed) — signup is invite-only
 *
 * Options:
 *   --admin=NAME            admin username (default: admin)
 *   --invites=N             how many invite codes to make (default: 3)
 *   --password-hash=HASH    use this password_hash() value instead of making a random password
 *   --invite-codes=A,B,C    use these invite codes instead of random ones
 *   --quiet                 print nothing on success
 *
 * Running it again is safe: an existing database and admin are left alone.
 */
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('Run this from a terminal.'); }
define('PEEP_NO_FILTER', true);
require_once __DIR__ . '/../include/config.php';

$opt = getopt('', ['admin::', 'invites::', 'password-hash::', 'invite-codes::', 'quiet']);
$admin = $opt['admin'] ?? 'admin';
$nInvites = max(0, (int)($opt['invites'] ?? 3));
$quiet = isset($opt['quiet']);
if (!preg_match('/^[A-Za-z0-9_]+$/', $admin)) { fwrite(STDERR, "Admin username: letters, numbers and _ only.\n"); exit(1); }

function code(int $n): string {
    $c = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; $s = '';
    for ($i = 0; $i < $n; $i++) { $s .= $c[random_int(0, strlen($c) - 1)]; }
    return $s;
}

$db = PEEP_DB;
$sample = __DIR__ . '/../chirp.db.sample';
if (!file_exists($db)) {
    if (!is_dir(dirname($db))) { mkdir(dirname($db), 0700, true); }
    if (!copy($sample, $db)) { fwrite(STDERR, "Could not create $db\n"); exit(1); }
    chmod($db, 0600);
    $quiet || print("Created database: $db\n");
}
$pdo = peep_db();
$pdo->exec('PRAGMA journal_mode = WAL');

$cols = array_column($pdo->query('PRAGMA table_info(users)')->fetchAll(PDO::FETCH_ASSOC), 'name');
if (!in_array('is_admin', $cols, true)) {
    $pdo->exec('ALTER TABLE users ADD COLUMN is_admin INTEGER NOT NULL DEFAULT 0');
}

$st = $pdo->prepare('SELECT id FROM users WHERE LOWER(username) = LOWER(:u)');
$st->execute(['u' => $admin]);
if ($st->fetchColumn()) {
    $pdo->prepare('UPDATE users SET is_admin = 1 WHERE LOWER(username) = LOWER(:u)')->execute(['u' => $admin]);
    $quiet || print("Admin @$admin already exists; left its password alone.\n");
} else {
    $plain = null;
    $hash = $opt['password-hash'] ?? null;
    if (!$hash) { $plain = code(4) . '-' . code(4) . '-' . code(4); $hash = password_hash($plain, PASSWORD_DEFAULT); }
    $ins = $pdo->prepare('INSERT INTO users (username, name, email, password_hash, usedInvite, is_admin, isVerified) VALUES (:u, :n, :e, :h, :i, 1, NULL)');
    $ins->execute(['u' => $admin, 'n' => $admin, 'e' => $admin . '@localhost', 'h' => $hash, 'i' => 'SETUP-ADMIN-' . code(6)]);
    $quiet || print("Admin account: @$admin\n");
    if ($plain && !$quiet) { print("Admin password (shown once, save it): $plain\n"); }
}

$given = array_filter(array_map('trim', explode(',', $opt['invite-codes'] ?? '')));
$have = (int)$pdo->query('SELECT COUNT(*) FROM invites')->fetchColumn();
$made = [];
if ($given) {
    foreach ($given as $c) { $pdo->prepare('INSERT OR IGNORE INTO invites (invite) VALUES (:c)')->execute(['c' => strtoupper($c)]); $made[] = strtoupper($c); }
} elseif ($have === 0) {
    for ($i = 0; $i < $nInvites; $i++) { $c = code(8); $pdo->prepare('INSERT INTO invites (invite) VALUES (:c)')->execute(['c' => $c]); $made[] = $c; }
}
if ($made && !$quiet) { print("Invite codes: " . implode(', ', $made) . "\n"); }
$quiet || print("Done. Start the site with:  php -S 0.0.0.0:8080\n");
