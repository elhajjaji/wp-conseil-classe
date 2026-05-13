# Comptes-rendus et PDF

## Côté parents (site public)

Les parents rédigent le compte-rendu via la page contenant `[cc_report_form]`, en général en suivant un lien depuis **Mes conseils** (paramètre `council_id` dans l’URL).

## Côté administration

**Conseil de classe → Comptes-rendus**

- Consultation et **édition** des champs du compte-rendu.
- **Validation** / dévalidation (workflow selon votre process interne).
- **Export PDF** ou **HTML** pour un compte-rendu donné (selon les actions proposées à l’écran).
- **Export CSV** global pour l’**année + trimestre actifs** (agrégation de tous les CR).

### Génération PDF

Le plugin tente dans cet ordre :

1. **Dompdf** (bibliothèque PHP **Dompdf** présente sur le site, souvent via Composer) → PDF généré **côté serveur**.
2. Sinon, **génération côté navigateur** via le script `html2pdf` fourni avec le plugin (téléchargement du PDF après rendu).

Si la génération navigateur échoue (bloqueurs, script manquant), utilisez l’**export HTML** comme secours.

---

## Templates PDF

**Conseil de classe → Templates PDF**

- Création de modèles **HTML + CSS** personnalisés.
- Un template peut être **activé** ; c’est ce modèle qui sert aux exports PDF / HTML des comptes-rendus.
- À défaut de template actif, un **modèle par défaut** intégré au plugin est utilisé.

### Tokens (substitution dans le HTML)

Dans le HTML du template, utilisez les marqueurs suivants ; ils sont remplacés à l’export :

| Token | Contenu |
|-------|---------|
| `{{association_nom}}` | Nom de l’association parents |
| `{{association_adresse}}` | Adresse |
| `{{association_telephone}}` | Téléphone |
| `{{association_email}}` | Email |
| `{{etablissement_nom}}` | Nom de l’établissement |
| `{{annee}}` | Année scolaire (libellé) |
| `{{trimestre}}` | Trimestre (libellé) |
| `{{classe}}` | Nom de la classe |
| `{{classe_niveau}}` | Niveau |
| `{{date_conseil}}` | Date formatée |
| `{{heure_conseil}}` | Plage horaire (début ± fin) |
| `{{salle}}` | Salle |
| `{{president}}` | Président(e) de séance |
| `{{profs_participants}}` | Liste des professeurs participants |
| `{{delegues_eleves}}` | Délégués élèves (combinés) |
| `{{delegues_parents}}` | Délégués parents (combinés) |
| `{{decisions}}` | Bloc HTML récapitulant félicitations, encouragements, mises en garde (compteurs) |
| `{{remarque_principal}}` | Remarque chef d’établissement |
| `{{remarque_prof_principal}}` | Remarque professeur principal |
| `{{remarques_autres_profs}}` | Autres professeurs |
| `{{remarques_eleves_delegues}}` | Délégués élèves |
| `{{remarques_parents}}` | Délégués parents |
| `{{date_generation}}` | Date/heure de génération du document |

---

## Logs

**Conseil de classe → Logs** — journal d’activité récent pour le suivi des opérations sensibles (aperçu des dernières entrées).
