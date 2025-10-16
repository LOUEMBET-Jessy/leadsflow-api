# 🧪 Guide de Test Postman - LeadFlow API

## 📥 Installation

### 1. Importer la Collection

1. Ouvrir Postman
2. Cliquer sur **Import**
3. Sélectionner le fichier `LeadFlow_API_Complete.postman_collection.json`
4. La collection apparaît dans la barre latérale gauche

### 2. Configuration des Variables

Les variables suivantes sont automatiquement configurées :

```
base_url: http://127.0.0.1:8000/api/v1
token: (sera rempli automatiquement après login)
user_id: (sera rempli automatiquement)
lead_id: (sera rempli automatiquement)
task_id: (sera rempli automatiquement)
pipeline_id: (sera rempli automatiquement)
```

---

## 🚀 Scénario de Test Complet

### Étape 1 : Authentification ✅

#### 1.1 Register User (Première fois)

**Endpoint:** `POST /auth/register`

**Body:**
```json
{
  "name": "Votre Nom",
  "email": "votre.email@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "role_id": 1
}
```

**Résultat attendu:**
- ✅ Status Code: 201 Created
- ✅ Token automatiquement sauvegardé dans les variables
- ✅ User ID sauvegardé

---

#### 1.2 Login User

**Endpoint:** `POST /auth/login`

**Body:**
```json
{
  "email": "votre.email@example.com",
  "password": "password123"
}
```

**Résultat attendu:**
- ✅ Status Code: 200 OK
- ✅ Token automatiquement sauvegardé
- ✅ Utilisateur connecté

---

#### 1.3 Get User Info

**Endpoint:** `GET /auth/user`

**Headers:** `Authorization: Bearer {{token}}`

**Résultat attendu:**
- ✅ Status Code: 200 OK
- ✅ Données utilisateur retournées

---

### Étape 2 : Dashboard 📊

#### 2.1 Dashboard Summary

**Endpoint:** `GET /dashboard/summary`

**Résultat attendu:**
```json
{
  "summary": {
    "total_leads": 150,
    "user_leads": 45,
    "conversion_rate": 25.5,
    "pipeline_value": 125000,
    "monthly_revenue": 35000,
    "recent_leads": 12,
    "tasks_due_today": 5,
    "overdue_tasks": 2
  }
}
```

---

#### 2.2 Dashboard Stats

**Endpoint:** `GET /dashboard/stats`

**Résultat:** Identique à Summary

---

#### 2.3 Dashboard Activity

**Endpoint:** `GET /dashboard/activity?limit=20`

**Résultat attendu:**
```json
{
  "activities": [
    {
      "type": "lead",
      "action": "updated",
      "description": "Lead XYZ mis à jour",
      "timestamp": "2024-01-15T10:30:00Z"
    }
  ]
}
```

---

#### 2.4 Dashboard Funnel

**Endpoint:** `GET /dashboard/funnel?period=month`

**Paramètres disponibles:**
- `period`: week, month, quarter, year

**Résultat attendu:**
```json
{
  "funnel": [
    {
      "stage": "Nouveau Lead",
      "count": 50,
      "conversion_rate": 100
    }
  ],
  "total_leads": 50
}
```

---

#### 2.5 Dashboard Charts

**Endpoint:** `GET /dashboard/charts?period=month`

**Résultat:** Données pour graphiques (sources, statuts, tendances)

---

### Étape 3 : Gestion des Leads 👥

#### 3.1 Get All Leads

**Endpoint:** `GET /leads?page=1&per_page=15`

**Paramètres optionnels:**
- `page`: Numéro de page (défaut: 1)
- `per_page`: Éléments par page (défaut: 15)
- `status`: Filtrer par statut
- `source`: Filtrer par source
- `search`: Recherche par mot-clé

**Résultat attendu:**
- ✅ Status Code: 200 OK
- ✅ Liste de leads paginée
- ✅ Premier lead_id sauvegardé automatiquement

---

#### 3.2 Create Lead

**Endpoint:** `POST /leads`

