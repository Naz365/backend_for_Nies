<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class DeployWebhookService
{
    /**
     * Trigger GitHub Actions deploy webhook
     */
    public static function triggerDeploy(): bool
    {
        $repo = config('services.github.repository', 'username/ni-engineering');
        $token = config('services.github.token', env('GITHUB_TOKEN'));

        if (!$token) {
            Log::info("Deploy Webhook Triggered (Simulation Mode: No GITHUB_TOKEN configured).");
            return true;
        }

        try {
            $ch = curl_init("https://api.github.com/repos/{$repo}/dispatches");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
                'event_type' => 'build-static-site',
                'client_payload' => [
                    'timestamp' => now()->toIso8601String(),
                    'triggered_by' => 'Filament CMS Portal',
                ],
            ]));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Authorization: token {$token}",
                'Accept: application/vnd.github.v3+json',
                'User-Agent: Laravel-CMS'
            ]);
            $response = curl_exec($ch);
            curl_close($ch);
            
            Log::info("Deploy Webhook dispatched to GitHub Actions: " . $response);
            return true;
        } catch (\Throwable $e) {
            Log::error("Error dispatching deploy webhook: " . $e->getMessage());
            return false;
        }
    }
}
