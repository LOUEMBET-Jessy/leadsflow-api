# 📋 LeadFlow API - Liste Complète des Routes

## 🔑 Variables d'Environnement Postman

```
base_url: http://127.0.0.1:8000/api/v1
token: (sera automatiquement rempli après login)
```

---

## 1. 🔐 AUTHENTIFICATION

### 1.1 Inscription
**POST** `/auth/register`

```json
{
  "name": "Moussavou Francis",
  "email": "moussavoufrancis1@gmail.com",
  "password": "password123",
  "password_confirmation": "password123",
  "role_id": 1
}
```

**Réponse 201:**
```json
{
  "message": "User registered successfully",
  "user": {
    "id": 1,
    "name": "Moussavou Francis",
    "email": "moussavoufrancis1@gmail.com"
  },
  "token": "MTF8MTc1ODI5MjQyMg=="
}
```

---

### 1.2 Connexion
**POST** `/auth/login`

```json
{
  "email": "moussavoufrancis1@gmail.com",
  "password": "password123"
}
```

**Réponse 200:**
```json
{
  "message": "Login successful",
  "user": {...},
  "token": "MTF8MTc1ODI5MjQyMg=="
}
```

---

### 1.3 Déconnexion
**POST** `/auth/logout`
**Headers:** `Authorization: Bearer {{token}}`

**Réponse 200:**
```json
{
  "message": "Logout successful"
}
```

---

### 1.4 Rafraîchir le Token
**POST** `/auth/refresh`
**Headers:** `Authorization: Bearer {{token}}`

**Réponse 200:**
```json
{
  "message": "Token refreshed successfully",
  "token": "NEW_TOKEN"
}
```

---

### 1.5 Informations Utilisateur
**GET** `/auth/user`
**Headers:** `Authorization: Bearer {{token}}`

**Réponse 200:**
```json
{
  "user": {
    "id": 1,
    "name": "Moussavou Francis",
    "email": "moussavoufrancis1@gmail.com",
    "role": {...}
  }
}
```

---

### 1.6 Mot de Passe Oublié
**POST** `/auth/forgot-password`

```json
{
  "email": "moussavoufrancis1@gmail.com"
}
```

---

### 1.7 Réinitialiser le Mot de Passe
**POST** `/auth/reset-password`

```json
{
  "email": "moussavoufrancis1@gmail.com",
  "token": "reset_token",
  "password": "newpassword123",
  "password_confirmation": "newpassword123"
}
```

---

### 1.8 Activer 2FA
**POST** `/auth/2fa/enable`
**Headers:** `Authorization: Bearer {{token}}`

---

### 1.9 Désactiver 2FA
**POST** `/auth/2fa/disable`
**Headers:** `Authorization: Bearer {{token}}`

---

### 1.10 Vérifier 2FA
**POST** `/auth/2fa/verify`
**Headers:** `Authorization: Bearer {{token}}`

```json
{
  "code": "123456"
}
```

---

## 2. 📊 DASHBOARD

**Toutes les routes nécessitent:** `Authorization: Bearer {{token}}`

### 2.1 Résumé du Dashboard
**GET** `/dashboard/summary`

**Réponse 200:**
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

### 2.2 Statistiques (alias de summary)
**GET** `/dashboard/stats`

---

### 2.3 Activité Récente
**GET** `/dashboard/activity?limit=20`

**Réponse 200:**
```json
{
  "activities": [
    {
      "type": "lead",
      "action": "updated",
      "description": "Lead ABC Company mis à jour",
      "timestamp": "2024-01-15T10:30:00Z",
      "data": {...}
    },
    {
      "type": "task",
      "action": "updated",
      "description": "Tâche Follow up mise à jour",
      "timestamp": "2024-01-15T09:15:00Z",
      "data": {...}
    }
  ]
}
```

---

### 2.4 Entonnoir de Conversion
**GET** `/dashboard/funnel?period=month`

**Paramètres:** `period` (week, month, quarter, year)

**Réponse 200:**
```json
{
  "funnel": [
    {
      "stage": "Nouveau Lead",
      "order": 1,
      "count": 50,
      "conversion_rate": 100
    },
    {
      "stage": "Qualifié",
      "order": 2,
      "count": 30,
      "conversion_rate": 60
    },
    {
      "stage": "Proposition",
      "order": 3,
      "count": 15,
      "conversion_rate": 30
    }
  ],
  "total_leads": 50,
  "period": "month"
}
```

---

### 2.5 Graphiques
**GET** `/dashboard/charts?period=month`

**Réponse 200:**
```json
{
  "charts": {
    "leads_by_source": [...],
    "leads_by_status": [...],
    "monthly_trend": [...],
    "conversion_funnel": [...]
  }
}
```

