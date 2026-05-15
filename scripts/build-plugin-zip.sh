#!/bin/sh

set -eu

repo_root=$(CDPATH= cd -- "$(dirname -- "$0")/.." && pwd)
src_dir="$repo_root/conseil-classe"
main_php="$src_dir/conseil-classe.php"
readme_txt="$src_dir/readme.txt"

if [ ! -f "$main_php" ]; then
  echo "Exécutez ce script depuis le dépôt (dossier conseil-classe attendu à la racine)." >&2
  exit 1
fi

version_before=$(sed -nE "s/^[[:space:]]*\*[[:space:]]*Version:[[:space:]]*([0-9]+\.[0-9]+\.[0-9]+)[[:space:]]*$/\1/p" "$main_php" | head -n 1)

if [ -z "$version_before" ]; then
  echo "Version introuvable dans conseil-classe.php" >&2
  exit 1
fi

IFS=. read -r major minor patch <<EOF
$version_before
EOF

case "$major.$minor.$patch" in
  ''|*[^0-9.]*|*.*.*.*)
    echo "Version non supportée pour auto-incrément : $version_before" >&2
    exit 1
    ;;
esac

new_version="$major.$minor.$((patch + 1))"

NEW_VERSION="$new_version" perl -0pi -e 's/^(\s*\*\s*Version:\s*).*$/"$1$ENV{NEW_VERSION}"/me; s/^define\('\''CC_PLUGIN_VERSION'\'',\s*'\''.*'\''\);$/"define('\''CC_PLUGIN_VERSION'\'', '\''$ENV{NEW_VERSION}'\'');"/me' "$main_php"

if [ -f "$readme_txt" ]; then
  NEW_VERSION="$new_version" perl -0pi -e 's/^(\s*Stable tag:\s*).*$/"$1$ENV{NEW_VERSION}"/me' "$readme_txt"
fi

echo "Version incrémentée : $version_before -> $new_version"

stable_zip="$repo_root/conseil-classe.zip"
version_zip="$repo_root/conseil-classe-$new_version.zip"

rm -f "$stable_zip" "$version_zip"

(
  cd "$repo_root"
  zip -rq "$stable_zip" conseil-classe
)

cp "$stable_zip" "$version_zip"

first_entry=$(unzip -Z1 "$stable_zip" | sed -n '1p')
if [ "$first_entry" != "conseil-classe/" ]; then
  echo "Structure ZIP invalide (attendu conseil-classe/) : $first_entry" >&2
  exit 1
fi

if ! unzip -Z1 "$stable_zip" | grep -qx 'conseil-classe/conseil-classe.php'; then
  echo "Fichier principal manquant dans le ZIP : conseil-classe/conseil-classe.php" >&2
  exit 1
fi

echo "OK : $stable_zip"
echo "OK : $version_zip"