# LeadFlow API - Documentation

## Vue d'ensemble

LeadFlow est une API REST complète construite avec Laravel 12, conçue pour la gestion avancée des leads commerciaux. Elle intègre des fonctionnalités d'IA, d'automatisation, et d'intégrations externes pour optimiser les processus de vente.

## Fonctionnalités principales

### 🎯 Gestion des Leads
- CRUD complet des leads
- Système de scoring intelligent
- Pipeline de vente personnalisable
- Historique 360° des interactions
- Capture automatique via formulaires web et email

### 🤖 Intelligence Artificielle
- Analyse automatique des leads
- Recommandations personnalisées
- Scoring prédictif
- Insights globaux et tendances

### 🔄 Automatisation
- Règles d'automatisation configurables
- Notifications intelligentes
- Workflows de nurturing
- Déclencheurs basés sur les événements

### 🔗 Intégrations
- Gmail/Outlook (synchronisation email)
- Salesforce/HubSpot (CRM)
- Google Calendar (planification)
- Webhooks personnalisés

### 📊 Dashboard & Analytics
- KPIs en temps réel
- Graphiques interactifs
- Performance d'équipe
- Rapports automatisés

## Installation

### Prérequis
- PHP 8.2+
- Composer
- MySQL/PostgreSQL
- Redis (optionnel, pour les queues)

### Configuration

1. **Cloner le projet**
```bash
git clone <repository-url>
cd lead-flow-api
```

2. **Installer les dépendances**
```bash
composer install
```

3. **Configuration de l'environnement**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Configuration de la base de données**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=leadflow
DB_USERNAME=root
DB_PASSWORD=
```

5. **Exécuter les migrations et seeders**
```bash
php artisan migrate
php artisan db:seed
```

6. **Installer les packages frontend (optionnel)**
```bash
npm install
npm run dev
```

## Structure de l'API

### Base URL
```
https://votre-domaine.com/api/v1
```

### Authentification
L'API utilise Laravel Sanctum pour l'authentification par token.

```bash
# Enregistrement
POST /api/v1/auth/register

# Connexion
POST /api/v1/auth/login

# Utilisation du token
Authorization: Bearer {token}
```

### Endpoints principaux

#### Authentification
- `POST /auth/register` - Enregistrement
- `POST /auth/login` - Connexion
- `POST /auth/logout` - Déconnexion
- `POST /auth/refresh` - Rafraîchir le token
- `GET /auth/user` - Profil utilisateur

#### Dashboard
- `GET /dashboard/summary` - Résumé général
- `GET /dashboard/charts` - Données graphiques
- `GET /dashboard/recent-leads` - Leads récents
- `GET /dashboard/daily-tasks` - Tâches du jour
- `GET /dashboard/team-performance` - Performance équipe

#### Leads
- `GET /leads` - Liste des leads
- `POST /leads` - Créer un lead
- `GET /leads/{id}` - Détails d'un lead
- `PUT /leads/{id}` - Modifier un lead
- `DELETE /leads/{id}` - Supprimer un lead
- `POST /leads/{id}/assign` - Assigner un lead
- `POST /leads/import` - Importer des leads
- `GET /leads/export` - Exporter des leads

#### Pipelines
- `GET /pipelines` - Liste des pipelines
- `POST /pipelines` - Créer un pipeline
- `GET /pipelines/{id}` - Détails d'un pipeline
- `PUT /pipelines/{id}` - Modifier un pipeline
- `GET /pipeline-view/{id}` - Vue Kanban

#### Tâches
- `GET /tasks` - Liste des tâches
- `POST /tasks` - Créer une tâche
- `PUT /tasks/{id}` - Modifier une tâche
- `POST /tasks/{id}/complete` - Marquer comme terminée

## Exemples d'utilisation

### Créer un lead
```bash
curl -X POST https://votre-domaine.com/api/v1/leads \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "first_name": "Jean",
    "last_name": "Dupont",
    "email": "jean.dupont@example.com",
    "company": "TechCorp",
    "status_id": 1,
    "priority": "Hot"
  }'
```

### Obtenir le dashboard
```bash
curl -X GET https://votre-domaine.com/api/v1/dashboard/summary \
  -H "Authorization: Bearer {token}"
```

### Assigner un lead
```bash
curl -X POST https://votre-domaine.com/api/v1/leads/1/assign \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "assigned_to_user_id": 2
  }'
```

## Configuration des services

### IA (OpenAI)
```env
AI_API_KEY=your_openai_api_key
AI_BASE_URL=https://api.openai.com/v1
AI_MODEL=gpt-3.5-turbo
```

### Notifications (Pusher)
```env
PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_app_key
PUSHER_APP_SECRET=your_app_secret
PUSHER_APP_CLUSTER=your_cluster
```

### Intégrations
```env
# Gmail
GMAIL_CLIENT_ID=your_client_id
GMAIL_CLIENT_SECRET=your_client_secret

# Salesforce
SALESFORCE_CLIENT_ID=your_client_id
SALESFORCE_CLIENT_SECRET=your_client_secret
SALESFORCE_INSTANCE_URL=your_instance_url
```

## Tests

```bash
# Tests unitaires
php artisan test --testsuite=Unit

# Tests fonctionnels
php artisan test --testsuite=Feature

# Tous les tests
php artisan test
```

## Déploiement

### Production
1. Configurer le serveur web (Nginx/Apache)
2. Configurer la base de données
3. Configurer Redis pour les queues
4. Configurer les variables d'environnement
5. Exécuter les migrations
6. Configurer les tâches cron

### Docker (optionnel)
```bash
docker-compose up -d
```

## Sécurité

- Authentification JWT via Laravel Sanctum
- Validation stricte des données
- Middleware de permissions
- Logs d'audit
- Protection CSRF
- Rate limiting

## Monitoring

- Logs Laravel
- Métriques de performance
- Monitoring des queues
- Alertes d'erreur

## Support

Pour toute question ou problème :
- Documentation : [Lien vers la documentation]
- Support : support@leadflow.com
- Issues : [Lien vers GitHub Issues]

## Licence

MIT License - Voir le fichier LICENSE pour plus de détails.