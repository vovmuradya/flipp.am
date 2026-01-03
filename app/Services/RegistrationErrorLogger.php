<?php

namespace App\Services;

use App\Models\RegistrationError;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RegistrationErrorLogger
{
    /**
     * Логирует ошибку регистрации
     */
    public function logError(array $errorData): RegistrationError
    {
        $request = $errorData['request'] ?? request();
        $requestData = $this->sanitizeRequestData($request->all());

        return RegistrationError::create([
            'email' => $errorData['email'] ?? $request->input('email'),
            'username' => $errorData['username'] ?? $request->input('name') ?? $request->input('username'),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'error_type' => $errorData['type'] ?? 'unknown',
            'error_code' => $errorData['code'] ?? null,
            'error_message' => $errorData['message'],
            'request_data' => $requestData,
            'validation_errors' => $errorData['validation_errors'] ?? null,
            'occurred_at' => now(),
        ]);
    }

    /**
     * Логирует ошибку валидации при регистрации
     */
    public function logValidationError(Request $request, array $errors): RegistrationError
    {
        return $this->logError([
            'request' => $request,
            'type' => 'validation',
            'message' => 'Ошибка валидации данных регистрации',
            'validation_errors' => $errors,
        ]);
    }

    /**
     * Логирует техническую ошибку при регистрации
     */
    public function logTechnicalError(Request $request, string $message, ?string $code = null): RegistrationError
    {
        return $this->logError([
            'request' => $request,
            'type' => 'technical',
            'message' => $message,
            'code' => $code,
        ]);
    }

    /**
     * Логирует ошибку дублирования данных при регистрации
     */
    public function logDuplicateError(Request $request, string $field, string $value): RegistrationError
    {
        return $this->logError([
            'request' => $request,
            'type' => 'duplicate',
            'message' => "Поле {$field} уже используется другим пользователем",
            'email' => $field === 'email' ? $value : $request->input('email'),
        ]);
    }

    /**
     * Санитизирует данные запроса, удаляя чувствительную информацию
     */
    private function sanitizeRequestData(array $data): array
    {
        $sanitized = $data;
        
        // Удаляем чувствительные поля
        unset($sanitized['password']);
        unset($sanitized['password_confirmation']);
        unset($sanitized['_token']);
        unset($sanitized['_method']);
        
        // Маскируем email, если он есть
        if (isset($sanitized['email'])) {
            $email = $sanitized['email'];
            $parts = explode('@', $email);
            if (count($parts) === 2) {
                $localPart = $parts[0];
                $domain = $parts[1];
                
                // Маскируем часть локального адреса
                if (strlen($localPart) > 2) {
                    $maskedLocal = substr($localPart, 0, 1) . str_repeat('*', max(0, strlen($localPart) - 2)) . substr($localPart, -1);
                } else {
                    $maskedLocal = str_repeat('*', max(1, strlen($localPart)));
                }
                
                $sanitized['email'] = $maskedLocal . '@' . $domain;
            }
        }
        
        return $sanitized;
    }

    /**
     * Получает статистику по ошибкам регистрации
     */
    public function getStatistics(?string $startDate = null, ?string $endDate = null): array
    {
        $query = RegistrationError::query();
        
        if ($startDate && $endDate) {
            $query->whereBetween('occurred_at', [$startDate, $endDate]);
        }
        
        $totalErrors = $query->count();
        $resolvedErrors = $query->where('is_resolved', true)->count();
        $unresolvedErrors = $totalErrors - $resolvedErrors;
        
        // Статистика по типам ошибок
        $errorsByType = $query->selectRaw('error_type, COUNT(*) as count')
            ->groupBy('error_type')
            ->pluck('count', 'error_type');
        
        return [
            'total_errors' => $totalErrors,
            'resolved_errors' => $resolvedErrors,
            'unresolved_errors' => $unresolvedErrors,
            'errors_by_type' => $errorsByType,
        ];
    }

    /**
     * Отмечает ошибку как решенную
     */
    public function markAsResolved(int $errorId, int $resolverId, string $notes = ''): bool
    {
        $error = RegistrationError::find($errorId);
        
        if (!$error) {
            return false;
        }
        
        $error->update([
            'is_resolved' => true,
            'resolved_at' => now(),
            'resolved_by' => $resolverId,
            'resolution_notes' => $notes,
        ]);
        
        return true;
    }
}