**Body:**
```json
{
  "name": "Jean Dupont",
  "email": "jean.dupont@example.com",
  "phone": "+241123456789",
  "company": "ABC Company",
  "source": "website",
  "status_id": 1,
  "pipeline_id": 1,
  "pipeline_stage_id": 1,
  "assigned_to_user_id": 1,
  "notes": "Lead intéressé par nos services"
}
```

**Résultat attendu:**
- ✅ Status Code: 201 Created
- ✅ Lead créé avec ID
- ✅ Lead ID sauvegardé automatiquement

---

#### 3.3 Get Lead by ID

**Endpoint:** `GET /leads/{{lead_id}}`

**Résultat:** Détails complets du lead

---

#### 3.4 Update Lead

**Endpoint:** `PUT /leads/{{lead_id}}`

**Body:**
```json
{
  "name": "Jean Dupont Updated",
  "company": "ABC Company SA",
  "notes": "Lead qualifié après appel"
}
```

**Résultat attendu:**
- ✅ Status Code: 200 OK
- ✅ Lead mis à jour

---

#### 3.5 Update Lead Status

**Endpoint:** `POST /leads/{{lead_id}}/status`

**Body:**
```json
{
  "status_id": 2,
  "notes": "Lead qualifié après appel téléphonique"
}
```

**Résultat attendu:**
- ✅ Statut changé
- ✅ Événement "LeadStatusChanged" déclenché

---

#### 3.6 Assign Lead

**Endpoint:** `POST /leads/{{lead_id}}/assign`

**Body:**
```json
{
  "assigned_to_user_id": 2
}
```

**Résultat attendu:**
- ✅ Lead assigné
- ✅ Notification envoyée au nouvel assigné

---

#### 3.7 Get Lead Interactions

**Endpoint:** `GET /leads/{{lead_id}}/interactions`

**Résultat:** Liste des interactions du lead

---

#### 3.8 Create Interaction

**Endpoint:** `POST /leads/{{lead_id}}/interactions`

**Body:**
```json
{
  "type": "call",
  "notes": "Discussion sur les besoins du client",
  "duration_minutes": 30,
  "outcome": "positive"
}
```

**Types disponibles:**
- `call`: Appel téléphonique
- `email`: Email
- `meeting`: Réunion
- `note`: Note

**Résultat attendu:**
- ✅ Interaction créée
- ✅ Historique mis à jour

---

### Étape 4 : Pipelines 🔄

#### 4.1 Get All Pipelines

**Endpoint:** `GET /pipelines`

**Résultat attendu:**
- ✅ Liste des pipelines avec leurs étapes
- ✅ Premier pipeline_id sauvegardé automatiquement

---

#### 4.2 Create Pipeline

**Endpoint:** `POST /pipelines`

**Body:**
```json
{
  "name": "Pipeline Commercial",
  "description": "Pipeline principal pour les ventes",
  "is_active": true,
  "stages": [
    {
      "name": "Nouveau Lead",
      "order": 1,
      "color": "#3498db"
    },
    {
      "name": "Qualifié",
      "order": 2,
      "color": "#f39c12"
    },
    {
      "name": "Proposition",
      "order": 3,
      "color": "#e74c3c"
    },
    {
      "name": "Gagné",
      "order": 4,
      "color": "#27ae60"
    }
  ]
}
```

**Résultat attendu:**
- ✅ Pipeline créé avec toutes ses étapes

---

#### 4.3 Get Pipeline by ID

**Endpoint:** `GET /pipelines/{{pipeline_id}}`

**Résultat:** Détails du pipeline avec statistiques

---

### Étape 5 : Tâches ✅

#### 5.1 Get All Tasks

**Endpoint:** `GET /tasks`

**Paramètres optionnels:**
- `status`: all, pending, in_progress, completed
- `priority`: all, low, medium, high, urgent
- `assigned_to`: User ID

**Résultat attendu:**
- ✅ Liste des tâches
- ✅ Premier task_id sauvegardé

---

#### 5.2 Create Task

