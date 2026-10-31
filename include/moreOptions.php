<?php require_once __DIR__ . '/../include/config.php'; ?>
<div class="morePostOptionWrapper">
    <button type="button" class="morePostOptions" aria-label="More options" aria-expanded="false" aria-controls="moreOptionsModal"
        onclick="var m=document.getElementById('moreOptionsModal');var open=!m.classList.contains('openOptions');m.classList.toggle('openOptions',open);this.setAttribute('aria-expanded',open);if(open){m.querySelector('button').focus();}">
        <svg class="peepIcon" aria-hidden="true" focusable="false" viewBox="0 0 24 24" width="1em" height="1em"><circle cx="5" cy="12" r="2.2" fill="currentColor"/><circle cx="12" cy="12" r="2.2" fill="currentColor"/><circle cx="19" cy="12" r="2.2" fill="currentColor"/></svg>
    </button>
    <div class="morePostOptionsModal" id="moreOptionsModal">
        <ul>
            <!-- peep: Chirp's Edit / Delete items were never wired up, so they are not shown. -->
            <li><button type="button" class="peepMenuItem" onclick="navigator.clipboard.writeText(document.querySelector('.chirpThread pre').innerText).then(function(){peepAnnounce('Copied the text')});">📋 Copy text</button></li>
            <li><button type="button" class="peepMenuItem" onclick="navigator.clipboard.writeText(location.href).then(function(){peepAnnounce('Copied the link')});">🔗 Copy link</button></li>
        </ul>
    </div>
</div>
