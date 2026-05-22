#!/usr/bin/env python3
from __future__ import annotations

import re
import shutil
from pathlib import Path
from zipfile import ZIP_DEFLATED, ZipFile


def read_version(main_php: Path) -> str:
    content = main_php.read_text(encoding="utf-8", errors="replace")
    match = re.search(r"^\s*\*\s*Version:\s*(.+?)\s*$", content, re.MULTILINE)
    if not match:
        raise RuntimeError("Version introuvable dans conseil-classe.php")
    return match.group(1).strip()


def validate_version(version: str) -> str:
    if not re.fullmatch(r"\d+\.\d+\.\d+", version):
        raise RuntimeError(f"Version non supportée ({version}). Format attendu: x.y.z")
    return version


def build_zip(source_dir: Path, destination: Path) -> None:
    if destination.exists():
        destination.unlink()

    with ZipFile(destination, "w", compression=ZIP_DEFLATED) as archive:
        for file_path in sorted(source_dir.rglob("*")):
            if not file_path.is_file():
                continue
            arcname = file_path.relative_to(source_dir.parent).as_posix()
            archive.write(file_path, arcname)


def validate_zip(zip_path: Path) -> None:
    with ZipFile(zip_path, "r") as archive:
        names = archive.namelist()
        if not names:
            raise RuntimeError(f"ZIP vide : {zip_path}")

        for name in names:
            if "\\" in name:
                raise RuntimeError(
                    f"Séparateur invalide dans l'archive ({name}). Chemins attendus avec '/'."
                )
            if not name.startswith("conseil-classe/"):
                raise RuntimeError(
                    f"Structure ZIP invalide (entrée hors racine conseil-classe/) : {name}"
                )

        if "conseil-classe/conseil-classe.php" not in names:
            raise RuntimeError(
                "Fichier principal manquant dans le ZIP : conseil-classe/conseil-classe.php"
            )

        root_php_files = [
            name
            for name in names
            if name.startswith("conseil-classe/")
            and name.count("/") == 1
            and name.endswith(".php")
        ]
        if root_php_files != ["conseil-classe/conseil-classe.php"]:
            raise RuntimeError(
                "Bootstrap plugin invalide: seul conseil-classe/conseil-classe.php est autorisé à la racine. "
                f"Trouvé: {root_php_files}"
            )

        legacy_bootstrap = [
            name
            for name in names
            if name.lower().endswith("/conseil-classe-plugin.php")
        ]
        if legacy_bootstrap:
            raise RuntimeError(
                "Bootstrap legacy détecté dans le ZIP (conseil-classe-plugin.php): "
                f"{legacy_bootstrap}"
            )


def main() -> int:
    repo_root = Path(__file__).resolve().parent.parent
    source_dir = repo_root / "conseil-classe"
    main_php = source_dir / "conseil-classe.php"

    if not main_php.exists():
        raise RuntimeError(
            "Exécutez ce script depuis le dépôt (dossier conseil-classe attendu à la racine)."
        )

    version = validate_version(read_version(main_php))
    print(f"Version détectée : {version}")

    stable_zip = repo_root / "conseil-classe.zip"
    version_zip = repo_root / f"conseil-classe-{version}.zip"

    build_zip(source_dir, stable_zip)
    shutil.copy2(stable_zip, version_zip)

    validate_zip(stable_zip)
    validate_zip(version_zip)

    print(f"OK : {stable_zip}")
    print(f"OK : {version_zip}")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
