<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\DomCrawler\Crawler;

class UpdateTariffs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tariffs:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch delivery tariffs from auctioncars.am/calculator and store them to storage/app/tariffs.json';

    public function handle(): int
    {
        $this->info('Fetching tariffs from auctioncars.am...');
        $response = Http::withHeaders([
            'User-Agent' => 'Mozilla/5.0',
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Referer' => 'https://auctioncars.am/calculator/',
            'Accept-Language' => 'en-US,en;q=0.8,ru;q=0.6',
        ])->get('https://auctioncars.am/calculator/');

        if (!$response->ok()) {
            $this->error('Failed to fetch page: HTTP '.$response->status());
            return self::FAILURE;
        }

        $html = $response->body();
        $crawler = new Crawler($html);

        $rows = [];
        // Попытка через DOM
        $crawler->filter('table tr')->each(function (Crawler $tr) use (&$rows) {
            $cells = $tr->filter('td');
            if ($cells->count() < 2) {
                return;
            }
            $state = trim($cells->eq(0)->text());
            $priceRaw = trim($cells->eq(1)->text());
            $price = (int) preg_replace('/[^\d]/', '', $priceRaw);
            if ($state !== '' && $price > 0) {
                $rows[] = ['state' => $state, 'price' => $price];
            }
        });

        // Если DOM не сработал (страница может быть другой), парсим регексом
        if (empty($rows)) {
            // Regex fallback по HTML
            if (preg_match_all('/<tr[^>]*>\s*<td[^>]*>(.*?)<\/td>\s*<td[^>]*>(.*?)<\/td>/is', $html, $m, PREG_SET_ORDER)) {
                foreach ($m as $row) {
                    $state = trim(strip_tags($row[1]));
                    $priceRaw = trim(strip_tags($row[2]));
                    $price = (int) preg_replace('/[^\d]/', '', $priceRaw);
                    if ($state !== '' && $price > 0) {
                        $rows[] = ['state' => $state, 'price' => $price];
                    }
                }
            }
        }

        if (empty($rows)) {
            // Regex fallback по JSON-подобному содержимому (если тарифы лежат в JS)
            if (preg_match_all('/([A-Z]{2}-[A-Z0-9\-]+)[^\\d]{0,20}([\\d]{2,6})/m', $html, $m, PREG_SET_ORDER)) {
                foreach ($m as $row) {
                    $state = trim($row[1]);
                    $price = (int) $row[2];
                    if ($state !== '' && $price > 0) {
                        $rows[] = ['state' => $state, 'price' => $price];
                    }
                }
            }
        }

        if (empty($rows)) {
            Storage::put('tariffs-debug.html', $html);
            $this->error('No tariff rows parsed. Saved raw HTML to storage/app/tariffs-debug.html for inspection.');
            return self::FAILURE;
        }

        Storage::put('tariffs.json', json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $this->info('Saved '.count($rows).' tariffs to storage/app/tariffs.json');

        return self::SUCCESS;
    }
}
