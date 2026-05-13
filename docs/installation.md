# Installation

## Prérequis

- **WordPress** : version récente (versions stables courantes).
- **PHP** : **7.4** minimum ; **PHP 8.x** recommandé.

## Déploiement du plugin

1. Récupérez le dossier **`conseil-classe`** à la racine du dépôt.
2. Copiez-le dans le répertoire des extensions du site :
   - `wp-content/plugins/conseil-classe/`
3. Dans l’administration WordPress : menu **Extensions**, activez **Conseil de classe**.

Le fichier principal attendu après installation :

`wp-content/plugins/conseil-classe/conseil-classe.php`

## Mise à jour

- Remplacez **tout** le dossier `conseil-classe` par la nouvelle version, **ou**
- Utilisez un fichier ZIP : **Extensions → Ajouter une extension → Téléverser**.  
   Utilisez **`conseil-classe.zip`** à la racine du dépôt (ou régénérez avec `python scripts/build-plugin-zip.py`).

### Structure du ZIP

Le ZIP doit commencer par **`conseil-classe/`** (sans dossier parent portant la version ou le nom du fichier).  

**À ne pas faire :** décompresser `conseil-classe-0.4.0.zip` dans un dossier nommé ainsi, puis recompresser ce dossier pour l’installer → WordPress enregistre un chemin du type **`conseil-classe-0.4.0/conseil-classe/conseil-classe.php`**, fichier souvent absent ou incohérent, et erreurs **`file_get_contents(...): Failed to open stream`** dans les logs PHP.

### Génération fiable

```bash
python scripts/build-plugin-zip.py
```

> Après une mise à jour majeure, vérifiez les réglages et, si besoin, les pages associées aux shortcodes (**Conseil de classe → Paramètres**).

## Erreur « Le fichier de l’extension n’existe pas » à l’activation

WordPress vérifie que le fichier enregistré pour l’extension **existe sur le disque** (`WP_PLUGIN_DIR/chemin/vers/fichier.php`). Sinon : *Plugin file does not exist.*

Causes fréquentes :

1. **Ancien chemin en base** après changement de nom de dossier ou de fichier principal (ex. anciennes versions utilisaient `conseil-classe-plugin/conseil-classe-plugin.php`).
2. **Plusieurs copies** du plugin ou dossier mal renommé après décompression.
3. **Hébergement** : chemins personnalisés (`WP_PLUGIN_DIR`), permissions, ou outil de sécurité qui bloque des URL contenant plusieurs fois le mot « plugin ».
4. **ZIP mal formé** avec un dossier parent en trop (ex. `conseil-classe-0.4.0/conseil-classe/…`) — voir [Structure du ZIP](#structure-du-zip).

**Correctif conseillé :**

1. Par **FTP / gestionnaire de fichiers**, supprimez **tout** ancien dossier :
   - `wp-content/plugins/conseil-classe-plugin/` (s’il existe encore)
   - `wp-content/plugins/conseil-classe/` (pour repartir propre)
   - **`wp-content/plugins/conseil-classe-0.4.0/`** (ou tout dossier créé après extraction + re‑zippage du mauvais niveau)
2. Dans l’admin **Extensions**, si une entrée « fantôme » reste, ignorez-la ou nettoyez la ligne correspondante dans l’option **`active_plugins`** (table `wp_options`) si vous savez le faire.
3. Réinstallez le **ZIP à jour** du dépôt : à l’intérieur du ZIP, un seul niveau **`conseil-classe/conseil-classe.php`**.
4. Réactivez. Videz caches (Opcache, object cache, CDN).

## Docker (développement)

Voir la section **Environnement Docker** du [`README.md`](../README.md) : installation du plugin via **`conseil-classe.zip`** (pas de montage du code source dans le conteneur).

## Étapes suivantes

Suite logique : [Prise en main — première configuration](prise-en-main.md).
