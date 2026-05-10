<?php

if (!defined('ABSPATH')) {
    exit;
}

final class CC_Defaults {
    public static function default_pdf_html_template(): string {
        // Tokens supportés: {{association_nom}}, {{association_adresse}}, {{association_telephone}}, {{association_email}}
        // {{etablissement_nom}}, {{annee}}, {{trimestre}}, {{classe}}, {{classe_niveau}}, {{date_conseil}}, {{heure_conseil}},
        // {{salle}}, {{president}}, {{profs_participants}}, {{delegues_eleves}}, {{delegues_parents}},
        // {{decisions}}, {{remarque_principal}}, {{remarque_prof_principal}}, {{remarques_autres_profs}}, {{remarques_eleves_delegues}}, {{remarques_parents}},
        // {{date_generation}}
        return <<<HTML
<!doctype html>
<html>
  <head>
    <meta charset="utf-8" />
    <title>Compte-rendu</title>
  </head>
  <body>
    <div class="header">
      <h1>{{association_nom}}</h1>
      <div class="muted">{{association_adresse}}</div>
      <div class="muted">{{association_telephone}} {{association_email}}</div>
    </div>

    <h2>Compte-rendu de conseil de classe</h2>

    <div class="info">
      <p><strong>Classe :</strong> {{classe}} ({{classe_niveau}})</p>
      <p><strong>Année scolaire :</strong> {{annee}} — <strong>Trimestre :</strong> {{trimestre}}</p>
      <p><strong>Date :</strong> {{date_conseil}} — <strong>Horaire :</strong> {{heure_conseil}}</p>
      <p><strong>Salle :</strong> {{salle}} — <strong>Président :</strong> {{president}}</p>
    </div>

    <h3>Participants</h3>
    <div class="box">{{profs_participants}}</div>

    <h3>Délégués</h3>
    <div class="box">
      <p><strong>Élèves :</strong> {{delegues_eleves}}</p>
      <p><strong>Parents :</strong> {{delegues_parents}}</p>
    </div>

    <h3>Décisions et récompenses</h3>
    <div class="box">{{decisions}}</div>

    <h3>Remarques</h3>
    <div class="box">
      <h4>Chef d'établissement</h4>
      <div>{{remarque_principal}}</div>
      <h4>Professeur principal</h4>
      <div>{{remarque_prof_principal}}</div>
      <h4>Autres professeurs</h4>
      <div>{{remarques_autres_profs}}</div>
      <h4>Délégués élèves</h4>
      <div>{{remarques_eleves_delegues}}</div>
      <h4>Délégués parents</h4>
      <div>{{remarques_parents}}</div>
    </div>

    <div class="footer muted">
      Document généré le {{date_generation}}
    </div>
  </body>
</html>
HTML;
    }

    public static function default_pdf_css(): string {
        return <<<CSS
body { font-family: Arial, sans-serif; font-size: 12px; line-height: 1.4; color: #111; margin: 0; padding: 20px; }
.header { text-align: center; border-bottom: 3px solid #2c5aa0; padding-bottom: 10px; margin-bottom: 18px; }
.header h1 { margin: 0 0 6px; font-size: 18px; color: #2c5aa0; text-transform: uppercase; }
.muted { color: #555; font-size: 11px; }
h2 { text-align: center; font-size: 16px; margin: 16px 0; text-decoration: underline; }
h3 { margin: 16px 0 8px; border-bottom: 1px solid #333; padding-bottom: 2px; font-size: 13px; }
.info { background: #f6f7f7; border: 1px solid #dcdcde; padding: 10px; margin-bottom: 12px; }
.box { border: 1px solid #dcdcde; padding: 10px; margin-bottom: 12px; background: #fff; }
.box h4 { margin: 10px 0 4px; font-size: 12px; }
.footer { margin-top: 18px; text-align: center; border-top: 1px solid #dcdcde; padding-top: 8px; }
CSS;
    }
}

