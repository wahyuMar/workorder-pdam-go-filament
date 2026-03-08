#!/bin/sh
# Run this once after cloning to install the Git hooks for this repository.
# Usage (from repo root):
#   sh scripts/install-hooks.sh

HOOKS_DIR=".git/hooks"
SCRIPTS_DIR="scripts/hooks"

echo "Installing Git hooks..."

for hook in "${SCRIPTS_DIR}"/*; do
    name=$(basename "${hook}")
    target="${HOOKS_DIR}/${name}"
    cp "${hook}" "${target}"
    chmod +x "${target}"
    echo "  Installed: ${target}"
done

echo "Done. Git hooks installed."
