#!/bin/sh

set -eu

if [ $# -lt 2 ]
then
    echo "Usage: $0 \"commit message\" version"
    exit 1
fi

MESSAGE="$1"
VERSION="$2"

# Sanity check release metadata before doing anything
if ! grep -qiE "^[[:space:]]*\*[[:space:]]*Version:[[:space:]]*$VERSION[[:space:]]*$" shift8-zoom.php
then
    echo "ERROR: shift8-zoom.php Version does not match $VERSION"
    exit 1
fi

if ! grep -qiE "^[[:space:]]*\*[[:space:]]*Stable tag:[[:space:]]*$VERSION[[:space:]]*$" readme.txt
then
    echo "ERROR: readme.txt Stable tag does not match $VERSION"
    exit 1
fi

# Push Git first
git add .

if ! git diff --cached --quiet
then
    git commit -m "$MESSAGE"
fi

git push origin master

# Sync Git working tree -> SVN trunk.
# --delete removes stale SVN working-copy files which no longer exist in Git.
rsync -avzp --delete \
    --exclude-from='./push.exclude' \
    ./ ./svn/trunk/

rsync -avzp --delete \
    ./assets/ ./svn/assets/

cd svn

# Add genuinely new files/directories
svn add --force trunk assets >/dev/null 2>&1 || true

# Mark files deleted by rsync as deleted in SVN
svn status | while IFS= read -r line
do
    case "$line" in
        '!'*)
            path=$(printf '%s\n' "$line" | cut -c9-)
            svn rm -- "$path"
            ;;
    esac
done

# Safety check: there should only be one WordPress plugin header
PLUGIN_HEADERS=$(grep -RilE \
    --include='*.php' \
    '^[[:space:]]*\*[[:space:]]*Plugin Name:' \
    trunk | wc -l)

if [ "$PLUGIN_HEADERS" -ne 1 ]
then
    echo "ERROR: Found $PLUGIN_HEADERS PHP files containing Plugin Name headers:"
    grep -RniE \
        --include='*.php' \
        '^[[:space:]]*\*[[:space:]]*Plugin Name:' \
        trunk
    exit 1
fi

# Refuse to overwrite/reuse an existing release tag
if [ -e "tags/$VERSION" ]
then
    echo "ERROR: SVN tag $VERSION already exists"
    exit 1
fi

# Create SVN release tag from clean trunk
svn copy trunk "tags/$VERSION"

svn status

svn ci -m "$MESSAGE" --username shift8

cd ../
