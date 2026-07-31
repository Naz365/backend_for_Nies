<?php

namespace App\Observers;

use App\Services\DeployWebhookService;

class ContentObserver
{
    public function saved($model): void
    {
        DeployWebhookService::triggerDeploy();
    }

    public function deleted($model): void
    {
        DeployWebhookService::triggerDeploy();
    }
}
