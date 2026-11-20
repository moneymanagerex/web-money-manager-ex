#!/bin/sh
set -eu

web_root=$(CDPATH= cd -- "$(dirname -- "$0")/.." && pwd)
database_root=${DATABASE_ROOT:-"$web_root/../database"}
source_dir="$database_root/pocketbase"
target_dir="$web_root/docs/PocketBaseWebApp"

for file in schema-import.json defaults.json; do
  if [ ! -f "$source_dir/$file" ]; then
    echo "Missing database source: $source_dir/$file" >&2
    exit 1
  fi
  if [ "${1:-}" = "--check" ]; then
    cmp "$source_dir/$file" "$target_dir/$file" || {
      echo "Out of sync: $file" >&2
      exit 1
    }
  else
    cp "$source_dir/$file" "$target_dir/$file"
  fi
done
