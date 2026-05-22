#!/bin/sh

set -eu

repo_root=$(CDPATH= cd -- "$(dirname -- "$0")/.." && pwd)
src_dir="$repo_root/conseil-classe"
main_php="$src_dir/conseil-classe.php"

if [ ! -f "$main_php" ]; then
  echo "Exécutez ce script depuis le dépôt (dossier conseil-classe attendu à la racine)." >&2
  exit 1
fi

version=$(sed -nE "s/^[[:space:]]*\*[[:space:]]*Version:[[:space:]]*([0-9]+\.[0-9]+\.[0-9]+)[[:space:]]*$/\1/p" "$main_php" | head -n 1)

if [ -z "$version" ]; then
  echo "Version introuvable dans conseil-classe.php" >&2
  exit 1
fi

case "$version" in
  ''|*[^0-9.]*|*.*.*.*)
    echo "Version non supportée : $version" >&2
    exit 1
    ;;
esac

echo "Version détectée : $version"

stable_zip="$repo_root/conseil-classe.zip"
version_zip="$repo_root/conseil-classe-$version.zip"

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