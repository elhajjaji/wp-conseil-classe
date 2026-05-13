# Fichiers pour WordPress.org (Subversion)

Ce dossier contient ce qui doit être déposé sur le dépôt **SVN** WordPress.org **en dehors** du code distribué dans le ZIP (`trunk/`).

## Dossier `assets/`

Copiez le contenu de **`wordpress-org/assets/`** vers le dossier **`assets/`** à la racine du dépôt SVN du plugin (au même niveau que `trunk/` et `tags/`), pas dans `trunk/`.

Fichiers attendus (noms **exactement** comme ci-dessous) :

| Fichier | Usage |
|---------|--------|
| `banner-772x250.png` | Bannière standard |
| `banner-1544x500.png` | Bannière haute définition |
| `icon-128x128.png` | Icône |
| `icon-256x256.png` | Icône HD |
| `screenshot-1.png` … `screenshot-n.png` | Captures (légendes dans `readme.txt`) |

Après modification des visuels dans Git, resynchronisez ce dossier vers SVN.

## Fichier `readme.txt`

Le fichier **`readme.txt`** à la racine du plugin (dans **`trunk/`**, à côté du fichier PHP principal **`conseil-classe.php`**) est celui lu par WordPress.org.

Lors d’un tag de version, copiez aussi `readme.txt` dans `tags/X.Y.Z/` sur SVN.

## Compte contributeur

La ligne `Contributors:` du `readme.txt` doit lister des **identifiants wordpress.org** valides, séparés par des virgules. Adaptez-la si besoin avant la première soumission.

## Ressources

* [Plugin Assets](https://developer.wordpress.org/plugins/wordpress-org/plugin-assets/)
* [How your readme.txt works](https://developer.wordpress.org/plugins/wordpress-org/how-your-readme-txt-works/)
