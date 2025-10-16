# 📋 LeadFlow API - Référence Rapide

## 🔗 Base URL
```
http://127.0.0.1:8000/api/v1
```

---

## 🔐 Authentification

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| POST | `/auth/register` | Inscription |
| POST | `/auth/login` | Connexion |
| POST | `/auth/logout` | Déconnexion |
| GET | `/auth/user` | Info utilisateur |
| POST | `/auth/refresh` | Rafraîchir token |

**Exemple Login:**
```json
POST /auth/login
{
  "email": "user@example.com",
  "password": "password123"
}
```

---

## 📊 Dashboard

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| GET | `/dashboard/summary` | Résumé complet |
| GET | `/dashboard/stats` | Statistiques |
| GET | `/dashboard/activity` | Activité récente |
| GET | `/dashboard/funnel` | Entonnoir de conversion |
| GET | `/dashboard/charts` | Données graphiques |

**Headers requis:** `Authorization: Bearer {{token}}`

---

## 👥 Leads

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| GET | `/leads` | Liste des leads |
| POST | `/leads` | Créer un lead |
| GET | `/leads/{id}` | Détails d'un lead |
| PUT | `/leads/{id}` | Mettre à jour |
| DELETE | `/leads/{id}` | Supprimer |
| POST | `/leads/{id}/status` | Changer le statut |
| POST | `/leads/{id}/assign` | Assigner |
| GET | `/leads/{id}/interactions` | Interactions |
| POST | `/leads/{id}/interactions` | Créer interaction |

**Exemple Création:**
```json
POST /leads
{
  "name": "Jean Dupont",
  "email": "jean@example.com",
  "phone": "+241123456789",
  "company": "ABC Company",
  "source": "website",
  "status_id": 1,
  "pipeline_id": 1,
  "pipeline_stage_id": 1,
  "assigned_to_user_id": 1
}
```

---

## 🔄 Pipelines

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| GET | `/pipelines` | Liste des pipelines |
| POST | `/pipelines` | Créer un pipeline |
| GET | `/pipelines/{id}` | Détails |
| PUT | `/pipelines/{id}` | Mettre à jour |
| DELETE | `/pipelines/{id}` | Supprimer |

---

## ✅ Tâches

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| GET | `/tasks` | Liste des tâches |
| POST | `/tasks` | Créer une tâche |
| GET | `/tasks/{id}` | Détails |
| PUT | `/tasks/{id}` | Mettre à jour |
| DELETE | `/tasks/{id}` | Supprimer |
| POST | `/tasks/{id}/complete` | Marquer complétée |

**Exemple Création:**
```json
POST /tasks
{
  "title": "Appeler le client",
  "description": "Follow-up",
  "type": "call",
  "priority": "high",
  "status": "pending",
  "assigned_to_user_id": 1,
  "lead_id": 1,
  "due_date": "2024-12-31 14:00:00"
}
```

---

## ⚙️ Paramètres

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| GET | `/settings/profile` | Profil utilisateur |
| PUT | `/settings/profile` | Mettre à jour profil |
| PUT | `/settings/profile/password` | Changer mot de passe |
| GET | `/settings/roles` | Liste des rôles |
| GET | `/settings/teams` | Liste des équipes |

---

## 🔔 Notifications

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| GET | `/notifications` | Liste notifications |
| GET | `/notifications/unread-count` | Nombre non lues |
| PUT | `/notifications/{id}/read` | Marquer comme lue |
| PUT | `/notifications/mark-all-as-read` | Tout marquer lu |
| DELETE | `/notifications/{id}` | Supprimer |

---

## 🤖 AI Insights

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| GET | `/ai-insights` | Liste des insights |
| GET | `/ai-insights/statistics` | Statistiques |
| POST | `/ai-insights/generate/{lead_id}` | Générer insights |

---

## 📝 Codes de Réponse

| Code | Signification |
|------|---------------|
| 200 | ✅ Succès |
| 201 | ✅ Créé |
| 204 | ✅ Succès (pas de contenu) |
| 400 | ❌ Requête invalide |
| 401 | ❌ Non authentifié |
| 403 | ❌ Non autorisé |
| 404 | ❌ Non trouvé |
| 422 | ❌ Erreur de validation |
| 500 | ❌ Erreur serveur |

---

## 🔑 Headers Standards

### Routes Protégées
```
Authorization: Bearer {{token}}
Accept: application/json
Content-Type: application/json
```

### Routes Publiques de Capture
```
X-API-Key: your_api_key
Accept: application/json
Content-Type: application/json
```

---

## 🎯 Filtres & Pagination

### Pagination
```
?page=1&per_page=15
```

### Filtres Leads
```
?status=all&source=all&search=keyword
```

### Filtres Tâches
```
?status=pending&priority=high&assigned_to=1
```

### Période Dashboard
```
?period=month
```
Options: `week`, `month`, `quarter`, `year`

---

## 📦 Types de Données

### Lead Sources
- `website` - Site web
- `referral` - Référence
- `social_media` - Réseaux sociaux
- `email` - Email
- `phone` - Téléphone
- `event` - Événement
- `other` - Autre

### Task Types
- `call` - Appel
- `email` - Email
- `meeting` - Réunion
- `todo` - À faire
- `follow_up` - Relance

### Task Priorities
- `low` - Basse
- `medium` - Moyenne
- `high` - Haute
- `urgent` - Urgente

### Task Status
- `pending` - En attente
- `in_progress` - En cours
- `completed` - Complétée
- `cancelled` - Annulée

### Interaction Types
- `call` - Appel
- `email` - Email
- `meeting` - Réunion
- `note` - Note

---

## 🚀 Commandes Utiles

### Démarrer le serveur
```bash
php artisan serve --host=127.0.0.1 --port=8000
```

### Vider le cache
```bash
php artisan cache:clear
php artisan route:clear
php artisan config:clear
```

### Migrations
```bash
php artisan migrate
php artisan migrate:fresh --seed
```

### Logs
```bash
tail -f storage/logs/laravel.log
```

---

## 📚 Fichiers Importants

- `API_COMPLETE_ROUTES.md` - Documentation complète de toutes les routes
- `GUIDE_TEST_POSTMAN.md` - Guide détaillé pour tester avec Postman
- `LeadFlow_API_Complete.postman_collection.json` - Collection Postman complète
- `test_api.ps1` - Script PowerShell pour tests rapides

---

## 🔧 Variables Postman

Configurées automatiquement :
- `base_url` - URL de base de l'API
- `token` - Token d'authentification
- `user_id` - ID utilisateur
- `lead_id` - ID lead
- `task_id` - ID tâche
- `pipeline_id` - ID pipeline

---

## ✅ Checklist Démarrage Rapide

1. [ ] Serveur Laravel démarré
2. [ ] Base de données configurée
3. [ ] Migrations exécutées
4. [ ] Collection Postman importée
5. [ ] Health check OK (`GET /api/health`)
6. [ ] Inscription réussie
7. [ ] Login réussi
8. [ ] Token sauvegardé

---

## 🆘 Dépannage Rapide

### Erreur 401
→ Refaire le login pour obtenir un nouveau token

### Erreur 422
→ Vérifier le format des données et les champs requis

### Erreur 404
→ Vérifier que le serveur est démarré et l'URL correcte

### Erreur 500
→ Consulter les logs Laravel dans `storage/logs/`

---

## 📞 Support

Pour plus de détails, consultez :
- Documentation complète : `API_COMPLETE_ROUTES.md`
- Guide de test : `GUIDE_TEST_POSTMAN.md`
- Logs Laravel : `storage/logs/laravel.log`

