# 🎯 LeadFlow API - Guide de Test Complet

Bienvenue dans le guide de test de l'API LeadFlow ! Ce document vous guidera à travers tous les fichiers et ressources disponibles pour tester l'API.

---

## 📁 Fichiers de Documentation

### 1. **API_COMPLETE_ROUTES.md** 📖
**Description:** Documentation exhaustive de toutes les routes de l'API

**Contenu:**
- 📋 Liste complète des 80+ endpoints
- 💡 Exemples de requêtes et réponses
- 🔑 Headers requis
- 📊 Codes de réponse HTTP
- 🎯 Paramètres de filtrage et pagination

**Utilisation:** Référence complète pour connaître tous les endpoints disponibles

---

### 2. **GUIDE_TEST_POSTMAN.md** 🧪
**Description:** Guide pas à pas pour tester l'API avec Postman

**Contenu:**
- 📥 Instructions d'installation
- 🚀 Scénarios de test complets
- ✅ Tests automatisés
- 🎯 Ordre de test recommandé
- 💡 Astuces et bonnes pratiques
- ❌ Résolution de problèmes

**Utilisation:** Guide détaillé pour exécuter tous les tests

---

### 3. **QUICK_REFERENCE.md** ⚡
**Description:** Référence rapide pour un accès instantané aux informations clés

**Contenu:**
- 📋 Tableau récapitulatif des routes principales
- 🔑 Headers standards
- 📦 Types de données
- 🚀 Commandes utiles
- ✅ Checklist de démarrage
- 🆘 Dépannage rapide

**Utilisation:** Consultation rapide pendant les tests

---

## 📦 Collection Postman

### **LeadFlow_API_Complete.postman_collection.json** 🎁

**Description:** Collection Postman complète avec tous les tests

**Contenu:**
- ✅ 50+ requêtes pré-configurées
- 🤖 Tests automatisés
- 🔄 Variables auto-remplies
- 📊 Scénarios de test complets

**Modules inclus:**
1. **Authentication** (5 requêtes)
   - Register, Login, Logout, User Info, Refresh Token

2. **Dashboard** (5 requêtes)
   - Summary, Stats, Activity, Funnel, Charts

3. **Leads Management** (8 requêtes)
   - CRUD, Status, Assign, Interactions

4. **Pipelines** (3 requêtes)
   - Liste, Création, Détails

5. **Tasks** (5 requêtes)
   - CRUD, Complete

6. **Settings** (4 requêtes)
   - Profile, Roles, Teams

7. **Notifications** (3 requêtes)
   - Liste, Unread Count, Mark as Read

8. **AI Insights** (3 requêtes)
   - Liste, Statistics, Generate

9. **Health Check** (1 requête)
   - API Health

**Import dans Postman:**
1. Ouvrir Postman
2. File → Import
3. Sélectionner `LeadFlow_API_Complete.postman_collection.json`
4. ✅ Collection prête à l'emploi !

---

## 🛠️ Script de Test Automatique

### **test_api.ps1** 🔧

**Description:** Script PowerShell pour tester rapidement les routes principales

**Contenu:**
- 🔐 Login automatique
- 🧪 Test des 4 routes principales :
  - `/dashboard/stats`
  - `/dashboard/activity`
  - `/dashboard/funnel`
  - `/ai-insights`

**Utilisation:**
```powershell
powershell -ExecutionPolicy Bypass -File test_api.ps1
```

**Résultat attendu:**
```
Token obtenu: MTF8MTc1ODI5MjQyMg==
=== Test des routes du dashboard ===
✅ /dashboard/stats - Status: 200
✅ /dashboard/activity - Status: 200
✅ /dashboard/funnel - Status: 200
✅ /ai-insights - Status: 200
=== Tests terminés ===
```

---

## 🚀 Démarrage Rapide

### Étape 1 : Prérequis ✅

```bash
# Vérifier que le serveur Laravel est démarré
php artisan serve --host=127.0.0.1 --port=8000

# Vérifier que la base de données est configurée
php artisan migrate --seed
```

### Étape 2 : Importer la Collection Postman 📥

1. Ouvrir Postman
2. Cliquer sur **Import**
3. Sélectionner `LeadFlow_API_Complete.postman_collection.json`
4. La collection apparaît dans la barre latérale

