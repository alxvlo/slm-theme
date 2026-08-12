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

**No deploy method here ever deletes files on the server.** They overwrite what
they ship and leave everything else alone, so uploads, media and anything added
directly on the server survive a deploy.

## Primary: scp, via the `slm-deploy` skill

**This is the live deploy path.** `.claude/skills/slm-deploy/SKILL.md` is the
procedure: build an explicit manifest of changed files, `php -l` them locally,
tar once, **one `scp` and one `ssh`**, back up the remote theme before
extracting, lint again on the server, roll back on failure.

It ships only the files on the manifest, which is why it is the default — the
blast radius is the change itself and nothing else.

Two hard rules from that skill, repeated here because breaking them is
expensive:

- **Exactly one `scp` and one `ssh` per deploy.** A burst of separate scp calls
  trips Bluehost's brute-force protection and IP-bans port 22 *mid-deploy*,
  which can leave the theme half-written with no way back in.
- **Never run `deploy.sh` or any `rsync`.** Its rsync is broken on this setup.

Cloudflare sits in front of prod, so nothing is verifiable until the cache is
purged. That is a manual step — ask for it, don't assume it happened.

## Conditional: cPanel Git Version Control

> 🔴 **`Deploy HEAD Commit` overwrites production with whatever `main` contains.**
> If `HEAD` is behind what is actually running, it silently reverts live fixes.
> No error, no warning — the site just goes backwards. This has been a real
> risk on this repo: 19 files were deployed by scp and left uncommitted, so a
> Git deploy would have rolled back the booking-CTA fix, the removal of the
> fabricated portfolio metrics, the testimonial rendering, the narrowed
> homepage early return and the Social Media Management card.
>
> **Only use this path when `HEAD` provably equals production.** Prove it with
> the check below every single time. "It should be fine" is not the check.

### The check to run before ever clicking Deploy

**1. The working tree must be clean.**

```bash
cd "/c/Users/kevin/Local Sites/real-estate-website/app/public/wp-content/themes/slm-theme"
git status --porcelain
```

Any output means edits exist locally that are not in `HEAD`. If those edits are
already live (the usual case after an scp deploy), they must be verified against
prod and committed *before* a Git deploy — see step 2. Never resolve this with
`checkout`, `restore`, `stash` or `reset`: after an scp deploy the working tree
may be the only complete copy of what is live besides prod itself.

**2. Every tracked file must match production byte-for-byte.**

One read-only SSH session — do not loop `scp` or reconnect per file:

```bash
# local hashes (CR-stripped, because core.autocrlf=true makes raw hashes lie)
cd "/c/Users/kevin/Local Sites/real-estate-website/app/public/wp-content/themes/slm-theme"
git ls-files | while read -r f; do
  echo "$f|$(tr -d '\r' < "$f" | md5sum | cut -d' ' -f1)"
done | sort > /tmp/local-hashes.txt

# prod hashes, one connection
ssh -i ~/.ssh/bluehost_showcase_ed25519 xbxkhdmy@50.6.252.165 \
  'cd /home2/xbxkhdmy/public_html/wp-content/themes/slm-theme && \
   find . -type f -not -path "./assets/media/*" -not -path "./.git/*" \
     -printf "%P\n" | while read -r f; do \
       echo "$f|$(tr -d "\r" < "$f" | md5sum | cut -d" " -f1)"; \
     done' | sort > /tmp/prod-hashes.txt

comm -3 /tmp/local-hashes.txt /tmp/prod-hashes.txt
```

`comm` printing nothing means local matches prod and a Git deploy is safe.
**Any line of output is a stop.** Investigate what diverged before deploying —
a file that differs is either an untested local edit about to be shipped, or a
prod hotfix about to be destroyed, and you cannot tell which from the hash
alone.

**3. Only then:**

1. Commit and push to `main` on GitHub.
2. cPanel > Files > **Git Version Control** > Manage (slm-theme).
3. **Pull or Deploy** tab > *Update from Remote* (fetches the push) > *Deploy
   HEAD Commit* (runs `.cpanel.yml`).

`.cpanel.yml` backs up the live theme first, prunes old backups, then rsyncs the
repo into place minus dev-only files.

Nothing deploys until you click Deploy — pushing to GitHub alone changes nothing.

### Line endings

`core.autocrlf=true` is set and there is no `.gitattributes`, so some files are
stored LF in git but sit CRLF in the working tree and on prod. A Git deploy
therefore writes LF where scp wrote CRLF. Harmless for PHP and CSS, but it means
**raw** md5 comparison after a Git deploy will show mismatches that are not real
changes. Compare CR-stripped hashes, as the check above does.

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
