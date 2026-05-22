# Conseil de classe — plugin WordPress

Plugin de gestion des **conseils de classe** pour WordPress : paramètres établissement / association, années et trimestres, classes, planning, inscriptions parents, comptes-rendus et exports (CSV / PDF).

**Dépôt source :** [github.com/elhajjaji/wp-conseil-classe](https://github.com/elhajjaji/wp-conseil-classe)

Le code du plugin se trouve dans le dossier **`conseil-classe/`** à la racine de ce dépôt.

**Documentation détaillée :** voir le dossier **[`docs/`](docs/README.md)** (installation, shortcodes, rôles, CSV, PDF, etc.).

**Publication WordPress.org :** le plugin inclut un [`readme.txt`](conseil-classe/readme.txt) conforme ; les bannières, icônes et captures pour Subversion se trouvent dans **[`wordpress-org/assets/`](wordpress-org/assets/)** (voir [`wordpress-org/README.md`](wordpress-org/README.md)).

---

## Prérequis

- WordPress récent (testé en usage courant avec les versions stables actuelles).
- PHP **7.4** minimum (recommandé : PHP 8.x).

---

## Installation

1. Copiez le dossier **`conseil-classe`** depuis ce dépôt.
2. Déposez-le dans le répertoire des extensions de votre site :  
   `wp-content/plugins/conseil-classe/`
3. Dans l’administration WordPress, menu **Extensions**, activez **Conseil de classe**.

> **Mise à jour :** remplacez tout le dossier `conseil-classe` par la nouvelle version, ou utilisez un ZIP téléversé (Extensions → Ajouter).

---

## Environnement Docker (tests locaux)

Un **`docker-compose.yml`** lance WordPress + MySQL. **Aucun dossier du plugin n’est monté dans le conteneur** : l’installation se fait comme sur un site classique en **téléversant le ZIP** (`Extensions → Ajouter une extension → Téléverser`).

À la racine du dépôt : **`conseil-classe.zip`** (et une copie versionnée `conseil-classe-VERSION.zip`). À l’intérieur du ZIP, la **première** entrée doit être **`conseil-classe/`** — pas un dossier du type `conseil-classe-1.0.0/` par-dessus.

**Piège fréquent (chemin inexistant)** : si vous décompressez `conseil-classe-1.0.0.zip` dans un dossier du même nom, puis vous **recompressez** ce dossier pour le téléverser dans WordPress, vous obtenez :  
`conseil-classe-1.0.0/conseil-classe/conseil-classe.php` → erreurs du type `file_get_contents(...) No such file or directory`.  
**Correctif :** téléversez **directement** l’archive fournie par le dépôt (sans repasser par une extraction ou un re-zippage), ou régénérez avec `python scripts/build-plugin-zip.py`.

Prérequis : [Docker Desktop](https://www.docker.com/products/docker-desktop/) (Windows / macOS) ou Docker Engine + Compose v2.

```bash
cd wp-conseil-classe   # racine du dépôt
docker compose up -d
```

1. Ouvrir **http://localhost:8890** et terminer l’assistant WordPress.
2. **Extensions → Ajouter une extension → Téléverser une extension** et choisir **`conseil-classe.zip`** (ou la copie `conseil-classe-1.0.0.zip` au besoin).
3. **Activer** « Conseil de classe ».

Si le port publié est déjà pris sur ta machine, change la ligne `8890:80` dans `docker-compose.yml` (ex. `8081:80`).

Les volumes nommés `conseil_wp_db` et `conseil_wp_html` servent uniquement à **persister la base et les fichiers WordPress** (ce n’est pas un montage du code source du plugin).

Identification base de données (démo) : utilisateur `wordpress`, mot de passe `wordpress`, base `wordpress`, hôte **`db`** dans le conteneur (déjà configuré par l’image).

**Remise à zéro** (supprime le contenu WordPress + la base) :

```bash
docker compose down -v
```

---

## Première configuration (admin)

Après activation, ouvrez le menu **Conseil de classe** et configurez dans l’ordre :


| Écran                | Action                                                                                                              |
| -------------------- | ------------------------------------------------------------------------------------------------------------------- |
| **Paramètres**       | Établissement, association parents, quota d’inscriptions par conseil, pages du site (shortcodes — voir ci‑dessous). |
| **Années scolaires** | Créer au moins une année et la définir **active**.                                                                  |
| **Trimestres**       | Choisir le **trimestre actif**.                                                                                     |
| **Classes**          | Renseigner les classes pour l’année active.                                                                         |
| **Conseils**         | Saisir le planning (dates, salles, etc.).                                                                           |
| **Parents**          | Création / import CSV parents et équipe conseil, export.                                                            |


Sans **année** et **trimestre actifs**, le planning et les écrans parents ne peuvent pas fonctionner correctement.

---

## Pages publiques (shortcodes)

Créez des **pages WordPress** et associez-les sous **Conseil de classe → Paramètres → Pages (front)**.


| Shortcode           | Rôle                                                                                      |
| ------------------- | ----------------------------------------------------------------------------------------- |
| `[cc_parent_login]` | Espace parents (connexion WordPress, liens vers le reste de l’espace).                    |
| `[cc_plannings]`    | Planning des conseils (inscription / désinscription pour les comptes autorisés).          |
| `[cc_my_councils]`  | Conseils du parent (actifs + historique), lien vers la rédaction du compte-rendu.         |
| `[cc_report_form]`  | Formulaire de compte-rendu (souvent ouvert avec `?council_id=…` depuis « Mes conseils »). |


---

## Imports / exports CSV (aperçu)

- **Classes** : export / import `nom`, `niveau` (mise à jour si la classe existe pour l’année active).
- **Conseils** : export / import du planning pour l’année et le trimestre actifs.
- **Parents** : deux modèles possibles (parents seuls / admins conseil) — voir l’écran **Parents** dans l’admin.
- **Comptes-rendus** : export CSV depuis le menu dédié.
- **Paramètres** : export / import d’une ligne CSV (sauvegarde / restauration des champs paramétrables).

---

## Structure du dépôt

```
wp-conseil-classe/
├── README.md                 ← ce fichier
└── conseil-classe/           ← dossier à copier dans wp-content/plugins/
    ├── conseil-classe.php   ← fichier principal
    ├── readme.txt
    ├── assets/
    ├── admin/
    ├── includes/
    └── public/
```

---

## Licence

Le plugin est publié sous licence **GPL v2 ou ultérieure**, conformément à l’écosystème WordPress.

---

## Contribution

Les rapports de bug et les propositions d’évolution sont les bienvenues via les [issues GitHub](https://github.com/elhajjaji/wp-conseil-classe/issues) du dépôt.