### Étape 3 : Premier Test 🧪

1. Ouvrir la requête **1.1 Register User**
2. Modifier l'email pour qu'il soit unique
3. Cliquer sur **Send**
4. ✅ Le token est automatiquement sauvegardé !

### Étape 4 : Tester Tout 🎯

1. Sélectionner la collection "LeadFlow API"
2. Cliquer sur **Run**
3. Sélectionner toutes les requêtes
4. Cliquer sur **Run LeadFlow API**
5. 🎉 Voir les résultats des tests !

---

## 📊 Structure de Test Recommandée

### 🔰 Niveau 1 - Tests Basiques (10 min)

1. ✅ Health Check
2. ✅ Register/Login
3. ✅ Dashboard Stats
4. ✅ Get Leads
5. ✅ Get Tasks

**Objectif:** Vérifier que l'API fonctionne

---

### 🔸 Niveau 2 - Tests Intermédiaires (30 min)

1. ✅ Tous les tests du Niveau 1
2. ✅ Create Lead
3. ✅ Update Lead
4. ✅ Create Task
5. ✅ Update Profile
6. ✅ Get Pipelines

**Objectif:** Tester les opérations CRUD

---

### 🔥 Niveau 3 - Tests Avancés (1h)

1. ✅ Tous les tests du Niveau 2
2. ✅ Lead Status Change
3. ✅ Lead Assignment
4. ✅ Create Interactions
5. ✅ Complete Tasks
6. ✅ AI Insights Generation
7. ✅ Notifications
8. ✅ Pipeline Creation

**Objectif:** Tester tous les workflows

---

## 🎯 Scénarios de Test par Cas d'Usage

### 📌 Cas d'Usage 1 : Gestion d'un Nouveau Lead

```
1. Login                              → Authentification
2. POST /leads                        → Créer le lead
3. POST /leads/{id}/interactions      → Ajouter une interaction
4. POST /leads/{id}/status            → Changer le statut
5. POST /tasks                        → Créer une tâche de suivi
6. GET /dashboard/stats               → Voir les stats mises à jour
```

---

### 📌 Cas d'Usage 2 : Suivi de Performance

```
1. Login                              → Authentification
2. GET /dashboard/summary             → Vue d'ensemble
3. GET /dashboard/funnel              → Entonnoir de conversion
4. GET /dashboard/charts              → Graphiques
5. GET /dashboard/activity            → Activité récente
6. GET /leads?status=won              → Leads gagnés
```

---

### 📌 Cas d'Usage 3 : Gestion des Tâches

```
1. Login                              → Authentification
2. GET /tasks                         → Liste des tâches
3. POST /tasks                        → Créer une tâche
4. PUT /tasks/{id}                    → Mettre à jour
5. POST /tasks/{id}/complete          → Marquer complétée
6. GET /dashboard/daily-tasks         → Tâches du jour
```

---

## 📈 Métriques de Test

### ✅ Tests de Succès

- **Codes 2xx** : Requêtes réussies
- **Token sauvegardé** : Authentification OK
- **Données retournées** : API fonctionnelle
- **Variables auto-remplies** : Workflow automatisé

### ❌ Gestion des Erreurs

- **Code 401** : Problème d'authentification → Refaire le login
- **Code 422** : Validation échouée → Vérifier les données
- **Code 404** : Ressource non trouvée → Vérifier l'ID
- **Code 500** : Erreur serveur → Consulter les logs

---

## 🔍 Débogage

### Logs Laravel

```bash
# Voir les logs en temps réel
tail -f storage/logs/laravel.log

# Voir les dernières erreurs
tail -n 100 storage/logs/laravel.log | grep ERROR
```

### Vérifier les Routes

```bash
# Liste toutes les routes
php artisan route:list

# Routes spécifiques au dashboard
php artisan route:list --path=dashboard

# Routes des leads
php artisan route:list --path=leads
```

### Vider les Caches

```bash
php artisan cache:clear
php artisan route:clear
php artisan config:clear
php artisan optimize:clear
```

---

## 📦 Variables Postman

### Variables de Collection

