<?php require_once __DIR__ . '/../include/config.php'; ?>


<?php if (isset($_SESSION['username'])): ?>

<?php endif; ?>
<div class="peepCredit">
    <p class="subText"><?php echo htmlspecialchars(PEEP_NAME); ?> is a fork of Chirp by <a class="subText" href="https://aridan.net" rel="noopener noreferrer">Adnan Bukvic</a> and the Chirp contributors, MIT license.</p>
    <p class="subText"><a class="subText" href="<?php echo PEEP_SOURCE_URL; ?>" rel="noopener noreferrer">Source code on GitHub</a>. Co-written with Tab. <a class="subText" href="https://2three1y.github.io/">Made by Chaotic Carter</a>.</p>
</div>