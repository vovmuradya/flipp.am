<?php

namespace App\Console\Commands;

use App\Services\AuctionParserService;
use App\Services\CopartImageService;
use Illuminate\Console\Command;

class TestCopartImages extends Command
{
    protected $signature = 'test:copart-images {lotId}';
    protected $description = 'Test Copart image fetching for a specific lot';

    public function handle(
        AuctionParserService $parser,
        CopartImageService $imageService
    ) {
        $lotId = $this->argument('lotId');

        $this->info('╔════════════════════════════════════════════════╗');
        $this->info('║   Testing Copart Image Fetching                ║');
        $this->info('╚════════════════════════════════════════════════╝');
        $this->newLine();

        $this->line("📦 Lot ID: <fg=cyan>{$lotId}</>");
        $this->newLine();

        $this->testFullParsing($parser, $lotId);
        $this->testImagesOnly($imageService, $lotId);
        $this->testHeadlessDirect($lotId);
        $this->testCdnPattern($lotId);
    }

    private function testFullParsing(AuctionParserService $parser, string $lotId): void
    {
        $this->info('🔄 Test 1: Full Parsing (with images)');
        $url = "https://www.copart.com/lot/{$lotId}";

        try {
            $result = $parser->parseFromUrl($url);

            if ($result) {
                $hasImages = !empty($result['photos']);
                $imageCount = count($result['photos'] ?? []);
                $hasRealImages = !empty($result['photos']) && !str_contains($result['photos'][0], 'placeholder');

                if ($hasRealImages) {
                    $this->info("   ✅ SUCCESS: Found {$imageCount} real images");
                    $this->line('   First 3 images:');
                    foreach (array_slice($result['photos'], 0, 3) as $i => $url) {
                        $this->line('   ' . ($i + 1) . ". {$url}");
                    }
                } else {
                    $this->warn('   ⚠️  Got placeholder images only');
                    if ($hasImages) {
                        $this->line('   Placeholder: ' . $result['photos'][0]);
                    }
                }
            } else {
                $this->error('   ❌ FAILED: No result returned');
            }
        } catch (\Throwable $e) {
            $this->error('   ❌ EXCEPTION: ' . $e->getMessage());
        }

        $this->newLine();
    }

    private function testImagesOnly(CopartImageService $imageService, string $lotId): void
    {
        $this->info('🖼️  Test 2: Images Only (via CopartImageService)');

        try {
            $images = $imageService->fetchLotImages($lotId);

            if (!empty($images) && !str_contains($images[0], 'placeholder')) {
                $this->info('   ✅ SUCCESS: Found ' . count($images) . ' images');

                foreach (array_slice($images, 0, 5) as $i => $url) {
                    $this->line('   ' . ($i + 1) . ". {$url}");
                }
            } else {
                $this->warn('   ⚠️  No real images found');
            }
        } catch (\Throwable $e) {
            $this->error('   ❌ EXCEPTION: ' . $e->getMessage());
        }

        $this->newLine();
    }

    private function testHeadlessDirect(string $lotId): void
    {
        $this->info('🌐 Test 3: Headless Browser (direct script call)');

        $scriptPath = base_path('scraper/fetch-copart-lot.cjs');

        if (!is_file($scriptPath)) {
            $this->error("   ❌ Script not found: {$scriptPath}");
            $this->newLine();
            return;
        }

        $command = sprintf('node %s %s 2>&1', escapeshellarg($scriptPath), escapeshellarg($lotId));

        $this->line("   Running: <fg=gray>{$command}</>");

        exec($command, $output, $returnCode);

        if ($returnCode === 0) {
            $result = json_decode(implode('', $output), true);

            if (isset($result['data']['images']) && !empty($result['data']['images'])) {
                $imageCount = count($result['data']['images']);
                $this->info("   ✅ SUCCESS: Headless found {$imageCount} images");

                foreach (array_slice($result['data']['images'], 0, 3) as $i => $url) {
                    $this->line('   ' . ($i + 1) . ". {$url}");
                }
            } else {
                $this->warn('   ⚠️  Headless ran but no images found');
            }
        } else {
            $this->error("   ❌ Headless failed with code {$returnCode}");
            $this->line('   Output:');
            foreach (array_slice($output, -5) as $line) {
                $this->line("   <fg=red>{$line}</>");
            }
        }

        $this->newLine();
    }

    private function testCdnPattern(string $lotId): void
    {
        $this->info('🔗 Test 4: CDN URL Pattern');

        $patterns = [
            'https://cs.copart.com/v1/AUTH_svc.pdoc00001/PIX%s/%s/1.JPG',
            'https://lotpix.copart.com/%s/1.JPG',
        ];

        $lotIdPart = substr($lotId, -3);

        foreach ($patterns as $pattern) {
            if (substr_count($pattern, '%s') === 2) {
                $url = sprintf($pattern, $lotIdPart, $lotId);
            } else {
                $url = sprintf($pattern, $lotId);
            }

            $this->line("   Testing: {$url}");

            try {
                $response = Http::timeout(5)->head($url);

                if ($response->successful()) {
                    $this->info('   ✅ WORKS! This CDN pattern is valid');

                    $count = 0;
                    for ($i = 1; $i <= 10; $i++) {
                        if (substr_count($pattern, '%s') === 2) {
                            $testUrl = sprintf($pattern, $lotIdPart, $lotId);
                            $testUrl = str_replace('/1.JPG', "/{$i}.JPG", $testUrl);
                        } else {
                            $testUrl = sprintf($pattern, $lotId);
                            $testUrl = str_replace('/1.JPG', "/{$i}.JPG", $testUrl);
                        }

                        if (Http::timeout(3)->head($testUrl)->successful()) {
                            $count++;
                        }
                    }

                    $this->info("   Found approximately {$count} images with this pattern");
                    $this->newLine();
                    return;
                }
            } catch (\Throwable $e) {
                // ignore
            }
        }

        $this->warn('   ⚠️  No CDN patterns work for this lot');
        $this->newLine();
    }
}
