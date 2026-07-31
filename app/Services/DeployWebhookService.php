<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
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
            $response = Http::withHeaders([
                'Authorization' => "token {$token}",
                'Accept' => 'application/vnd.github.v3+json',
            ])->post("https://api.github.com/repos/{$repo}/dispatches", [
                'event_type' => 'build-static-site',
                'client_payload' => [
                    'timestamp' => now()->toIso8601String(),
                    'triggered_by' => 'Filament CMS Portal',
                ],
            ]);

            if ($response->successful()) {
                Log::info("Deploy Webhook successfully dispatched to GitHub Actions.");
                return true;
            }

            Log::error("GitHub Actions Webhook dispatch failed: " . $response->body());
            return false;
        } catch (\Exception $e) {
            Log::error("Error dispatching deploy webhook: " . $e->getMessage());
            return false;
        }
    }
}