**Endpoint:** `POST /tasks`

**Body:**
```json
{
  "title": "Appeler le client",
  "description": "Follow-up suite à la démo",
  "type": "call",
  "priority": "high",
  "status": "pending",
  "assigned_to_user_id": 1,
  "lead_id": 1,
  "due_date": "2024-12-31 14:00:00"
}
```

**Types de tâches:**
- `call`: Appel
- `email`: Email
- `meeting`: Réunion
- `todo`: À faire
- `follow_up`: Relance

**Priorités:**
- `low`: Basse
- `medium`: Moyenne
- `high`: Haute
- `urgent`: Urgente

**Résultat attendu:**
- ✅ Tâche créée
- ✅ Notification envoyée à l'assigné

---

#### 5.3 Get Task by ID

**Endpoint:** `GET /tasks/{{task_id}}`

**Résultat:** Détails de la tâche

---

#### 5.4 Update Task

**Endpoint:** `PUT /tasks/{{task_id}}`

**Body:**
```json
{
  "status": "in_progress",
  "priority": "urgent"
}
```

**Résultat:** Tâche mise à jour

---

#### 5.5 Complete Task

**Endpoint:** `POST /tasks/{{task_id}}/complete`

**Body:**
```json
{
  "notes": "Tâche terminée avec succès"
}
```

**Résultat attendu:**
- ✅ Tâche marquée comme complétée
- ✅ Historique enregistré

---

### Étape 6 : Paramètres ⚙️

#### 6.1 Get Profile

**Endpoint:** `GET /settings/profile`

**Résultat:** Profil utilisateur complet

---

#### 6.2 Update Profile

**Endpoint:** `PUT /settings/profile`

**Body:**
```json
{
  "name": "Moussavou Francis Updated",
  "phone": "+241123456789",
  "settings": {
    "timezone": "Africa/Libreville",
    "language": "fr",
    "notifications": {
      "email": true,
      "push": true
    }
  }
}
```

**Résultat:** Profil mis à jour

---

#### 6.3 Get Roles

**Endpoint:** `GET /settings/roles`

**Résultat:**
```json
{
  "roles": [
    {"id": 1, "name": "Admin"},
    {"id": 2, "name": "Manager"},
    {"id": 3, "name": "Sales"}
  ]
}
```

---

#### 6.4 Get Teams

**Endpoint:** `GET /settings/teams`

**Résultat:** Liste des équipes

---

### Étape 7 : Notifications 🔔

#### 7.1 Get All Notifications

**Endpoint:** `GET /notifications`

**Résultat:** Liste des notifications

---

#### 7.2 Get Unread Count

**Endpoint:** `GET /notifications/unread-count`

**Résultat:**
```json
{
  "unread_count": 5
}
```

---

#### 7.3 Mark All As Read

**Endpoint:** `PUT /notifications/mark-all-as-read`

**Résultat:** Toutes les notifications marquées comme lues

---

### Étape 8 : AI Insights 🤖

#### 8.1 Get All Insights

**Endpoint:** `GET /ai-insights`

**Paramètres optionnels:**
- `type`: Type d'insight (recommendation, prediction, alert)
- `limit`: Nombre de résultats

**Résultat:**
```json
{
  "insights": [
    {
      "id": 1,
      "type": "recommendation",
      "content": {
        "title": "Analyse du Lead",
        "summary": "Ce lead montre un fort potentiel",
        "recommendations": [
          "Relancer dans les 24h",
          "Préparer une proposition"
        ],
        "confidence": 85
      }
    }
  ]
}
```

---

#### 8.2 Get Insights Statistics

**Endpoint:** `GET /ai-insights/statistics`

**Résultat:**
```json
{
  "statistics": {
    "total": 50,
    "unread": 12,
    "by_type": {
      "recommendation": 25,
      "prediction": 15,
      "alert": 10
    }
  }
}
```

---

#### 8.3 Generate Insights for Lead

**Endpoint:** `POST /ai-insights/generate/{{lead_id}}`

