# Deploying the SLM theme

The live site is WordPress on Bluehost. The theme lives at:

```
/home2/xbxkhdmy/public_html/wp-content/themes/slm-theme
```

**Neither deploy method ever deletes files on the server.** They overwrite what
they ship and leave everything else alone, so uploads, media and anything added
directly on the server survive a deploy.

## Primary: cPanel Git Version Control

1. Commit and push to `main` on GitHub.
2. cPanel > Files > **Git Version Control** > Manage (slm-theme).
3. **Pull or Deploy** tab > *Update from Remote* (fetches the push) > *Deploy
   HEAD Commit* (runs `.cpanel.yml`).

`.cpanel.yml` backs up the live theme first, prunes old backups, then rsyncs the
repo into place minus dev-only files.

Nothing deploys until you click Deploy — pushing to GitHub alone changes nothing.

## Secondary: SSH script

```bash
./scripts/deploy.sh --dry   # list what would be sent, change nothing
./scripts/deploy.sh         # deploy
```

Requires a `bluehost-showcase` host alias in `~/.ssh/config`. Same exclude list,
same never-delete behaviour, no automatic backup — take one first if the change
is risky (see below).

## Media

`assets/media/` is **not** in the repo and is not deployed by either method.
Media belongs in the WordPress media library (WP Admin > Media). The existing
media files still sit on the server and keep working; do not re-add the folder
to git.

## Backups and rollback

`.cpanel.yml` writes a timestamped tarball of the live theme to
`/home2/xbxkhdmy/backups/` before every deploy and keeps the newest 5.
`slm-theme-media-ONETIME.tar.gz` in the same directory is a one-off copy of the
server's media folder and is never rotated away.

Take a manual backup (e.g. before an SSH deploy):

```bash
tar czf /home2/xbxkhdmy/backups/slm-theme-$(date +%Y%m%d-%H%M%S).tar.gz \
  --exclude=slm-theme/assets/media \
  -C /home2/xbxkhdmy/public_html/wp-content/themes slm-theme
```

Roll back:

```bash
tar xzf /home2/xbxkhdmy/backups/slm-theme-<TIMESTAMP>.tar.gz \
  -C /home2/xbxkhdmy/public_html/wp-content/themes/
```

Restore media, if it is ever damaged:

```bash
tar xzf /home2/xbxkhdmy/backups/slm-theme-media-ONETIME.tar.gz \
  -C /home2/xbxkhdmy/public_html/wp-content/themes/slm-theme/
```

Rolling back only fixes the server. Also revert the bad commit in git, or the
next deploy ships it again.
