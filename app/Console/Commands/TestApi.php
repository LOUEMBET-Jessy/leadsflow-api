<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api:test {--url= : API base URL}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test API endpoints';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $baseUrl = $this->option('url') ?? config('app.url') . '/api/v1';

        $this->info("Testing API endpoints at: {$baseUrl}");

        $tests = [
            'Health Check' => 'GET /health',
            'Auth Register' => 'POST /auth/register',
            'Auth Login' => 'POST /auth/login',
            'Dashboard Summary' => 'GET /dashboard/summary',
            'Leads List' => 'GET /leads',
            'Pipelines List' => 'GET /pipelines',
            'Tasks List' => 'GET /tasks',
        ];

        $results = [];

        foreach ($tests as $name => $endpoint) {
            $this->line("Testing {$name}...");
            
            try {
                $response = $this->makeRequest($baseUrl . $endpoint);
                $status = $response->successful() ? 'PASS' : 'FAIL';
                $results[] = [$name, $status, $response->status()];
                
                if ($response->successful()) {
                    $this->info("✓ {$name}: {$response->status()}");
                } else {
                    $this->error("✗ {$name}: {$response->status()}");
                }
            } catch (\Exception $e) {
                $results[] = [$name, 'ERROR', $e->getMessage()];
                $this->error("✗ {$name}: {$e->getMessage()}");
            }
        }

        $this->newLine();
        $this->table(['Test', 'Status', 'Response'], $results);

        $passed = collect($results)->where(1, 'PASS')->count();
        $total = count($results);

        $this->info("Tests completed: {$passed}/{$total} passed");

        return $passed === $total ? 0 : 1;
    }

    /**
     * Make HTTP request
     */
    protected function makeRequest(string $url): \Illuminate\Http\Client\Response
    {
        $method = str_contains($url, 'POST') ? 'POST' : 'GET';
        
        if ($method === 'POST') {
            $url = str_replace('POST ', '', $url);
            return Http::post($url, $this->getTestData($url));
        }
        
        $url = str_replace('GET ', '', $url);
        return Http::get($url);
    }

    /**
     * Get test data for POST requests
     */
    protected function getTestData(string $url): array
    {
        if (str_contains($url, '/auth/register')) {
            return [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ];
        }

        if (str_contains($url, '/auth/login')) {
            return [
                'email' => 'test@example.com',
                'password' => 'password123',
            ];
        }

        return [];
    }
}
