# Documentation des Routes API - LeadFlow

## Base URL
```
http://127.0.0.1:8000/api/v1
```

## Authentification
Toutes les routes protégées nécessitent un token Bearer dans l'en-tête :
```
Authorization: Bearer {token}
```

---

## 1. AUTHENTIFICATION

### POST /auth/register
**Description:** Inscription d'un nouvel utilisateur
**Headers:** `Content-Type: application/json`
**Body:**
```json
{
  "name": "John Doe",
  "email": "john.doe@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "role_id": 2
}
```

### POST /auth/login
**Description:** Connexion utilisateur
**Headers:** `Content-Type: application/json`
**Body:**
```json
{
  "email": "john.doe@example.com",
  "password": "password123"
}
```
**Response:** Retourne un token JWT

### POST /auth/forgot-password
**Description:** Demande de réinitialisation de mot de passe
**Headers:** `Content-Type: application/json`
**Body:**
```json
{
  "email": "john.doe@example.com"
}
```

### POST /auth/reset-password
**Description:** Réinitialisation du mot de passe
**Headers:** `Content-Type: application/json`
**Body:**
```json
{
  "email": "john.doe@example.com",
  "token": "reset-token-here",
  "password": "newpassword123",
  "password_confirmation": "newpassword123"
}
```

### POST /auth/logout
**Description:** Déconnexion utilisateur
**Headers:** `Authorization: Bearer {token}`

---

## 2. CAPTURE DE LEADS (PUBLIC)

### POST /leads/capture/web-form
**Description:** Capture d'un lead via formulaire web
**Headers:** `Content-Type: application/json`, `X-API-Key: {api_key}`
**Body:**
```json
{
  "name": "Jane Smith",
  "email": "jane.smith@example.com",
  "phone": "+241123456789",
  "company": "ACME Corp",
  "source": "website",
  "notes": "Interested in our premium package"
}
```

### POST /leads/capture/email
**Description:** Capture d'un lead via email
**Headers:** `Content-Type: application/json`, `X-API-Key: {api_key}`
**Body:**
```json
{
  "email": "prospect@example.com",
  "subject": "Inquiry about services",
  "content": "Hello, I am interested in your services..."
}
```

---

## 3. DASHBOARD

### GET /dashboard/stats
**Description:** Statistiques du tableau de bord
**Headers:** `Authorization: Bearer {token}`

### GET /dashboard/activity
**Description:** Activité récente
**Headers:** `Authorization: Bearer {token}`
**Query Parameters:** `limit=10`

### GET /dashboard/funnel
**Description:** Tunnel de conversion des leads
**Headers:** `Authorization: Bearer {token}`

---

## 4. GESTION DES LEADS

### GET /leads
**Description:** Liste des leads
**Headers:** `Authorization: Bearer {token}`
**Query Parameters:** 
- `page=1`
- `per_page=10`
- `status=new`
- `source=website`
- `assigned_to=1`

### GET /leads/{id}
**Description:** Détails d'un lead
**Headers:** `Authorization: Bearer {token}`

### POST /leads
**Description:** Créer un lead
**Headers:** `Authorization: Bearer {token}`, `Content-Type: application/json`
**Body:**
```json
{
  "name": "Alice Johnson",
  "email": "alice.johnson@example.com",
  "phone": "+241987654321",
  "company": "Tech Solutions Inc",
  "source": "referral",
  "status_id": 1,
  "pipeline_id": 1,
  "pipeline_stage_id": 1,
  "assigned_to_user_id": 1,
  "notes": "High priority lead from referral"
}
```

### PUT /leads/{id}
**Description:** Modifier un lead
**Headers:** `Authorization: Bearer {token}`, `Content-Type: application/json`
**Body:** (même structure que POST)

### DELETE /leads/{id}
**Description:** Supprimer un lead
**Headers:** `Authorization: Bearer {token}`

