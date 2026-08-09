# Deploying the SLM theme

WordPress on Bluehost, with two environments:

| Environment | Branch | Theme path |
| --- | --- | --- |
| Production | `main` | `/home2/xbxkhdmy/public_html/wp-content/themes/slm-theme` |
| Staging | `staging` | `/home2/xbxkhdmy/public_html/staging/5169/wp-content/themes/slm-theme` |

Staging is served at `https://showcaselistingsmedia.com/staging/5169/`. The
`5169` is Bluehost's generated directory name — if the staging site is ever
deleted and recreated the number changes, and the staging path in `.cpanel.yml`
has to be updated to match.

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

Staging is a Bluehost staging site: its own copy of the files and its own set of
tables, so it renders real content without touching production. The content has
to stay separate — `functions.php` creates and edits pages on every request (the
`init` hooks around lines 748-906), so a staging build sharing prod's content
would rewrite it just by being viewed.

**Both sites live in one MySQL database** (`xbxkhdmy_WPVFO`). They are separated
by table prefix, not by database:

| Site | Prefix |
| --- | --- |
| Production | `wp_` |
| Staging | `staging_wp_` |

That matters for any manual DB work: a dump-and-restore that is not scoped to
one prefix will write into the other site's tables. Scope every operation with
`wp --path=<the site>` and `--all-tables-with-prefix`, never `--all-tables`.

### Which clone deploys where

There is one `.cpanel.yml`, shared by both branches. **The clone directory
decides the target, not the branch:**

| cPanel Git clone | Deploys to |
| --- | --- |
| `~/repositories/slm-theme` | production |
| `~/repositories/slm-theme-staging,` | staging |

The staging clone directory really does end in a comma — a typo from when the
cPanel repo was created. It is harmless: routing treats anything that is not
exactly `slm-theme` as staging, and backup names come from `$ENVNAME`, not the
directory. Fixing it would mean re-cloning ~510MB of history, so it stays.

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

### Refreshing staging from production

Staging content drifts from prod (its menus and pages are a snapshot from
whenever it was last cloned). To resync, use Bluehost's own staging tool and
pick the **production → staging** direction. It copies files, tables and rewrites
URLs to `/staging/5169/` prefix-safely, which hand-rolled SQL does not.

Afterwards: confirm the staging directory is still `5169` (if Bluehost recreated
it, update `.cpanel.yml`), turn the caching plugin back off, and re-deploy the
`slm-theme-staging` clone if staging needs commits that prod does not have yet.

### Two things not to do on staging

- **Never press Bluehost's "Deploy to Production" / "Publish" button.** That is
  the *opposite* direction to the refresh above: it copies staging files *and
  database* over production, bypassing git completely, and would undo whatever
  prod currently has. Git is the only promotion path.
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
