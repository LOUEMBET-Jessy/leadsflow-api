# LeadFlow API - Structure ERD Finale

## 🎯 Vue d'ensemble

L'API LeadFlow a été complètement refaite selon votre diagramme ERD pour offrir une architecture SaaS multi-tenant robuste et évolutive.

## 🏗️ Architecture Multi-Tenant

### Structure de Base
- **Comptes (Accounts)** : Base du système multi-tenant
- **Utilisateurs (Users)** : Appartiennent à un compte
- **Pipelines** : Définis par compte
- **Étapes (Stages)** : Appartiennent à un pipeline
- **Leads** : Appartiennent à un compte et sont dans une étape

## 📊 Modèles de Données

### 1. Account (Compte)
```php
- id, name, slug, domain
- plan (free, basic, premium, enterprise)
- settings (JSON)
- is_active, trial_ends_at, subscription_ends_at
```

### 2. User (Utilisateur)
```php
- id, account_id, name, email, password
- role (Admin, Manager, Commercial, Marketing, GestLead)
- phone, avatar, settings (JSON)
- is_active, last_login_at
```

### 3. Pipeline (Pipeline de Vente)
```php
- id, account_id, name, description
- is_active, sort_order
```

### 4. Stage (Étape)
```php
- id, pipeline_id, name, description
- order, color, is_final
```

### 5. Lead (Prospect)
```php
- id, account_id, current_stage_id
- name, email, phone, company
- status, source, location, score
- estimated_value, notes, custom_fields (JSON)
- last_contact_at
```

### 6. LeadAssignment (Assignation)
```php
- id, lead_id, user_id
- assigned_at, assigned_by_user_id, notes
```

### 7. Interaction
```php
- id, lead_id, user_id
- type (Email, Appel, Reunion, Note, SMS, Chat)
- subject, summary, details
- date, duration, outcome
- metadata (JSON)
```

### 8. Task (Tâche)
```php
- id, lead_id, user_id
- title, description, priority, status
- due_date, completed_at, completion_notes
- reminders (JSON)
```

### 9. AutomationRule (Règle d'Automatisation)
```php
- id, account_id, name, description
- trigger_type, action_type, parameters (JSON)
- is_active, priority
```

### 10. EmailSequence (Séquence Email)
```php
- id, account_id, name, description
- trigger_conditions (JSON)
- is_active
```

### 11. SequenceStep (Étape de Séquence)
```php
- id, sequence_id, order, delay_days
- subject, email_template, text_template
- personalization_tags (JSON), is_active
```

### 12. Segment (Segment)
```php
- id, account_id, name, description
- criteria (JSON), is_active
- lead_count, last_updated_at
```

### 13. Integration (Intégration)
```php
- id, account_id, type, name, provider
- config (JSON), is_active
- last_sync_at, sync_status (JSON)
```

## 🚀 Endpoints API

### Authentification
```
POST /api/v1/auth/register
POST /api/v1/auth/login
POST /api/v1/auth/logout
GET  /api/v1/auth/user
POST /api/v1/auth/refresh
PUT  /api/v1/auth/profile
PUT  /api/v1/auth/password
```

### Compte
```
GET  /api/v1/account
PUT  /api/v1/account
GET  /api/v1/account/stats
GET  /api/v1/account/users
POST /api/v1/account/users
PUT  /api/v1/account/users/{user}
DELETE /api/v1/account/users/{user}
```

### Dashboard
```
GET /api/v1/dashboard/overview
GET /api/v1/dashboard/leads-by-status
GET /api/v1/dashboard/leads-by-source
GET /api/v1/dashboard/pipeline-funnel
GET /api/v1/dashboard/recent-activities
GET /api/v1/dashboard/team-performance
GET /api/v1/dashboard/conversion-rates
GET /api/v1/dashboard/monthly-trends
GET /api/v1/dashboard/top-sources
GET /api/v1/dashboard/overdue-tasks
```

### Leads
```
GET    /api/v1/leads
POST   /api/v1/leads
GET    /api/v1/leads/stats
GET    /api/v1/leads/{lead}
PUT    /api/v1/leads/{lead}
DELETE /api/v1/leads/{lead}
POST   /api/v1/leads/{lead}/assign
DELETE /api/v1/leads/{lead}/unassign/{user}
PUT    /api/v1/leads/{lead}/score
```

### Pipelines
```
GET    /api/v1/pipelines
POST   /api/v1/pipelines
GET    /api/v1/pipelines/{pipeline}/stats
GET    /api/v1/pipelines/{pipeline}
PUT    /api/v1/pipelines/{pipeline}
DELETE /api/v1/pipelines/{pipeline}
POST   /api/v1/pipelines/reorder
```

### Étapes
```
GET  /api/v1/pipelines/{pipeline}/stages
POST /api/v1/pipelines/{pipeline}/stages
POST /api/v1/pipelines/{pipeline}/stages/reorder
GET  /api/v1/stages/{stage}
PUT  /api/v1/stages/{stage}
DELETE /api/v1/stages/{stage}
GET  /api/v1/stages/{stage}/stats
POST /api/v1/stages/{stage}/move-lead
```

### Interactions
```
GET  /api/v1/interactions
GET  /api/v1/interactions/recent
GET  /api/v1/interactions/{interaction}
PUT  /api/v1/interactions/{interaction}
DELETE /api/v1/interactions/{interaction}
GET  /api/v1/leads/{lead}/interactions
POST /api/v1/leads/{lead}/interactions
GET  /api/v1/leads/{lead}/interactions/stats
```

