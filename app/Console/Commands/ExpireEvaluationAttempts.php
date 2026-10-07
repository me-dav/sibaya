<?php

namespace App\Console\Commands;

use App\Models\EvaluationAttempt;
use App\Services\EvaluationService;
use Illuminate\Console\Command;

class ExpireEvaluationAttempts extends Command
{
    protected $signature = 'evaluations:expire';

    protected $description = 'Tandai attempt evaluasi yang waktunya habis sebagai expired dan nilai jawaban tersimpan';

    public function handle(EvaluationService $service): int
    {
        $count = 0;

        EvaluationAttempt::where('status', 'in_progress')->chunkById(100, function ($attempts) use ($service, &$count) {
            foreach ($attempts as $attempt) {
                if ($service->finalizeIfExpired($attempt)->status !== 'in_progress') {
                    $count++;
                }
            }
        });

        $this->info("{$count} attempt ditandai expired.");

        return self::SUCCESS;
    }
}