**Résultat:** Nouveaux insights générés pour le lead

---

### Étape 9 : Health Check 🏥

#### 9.1 API Health

**Endpoint:** `GET /api/health`

**Résultat:**
```json
{
  "status": "ok",
  "timestamp": "2024-01-15T10:30:00Z",
  "version": "1.0.0"
}
```

---

## 🧪 Tests Automatisés

La collection Postman inclut des tests automatiques pour :

### Tests d'Authentification
- ✅ Vérification du status code 201/200
- ✅ Sauvegarde automatique du token
- ✅ Vérification de l'existence du token
- ✅ Sauvegarde de l'user_id

### Tests de Leads
- ✅ Vérification du status code 200/201
- ✅ Sauvegarde automatique du lead_id
- ✅ Vérification de la structure des données

### Tests de Dashboard
- ✅ Vérification de l'existence des données summary
- ✅ Validation de la structure des activités
- ✅ Validation des données de funnel

---

## 🎯 Ordre de Test Recommandé

1. **Health Check** - Vérifier que l'API fonctionne
2. **Register/Login** - S'authentifier
3. **Dashboard** - Vérifier les données agrégées
4. **Leads** - Créer et gérer des leads
5. **Pipelines** - Vérifier les pipelines
6. **Tasks** - Créer et gérer des tâches
7. **Settings** - Gérer le profil
8. **Notifications** - Vérifier les notifications
9. **AI Insights** - Tester les insights IA

---

## 💡 Astuces

### Exécuter Toute la Collection

1. Cliquer sur la collection "LeadFlow API"
2. Cliquer sur "Run"
3. Sélectionner tous les tests
4. Cliquer sur "Run LeadFlow API"

### Utiliser les Variables

Les variables sont automatiquement remplies lors de l'exécution des requêtes :
- `{{token}}` - Token d'authentification
- `{{user_id}}` - ID utilisateur connecté
- `{{lead_id}}` - ID du dernier lead
- `{{task_id}}` - ID de la dernière tâche
- `{{pipeline_id}}` - ID du dernier pipeline

### Personnaliser les Tests

Vous pouvez modifier les tests dans l'onglet "Tests" de chaque requête.

Exemple de test personnalisé :
```javascript
pm.test("Lead créé avec succès", () => {
    const response = pm.response.json();
    pm.expect(response.lead.name).to.equal("Jean Dupont");
});
```

---

## ❌ Résolution de Problèmes

### Erreur 401 Unauthorized

**Solution:** 
1. Relancer la requête "Login User"
2. Vérifier que le token est bien sauvegardé dans les variables

### Erreur 422 Validation Error

**Solution:**
1. Vérifier le format des données envoyées
2. S'assurer que tous les champs requis sont présents
3. Vérifier les règles de validation (email unique, etc.)

### Erreur 404 Not Found

**Solution:**
1. Vérifier l'URL de base (base_url)
2. S'assurer que le serveur Laravel est démarré
3. Vérifier que l'ID dans l'URL existe

### Erreur 500 Server Error

**Solution:**
1. Vérifier les logs Laravel (`storage/logs/laravel.log`)
2. Vérifier la configuration de la base de données
3. S'assurer que toutes les migrations sont exécutées

---

## 📚 Ressources

- **Documentation API:** `API_COMPLETE_ROUTES.md`
- **Collection Postman:** `LeadFlow_API_Complete.postman_collection.json`
- **Logs Laravel:** `storage/logs/laravel.log`

---

## ✅ Checklist de Test Complet

- [ ] Health Check réussi
- [ ] Registration réussie
- [ ] Login réussi
- [ ] Dashboard stats chargées
- [ ] Lead créé avec succès
- [ ] Lead mis à jour
- [ ] Lead assigné
- [ ] Interaction créée
- [ ] Pipeline récupéré
- [ ] Tâche créée
- [ ] Tâche complétée
- [ ] Profil mis à jour
- [ ] Notifications récupérées
- [ ] AI Insights récupérés

---

Bonne chance pour vos tests ! 🚀