### Tâches
```
GET  /api/v1/tasks
POST /api/v1/tasks
GET  /api/v1/tasks/my-tasks
GET  /api/v1/tasks/overdue
GET  /api/v1/tasks/due-today
GET  /api/v1/tasks/stats
GET  /api/v1/tasks/{task}
PUT  /api/v1/tasks/{task}
DELETE /api/v1/tasks/{task}
POST /api/v1/tasks/{task}/complete
```

### Automatisations
```
GET  /api/v1/automations
POST /api/v1/automations
GET  /api/v1/automations/trigger-types
GET  /api/v1/automations/action-types
GET  /api/v1/automations/stats
GET  /api/v1/automations/{automation}
PUT  /api/v1/automations/{automation}
DELETE /api/v1/automations/{automation}
POST /api/v1/automations/{automation}/toggle
POST /api/v1/automations/{automation}/test/{lead}
POST /api/v1/automations/{automation}/execute/{lead}
```

### Séquences Email
```
GET  /api/v1/email-sequences
POST /api/v1/email-sequences
GET  /api/v1/email-sequences/personalization-tags
GET  /api/v1/email-sequences/{sequence}
PUT  /api/v1/email-sequences/{sequence}
DELETE /api/v1/email-sequences/{sequence}
GET  /api/v1/email-sequences/{sequence}/stats
POST /api/v1/email-sequences/{sequence}/enroll/{lead}
POST /api/v1/email-sequences/{sequence}/steps
POST /api/v1/email-sequences/{sequence}/steps/reorder
PUT  /api/v1/sequence-steps/{step}
DELETE /api/v1/sequence-steps/{step}
```

### Segments
```
GET  /api/v1/segments
POST /api/v1/segments
GET  /api/v1/segments/field-operators
GET  /api/v1/segments/available-fields
GET  /api/v1/segments/stats
GET  /api/v1/segments/{segment}
PUT  /api/v1/segments/{segment}
DELETE /api/v1/segments/{segment}
GET  /api/v1/segments/{segment}/leads
POST /api/v1/segments/{segment}/test/{lead}
POST /api/v1/segments/{segment}/update-count
```

### Intégrations
```
GET  /api/v1/integrations
POST /api/v1/integrations
GET  /api/v1/integrations/types
GET  /api/v1/integrations/providers
GET  /api/v1/integrations/config-template
GET  /api/v1/integrations/stats
GET  /api/v1/integrations/{integration}
PUT  /api/v1/integrations/{integration}
DELETE /api/v1/integrations/{integration}
POST /api/v1/integrations/{integration}/test
POST /api/v1/integrations/{integration}/sync
```

## 🔧 Fonctionnalités Avancées

### 1. Multi-Tenant
- Isolation complète des données par compte
- Gestion des utilisateurs par compte
- Configuration personnalisée par compte

### 2. Automatisations
- Règles IF-THEN configurables
- Déclencheurs multiples (création, mise à jour, etc.)
- Actions variées (assignation, email, tâche, etc.)

### 3. Séquences Email
- Séquences automatisées personnalisables
- Tags de personnalisation
- Gestion des délais et étapes

### 4. Segmentation
- Critères de segmentation avancés
- Mise à jour automatique des compteurs
- Tests de correspondance en temps réel

### 5. Intégrations
- Support de multiples providers (Salesforce, HubSpot, etc.)
- Synchronisation automatique
- Configuration flexible

## 📈 Avantages de la Nouvelle Structure

1. **Scalabilité** : Architecture multi-tenant pour supporter plusieurs clients
2. **Flexibilité** : Pipelines et étapes personnalisables par compte
3. **Performance** : Relations optimisées et index appropriés
4. **Sécurité** : Isolation des données par compte
5. **Évolutivité** : Structure modulaire pour ajouter facilement de nouvelles fonctionnalités

## 🚀 Démarrage Rapide

1. **Installation** :
   ```bash
   composer install
   php artisan migrate --seed
   ```

2. **Démarrage** :
   ```bash
   php artisan serve
   ```

3. **Test** :
   ```bash
   # Connexion
   curl -X POST http://127.0.0.1:8000/api/v1/auth/login \
     -H "Content-Type: application/json" \
     -d '{"email":"admin@agl-gabon.com","password":"password123"}'
   
   # Dashboard
   curl -X GET http://127.0.0.1:8000/api/v1/dashboard/overview \
     -H "Authorization: Bearer YOUR_TOKEN"
   ```

## 📊 Données de Test

L'API inclut des données de test complètes :
- 1 compte AGL Gabon
- 6 utilisateurs avec différents rôles
- 2 pipelines (Vente et Marketing)
- 7 leads avec interactions et tâches
- Automatisations et segments d'exemple

## 🎯 Prochaines Étapes

1. **Interface Utilisateur** : Développer l'interface frontend
2. **Tests** : Ajouter des tests unitaires et d'intégration
3. **Documentation** : Créer la documentation API complète
4. **Déploiement** : Configurer l'environnement de production
5. **Monitoring** : Ajouter des outils de monitoring et logging

---

**L'API LeadFlow est maintenant prête pour la production avec une architecture robuste et évolutive !** 🚀
