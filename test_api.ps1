# Script de test pour l'API LeadFlow

# 1. Connexion pour obtenir un token
$loginBody = @{
    email = "test4@example.com"
    password = "password123"
} | ConvertTo-Json

$loginResponse = Invoke-WebRequest -Uri "http://127.0.0.1:8000/api/v1/auth/login" -Method POST -ContentType "application/json" -Body $loginBody -Headers @{"Accept"="application/json"}

$loginData = $loginResponse.Content | ConvertFrom-Json
$token = $loginData.token

Write-Host "Token obtenu: $token"

# 2. Test des routes du dashboard
$headers = @{
    "Authorization" = "Bearer $token"
    "Accept" = "application/json"
}

Write-Host "`n=== Test des routes du dashboard ==="

# Test /dashboard/stats
try {
    $statsResponse = Invoke-WebRequest -Uri "http://127.0.0.1:8000/api/v1/dashboard/stats" -Method GET -Headers $headers
    Write-Host "✅ /dashboard/stats - Status: $($statsResponse.StatusCode)"
} catch {
    Write-Host "❌ /dashboard/stats - Erreur: $($_.Exception.Message)"
}

# Test /dashboard/activity
try {
    $activityResponse = Invoke-WebRequest -Uri "http://127.0.0.1:8000/api/v1/dashboard/activity" -Method GET -Headers $headers
    Write-Host "✅ /dashboard/activity - Status: $($activityResponse.StatusCode)"
} catch {
    Write-Host "❌ /dashboard/activity - Erreur: $($_.Exception.Message)"
}

# Test /dashboard/funnel
try {
    $funnelResponse = Invoke-WebRequest -Uri "http://127.0.0.1:8000/api/v1/dashboard/funnel" -Method GET -Headers $headers
    Write-Host "✅ /dashboard/funnel - Status: $($funnelResponse.StatusCode)"
} catch {
    Write-Host "❌ /dashboard/funnel - Erreur: $($_.Exception.Message)"
}

# Test /ai-insights
try {
    $aiResponse = Invoke-WebRequest -Uri "http://127.0.0.1:8000/api/v1/ai-insights" -Method GET -Headers $headers
    Write-Host "✅ /ai-insights - Status: $($aiResponse.StatusCode)"
} catch {
    Write-Host "❌ /ai-insights - Erreur: $($_.Exception.Message)"
}

Write-Host "`n=== Tests terminés ==="
