# WordPress.org SVN notes

Ce dossier contient uniquement des notes de préparation pour la publication sur WordPress.org.

Le fichier lu par WordPress.org pour la fiche de l’extension n’est pas ce document. Le fichier à maintenir est [conseil-classe/readme.txt](../conseil-classe/readme.txt), au format officiel WordPress.org :

```text
=== Plugin Name ===
Contributors: wordpress-org-user
Tags: tag1, tag2
Requires at least: 6.0
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Short description without markup.

== Description ==
== Installation ==
== Frequently Asked Questions ==
== Screenshots ==
== Changelog ==
== Upgrade Notice ==
```

## Assets SVN

Copier le contenu de `wordpress-org/assets/` vers `assets/` à la racine du dépôt SVN, au même niveau que `trunk/` et `tags/`.

Fichiers attendus :

| Fichier | Usage |
|---------|-------|
| `banner-772x250.png` | Bannière standard |
| `banner-1544x500.png` | Bannière HD |
| `icon-128x128.png` | Icône |
| `icon-256x256.png` | Icône HD |
| `screenshot-1.png` à `screenshot-n.png` | Captures référencées dans la section `== Screenshots ==` du readme |

Après modification des visuels dans Git, resynchroniser ce dossier vers SVN.

## Publication d’une version

1. Vérifier que la version de [conseil-classe/conseil-classe.php](../conseil-classe/conseil-classe.php) correspond à `Stable tag` dans [conseil-classe/readme.txt](../conseil-classe/readme.txt).
2. Copier le contenu du plugin dans `trunk/` sur SVN.
3. Copier aussi `readme.txt` dans `tags/X.Y.Z/` lors de la création du tag.

## Checklist SVN exacte

Pour une première publication de la version 1.0.0, la structure cible sur WordPress.org doit être :

```text
slug-du-plugin/
	assets/
		banner-772x250.png
		banner-1544x500.png
		icon-128x128.png
		icon-256x256.png
		screenshot-1.png
		screenshot-2.png
		screenshot-3.png
		screenshot-4.png
		screenshot-5.png
		screenshot-6.png
	trunk/
		conseil-classe.php
		readme.txt
		admin/
		assets/
		includes/
		public/
	tags/
		1.0.0/
			conseil-classe.php
			readme.txt
			admin/
			assets/
			includes/
			public/
```

Ordre recommandé :

1. Créer ou récupérer le dépôt SVN du plugin WordPress.org.
2. Copier le contenu de `wordpress-org/assets/` dans `assets/` à la racine SVN.
3. Copier le contenu du dossier `conseil-classe/` dans `trunk/`.
4. Copier ce même contenu dans `tags/1.0.0/` pour figer la première version publiée.
5. Vérifier dans `trunk/readme.txt` et `tags/1.0.0/readme.txt` que `Stable tag: 1.0.0` est identique.
6. Vérifier dans `trunk/conseil-classe.php` et `tags/1.0.0/conseil-classe.php` que `Version: 1.0.0` est identique.
7. Valider puis publier avec `svn add`, `svn status`, puis `svn commit`.

Commandes type :

```bash
svn checkout https://plugins.svn.wordpress.org/<slug-du-plugin>/
cd <slug-du-plugin>

mkdir -p assets trunk tags/1.0.0
cp -R /chemin/vers/wp-conseil-classe/wordpress-org/assets/. assets/
cp -R /chemin/vers/wp-conseil-classe/conseil-classe/. trunk/
cp -R /chemin/vers/wp-conseil-classe/conseil-classe/. tags/1.0.0/

svn add --force .
svn status
svn commit -m "Initial release 1.0.0"
```

Points de contrôle avant commit :

* Ne pas copier `wordpress-org/assets/` dans `trunk/assets/`.
* `trunk/` doit contenir directement `conseil-classe.php` et `readme.txt`, pas un sous-dossier `conseil-classe/` supplémentaire.
* Le nombre de fichiers `screenshot-N.png` doit correspondre aux lignes de la section `== Screenshots ==` du readme.
* Pour une première publication, `trunk/` et `tags/1.0.0/` peuvent être identiques.

## Ressources officielles

* https://wordpress.org/plugins/developers/add/
* https://developer.wordpress.org/plugins/wordpress-org/how-your-readme-txt-works/
* https://developer.wordpress.org/plugins/wordpress-org/plugin-assets/
