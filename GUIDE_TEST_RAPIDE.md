# Guide de Test Rapide - LeadFlow API

## Configuration Postman

### 1. Variables d'Environnement
Créez un environnement Postman avec ces variables :
```json
{
  "base_url": "http://127.0.0.1:8000/api/v1",
  "token": "",
  "api_key": "test-api-key-123"
}
```

### 2. Import de la Collection
Importez le fichier `LeadFlow_Postman_Collection.json` dans Postman.

## Séquence de Test Recommandée

### Étape 1: Authentification
1. **Register User** - Créer un utilisateur de test
2. **Login User** - Se connecter (le token sera automatiquement sauvegardé)

### Étape 2: Test des Routes Publiques
3. **Capture Web Form Lead** - Tester la capture de lead publique

### Étape 3: Test des Routes Protégées
4. **Get Dashboard Stats** - Vérifier les statistiques
5. **Get All Leads** - Lister les leads
6. **Create Lead** - Créer un nouveau lead
7. **Update Lead** - Modifier le lead créé
8. **Get All Pipelines** - Lister les pipelines
9. **Create Pipeline** - Créer un pipeline
10. **Get All Tasks** - Lister les tâches
11. **Create Task** - Créer une tâche
12. **Get User Profile** - Voir le profil utilisateur
13. **Update Profile** - Modifier le profil
14. **Get All Notifications** - Voir les notifications

## Données de Test Prêtes à l'Emploi

### Utilisateur de Test
```json
{
  "name": "Test User",
  "email": "test@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "role_id": 2
}
```

### Lead de Test
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
  "notes": "High priority lead"
}
```

### Pipeline de Test
```json
{
  "name": "Sales Pipeline",
  "description": "Main sales pipeline",
  "is_active": true,
  "stages": [
    {"name": "New Lead", "order": 1, "color": "#3498db"},
    {"name": "Qualified", "order": 2, "color": "#f39c12"},
    {"name": "Proposal", "order": 3, "color": "#e74c3c"},
    {"name": "Closed Won", "order": 4, "color": "#27ae60"}
  ]
}
```

### Tâche de Test
```json
{
  "title": "Follow up with client",
  "description": "Call the client to discuss proposal",
  "type": "call",
  "priority": "high",
  "status": "pending",
  "assigned_to_user_id": 1,
  "lead_id": 1,
  "due_date": "2024-12-31 14:00:00"
}
```

## Codes de Réponse Attendus

### Succès
- **200 OK** - Requête réussie
- **201 Created** - Ressource créée
- **204 No Content** - Suppression réussie

### Erreurs
- **400 Bad Request** - Données invalides
- **401 Unauthorized** - Non authentifié
- **403 Forbidden** - Permissions insuffisantes
- **404 Not Found** - Ressource non trouvée
- **422 Unprocessable Entity** - Erreurs de validation

## Tests de Validation

### Test 1: Authentification
- ✅ Inscription utilisateur
- ✅ Connexion utilisateur
- ✅ Sauvegarde automatique du token

### Test 2: Capture de Leads
- ✅ Capture via formulaire web
- ✅ Validation des données requises

### Test 3: Gestion des Leads
- ✅ Création de lead
- ✅ Modification de lead
- ✅ Liste des leads avec pagination

### Test 4: Gestion des Pipelines
- ✅ Création de pipeline
- ✅ Création des étapes du pipeline

### Test 5: Gestion des Tâches
- ✅ Création de tâche
- ✅ Association à un lead

### Test 6: Paramètres
- ✅ Consultation du profil
- ✅ Modification du profil

## Dépannage

### Problème: Token expiré
**Solution:** Relancer la requête "Login User"

### Problème: Erreur 500
**Solution:** Vérifier que Laravel est démarré et que la base de données est configurée

### Problème: Erreur 404
**Solution:** Vérifier que l'URL de base est correcte

### Problème: Erreur 422
**Solution:** Vérifier le format des données JSON

## Commandes Laravel Utiles

```bash
# Démarrer le serveur
php artisan serve

# Vérifier les routes
php artisan route:list

# Vérifier les logs
tail -f storage/logs/laravel.log

# Tester la base de données
php artisan tinker
```

## Vérification Rapide

Après avoir exécuté tous les tests, vérifiez que :
1. ✅ L'utilisateur est créé et peut se connecter
2. ✅ Les leads sont créés et listés
3. ✅ Les pipelines sont créés avec leurs étapes
4. ✅ Les tâches sont créées et associées aux leads
5. ✅ Le profil utilisateur peut être modifié
6. ✅ Les notifications sont accessibles

## Notes Importantes

- Le token JWT est automatiquement sauvegardé après la connexion
- Toutes les routes protégées nécessitent le token Bearer
- Les routes de capture de leads sont publiques mais nécessitent une clé API
- La pagination est disponible sur les listes (page, per_page)
- Les filtres sont disponibles sur les listes (status, source, etc.)
