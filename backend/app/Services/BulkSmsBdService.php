<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BulkSmsBdService
{
    private string $apiKey;
    private string $senderId;
    private string $baseUrl;

    public function __construct()
    {
        $this->apiKey   = (string) config('bulksmsbd.api_key', '');
        $this->senderId = (string) config('bulksmsbd.sender_id', '');
        $this->baseUrl  = rtrim((string) config('bulksmsbd.base_url', 'https://bulksmsbd.net/api'), '/');
    }

    public function send(string $number, string $message): array
    {
        $number = $this->formatNumber($number);
        try {
            $response = Http::timeout(15)->get("{$this->baseUrl}/smsapi", [
                'api_key'  => $this->apiKey,
                'type'     => 'text',
                'number'   => $number,
                'senderid' => $this->senderId,
                'message'  => $this->appendFooter($message),
            ]);
            $success = $this->isApiSuccess($response->body());
            Log::info('BulkSmsBd send', ['number' => $number, 'success' => $success, 'response' => $response->body()]);
            return ['success' => $success, 'response' => $response->body()];
        } catch (\Throwable $e) {
            Log::error('BulkSmsBd send failed', ['number' => $number, 'error' => $e->getMessage()]);
            return ['success' => false, 'response' => $e->getMessage()];
        }
    }

    /** One message broadcast to many numbers (comma-separated in a single request). */
    public function sendMany(array $numbers, string $message): array
    {
        $formatted = array_map([$this, 'formatNumber'], $numbers);
        $joined    = implode(',', $formatted);
        try {
            $response = Http::timeout(30)->get("{$this->baseUrl}/smsapi", [
                'api_key'  => $this->apiKey,
                'type'     => 'text',
                'number'   => $joined,
                'senderid' => $this->senderId,
                'message'  => $this->appendFooter($message),
            ]);
            $success = $this->isApiSuccess($response->body());
            Log::info('BulkSmsBd sendMany', ['count' => count($formatted), 'success' => $success]);
            return ['success' => $success, 'response' => $response->body()];
        } catch (\Throwable $e) {
            Log::error('BulkSmsBd sendMany failed', ['count' => count($numbers), 'error' => $e->getMessage()]);
            return ['success' => false, 'response' => $e->getMessage()];
        }
    }

    /**
     * Send a different personalised message to each recipient.
     * $payload = [['number' => '017...', 'message' => '...'], ...]
     */
    public function sendBulkDifferent(array $payload): array
    {
        $messages = array_map(fn($item) => [
            'to'      => $this->formatNumber((string) $item['number']),
            'message' => $this->appendFooter((string) $item['message']),
        ], $payload);

        try {
            $response = Http::timeout(30)->asForm()->post("{$this->baseUrl}/smsapimany", [
                'api_key'  => $this->apiKey,
                'senderid' => $this->senderId,
                'messages' => json_encode($messages),
            ]);
            $success = $this->isApiSuccess($response->body());
            Log::info('BulkSmsBd sendBulkDifferent', ['count' => count($messages), 'success' => $success]);
            return ['success' => $success, 'response' => $response->body()];
        } catch (\Throwable $e) {
            Log::error('BulkSmsBd sendBulkDifferent failed', ['count' => count($payload), 'error' => $e->getMessage()]);
            return ['success' => false, 'response' => $e->getMessage()];
        }
    }

    /** Chunk a large number list and broadcast via sendMany() to avoid oversized requests. */
    public function broadcast(array $numbers, string $message, int $chunkSize = 300): array
    {
        $results = [];
        foreach (array_chunk($numbers, $chunkSize) as $chunk) {
            $results[] = $this->sendMany($chunk, $message);
        }
        $allSuccess = !empty($results) && collect($results)->every(fn($r) => $r['success']);
        return ['success' => $allSuccess, 'response' => $results];
    }

    public function getBalance(): array
    {
        try {
            $response = Http::timeout(10)->get("{$this->baseUrl}/getBalanceApi", [
                'api_key' => $this->apiKey,
            ]);
            $success = $response->successful(); // balance API returns plain text, not JSON
            Log::info('BulkSmsBd getBalance', ['success' => $success, 'response' => $response->body()]);
            return ['success' => $success, 'response' => $response->body()];
        } catch (\Throwable $e) {
            Log::error('BulkSmsBd getBalance failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'response' => $e->getMessage()];
        }
    }

    private function appendFooter(string $message): string
    {
        return $message . "\n\nRegards,\nApex Tutor Team";
    }

    // BulkSMSBD always returns HTTP 200; actual success is response_code === 202
    private function isApiSuccess(string $body): bool
    {
        $decoded = json_decode($body, true);
        return isset($decoded['response_code']) && (int) $decoded['response_code'] === 202;
    }

    // Normalises 017xxxxxxxx / +88017xxxxxxxx / 88017xxxxxxxx → 88017xxxxxxxx
    private function formatNumber(string $number): string
    {
        $number = preg_replace('/\D/', '', $number);
        if (str_starts_with($number, '880')) {
            return $number;
        }
        if (str_starts_with($number, '0')) {
            return '88' . $number;
        }
        return '880' . $number;
    }
}
