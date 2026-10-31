<?php
/*
 * peep configuration — the one place to rename the app or move the database.
 *
 * peep is a fork of Chirp by Adnan Bukvic and the Chirp contributors (MIT).
 * Adnan asks forks not to use the Chirp name, so the visible name lives here.
 * To rename the app, change PEEP_NAME (and PEEP_POST_WORD if you like) and
 * the README. Nothing else needs to change.
 */
if (defined('PEEP_NAME')) { return; }

// Never print PHP warnings into pages or JSON (they broke the timeline); log them instead.
ini_set('display_errors', '0');
ini_set('log_errors', '1');

define('PEEP_NAME', getenv('PEEP_NAME') ?: 'peep');          // shown in titles, menus, headings
define('PEEP_POST_WORD', getenv('PEEP_POST_WORD') ?: 'peep');  // what one post is called ("Peep", "Repeep")
define('PEEP_ASSET_VERSION', '2026-10-31f');  // bump after changing CSS/JS so browsers and caches fetch the new files
define('PEEP_SOURCE_URL', 'https://github.com/2three1y/peep');

// Database: PEEP_DB env var, else chirp.db one folder ABOVE the site (so it can never be downloaded).
define('PEEP_DB', getenv('PEEP_DB') ?: dirname(__DIR__, 2) . '/chirp.db');

function peep_db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO('sqlite:' . PEEP_DB);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec('PRAGMA busy_timeout = 5000');
    }
    return $pdo;
}

// The public host name, also behind a proxy that forwards it.
function peep_host(): string {
    // Behind Tab's site router the request arrives addressed to the box, so the public name comes from PEEP_PUBLIC_HOST.
    if (getenv('PEEP_PUBLIC_HOST')) { return getenv('PEEP_PUBLIC_HOST'); }
    $fwd = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? '';
    if ($fwd !== '') { return trim(explode(',', $fwd)[0]); }
    return $_SERVER['HTTP_HOST'] ?? '';
}
// True when a POST came from this same site (or from a client that sends no Origin header).
function peep_same_origin(): bool {
    $o = $_SERVER['HTTP_ORIGIN'] ?? '';
    if ($o === '') { return true; }
    $oh = parse_url($o, PHP_URL_HOST) . (parse_url($o, PHP_URL_PORT) ? ':' . parse_url($o, PHP_URL_PORT) : '');
    return $oh !== '' && ($oh === peep_host() || $oh === ($_SERVER['HTTP_HOST'] ?? ''));
}

function peep_is_https(): bool {
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
        || (strpos($_SERVER['HTTP_CF_VISITOR'] ?? '', '"https"') !== false);
}

// Session cookie: http-only, Lax, and Secure whenever the site is reached over https.
if (session_status() === PHP_SESSION_NONE && PHP_SAPI !== 'cli') {
    session_set_cookie_params([
        'lifetime' => 3456000, 'path' => '/', 'domain' => '',
        'secure' => peep_is_https(), 'httponly' => true, 'samesite' => 'Lax',
    ]);
}

// Admins are users with is_admin = 1 (set by setup.php). Replaces Chirp's hardcoded "chirp" username.
function peep_is_admin(): bool {
    if (session_status() === PHP_SESSION_NONE) { @session_start(); }
    if (empty($_SESSION['username'])) { return false; }
    static $cache = [];
    $u = strtolower($_SESSION['username']);
    if (!array_key_exists($u, $cache)) {
        try {
            $st = peep_db()->prepare('SELECT is_admin FROM users WHERE LOWER(username) = :u');
            $st->execute(['u' => $u]);
            $cache[$u] = (int)$st->fetchColumn() === 1;
        } catch (Throwable $e) { $cache[$u] = false; }
    }
    return $cache[$u];
}


// One-time messages ("Posted", "Reply posted") read out by the page's single polite live region.
function peep_flash(string $msg): void {
    if (session_status() === PHP_SESSION_NONE) { @session_start(); }
    $_SESSION['peep_flash'] = $msg;
}
function peep_take_flash(): string {
    if (session_status() === PHP_SESSION_NONE) { @session_start(); }
    $m = $_SESSION['peep_flash'] ?? '';
    unset($_SESSION['peep_flash']);
    return htmlspecialchars($m, ENT_QUOTES);
}

/*
 * Visible-name filter: the pages still say "Chirp" in many places (Adnan's original markup).
 * Instead of hand-editing 40 files, HTML output passes through this filter, which renames
 * the brand ONLY in visible text and in alt/title/aria-label/placeholder/content attributes
 * (never in URLs, ids, classes or scripts).
 */
function peep_brand_text(string $t): string {
    $P = PEEP_POST_WORD; $Pc = ucfirst($P); $N = PEEP_NAME;
    $map = [
        '/\bRechirps\b/' => 'Re' . $P . 's', '/\bRechirp\b/' => 'Re' . $P, '/\brechirps\b/' => 're' . $P . 's', '/\brechirp\b/' => 're' . $P,
        '/\bRechirped\b/' => 'Re' . $P . 'ed', '/\brechirped\b/' => 're' . $P . 'ed',
        '/\bChirp Beta 0\.7b\b/' => $N . ' (based on Chirp Beta 0.7b)',
        '/\bChirps source code\b/' => $N . "'s source code",
        '/\bChirpie\b/' => $N,
        '/\bChirps\b/' => $Pc . 's', '/\bChirp\b/' => $Pc, '/\bchirps\b/' => $P . 's', '/\bchirp\b/' => $P,
    ];
    // Keep the credit to the original project readable.
    $t = preg_replace('/\bfork of Chirp\b/', 'fork of CHIRP_KEEP', $t);
    $t = preg_replace(array_keys($map), array_values($map), $t);
    $t = str_replace('CHIRP_KEEP', 'Chirp', $t);
    // The site name itself (PEEP_NAME) is lowercase by design; titles read "Home - peep".
    if ($N !== ucfirst($P)) { $t = preg_replace('/ - ' . preg_quote($Pc, '/') . '$/', ' - ' . $N, $t); }
    return $t;
}

function peep_brand_filter(string $html): string {
    $trim = ltrim($html);
    if ($trim === '' || $trim[0] !== '<') { return $html; }   // JSON and other non-HTML output untouched
    foreach (headers_list() as $h) {                          // only real HTML pages
        if (stripos($h, 'content-type:') === 0 && stripos($h, 'text/html') === false) { return $html; }
    }
    $parts = preg_split('/(<script\b.*?<\/script>|<style\b.*?<\/style>|<[^>]*>)/is', $html, -1, PREG_SPLIT_DELIM_CAPTURE);
    foreach ($parts as $i => $p) {
        if ($p === '') { continue; }
        if ($p[0] === '<') {
            if (preg_match('/^<(script|style)\b/i', $p)) { continue; }
            $parts[$i] = preg_replace_callback('/\b(alt|title|aria-label|placeholder|content)="([^"]*)"/i',
                function ($m) { return $m[1] . '="' . peep_brand_text($m[2]) . '"'; }, $p);
        } else {
            $parts[$i] = peep_brand_text($p);
        }
    }
    return implode('', $parts);
}

if (PHP_SAPI !== 'cli' && !defined('PEEP_NO_FILTER')) {
    ob_start('peep_brand_filter');
}