---

### 2.6 Leads Récents
**GET** `/dashboard/recent-leads?limit=10`

---

### 2.7 Tâches du Jour
**GET** `/dashboard/daily-tasks`

---

### 2.8 Performance de l'Équipe
**GET** `/dashboard/team-performance?period=month`

---

### 2.9 Vue d'ensemble du Pipeline
**GET** `/dashboard/pipeline-overview?pipeline_id=1`

---

### 2.10 Recommandations IA
**GET** `/dashboard/ai-recommendations`

---

## 3. 👥 GESTION DES LEADS

**Toutes les routes nécessitent:** `Authorization: Bearer {{token}}`

### 3.1 Liste des Leads
**GET** `/leads?page=1&per_page=15&status=all&source=all&search=keyword`

**Réponse 200:**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Jean Dupont",
      "email": "jean.dupont@example.com",
      "phone": "+241123456789",
      "company": "ABC Company",
      "source": "website",
      "status": {...},
      "assigned_to": {...},
      "pipeline_stage": {...}
    }
  ],
  "meta": {
    "current_page": 1,
    "total": 150
  }
}
```

---

### 3.2 Créer un Lead
**POST** `/leads`

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

**Réponse 201:**
```json
{
  "message": "Lead created successfully",
  "lead": {...}
}
```

---

### 3.3 Détails d'un Lead
**GET** `/leads/{id}`

---

### 3.4 Mettre à jour un Lead
**PUT** `/leads/{id}`

```json
{
  "name": "Jean Dupont Updated",
  "email": "jean.dupont@example.com",
  "phone": "+241123456789",
  "company": "ABC Company SA",
  "notes": "Lead qualifié"
}
```

---

### 3.5 Supprimer un Lead
**DELETE** `/leads/{id}`

**Réponse 200:**
```json
{
  "message": "Lead deleted successfully"
}
```

---

### 3.6 Changer le Statut d'un Lead
**POST** `/leads/{id}/status`

```json
{
  "status_id": 2,
  "notes": "Lead qualifié après appel"
}
```

---

### 3.7 Assigner un Lead
**POST** `/leads/{id}/assign`

```json
{
  "assigned_to_user_id": 2
}
```

---

### 3.8 Recalculer le Score d'un Lead
**POST** `/leads/{id}/score`

---

### 3.9 Interactions d'un Lead
**GET** `/leads/{id}/interactions`

**Réponse 200:**
```json
{
  "interactions": [
    {
      "id": 1,
      "type": "call",
      "notes": "Appel de suivi",
      "created_at": "2024-01-15T10:30:00Z",
      "user": {...}
    }
  ]
}
```

---

### 3.10 Créer une Interaction
**POST** `/leads/{id}/interactions`

```json
{
  "type": "call",
  "notes": "Discussion sur les besoins",
  "duration_minutes": 30,
  "outcome": "positive"
}
```

---

### 3.11 Tâches d'un Lead
**GET** `/leads/{id}/tasks`

---

### 3.12 Historique d'un Lead
**GET** `/leads/{id}/history`

---

### 3.13 Capture de Lead (Web Form)
**POST** `/leads/capture/web-form`
**Headers:** `X-API-Key: your_api_key`

```json
{
  "name": "Nouveau Lead",
  "email": "lead@example.com",
  "phone": "+241123456789",
  "message": "Intéressé par vos services",
  "source": "landing_page_1"
}
```

---

### 3.14 Capture de Lead (Email)
**POST** `/leads/capture/email`
**Headers:** `X-API-Key: your_api_key`

```json
{
  "from": "lead@example.com",
  "subject": "Demande d'information",
  "body": "Je souhaiterais obtenir plus d'informations...",
  "source": "email_campaign_1"
}
```

---

### 3.15 Importer des Leads
**POST** `/leads/import`

```json
{
  "file": "base64_encoded_csv_or_excel"
}
```

---

### 3.16 Exporter des Leads
**GET** `/leads/export?format=csv&status=all`

**Paramètres:** `format` (csv, excel, pdf)

---

## 4. 🔄 GESTION DES PIPELINES

**Toutes les routes nécessitent:** `Authorization: Bearer {{token}}`

### 4.1 Liste des Pipelines
**GET** `/pipelines`

**Réponse 200:**
```json
{
  "pipelines": [
    {
      "id": 1,
      "name": "Pipeline Commercial",
      "description": "Pipeline principal",
      "is_active": true,
      "stages": [...]
    }
  ]
}
```

---

### 4.2 Créer un Pipeline
**POST** `/pipelines`

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

---

### 4.3 Détails d'un Pipeline
**GET** `/pipelines/{id}`

---

### 4.4 Mettre à jour un Pipeline
**PUT** `/pipelines/{id}`

```json
{
  "name": "Pipeline Commercial Mis à Jour",
  "description": "Description mise à jour",
  "is_active": true
}
```

---

### 4.5 Supprimer un Pipeline
**DELETE** `/pipelines/{id}`

---

### 4.6 Leads d'un Pipeline
**GET** `/pipelines/{id}/leads`

---

### 4.7 Statistiques d'un Pipeline
**GET** `/pipelines/{id}/statistics`

---

### 4.8 Déplacer un Lead dans le Pipeline
**POST** `/pipelines/{id}/move-lead`

```json
{
  "lead_id": 1,
  "stage_id": 3
}
```

---

## 5. ✅ GESTION DES TÂCHES

**Toutes les routes nécessitent:** `Authorization: Bearer {{token}}`

### 5.1 Liste des Tâches
**GET** `/tasks?status=all&priority=all&assigned_to=all&page=1`

**Réponse 200:**
```json
{
  "data": [
    {
      "id": 1,
      "title": "Appeler le client",
      "description": "Follow-up suite à la démo",
      "type": "call",
      "priority": "high",
      "status": "pending",
      "due_date": "2024-01-20T14:00:00Z",
      "assigned_to": {...},
      "lead": {...}
    }
  ]
}
```

---

### 5.2 Créer une Tâche
**POST** `/tasks`

```json
{
  "title": "Appeler le client",
  "description": "Follow-up suite à la démo",
  "type": "call",
  "priority": "high",
  "status": "pending",
  "assigned_to_user_id": 1,
  "lead_id": 1,
  "due_date": "2024-01-20 14:00:00"
}
```

---

### 5.3 Détails d'une Tâche
**GET** `/tasks/{id}`

---

### 5.4 Mettre à jour une Tâche
**PUT** `/tasks/{id}`

```json
{
  "title": "Appeler le client - Urgent",
  "status": "in_progress",
  "priority": "urgent"
}
```

---

### 5.5 Supprimer une Tâche
**DELETE** `/tasks/{id}`

---

### 5.6 Marquer une Tâche comme Complétée
**POST** `/tasks/{id}/complete`

```json
{
  "notes": "Tâche terminée avec succès"
}
```

---

### 5.7 Tâches en Retard
**GET** `/tasks/overdue`

---

### 5.8 Tâches d'Aujourd'hui
**GET** `/tasks/today`

---

### 5.9 Tâches de la Semaine
**GET** `/tasks/week`

---

## 6. ⚙️ PARAMÈTRES

**Toutes les routes nécessitent:** `Authorization: Bearer {{token}}`

### 6.1 Profil Utilisateur
**GET** `/settings/profile`

**Réponse 200:**
```json
{
  "user": {
    "id": 1,
    "name": "Moussavou Francis",
    "email": "moussavoufrancis1@gmail.com",
    "role": {...},
    "team": {...},
    "settings": {...}
  }
}
```

---

### 6.2 Mettre à jour le Profil
**PUT** `/settings/profile`

```json
{
  "name": "Moussavou Francis",
  "email": "moussavoufrancis1@gmail.com",
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

---

### 6.3 Changer le Mot de Passe
**PUT** `/settings/profile/password`

```json
{
  "current_password": "password123",
  "new_password": "newpassword123",
  "new_password_confirmation": "newpassword123"
}
```

---

### 6.4 Mettre à jour les Notifications
**PUT** `/settings/profile/notifications`

```json
{
  "email_notifications": true,
  "push_notifications": true,
  "lead_notifications": true,
  "task_notifications": true
}
```

---

### 6.5 Liste des Utilisateurs (Admin)
**GET** `/settings/users?role=all&team=all`

---

### 6.6 Créer un Utilisateur (Admin)
**POST** `/settings/users`

```json
{
  "name": "Nouvel Utilisateur",
  "email": "newuser@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "role_id": 2,
  "team_id": 1
}
```

---

### 6.7 Détails d'un Utilisateur
**GET** `/settings/users/{id}`

---

### 6.8 Mettre à jour un Utilisateur
**PUT** `/settings/users/{id}`

```json
{
  "name": "Utilisateur Mis à Jour",
  "role_id": 2,
  "team_id": 1
}
```

---

### 6.9 Supprimer un Utilisateur
**DELETE** `/settings/users/{id}`

---

### 6.10 Liste des Rôles
**GET** `/settings/roles`

**Réponse 200:**
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

### 6.11 Liste des Équipes
**GET** `/settings/teams`

---

### 6.12 Liste des Intégrations
**GET** `/settings/integrations`

---

### 6.13 Connecter une Intégration
**POST** `/settings/integrations/{service}/connect`

```json
{
  "api_key": "your_api_key",
  "api_secret": "your_api_secret",
  "config": {...}
}
```

---

### 6.14 Déconnecter une Intégration
**DELETE** `/settings/integrations/{service}/disconnect`

---

### 6.15 Statut d'une Intégration
**GET** `/settings/integrations/{service}/status`

---

### 6.16 Importer des Données
**POST** `/settings/data/import`

```json
{
  "type": "leads",
  "file": "base64_encoded_file"
}
```

---

### 6.17 Exporter des Données
**GET** `/settings/data/export?type=leads&format=csv`

---

## 7. 🔔 NOTIFICATIONS

**Toutes les routes nécessitent:** `Authorization: Bearer {{token}}`

### 7.1 Liste des Notifications
**GET** `/notifications?page=1&per_page=15`

**Réponse 200:**
```json
{
  "data": [
    {
      "id": "uuid",
      "type": "lead_assigned",
      "data": {
        "message": "Un nouveau lead vous a été assigné",
        "lead_id": 1
      },
      "read_at": null,
      "created_at": "2024-01-15T10:30:00Z"
    }
  ]
}
```

---

### 7.2 Nombre de Notifications Non Lues
**GET** `/notifications/unread-count`

**Réponse 200:**
```json
{
  "unread_count": 5
}
```

---

### 7.3 Marquer une Notification comme Lue
**PUT** `/notifications/{id}/read`

---

### 7.4 Marquer Toutes comme Lues
**PUT** `/notifications/mark-all-as-read`

---

### 7.5 Supprimer une Notification
**DELETE** `/notifications/{id}`

---

### 7.6 Statistiques des Notifications
**GET** `/notifications/statistics`

---

## 8. 🤖 INSIGHTS IA

**Toutes les routes nécessitent:** `Authorization: Bearer {{token}}`

### 8.1 Liste des Insights IA
**GET** `/ai-insights?type=all&limit=15`

**Réponse 200:**
```json
{
  "insights": [
    {
      "id": 1,
      "type": "recommendation",
      "content": {
        "title": "Analyse du Lead Terminée",
        "summary": "Ce lead montre un fort potentiel...",
        "recommendations": [
          "Relancer dans les 24h",
          "Préparer une proposition détaillée"
        ],
        "confidence": 85
      },
      "lead": {...},
      "is_read": false,
      "created_at": "2024-01-15T10:30:00Z"
    }
  ]
}
```

---

### 8.2 Insights pour un Lead
**GET** `/ai-insights/leads/{lead_id}`

---

### 8.3 Insights Globaux
**GET** `/ai-insights/global`

---

### 8.4 Marquer un Insight comme Lu
**PUT** `/ai-insights/{id}/read`

---

### 8.5 Marquer Tous comme Lus
**PUT** `/ai-insights/mark-all-as-read`

---

### 8.6 Statistiques des Insights
**GET** `/ai-insights/statistics`

**Réponse 200:**
```json
{
  "statistics": {
    "total": 50,
    "unread": 12,
    "read": 38,
    "by_type": {
      "recommendation": 25,
      "prediction": 15,
      "alert": 10
    }
  }
}
```

---

### 8.7 Générer des Insights pour un Lead
**POST** `/ai-insights/generate/{lead_id}`

---

## 9. 🔗 WEBHOOKS

### 9.1 Recevoir un Webhook
**POST** `/webhooks/{endpoint}`
**Headers:** `X-Webhook-Signature: signature`

```json
{
  "event": "lead.created",
  "data": {...}
}
```

---

## 10. 🏥 SANTÉ DE L'API

### 10.1 Health Check
**GET** `/health`

**Réponse 200:**
```json
{
  "status": "ok",
  "timestamp": "2024-01-15T10:30:00Z",
  "version": "1.0.0"
}
```

---

## 📝 Notes Importantes

### Headers Requis

Pour toutes les routes protégées:
```
Authorization: Bearer {{token}}
Accept: application/json
Content-Type: application/json
```

Pour les routes publiques de capture:
```
X-API-Key: your_api_key
Accept: application/json
Content-Type: application/json
```

### Codes de Réponse HTTP

- **200** - Succès
- **201** - Créé avec succès
- **204** - Succès sans contenu
- **400** - Requête invalide
- **401** - Non authentifié
- **403** - Non autorisé
- **404** - Ressource non trouvée
- **422** - Erreur de validation
- **500** - Erreur serveur

### Pagination

Les routes avec pagination utilisent:
```
?page=1&per_page=15
```

### Filtres

Les routes avec filtres utilisent:
```
?status=all&priority=high&search=keyword
```

---

## 🚀 URL de Base

**Développement:** `http://127.0.0.1:8000/api/v1`
**Production:** `https://your-domain.com/api/v1`

