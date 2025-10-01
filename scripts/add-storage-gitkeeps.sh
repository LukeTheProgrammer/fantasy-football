#!/usr/bin/env bash

set -euo pipefail

# Add all .gitkeep files under the storage/ directory using the exact command:
#   git add -f .gitkeep
# We execute this command from each directory containing a .gitkeep file.

# Move to repo root to make path handling predictable (in case script is run from elsewhere)
REPO_ROOT=$(git rev-parse --show-toplevel 2>/dev/null || true)
if [[ -n "${REPO_ROOT}" ]]; then
  cd "${REPO_ROOT}"
fi

STORAGE_DIR="storage"
if [[ ! -d "${STORAGE_DIR}" ]]; then
  echo "Error: '${STORAGE_DIR}/' directory not found at $(pwd)" >&2
  exit 1
fi

# Find all .gitkeep files and add them forcibly
found_any=false
while IFS= read -r -d '' file; do
  found_any=true
  dir=$(dirname "$file")
  (
    cd "$dir"
    echo "Adding: $dir/.gitkeep"
    git add -f .gitkeep
  )
done < <(find "${STORAGE_DIR}" -type f -name ".gitkeep" -print0)

if [[ "${found_any}" == false ]]; then
  echo "No .gitkeep files found under '${STORAGE_DIR}/'."
fi
