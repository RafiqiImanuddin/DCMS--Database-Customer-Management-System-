<?php

namespace App\Jobs;

use App\Models\CrossSellingImport;
use App\Services\CrossSellingImporter;
use App\Services\RepeatOrderPredictor;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class ImportCrossSellingJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 3600;

    public function __construct(public CrossSellingImport $import) {}

    public function handle(CrossSellingImporter $importer, RepeatOrderPredictor $predictor): void
    {
        $importer->import($this->import);
        if (! $this->import->exists) {
            return;
        }

        $this->import->refresh();

        if ($this->import->status === 'completed') {
            $predictor->rebuild();
        }
    }

    public function failed(Throwable $exception): void
    {
        if (! $this->import->exists) {
            return;
        }

        $this->import->update([
            'status' => 'failed',
            'error_log' => $exception->getMessage(),
        ]);
    }
}
