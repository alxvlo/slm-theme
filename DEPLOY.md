# Deploying the SLM theme

WordPress on Bluehost, with two environments:

| Environment | Branch | Theme path |
| --- | --- | --- |
| Production | `main` | `/home2/xbxkhdmy/public_html/wp-content/themes/slm-theme` |
| Staging | `staging` | `/home2/xbxkhdmy/public_html/staging/wp-content/themes/slm-theme` |

**Neither deploy method ever deletes files on the server.** They overwrite what
they ship and leave everything else alone, so uploads, media and anything added
directly on the server survive a deploy.

## Primary: cPanel Git Version Control

1. Commit and push the branch to GitHub.
2. cPanel > Files > **Git Version Control** > Manage — pick the clone that
   matches the environment you want (see the table below).
3. **Pull or Deploy** tab > *Update from Remote* (fetches the push) > *Deploy
   HEAD Commit* (runs `.cpanel.yml`).

`.cpanel.yml` backs up the live theme first, prunes old backups, then rsyncs the
repo into place minus dev-only files.

Nothing deploys until you click Deploy — pushing to GitHub alone changes nothing.

## Staging

Staging is a Bluehost staging site: its own copy of the files **and its own
database**, so it renders real content without touching production. It has to be
a separate database — `functions.php` creates and edits pages on every request
(the `init` hooks around lines 748-906), so a staging build sharing prod's
database would rewrite prod's content just by being viewed.

### Which clone deploys where

There is one `.cpanel.yml`, shared by both branches. **The clone directory
decides the target, not the branch:**

| cPanel Git clone | Deploys to |
| --- | --- |
| `~/repositories/slm-theme` | production |
| `~/repositories/slm-theme-staging` | staging |

That is deliberate. If the branch decided, checking out `staging` in the
production clone would push unreviewed code onto the live site. Routing on the
directory makes that impossible: the prod clone only ever writes to prod, the
staging clone only ever writes to staging.

### The loop

```bash
git checkout staging
# ...work...
git commit -am "..."
git push origin staging
```

Deploy the **slm-theme-staging** clone, check the staging site, then promote:

```bash
git checkout main
git merge --ff-only staging
git push origin main
```

Deploy the **slm-theme** clone. `--ff-only` is deliberate: it fails loudly if
`main` moved underneath you instead of quietly making a merge commit. If it
fails, rebase `staging` onto `main` and re-verify on staging before promoting.

### Two things not to do on staging

- **Never press Bluehost's "Deploy to Production" / "Publish" button.** It copies
  staging files *and database* over production, bypassing git completely, and
  would undo whatever prod currently has. Git is the only promotion path.
- **Never place an order or run a checkout on staging.** The cloned database
  carries the production Stripe key, the production Aryeo key, and
  `slm_square_environment = production`. A test order there is a real charge on
  a real card. Staging is for layout, copy, navigation and content.

Also turn the Bluehost caching plugin off on staging — cached pages hide the
changes you went there to look at.

## Secondary: SSH script

```bash
./scripts/deploy.sh --dry   # list what would be sent, change nothing
./scripts/deploy.sh         # deploy
```

Requires a `bluehost-showcase` host alias in `~/.ssh/config`. Same exclude list,
same never-delete behaviour, no automatic backup — take one first if the change
is risky (see below).

**This script always targets production** (`DEPLOY_PATH` on line 13); it has no
staging mode. To test on staging first, use the cPanel Git route above.

## Media

`assets/media/` is **not** in the repo and is not deployed by either method.
Media belongs in the WordPress media library (WP Admin > Media). The existing
media files still sit on the server and keep working; do not re-add the folder
to git.

## Backups and rollback

`.cpanel.yml` writes a timestamped tarball of the theme it is about to overwrite
into `/home2/xbxkhdmy/backups/` before every deploy, and keeps the newest 5 **per
environment**:

| Deploy | Backup name | Rotation |
| --- | --- | --- |
| Production | `slm-theme-<TIMESTAMP>.tar.gz` | newest 5 |
| Staging | `slm-theme-staging-<TIMESTAMP>.tar.gz` | newest 5, independently |

`slm-theme-media-ONETIME.tar.gz` in the same directory is a one-off copy of the
server's media folder and is never rotated away.

Take a manual backup (e.g. before an SSH deploy):

```bash
tar czf /home2/xbxkhdmy/backups/slm-theme-$(date +%Y%m%d-%H%M%S).tar.gz \
  --exclude=slm-theme/assets/media \
  -C /home2/xbxkhdmy/public_html/wp-content/themes slm-theme
```

Roll back. The `-p` is required: this account's umask is restrictive, and
without it `tar` extracts the theme as mode 600, which Apache cannot read — the
site comes back unstyled and looks like the restore failed.

```bash
tar xzpf /home2/xbxkhdmy/backups/slm-theme-<TIMESTAMP>.tar.gz \
  -C /home2/xbxkhdmy/public_html/wp-content/themes/
```

Restore media, if it is ever damaged:

```bash
tar xzpf /home2/xbxkhdmy/backups/slm-theme-media-ONETIME.tar.gz \
  -C /home2/xbxkhdmy/public_html/wp-content/themes/slm-theme/
```

If anything ever renders unstyled after a restore, it is permissions. Fix with:

```bash
chmod -R u+rwX,go+rX,go-w /home2/xbxkhdmy/public_html/wp-content/themes/slm-theme
```

Rolling back only fixes the server. Also revert the bad commit in git, or the
next deploy ships it again.