### POST /leads/{id}/assign
**Description:** Assigner un lead
**Headers:** `Authorization: Bearer {token}`, `Content-Type: application/json`
**Body:**
```json
{
  "assigned_to_user_id": 2
}
```

### POST /leads/{id}/status
**Description:** Changer le statut d'un lead
**Headers:** `Authorization: Bearer {token}`, `Content-Type: application/json`
**Body:**
```json
{
  "status_id": 3,
  "notes": "Lead qualified and ready for proposal"
}
```

---

## 5. GESTION DES PIPELINES

### GET /pipelines
**Description:** Liste des pipelines
**Headers:** `Authorization: Bearer {token}`

### GET /pipelines/{id}
**Description:** Détails d'un pipeline
**Headers:** `Authorization: Bearer {token}`

### POST /pipelines
**Description:** Créer un pipeline
**Headers:** `Authorization: Bearer {token}`, `Content-Type: application/json`
**Body:**
```json
{
  "name": "Enterprise Sales Pipeline",
  "description": "Pipeline for enterprise clients",
  "is_active": true,
  "stages": [
    {
      "name": "Initial Contact",
      "order": 1,
      "color": "#3498db"
    },
    {
      "name": "Qualification",
      "order": 2,
      "color": "#f39c12"
    },
    {
      "name": "Proposal",
      "order": 3,
      "color": "#e74c3c"
    },
    {
      "name": "Negotiation",
      "order": 4,
      "color": "#9b59b6"
    },
    {
      "name": "Closed Won",
      "order": 5,
      "color": "#27ae60"
    }
  ]
}
```

### PUT /pipelines/{id}
**Description:** Modifier un pipeline
**Headers:** `Authorization: Bearer {token}`, `Content-Type: application/json`
**Body:** (même structure que POST)

### DELETE /pipelines/{id}
**Description:** Supprimer un pipeline
**Headers:** `Authorization: Bearer {token}`

---

## 6. GESTION DES TÂCHES

### GET /tasks
**Description:** Liste des tâches
**Headers:** `Authorization: Bearer {token}`
**Query Parameters:**
- `page=1`
- `per_page=10`
- `status=pending`
- `assigned_to=1`
- `lead_id=1`

### GET /tasks/{id}
**Description:** Détails d'une tâche
**Headers:** `Authorization: Bearer {token}`

### POST /tasks
**Description:** Créer une tâche
**Headers:** `Authorization: Bearer {token}`, `Content-Type: application/json`
**Body:**
```json
{
  "title": "Follow up with client",
  "description": "Call the client to discuss proposal details",
  "type": "call",
  "priority": "high",
  "status": "pending",
  "assigned_to_user_id": 1,
  "lead_id": 1,
  "due_date": "2024-01-15 14:00:00",
  "reminder_date": "2024-01-15 13:00:00"
}
```

### PUT /tasks/{id}
**Description:** Modifier une tâche
**Headers:** `Authorization: Bearer {token}`, `Content-Type: application/json`
**Body:** (même structure que POST)

### POST /tasks/{id}/complete
**Description:** Marquer une tâche comme terminée
**Headers:** `Authorization: Bearer {token}`, `Content-Type: application/json`
**Body:**
```json
{
  "completion_notes": "Successfully contacted client. They are interested in our proposal."
}
```

### DELETE /tasks/{id}
**Description:** Supprimer une tâche
**Headers:** `Authorization: Bearer {token}`

---

## 7. PARAMÈTRES

### GET /settings/profile
**Description:** Profil utilisateur
**Headers:** `Authorization: Bearer {token}`

### PUT /settings/profile
**Description:** Modifier le profil
**Headers:** `Authorization: Bearer {token}`, `Content-Type: application/json`
**Body:**
```json
{
  "name": "John Doe Updated",
  "email": "john.doe.updated@example.com",
  "phone": "+241123456789",
  "current_team_id": 1,
  "settings": {
    "timezone": "Africa/Libreville",
    "language": "fr",
    "notifications": {
      "email": true,
      "push": true,
      "sms": false
    }
  }
}
```