```javascript
base_url: http://127.0.0.1:8000/api/v1
token: (auto-rempli après login)
user_id: (auto-rempli après register/login)
lead_id: (auto-rempli après création/récupération lead)
task_id: (auto-rempli après création/récupération tâche)
pipeline_id: (auto-rempli après récupération pipeline)
```

### Accès aux Variables

Dans Postman :
- URL : `{{base_url}}/leads/{{lead_id}}`
- Headers : `Authorization: Bearer {{token}}`

---

## 🎨 Personnalisation des Tests

### Ajouter des Tests Personnalisés

Dans l'onglet **Tests** de chaque requête :

```javascript
// Test personnalisé pour vérifier le nom du lead
pm.test("Le nom du lead est correct", () => {
    const response = pm.response.json();
    pm.expect(response.lead.name).to.equal("Jean Dupont");
});

// Test personnalisé pour le statut
pm.test("Status code is 201", () => {
    pm.response.to.have.status(201);
});

// Sauvegarder une variable
pm.collectionVariables.set('custom_id', response.data.id);
```

---

## 📚 Documentation Disponible

| Fichier | Type | Utilisation |
|---------|------|-------------|
| `API_COMPLETE_ROUTES.md` | 📖 Doc | Référence complète |
| `GUIDE_TEST_POSTMAN.md` | 🧪 Guide | Tests détaillés |
| `QUICK_REFERENCE.md` | ⚡ Ref | Consultation rapide |
| `LeadFlow_API_Complete.postman_collection.json` | 📦 Collection | Import Postman |
| `test_api.ps1` | 🔧 Script | Tests automatiques |
| `README_TEST_API.md` | 📘 Index | Ce fichier |

---

## ✅ Checklist Complète

### Préparation
- [ ] Serveur Laravel démarré (`php artisan serve`)
- [ ] Base de données configurée (`.env`)
- [ ] Migrations exécutées (`php artisan migrate --seed`)
- [ ] Collection Postman importée

### Tests Basiques
- [ ] Health Check réussi
- [ ] Registration réussie
- [ ] Login réussi
- [ ] Dashboard stats chargées

### Tests CRUD
- [ ] Lead créé
- [ ] Lead mis à jour
- [ ] Lead supprimé
- [ ] Tâche créée
- [ ] Tâche complétée

### Tests Avancés
- [ ] Lead assigné
- [ ] Statut changé
- [ ] Interaction créée
- [ ] Pipeline récupéré
- [ ] AI Insights générés
- [ ] Notifications récupérées

### Finalisation
- [ ] Tous les tests passent
- [ ] Variables correctement sauvegardées
- [ ] Aucune erreur dans les logs
- [ ] Documentation consultée

---

## 🎓 Ressources Complémentaires

### Documentation Postman
- [Postman Learning Center](https://learning.postman.com/)
- [Writing Tests](https://learning.postman.com/docs/writing-scripts/test-scripts/)
- [Variables](https://learning.postman.com/docs/sending-requests/variables/)

### Documentation Laravel
- [Laravel Documentation](https://laravel.com/docs)
- [API Resources](https://laravel.com/docs/eloquent-resources)
- [Validation](https://laravel.com/docs/validation)

---

## 💪 Prêt à Tester !

Vous avez maintenant tout ce qu'il faut pour tester l'API LeadFlow de A à Z !

**Ordre recommandé :**

1. 📖 Lire `QUICK_REFERENCE.md` pour une vue d'ensemble
2. 📥 Importer `LeadFlow_API_Complete.postman_collection.json`
3. 🧪 Suivre `GUIDE_TEST_POSTMAN.md` étape par étape
4. 📋 Consulter `API_COMPLETE_ROUTES.md` pour les détails

**Bonne chance ! 🚀**

---

## 📞 Support

En cas de problème :
1. ✅ Consulter `QUICK_REFERENCE.md` → Section Dépannage
2. ✅ Vérifier les logs Laravel : `storage/logs/laravel.log`
3. ✅ Exécuter `test_api.ps1` pour un diagnostic rapide
4. ✅ Consulter `API_COMPLETE_ROUTES.md` pour les détails des routes

---

**Version:** 1.0.0  
**Date:** Janvier 2024  
**API:** LeadFlow v1

