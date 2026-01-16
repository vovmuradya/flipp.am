<?php

namespace App\Jobs;

use App\Models\AuctionParseJob as AuctionParseJobModel;
use App\Services\AuctionParserService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ParseAuctionJob implements ShouldQueue
{
    use Queueable;

    public $timeout = 600; // 10 минут - Increased timeout for server environment
    public $tries = 1;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $jobId,
        public string $url,
        public string $auctionType
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $jobRecord = AuctionParseJobModel::where('job_id', $this->jobId)->first();
        
        if (!$jobRecord) {
            Log::error("Parse job not found: {$this->jobId}");
            return;
        }

        try {
            $jobRecord->update(['status' => 'processing']);
            
            Log::info("🔄 Starting async parse", [
                'job_id' => $this->jobId,
                'url' => $this->url,
                'type' => $this->auctionType
            ]);

            $service = app(AuctionParserService::class);
            $aggressive = $this->auctionType === 'copart';
            $data = $service->parseFromUrl($this->url, aggressive: $aggressive);

            if ($data && !empty($data['make'])) {
                $jobRecord->update([
                    'status' => 'completed',
                    'result' => $data,
                ]);
                
                Log::info("✅ Async parse completed", [
                    'job_id' => $this->jobId,
                    'make' => $data['make']
                ]);
            } else {
                $jobRecord->update([
                    'status' => 'failed',
                    'error_message' => 'Не удалось извлечь данные',
                ]);
                
                Log::warning("⚠️ Async parse returned empty", ['job_id' => $this->jobId]);
            }

        } catch (\Exception $e) {
            $jobRecord->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
            
            Log::error("❌ Async parse failed", [
                'job_id' => $this->jobId,
                'error' => $e->getMessage()
            ]);
        }
    }
}
