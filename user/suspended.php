<?php require_once __DIR__ . '/../include/config.php'; ?>
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
    <title>Account Suspended - Chirp</title>
</head>

<body>
    <main>
        <h1 class="srOnly">Account Suspended</h1>
        <div id="feed">
            <button type="button" id="iconChirp" onclick="playChirpSound()" aria-label="Play the peep sound"><img src="/src/images/icons/chirp.svg" alt=""></button>
            <div id="notFound">
                <p>This account is temporarily suspended.</p>
                <p class="subText">Chirp regularly suspends accounts that break <a href="rules">our rules</a>.</p>
                <p class="subText">This suspension will expire in:</p>
                <p id="expirationDate">29<span>d</span>23<span>h</span>59<span>m</span></p>
            </div>
        </div>
    </main>
<div id="peepStatus" class="srOnly" role="status" aria-live="polite" data-flash="<?php echo peep_take_flash(); ?>"></div>
    <script src="/src/scripts/peep.js?v=<?php echo PEEP_ASSET_VERSION; ?>"></script>
</body>