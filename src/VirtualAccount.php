<?php

declare(strict_types=1);

namespace Esyede\Winpay;

use stdClass;

class VirtualAccount
{
    private string $apiKey;
    private string $secretKey;
    private array $headers = [];
    private string $environment;
    private Payloads $payloads;

    // ----------------------------------------------------------------------------------
    // Setters
    // ----------------------------------------------------------------------------------

    public function setApiKey(string $apiKey): self
    {
        $this->apiKey = $apiKey;
        return $this;
    }

    public function setSecretKey(string $secretKey): self
    {
        $this->secretKey = $secretKey;
        return $this;
    }

    public function setEnvironment(string $environment = 'development'): self
    {
        if ($environment !== 'development' && $environment !== 'production') {
            throw new Exceptions\InvalidWinpayEnvironmentException(
                "Only 'production' and 'development' environment are supported."
            );
        }

        $environment = ($environment === 'production')
            ? 'https://merchant.winpay.id/api/v3'
            : 'https://to.winpay.id/api/v3';

        $this->environment = $environment;
        return $this;
    }

    public function setPayloads(Payloads $payloads)
    {
        $this->payloads = $payloads;
        return $this;
    }

    // ----------------------------------------------------------------------------------
    // Actual requests
    // ----------------------------------------------------------------------------------

    public function payOneOff(): string
    {
        return $this->send('payment/va/oneoff');
    }

    public function payRecurring(): string
    {
        return $this->send('payment/va/recurring');
    }

    public function checkStatus(string $refNum): string
    {
        $this->payloads = (new Payloads())->setRefNum($refNum);
        return $this->send('payment/status');
    }

    public function cancelPayment(string $refNum): string
    {
        $this->payloads = (new Payloads())->setRefNum($refNum);
        return $this->send('payment/cancel');
    }

    // ----------------------------------------------------------------------------------
    // Helper methods
    // ----------------------------------------------------------------------------------

    private function send(string $endpoint): string
    {
        $signature = base64_encode($this->apiKey . ':' . $this->secretKey);
        $url = $this->environment . '/' . ltrim($endpoint, '/');
        $payloads = $this->payloads->toArray();

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS =>  json_encode($payloads),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Basic ' . $signature,
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
        ]);

        $results = curl_exec($ch);
        $errno = curl_errno($ch);
        $error = curl_error($ch);

        $results = json_decode($results);

        if (! is_object($results)) {
            return json_encode([
                'success' => false,
                'message' => 'Json decode returns a non-object value',
                'results' => $results,
                'payloads' => $payloads,
                'url' => $url,
            ], JSON_PRETTY_PRINT);
        }

        if (json_last_error() !== JSON_ERROR_NONE) {
            // Mimic winpay errors for consistency
            $errors = new stdClass();
            $errors->errors = ['json' => 'Unable to parse json data'];

            return json_encode([
                'success' => false,
                'results' => $errors,
                'payloads' => $payloads,
                'url' => $url,
            ], JSON_PRETTY_PRINT);
        }

        if (isset($results->errors)) {
            return json_encode([
                'success' => false,
                'results' => $results,
                'payloads' => $payloads,
                'url' => $url,
            ], JSON_PRETTY_PRINT);
        }

        if ($results->rc !== '00' && strtolower($results->rd) !== 'success') {
            return json_encode([
                'success' => false,
                'results' => $results,
                'payloads' => $payloads,
                'url' => $url,
            ], JSON_PRETTY_PRINT);
        }

        return json_encode([
            'success' => true,
            'results' => $results,
            'payloads' => $payloads,
            'url' => $url,
        ], JSON_PRETTY_PRINT);
    }
}