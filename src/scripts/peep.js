/*
 * peep.js — accessibility layer for peep (a fork of Chirp by Adnan Bukvic, MIT).
 * Loaded last on every page. Co-written with Tab.
 *
 * - one polite live region (#peepStatus) for short news: "Posted", "Liked"
 * - every post is an <article> with a heading, so VoiceOver's heading rotor jumps post to post
 * - like / repost are toggle buttons with aria-pressed and a spoken count
 * - compose and reply open as real dialogs (focus moves in, Escape closes, focus goes back)
 * - emoji stay native (no Twemoji images), so VoiceOver reads them by name
 */
(function () {
    'use strict';

    // Twemoji was loaded from a CDN in Chirp. peep keeps native emoji; this stub keeps old calls harmless.
    window.twemoji = window.twemoji || { parse: function () {} };

    var POST = 'peep', POSTS = 'peeps';

    // ---------- live region ----------
    function statusEl() {
        var el = document.getElementById('peepStatus');
        if (!el) {
            el = document.createElement('div');
            el.id = 'peepStatus'; el.className = 'srOnly';
            el.setAttribute('role', 'status'); el.setAttribute('aria-live', 'polite');
            document.body.appendChild(el);
        }
        return el;
    }
    var announceTimer = null;
    window.peepAnnounce = function (msg) {
        var el = statusEl();
        el.textContent = '';
        clearTimeout(announceTimer);
        announceTimer = setTimeout(function () { el.textContent = msg; }, 120);
    };

    function plural(n, one, many) { n = parseInt(n, 10) || 0; return n + ' ' + (n === 1 ? one : many); }
    function textOf(html) { var d = document.createElement('div'); d.innerHTML = html || ''; return d.textContent.trim(); }

    // ---------- timestamps: short on screen, full words for VoiceOver ----------
    function relative(ts) {
        var d = new Date(parseInt(ts, 10) * 1000), s = Math.floor((Date.now() - d) / 1000);
        if (isNaN(s)) return null;
        var m = Math.floor(s / 60), h = Math.floor(m / 60), day = Math.floor(h / 24);
        if (s < 60) return ['now', 'just now'];
        if (m < 60) return [m + 'm', plural(m, 'minute', 'minutes') + ' ago'];
        if (h < 24) return [h + 'h', plural(h, 'hour', 'hours') + ' ago'];
        if (day < 7) return [day + 'd', plural(day, 'day', 'days') + ' ago'];
        var full = d.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
        return [d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }), full];
    }
    window.updatePostedDates = function () {
        document.querySelectorAll('.chirp .postedDate[data-timestamp]').forEach(function (p) {
            var r = relative(p.getAttribute('data-timestamp'));
            if (!r || p.getAttribute('data-shown') === r[1]) return;
            p.setAttribute('data-shown', r[1]);
            p.innerHTML = '<span aria-hidden="true">' + r[0] + '</span><span class="srOnly">' + r[1] + '</span>';
        });
    };

    // ---------- toggle buttons ----------
    function setToggle(btn, kind, on, count) {
        count = parseInt(count, 10) || 0;
        btn.setAttribute('aria-pressed', on ? 'true' : 'false');
        btn.classList.toggle(kind === 'like' ? 'liked' : 'rechirped', !!on);
        var img = btn.querySelector('img');
        if (img) {
            img.alt = '';
            img.src = '/src/images/icons/' + (kind === 'like' ? (on ? 'liked' : 'like') : (on ? 'rechirped' : 'rechirp')) + '.svg';
        }
        var c = btn.querySelector('.' + kind + '-count');
        if (c) { c.textContent = count; c.setAttribute('aria-hidden', 'true'); }
        btn.querySelectorAll('.interactWithPostLabel').forEach(function (l) {
            l.setAttribute('aria-hidden', 'true');
            l.textContent = kind === 'like' ? (count === 1 ? 'like' : 'likes') : (count === 1 ? 're' + POST : 're' + POSTS);
        });
        if (kind === 'like') {
            btn.setAttribute('aria-label', (on ? 'Unlike, ' : 'Like, ') + plural(count, 'like', 'likes'));
        } else {
            btn.setAttribute('aria-label', (on ? 'Undo re' + POST + ', ' : 'Re' + POST + ', ') + plural(count, 're' + POST, 're' + POSTS));
        }
    }
    window.peepSetToggle = setToggle;

    window.updateChirpInteraction = function (chirpId, action, button) {
        fetch('/interact_chirp.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ chirpId: chirpId, action: action })
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.success) {
                    if (action === 'like') {
                        setToggle(button, 'like', data.like, data.like_count);
                        peepAnnounce(data.like ? 'Liked' : 'Like removed');
                    } else if (action === 'rechirp') {
                        setToggle(button, 'rechirp', data.rechirp, data.rechirp_count);
                        peepAnnounce(data.rechirp ? 'Re' + POST + 'ed' : 'Re' + POST + ' removed');
                    }
                } else if (data.error === 'not_signed_in') {
                    window.location.href = '/signin/';
                } else {
                    peepAnnounce('That did not work. Try again.');
                }
            })
            .catch(function () { peepAnnounce('Could not reach the server.'); });
    };

    // ---------- one post ----------
    window.peepRenderPost = function (chirp, level) {
        level = level || 2;
        var name = chirp.name ? chirp.name : 'Guest';
        var plainName = textOf(name);
        var username = chirp.username ? chirp.username : 'guest';

        var art = document.createElement('article');
        art.className = 'chirp';
        art.id = chirp.id;
        art.setAttribute('aria-labelledby', 'peep-h-' + chirp.id);

        // The card is still clickable with a mouse/finger, but it is not one giant link for VoiceOver.
        var clicker = document.createElement('div');
        clicker.className = 'chirpClicker';
        clicker.addEventListener('click', function (e) {
            if (e.target.closest('a, button')) return;
            if (window.getSelection && String(window.getSelection())) return;
            location.href = '/chirp?id=' + chirp.id;
        });

        var info = document.createElement('div');
        info.className = 'chirpInfo';
        var wrap = document.createElement('div');
        var pic = document.createElement('img');
        pic.className = 'userPic';
        pic.src = chirp.profilePic ? chirp.profilePic : '/src/images/users/guest/user.svg';
        pic.alt = '';                                   // the name is right next to it
        var details = document.createElement('div');
        var h = document.createElement('h' + level);
        h.className = 'postAuthor';
        h.id = 'peep-h-' + chirp.id;
        h.innerHTML = name + (chirp.isVerified ? ' <img class="verified" src="/src/images/icons/verified.svg" alt="Verified">' : '');
        var handle = document.createElement('p');
        handle.className = 'subText';
        var hl = document.createElement('a');
        hl.className = 'subText usernameLink';
        hl.href = '/user?id=' + encodeURIComponent(username);
        hl.textContent = '@' + username;
        handle.appendChild(hl);
        details.appendChild(h);
        details.appendChild(handle);
        wrap.appendChild(pic);
        wrap.appendChild(details);
        info.appendChild(wrap);
        var tsDiv = document.createElement('div');
        tsDiv.className = 'timestampTimeline';
        var ts = document.createElement('p');
        ts.className = 'subText postedDate';
        ts.setAttribute('data-timestamp', chirp.timestamp);
        tsDiv.appendChild(ts);
        info.appendChild(tsDiv);

        var text = document.createElement('pre');
        text.innerHTML = chirp.chirp;                   // already escaped + linkified by the server

        clicker.appendChild(info);
        clicker.appendChild(text);

        var bar = document.createElement('div');
        bar.className = 'chirpInteract';

        var reply = document.createElement('button');
        reply.type = 'button';
        reply.className = 'reply';
        reply.setAttribute('aria-label', 'Reply, ' + plural(chirp.reply_count, 'reply', 'replies'));
        reply.innerHTML = '<img alt="" src="/src/images/icons/reply.svg"> <span class="reply-count" aria-hidden="true">' + (parseInt(chirp.reply_count, 10) || 0) + '</span>';
        reply.onclick = function () { location.href = '/chirp/?id=' + chirp.id; };

        var re = document.createElement('button');
        re.type = 'button';
        re.className = 'rechirp';
        re.innerHTML = '<img alt="" src="/src/images/icons/rechirp.svg"> <span class="rechirp-count">0</span>';
        re.onclick = function () { updateChirpInteraction(chirp.id, 'rechirp', re); };
        setToggle(re, 'rechirp', chirp.rechirped_by_current_user, chirp.rechirp_count);

        var like = document.createElement('button');
        like.type = 'button';
        like.className = 'like';
        like.innerHTML = '<img alt="" src="/src/images/icons/like.svg"> <span class="like-count">0</span>';
        like.onclick = function () { updateChirpInteraction(chirp.id, 'like', like); };
        setToggle(like, 'like', chirp.liked_by_current_user, chirp.like_count);

        var open = document.createElement('a');
        open.className = 'interactLinkerPost';
        open.href = '/chirp?id=' + chirp.id;
        open.innerHTML = '<span class="srOnly">Open ' + POST + ' by ' + plainName.replace(/[<>&]/g, '') + '</span>';

        bar.appendChild(reply);
        bar.appendChild(re);
        bar.appendChild(like);
        bar.appendChild(open);

        art.appendChild(clicker);
        art.appendChild(bar);
        return art;
    };

    // ---------- dialogs ----------
    var lastFocus = null;
    function openDialog(id, fieldSelector) {
        var m = document.getElementById(id);
        if (!m) return;
        lastFocus = document.activeElement;
        m.style.display = 'flex';
        var field = m.querySelector(fieldSelector || 'textarea');
        if (field) setTimeout(function () { field.focus(); }, 30);
    }
    function closeDialog(id) {
        var m = document.getElementById(id);
        if (!m) return;
        m.style.display = 'none';
        if (lastFocus && document.contains(lastFocus)) lastFocus.focus();
    }
    window.openNewChirpModal = function () { openDialog('newChirpModal'); };
    window.closeNewChirpModal = function () { closeDialog('newChirpModal'); };
    window.openReplyModal = function () { openDialog('replyChirpModal'); };
    window.closeReplyModal = function () { closeDialog('replyChirpModal'); };
    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape') return;
        ['newChirpModal', 'replyChirpModal'].forEach(function (id) {
            var m = document.getElementById(id);
            if (m && m.style.display === 'flex') closeDialog(id);
        });
    });

    // Character count: on screen as a ring; for VoiceOver, spoken politely only near the limit.
    function watchCount(textarea, max) {
        var lastSaid = null;
        textarea.addEventListener('input', function () {
            var left = max - textarea.value.length;
            var say = null;
            if (left < 0) say = 'Over the limit by ' + plural(-left, 'character', 'characters');
            else if (left <= 20) say = plural(left, 'character', 'characters') + ' left';
            else if (left <= 50 && lastSaid !== '50') { say = '50 characters left'; lastSaid = '50'; return peepAnnounce(say); }
            if (say && say !== lastSaid) { lastSaid = say; peepAnnounce(say); }
        });
    }

    // Compose: post with fetch so errors are spoken instead of showing a raw error page.
    function wireCompose() {
        var f = document.getElementById('chirpForm');
        if (!f || f.dataset.peepWired) return;
        f.dataset.peepWired = '1';
        var ta = f.querySelector('textarea');
        if (ta) watchCount(ta, 500);
        f.addEventListener('submit', function (e) {
            e.preventDefault();
            fetch(f.action, { method: 'POST', body: new FormData(f), redirect: 'follow' })
                .then(function (r) {
                    if (r.redirected) { try { sessionStorage.setItem('peepFlash', 'Posted'); } catch (x) {} location.href = r.url; return null; }
                    return r.json();
                })
                .then(function (d) {
                    if (d && d.error) { peepAnnounce(d.error); if (ta) ta.focus(); }
                })
                .catch(function () { peepAnnounce('Could not post. Try again.'); });
        });
    }

    function init() {
        // labels for the compose + reply boxes (they only had placeholders)
        [['chirpComposeText', 'What is on your mind?'], ['replyComposeText', 'Your reply']].forEach(function (p) {
            var ta = document.querySelector('textarea[name="' + p[0] + '"]');
            if (ta && !ta.id) {
                ta.id = p[0];
                var l = document.createElement('label');
                l.setAttribute('for', p[0]); l.className = 'srOnly'; l.textContent = p[1];
                ta.parentNode.insertBefore(l, ta);
            }
        });
        var rta = document.querySelector('textarea[name="replyComposeText"]');
        if (rta) watchCount(rta, 500);
        ['newChirpModal', 'replyChirpModal'].forEach(function (id) {
            var m = document.getElementById(id);
            if (!m) return;
            var c = m.querySelector('.modal-content');
            if (c) {
                c.setAttribute('role', 'dialog');
                c.setAttribute('aria-modal', 'true');
                c.setAttribute('aria-label', id === 'newChirpModal' ? 'New ' + POST : 'Reply');
            }
        });
        wireCompose();
        // the loading spinner is decoration
        var sp = document.getElementById('noMoreChirps');
        if (sp && sp.querySelector('.lds-ring')) sp.setAttribute('aria-hidden', 'true');
        // one-time message from the server ("Posted")
        var s = statusEl(), flash = s.getAttribute('data-flash');
        try { if (!flash) flash = sessionStorage.getItem('peepFlash'); sessionStorage.removeItem('peepFlash'); } catch (x) {}
        if (flash) setTimeout(function () { peepAnnounce(flash); }, 900);
        updatePostedDates();
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
})();
