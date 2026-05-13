# Visuels pour WordPress.org

Sur **WordPress.org**, les images de la fiche du plugin ne viennent **pas** du `README.md` GitHub : elles sont fournies via le dépôt **Subversion (SVN)** du plugin, dans le dossier **`assets/`** (branche séparée du code), et décrites dans le fichier **`readme.txt`** au format WordPress (section **Screenshots**).

Dans **ce dépôt Git**, les fichiers prêts à copier vers SVN (avec les noms corrects `banner-772x250.png`, `icon-128x128.png`, etc.) se trouvent sous **`wordpress-org/assets/`**. Voir aussi **`wordpress-org/README.md`** à la racine du dépôt.

Référence officielle : [Plugin Assets](https://developer.wordpress.org/plugins/wordpress-org/plugin-assets/).

---

## 1. Fichiers obligatoires / attendus

| Fichier | Dimensions | Rôle |
|---------|------------|------|
| `banner-772x250.png` **ou** `.jpg` | **772 × 250 px** | Bannière en haut de la fiche du plugin. |
| `banner-1544x500.png` **ou** `.jpg` | **1544 × 500 px** | Même bannière en **haute définition** (recommandé). |
| `icon-128x128.png` **ou** `.jpg` | **128 × 128 px** | Icône (liste extensions, recherche). |
| `icon-256x256.png` **ou** `.jpg` | **256 × 256 px** | Icône HD (recommandé). |

Ces fichiers sont placés dans **`/assets/`** sur SVN (hors du ZIP distribué aux utilisateurs).

---

## 2. Captures d’écran (`screenshot-1.png`, etc.)

- Noms **fixes** : `screenshot-1.png`, `screenshot-2.png`, … (pas d’espaces ni noms libres).
- Format **PNG** ou **JPG**.
- Taille : pas de taille imposée ; évitez les fichiers énormes (largeur ~1280 px suffit souvent).
- Dans **`readme.txt`**, section :

```text
== Screenshots ==

1. Description courte de la première capture (français ou anglais selon votre readme).
2. Description de la deuxième capture.
```

Le **numéro** dans `readme.txt` correspond au numéro dans le nom de fichier.

Pour **Conseil de classe**, des captures **utiles** pour les utilisateurs qui parcourent le répertoire :

1. **Tableau de bord** admin du plugin (contexte actif, raccourcis).
2. **Paramètres** — au moins le bloc **Pages (front)** avec les shortcodes.
3. **Planning des conseils** (admin) ou **Conseils** avec le planning rempli.
4. **Côté site public** : page **Planning** avec inscription (parent connecté).
5. **Comptes-rendus** (admin) ou aperçu du formulaire / export PDF (selon ce que vous voulu mettre en avant).

Adaptez le nombre (3 à 5 captures est courant).

---

## 3. Règles importantes

- **Pas de liens** ni de QR codes dans les bannières ; texte lisible au besoin.
- Contenu **adapté à tous publics** (pas de données personnelles réelles sur les captures : emails élèves, noms identifiables, etc.).
- Les assets SVN peuvent être mis à **jour sans** republier une nouvelle version du plugin.

---

## 4. Lien avec votre dépôt Git

Sur GitHub vous pouvez garder un dossier `wordpress-org/assets/` (ou seulement une copie des maquettes) pour versionner des sources ; au moment de la publication, vous copiez vers le SVN `assets/` selon la procédure WordPress.org.

Le **`readme.txt`** à la racine du plugin (pas le `README.md`) est le fichier attendu par le répertoire WordPress : vous y ajouterez les sections **Screenshots**, **Changelog**, etc. Voir [How your readme.txt works](https://developer.wordpress.org/plugins/wordpress-org/how-your-readme-txt-works/).
