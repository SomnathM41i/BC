#!/usr/bin/env bash
#
# Install the repository's Git hooks into .git/hooks.
# Run once per clone:  bash scripts/install-git-hooks.sh
#
set -euo pipefail

root=$(git rev-parse --show-toplevel)
hooks_dir="$root/.git/hooks"

mkdir -p "$hooks_dir"
cp "$root/scripts/hooks/pre-commit" "$hooks_dir/pre-commit"
chmod +x "$hooks_dir/pre-commit"

echo "Installed pre-commit hook: rebuilds and stages public/build when frontend sources change."
