#!/usr/bin/perl -i

# Read the file
undef $/;
my $file = <STDIN>;

# Replace the isCookieAlive method
$file =~ s/private function isCookieAlive\(\?string \$cookieHeader\): bool\s*\{[^}]*\}/
private function isCookieAlive(?string \$cookieHeader): bool
    {
        if (! is_string(\$cookieHeader) || trim(\$cookieHeader) === '') {
            return false;
        }

        // Быстрая проверка наличия основных куки-идентификаторов перед HTTP-запросом
        if (!preg_match('\/(nlbi|visid_incap|incap_ses|reese84|PHPSESSID|XSRF-TOKEN|laravel_session)\/i', \$cookieHeader)) {
            return false;
        }

        try {
            \$response = Http::timeout(5) // Уменьшенный таймаут для более быстрой проверки
                ->withHeaders([
                    'Cookie' => \$cookieHeader,
                    'User-Agent' => config('services.copart.user_agent'),
                    'Accept' => 'application/json, text/plain, \/*',
                    'Accept-Encoding' => 'gzip', // Для быстрой передачи
                ])
                ->get(self::CHECK_URL);

            if (! \$response->successful()) {
                return false;
            }

            // Быстрая проверка ответа без полной десериализации
            \$body = \$response->body();
            return strpos(\$body, '"data"') !== false || strpos(\$body, '"lotDetails"') !== false;
        } catch (\\Throwable \$e) {
            \\Illuminate\\Support\\Facades\\Log::debug('CopartCookieManager: cookie check failed', ['error' => \$e->getMessage()]);
            return false;
        }
    }
/gx;

print $file;