# 🧭 Roadmap Hypermatrix – Automatisation des accès salles

## 🔧 Axes Structurants

1. **Collecte et intégration** (Hyperplanning / Matrix)
2. **Traitement & logique métier** (filtrage, vérification, attribution)
3. **Communication API (SOAP / REST)**
4. **Base de données & persistance (PGSQL)**
5. **Planification & automatisation**
6. **Monitoring interne & résilience**
7. **Extensibilité future (API REST Laravel unique)**

---

## 🛠️ Phase 1 — Analyse & Conception (Jours 1–3)

| Tâche                                                             | Détail                                                                                               |
| ----------------------------------------------------------------- | ---------------------------------------------------------------------------------------------------- |
| 📄 Rédaction du cahier des charges technique                      | Définir toutes les entrées/sorties + règles métier                                                   |
| 🔌 Étude des APIs                                                 | Analyse du WSDL Hyperplanning & endpoints Matrix REST                                                |
| 🗃️ Modélisation base de données                                   | Tables : `cours`, `salles`, `serrures`, `enseignants`, `logs_executions`, `autorisations_appliquees` |
| 🧠 Définir la logique de regroupement des cours par serrure/salle | Pour éviter des doublons d'autorisation sur une même période                                         |

---

## 🧪 Phase 2 — Développement du noyau API (Jours 4–10)

| Tâche                                          | Détail                                                                                    |
| ---------------------------------------------- | ----------------------------------------------------------------------------------------- |
| ⚙️ Création du projet Laravel                  | Config avec PostgreSQL + organisation dossier `Services/Hyperplanning`, `Services/Matrix` |
| 🔧 Développement du service SOAP Hyperplanning | Récupérer les cours sur 30 jours + parser les données                                     |
| 🧱 Développement du service REST Matrix        | Vérifier l'existence des serrures, des enseignants, des autorisations                     |
| 🔁 Intégration de la logique de traitement     | Script Laravel : `AuthorizeTeachersCommand.php` ou `SyncMatrixAccessJob.php`              |
| 📦 Stockage des données localement             | Sauvegarde des traitements pour éviter les doublons et auditer les actions                |
| ✅ Vérification de l’autorisation effective    | Confirmer l’application réelle de l’autorisation                                          |

---

## ⏱️ Phase 3 — Planification & Automatisation (Jours 11–13)

| Tâche                                                  | Détail                                                           |
| ------------------------------------------------------ | ---------------------------------------------------------------- |
| ⏲️ Mise en place de la planification Laravel Scheduler | Exécution toutes les 6 heures via cron ou Laravel Task Scheduler |
| 🧼 Ajout de clean-up automatisé                        | Nettoyage des logs ou autorisations expirées (si nécessaire)     |
| 🪪 Ajout de token/auth pour l’API unifiée               | Préparation à l’extension avec front-end                         |

---

## 🧪 Phase 4 — Tests & fiabilisation (Jours 14–16)

| Tâche                          | Détail                                                 |
| ------------------------------ | ------------------------------------------------------ |
| 🧪 Tests unitaires             | Services Hyperplanning & Matrix, logique d’attribution |
| 🧪 Tests d’intégration         | Cycle complet sur un échantillon réel de données       |
| ⚠️ Gestion des erreurs propre  | Try/catch, log des erreurs, réponses d’API malformées  |
| 📊 Génération de logs lisibles | Pour audit technique futur et debug                    |

---

## 🚀 Phase 5 — Mise en Production & suivi (Jours 17–18)

| Tâche                              | Détail                               |
| ---------------------------------- | ------------------------------------ |
| 🚀 Déploiement sur serveur cible   | Serveur Linux avec accès au cron     |
| ⏱️ Test de montée en charge légère | Voir l'impact réel sur Hyperplanning |
| 🧾 Validation finale               | Suivi en conditions réelles 48h      |

---

## 📈 Extensions futures (Facultatives)

| Idée                            | Détail                                                 |
| ------------------------------- | ------------------------------------------------------ |
| 🧍 Interface web interne        | Consulter les logs, voir qui a accès à quoi            |
| 🔔 Alerting (Slack/mail)        | En cas de serrure non reconnue ou autorisation échouée |
| 📱 Appli mobile chef de service | Forcer une ouverture ponctuelle, vérifier les accès    |
| 👩‍🏫 Portail enseignant           | Voir ses accès / historique / demander une extension   |

tableau salles,
tableau synchro

Ajuster en ligne l'heure de lancement du script
Ajouter la plage horaire nuit / jour


Matrix rapprochement dorma -> hyperplanning
Hyperplanning -> Matrix