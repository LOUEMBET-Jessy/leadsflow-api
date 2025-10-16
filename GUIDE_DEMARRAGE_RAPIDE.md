# Guide de Démarrage Rapide - LeadFlow API

## ✅ Configuration Terminée

Votre API LeadFlow est maintenant prête ! Voici ce qui a été configuré :

### Base de Données
- ✅ Base de données MySQL `leadflow_db` créée
- ✅ Toutes les migrations exécutées
- ✅ Données de test insérées (utilisateurs, leads, pipelines, tâches)

### Serveur
- ✅ Serveur Laravel démarré sur `http://127.0.0.1:8000`
- ✅ API accessible sur `http://127.0.0.1:8000/api/v1`

## 🚀 Test de l'API

### 1. Import de la Collection Postman
1. Ouvrez Postman
2. Importez le fichier `LeadFlow_Postman_Collection.json`
3. Créez un environnement avec ces variables :
   ```json
   {
     "base_url": "http://127.0.0.1:8000/api/v1",
     "token": "",
     "api_key": "test-api-key-123"
   }
   ```

### 2. Séquence de Test Rapide

#### Étape 1: Authentification
1. **Register User** - Créer un utilisateur
2. **Login User** - Se connecter (le token sera sauvegardé automatiquement)

#### Étape 2: Test des Routes
3. **Get Dashboard Stats** - Vérifier les statistiques
4. **Get All Leads** - Voir les leads existants
5. **Create Lead** - Créer un nouveau lead
6. **Get All Pipelines** - Voir les pipelines
7. **Create Task** - Créer une tâche

## 📊 Données de Test Disponibles

### Utilisateurs Créés
- **Admin**: `admin@leadflow.com` / `password`
- **Manager**: `manager@leadflow.com` / `password`
- **Sales**: `sales@leadflow.com` / `password`

### Leads de Test
- 10 leads avec différents statuts
- Sources variées (website, referral, email)
- Assignés à différents utilisateurs

### Pipelines
- Pipeline principal avec 4 étapes
- Pipeline secondaire pour les prospects

### Tâches
- Tâches de suivi
- Tâches d'appel
- Tâches de prospection

## 🔧 Commandes Utiles

### Démarrer le serveur
```bash
C:\laragon\bin\php\php-8.3.16-Win32-vs16-x64\php.exe artisan serve
```

### Vérifier les routes
```bash
C:\laragon\bin\php\php-8.3.16-Win32-vs16-x64\php.exe artisan route:list
```

### Vider et recharger la base de données
```bash
C:\laragon\bin\php\php-8.3.16-Win32-vs16-x64\php.exe artisan migrate:fresh --seed
```

### Voir les logs
```bash
tail -f storage/logs/laravel.log
```

## 📋 Endpoints Principaux

### Authentification
- `POST /auth/register` - Inscription
- `POST /auth/login` - Connexion
- `POST /auth/logout` - Déconnexion

### Leads
- `GET /leads` - Liste des leads
- `POST /leads` - Créer un lead
- `PUT /leads/{id}` - Modifier un lead
- `DELETE /leads/{id}` - Supprimer un lead

### Pipelines
- `GET /pipelines` - Liste des pipelines
- `POST /pipelines` - Créer un pipeline

### Tâches
- `GET /tasks` - Liste des tâches
- `POST /tasks` - Créer une tâche
- `PUT /tasks/{id}` - Modifier une tâche

### Dashboard
- `GET /dashboard/stats` - Statistiques
- `GET /dashboard/activity` - Activité récente

## 🧪 Exemple de Test Rapide

### 1. Connexion
```bash
POST http://127.0.0.1:8000/api/v1/auth/login
Content-Type: application/json

{
  "email": "admin@leadflow.com",
  "password": "password"
}
```

### 2. Voir les statistiques
```bash
GET http://127.0.0.1:8000/api/v1/dashboard/stats
Authorization: Bearer {token}
```

### 3. Créer un lead
```bash
POST http://127.0.0.1:8000/api/v1/leads
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "Test Lead",
  "email": "test@example.com",
  "phone": "+241123456789",
  "company": "Test Company",
  "source": "website",
  "status_id": 1,
  "pipeline_id": 1,
  "pipeline_stage_id": 1,
  "assigned_to_user_id": 1,
  "notes": "Lead de test"
}
```

## 🎯 Prochaines Étapes

1. **Tester tous les endpoints** avec la collection Postman
2. **Installer les packages manquants** (Sanctum, Spatie Permission) si nécessaire
3. **Configurer les intégrations** (Gmail, Salesforce, etc.)
4. **Personnaliser les pipelines** selon vos besoins
5. **Ajouter des automatisations** pour les workflows

## 📞 Support

Si vous rencontrez des problèmes :
1. Vérifiez que le serveur est démarré
2. Vérifiez les logs dans `storage/logs/laravel.log`
3. Vérifiez que la base de données est accessible
4. Vérifiez que les routes sont correctes

Votre API LeadFlow est maintenant opérationnelle ! 🎉
