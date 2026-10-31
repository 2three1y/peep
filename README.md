# peep

A small, invite-only social network for friends, made to work well with VoiceOver.

peep is a fork of **[Chirp](https://github.com/actuallyaridan/chirp)** by [Adnan Bukvic](https://aridan.net) and the Chirp contributors. Chirp was a Twitter-style social network written in PHP with SQLite. Adnan archived it; this fork brings it back to life, fixes a few things and makes it much friendlier to screen readers.

Adnan asked forks not to use the Chirp name, so this one is called peep. The name lives in one place (`include/config.php`, `PEEP_NAME` and `PEEP_POST_WORD`) and in this README, so renaming it later is a two-line change.

Live: https://peep.sites.tab.bot/ (invite-only)

## What works

- Sign up with an invite code, sign in, sign out
- Post, reply, like, follow, profiles, the home timeline
- Admin panel: make invite codes, reset someone's password, remove an account, download a backup

## What changed from Chirp

**Accessibility (VoiceOver first)**
- One `h1` on every page, and headings on the sign-in, sign-up and post pages
- Every post is an `<article>` with the author's name as a heading, so the VoiceOver rotor jumps post to post
- Real `<label>`s on every form field (sign up, sign in, compose, reply, search, settings)
- Like is a toggle button with `aria-pressed` that says "Like, 3 likes" or "Unlike, 3 likes"; repost works the same way
- One polite live region announces "Posted", "Reply posted", "Liked", "Following" and errors
- The empty link on every post now says "Open peep by (name)"
- The logo sound is a real button ("Play the peep sound")
- Times read as "5 minutes ago" instead of "5m"
- Compose and reply open as dialogs: focus moves in, Escape closes, focus goes back
- Native emoji (no emoji images), so VoiceOver reads them by name
- Respects Reduce Motion; visible focus rings

**Safety**
- The public "forgot password" reset (username + invite code was enough to take over an account) is gone. An admin resets passwords; signed-in people change theirs in Settings, Security and login, which needs the current password
- Signup stays invite-only, and each invite works once
- Admins are users with `is_admin = 1` instead of the hardcoded username `chirp`
- The session cookie is Secure whenever the site is on https (Chirp always set it, which broke plain-http testing)
- Posting checks that the request came from the same site (Chirp only allowed `beta.chirpsocial.net`)
- PHP warnings are logged, never printed into pages

**Cleanup**
- No Cloudflare analytics, no CDNs: Font Awesome icons are inline SVG and Twemoji is gone
- Removed dead links (help./legacy.chirpsocial.net), Twitter profile images and the placeholder "suggested accounts" and "trends"
- The sample database is vacuumed (7.3 MB to 76 KB)

## Run it

You need PHP 8 with `pdo_sqlite` and `mbstring`.

```sh
git clone https://github.com/2three1y/peep
cd peep
php tools/setup.php --admin=yourname   # makes ../chirp.db, your admin account and 3 invite codes
php -S localhost:8080
```

`setup.php` prints the admin password once. Set `PEEP_DB=/path/to/peep.db` to keep the database somewhere else. The database is never inside the site folder, so it can't be downloaded.

On Tab's hosting the app runs with a static PHP 8.3 build (from [static-php-cli](https://github.com/crazywhalecc/static-php-cli)); the database lives outside the site folder so updates never touch posts or accounts.

## Credits

- [Chirp](https://github.com/actuallyaridan/chirp) by Adnan Bukvic and the Chirp contributors (MIT)
- The bird logo is the Twemoji bird by Twitter, Inc. and other contributors, [CC-BY 4.0](https://creativecommons.org/licenses/by/4.0/)
- Fonts in `src/styles/fonts` come from the original Chirp repo

Co-written with Tab.

## License

MIT, same as Chirp. See [LICENSE](LICENSE).
