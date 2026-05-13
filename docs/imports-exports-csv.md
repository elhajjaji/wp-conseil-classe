# Imports et exports CSV

Le plugin utilise le CSV pour sauvegarder ou réinjecter des données. Le **séparateur** est en général **détecté automatiquement** à l’import (`;` ou `,` selon les fichiers).

Téléchargez les **modèles** depuis les boutons prévus dans l’admin (libellés du type « Modèle import (CSV) ») pour éviter les erreurs d’en-têtes.

---

## Paramètres (établissement + association)

**Écran :** **Conseil de classe → Paramètres** (export / import en bas de page).

**En-têtes (une seule ligne de données à l’import) :**

`nom_etablissement`, `adresse_etablissement`, `telephone_etablissement`, `email_etablissement`, `site_web_etablissement`, `nom_directeur`, `nom_principal`, `nom_association_parents`, `adresse_association_parents`, `telephone_association_parents`, `email_association_parents`, `site_web_association_parents`, `president_association`, `vice_president_association`, `secretaire_association`, `tresorier_association`, `max_parents_per_conseil`

---

## Classes (année active)

**Écran :** **Conseil de classe → Classes**

| Colonne | Description |
|---------|-------------|
| `nom` | Nom de la classe (ex. `6A`). |
| `niveau` | Niveau (ex. `6eme`). |

Si une classe du même **nom** existe déjà pour l’année active, l’import **met à jour** le niveau.

---

## Conseils / planning (année + trimestre actifs)

**Écran :** **Conseil de classe → Conseils**

| Colonne | Description |
|---------|-------------|
| `classe_nom` | Nom de la classe (doit exister pour l’année active). |
| `classe_niveau` | Niveau (doit correspondre à la classe). |
| `date_conseil` | Date au format `AAAA-MM-JJ`. |
| `heure_debut` | Ex. `18:00`. |
| `heure_fin` | Optionnel ; ex. `19:00`. |
| `salle_conseil` | Libellé de la salle. |
| `president_conseil` | Nom du ou de la présidente de séance. |

Les lignes qui violent une contrainte (ex. conseil déjà présent) peuvent être **ignorées** ; un résumé indique créations / ignorés après import.

---

## Parents

**Écran :** **Conseil de classe → Parents**

### Fichier « parents »

Colonnes : **Nom**, **Prénom**, **Email**, **Téléphone**, **Mot de passe ou code**

- Les lignes dont l’**email** existe déjà sont **mises à jour**.
- Le mot de passe WordPress (champ « code ») : **minimum 6 caractères** s’il est renseigné.
- Les codes d’accès doivent rester **uniques** entre fiches.

### Fichier « équipe conseil » (admins)

Colonnes supplémentaires : **Rôle (admin|super)**

- `admin` → rôle admin conseil du plugin.
- `super` → super admin conseil (réservé aux opérations permises par la politique du site ; l’import vérifie les droits du compte qui l’exécute).

Export CSV possible depuis la même page (avec filtre de profil : tous / parents seuls / admins conseil).

---

## Comptes-rendus

**Écran :** **Conseil de classe → Comptes-rendus** — export agrégé pour **l’année et le trimestre actifs**.

Colonnes d’export (libellés en français dans le fichier) : classe, date conseil, trimestre, président, professeurs participants, délégués, validation, compteurs de décisions, remarques, etc. Voir le export dans l’interface pour la liste exacte à jour.

Pour le **PDF** et les **templates**, voir [Comptes-rendus et PDF](comptes-rendus-et-pdf.md).