### POST /settings/password
**Description:** Changer le mot de passe
**Headers:** `Authorization: Bearer {token}`, `Content-Type: application/json`
**Body:**
```json
{
  "current_password": "password123",
  "password": "newpassword123",
  "password_confirmation": "newpassword123"
}
```

### GET /settings/team
**Description:** Membres de l'équipe
**Headers:** `Authorization: Bearer {token}`

### POST /settings/team
**Description:** Ajouter un membre d'équipe
**Headers:** `Authorization: Bearer {token}`, `Content-Type: application/json`
**Body:**
```json
{
  "name": "Jane Smith",
  "email": "jane.smith@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "role_id": 2,
  "team_id": 1
}
```

### PUT /settings/team/{id}
**Description:** Modifier un membre d'équipe
**Headers:** `Authorization: Bearer {token}`, `Content-Type: application/json`
**Body:** (même structure que POST)

### GET /settings/integrations
**Description:** Intégrations configurées
**Headers:** `Authorization: Bearer {token}`

### POST /settings/integrations
**Description:** Créer une intégration
**Headers:** `Authorization: Bearer {token}`, `Content-Type: application/json`
**Body:**
```json
{
  "name": "Gmail Integration",
  "type": "email",
  "provider": "gmail",
  "config": {
    "client_id": "your-gmail-client-id",
    "client_secret": "your-gmail-client-secret",
    "redirect_uri": "https://yourapp.com/oauth/callback"
  },
  "is_active": true
}
```

---

## 8. NOTIFICATIONS

### GET /notifications
**Description:** Liste des notifications
**Headers:** `Authorization: Bearer {token}`
**Query Parameters:**
- `page=1`
- `per_page=10`
- `unread_only=true`

### POST /notifications/{id}/read
**Description:** Marquer une notification comme lue
**Headers:** `Authorization: Bearer {token}`

### POST /notifications/mark-all-read
**Description:** Marquer toutes les notifications comme lues
**Headers:** `Authorization: Bearer {token}`

### DELETE /notifications/{id}
**Description:** Supprimer une notification
**Headers:** `Authorization: Bearer {token}`

---

## 9. INSIGHTS IA

### GET /ai-insights
**Description:** Liste des insights IA
**Headers:** `Authorization: Bearer {token}`
**Query Parameters:**
- `type=lead_scoring`
- `limit=10`

### POST /ai-insights/generate
**Description:** Générer des insights IA
**Headers:** `Authorization: Bearer {token}`, `Content-Type: application/json`
**Body:**
```json
{
  "type": "lead_scoring",
  "data": {
    "lead_id": 1,
    "interactions": [
      {
        "type": "email",
        "content": "Client showed high interest in premium package"
      },
      {
        "type": "call",
        "content": "Positive response to pricing discussion"
      }
    ]
  }
}
```

---

## 10. WEBHOOKS

### POST /webhooks/{endpoint}
**Description:** Gestion des webhooks
**Headers:** `Content-Type: application/json`, `X-Webhook-Signature: sha256={signature}`
**Body:**
```json
{
  "event": "lead.created",
  "data": {
    "lead_id": 1,
    "name": "John Doe",
    "email": "john.doe@example.com",
    "source": "website"
  },
  "timestamp": "2024-01-10T10:00:00Z"
}
```

---

## Codes de Réponse

- **200 OK** - Requête réussie
- **201 Created** - Ressource créée
- **204 No Content** - Suppression réussie
- **400 Bad Request** - Données invalides
- **401 Unauthorized** - Non authentifié
- **403 Forbidden** - Permissions insuffisantes
- **404 Not Found** - Ressource non trouvée
- **422 Unprocessable Entity** - Erreurs de validation
- **500 Internal Server Error** - Erreur serveur

## Variables d'Environnement Postman

```json
{
  "base_url": "http://127.0.0.1:8000/api/v1",
  "token": "",
  "api_key": "your-api-key-here"
}
```
