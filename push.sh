#!/bin/sh

set -eu

if [ $# -lt 2 ]
then
    echo "Usage: $0 \"commit message\" version"
    exit 1
fi

MESSAGE="$1"
VERSION="$2"

PLUGIN_FILE="shift8-zoom.php"

count_plugin_headers() {
    target="$1"
    grep -RilE \
        --include='*.php' \
        '^[[:space:]]*\*[[:space:]]*Plugin Name:' \
        "$target" | wc -l | tr -d '[:space:]'
}

print_plugin_headers() {
    target="$1"
    grep -RniE \
        --include='*.php' \
        '^[[:space:]]*\*[[:space:]]*Plugin Name:' \
        "$target"
}

assert_release_tree() {
    target="$1"
    label="$2"

    if [ ! -f "$target/$PLUGIN_FILE" ]
    then
        echo "ERROR: $label is missing $PLUGIN_FILE"
        exit 1
    fi

    plugin_headers=$(count_plugin_headers "$target")

    if [ "$plugin_headers" -ne 1 ]
    then
        echo "ERROR: Found $plugin_headers PHP files containing Plugin Name headers in $label:"
        print_plugin_headers "$target"
        exit 1
    fi

    if ! grep -qiE "^[[:space:]]*\*[[:space:]]*Version:[[:space:]]*$VERSION[[:space:]]*$" "$target/$PLUGIN_FILE"
    then
        echo "ERROR: $label/$PLUGIN_FILE Version does not match $VERSION"
        exit 1
    fi

    if ! grep -qiE "^[[:space:]]*\*[[:space:]]*Stable tag:[[:space:]]*$VERSION[[:space:]]*$" "$target/readme.txt"
    then
        echo "ERROR: $label/readme.txt Stable tag does not match $VERSION"
        exit 1
    fi

    forbidden_paths=$(find "$target" \( \
        -path "$target/.git" -o \
        -path "$target/.github" -o \
        -path "$target/svn" -o \
        -path "$target/trunk" -o \
        -path "$target/tags" \
    \) -print)

    if [ -n "$forbidden_paths" ]
    then
        echo "ERROR: $label contains repository-only directories that should not be published:"
        printf '%s\n' "$forbidden_paths"
        exit 1
    fi
}

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

assert_release_tree trunk trunk

# Refuse to overwrite/reuse an existing release tag
if [ -e "tags/$VERSION" ]
then
    echo "ERROR: SVN tag $VERSION already exists"
    exit 1
fi

# Create SVN release tag from clean trunk
svn copy trunk "tags/$VERSION"

assert_release_tree "tags/$VERSION" "tags/$VERSION"

svn status

svn ci -m "$MESSAGE" --username shift8

cd ../
