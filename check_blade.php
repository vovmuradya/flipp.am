<?php

$file = file_get_contents(__DIR__ . '/resources/views/listings/show.blade.php');

preg_match_all('/@if\(/', $file, $ifs);
preg_match_all('/@endif/', $file, $endifs);
preg_match_all('/@auth/', $file, $auths);
preg_match_all('/@endauth/', $file, $endauths);
preg_match_all('/@can\(/', $file, $cans);
preg_match_all('/@endcan/', $file, $endcans);
preg_match_all('/@php/', $file, $phps);
preg_match_all('/@endphp/', $file, $endphps);
preg_match_all('/@forelse\(/', $file, $foreaches);
preg_match_all('/@endforelse/', $file, $endforeaches);
preg_match_all('/@foreach\(/', $file, $foreachs);
preg_match_all('/@endforeach/', $file, $endforeachs);
preg_match_all('/@for\s*\(/', $file, $fors);
preg_match_all('/@endfor/', $file, $endfors);

echo "Проверка баланса директив Blade:\n\n";
echo "@if: " . count($ifs[0]) . " | @endif: " . count($endifs[0]) . " | " . (count($ifs[0]) === count($endifs[0]) ? "✓" : "✗") . "\n";
echo "@auth: " . count($auths[0]) . " | @endauth: " . count($endauths[0]) . " | " . (count($auths[0]) === count($endauths[0]) ? "✓" : "✗") . "\n";
echo "@can: " . count($cans[0]) . " | @endcan: " . count($endcans[0]) . " | " . (count($cans[0]) === count($endcans[0]) ? "✓" : "✗") . "\n";
echo "@php: " . count($phps[0]) . " | @endphp: " . count($endphps[0]) . " | " . (count($phps[0]) === count($endphps[0]) ? "✓" : "✗") . "\n";
echo "@forelse: " . count($foreaches[0]) . " | @endforelse: " . count($endforeaches[0]) . " | " . (count($foreaches[0]) === count($endforeaches[0]) ? "✓" : "✗") . "\n";
echo "@foreach: " . count($foreachs[0]) . " | @endforeach: " . count($endforeachs[0]) . " | " . (count($foreachs[0]) === count($endforeachs[0]) ? "✓" : "✗") . "\n";
echo "@for: " . count($fors[0]) . " | @endfor: " . count($endfors[0]) . " | " . (count($fors[0]) === count($endfors[0]) ? "✓" : "✗") . "\n";

