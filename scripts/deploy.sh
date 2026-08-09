#!/usr/bin/env bash
#
# Deploy the SLM theme to Bluehost over SSH.
#
#   ./scripts/deploy.sh          # deploy
#   ./scripts/deploy.sh --dry    # list what would be sent, change nothing
#
# Requires the `bluehost-showcase` host alias in ~/.ssh/config.

set -euo pipefail

SSH_HOST="bluehost-showcase"
DEPLOY_PATH="/home2/xbxkhdmy/public_html/wp-content/themes/slm-theme"
PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

# Everything that lives in the repo but must never reach the web server.
EXCLUDES=(
  ".git"
  ".github"
  ".claude"
  ".agent"
  ".tmp"
  ".backups"
  "scripts"
  "shared"
  "tests"
  "docs"
  "memory-bank"
  "node_modules"
  "run-tests.php"
  "AGENTS.md"
  "DEPLOY.md"
  "opencode.json"
  ".cpanel.yml"
  ".gitignore"
  ".gitattributes"
  "mfhs.html"
  # Media lives on the server and in WP Admin, not in the repo. See DEPLOY.md.
  "assets/media"
)

tar_excludes=()
for item in "${EXCLUDES[@]}"; do
  tar_excludes+=("--exclude=./${item}")
done

cd "$PROJECT_ROOT"

if [[ "${1:-}" == "--dry" ]]; then
  echo "Would deploy to ${SSH_HOST}:${DEPLOY_PATH}"
  echo "--- files ---"
  tar cf - "${tar_excludes[@]}" . | tar tf - | grep -v '/$' | sort
  exit 0
fi

echo "Deploying to ${SSH_HOST}:${DEPLOY_PATH} ..."

# Stream the theme straight into place. `tar x` overwrites changed files and
# leaves anything already on the server that we do not ship (e.g. uploads).
tar czf - "${tar_excludes[@]}" . \
  | ssh "$SSH_HOST" "mkdir -p '${DEPLOY_PATH}' && tar xzf - -C '${DEPLOY_PATH}'"

echo "Done."
