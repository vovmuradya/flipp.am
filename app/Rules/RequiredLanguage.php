<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use LanguageDetection\Language;

class RequiredLanguage implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $allowedLanguages = ['ru', 'en', 'hy'];

        try {
            // --- START: The Fix ---
            // Replace all newlines and multiple spaces with a single space.
            // This helps the library to correctly analyze multi-line text.
            $textToAnalyze = preg_replace('/\s+/', ' ', trim($value));
            // --- END: The Fix ---

            $detector = new Language();

            // Use the cleaned text for detection
            $results = $detector->detect($textToAnalyze)->bestResults()->close();

            foreach ($results as $language => $confidence) {
                if (in_array($language, $allowedLanguages) && $confidence > 0.4) {
                    return; // Validation passes
                }
            }

            $fail('Текст должен быть на русском, английском или армянском языке.');

        } catch (\Exception $e) {
            $fail('Не удалось определить язык. Текст должен быть на русском, английском или армянском языке.');
        }
    }
}
