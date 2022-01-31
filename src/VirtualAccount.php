<?php

declare(strict_types=1);

namespace Esyede\Winpay;

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
        // var_dump($environment);
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

        // var_dump($signature, $url, $payloads); die;

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS =>  $payloads,
            CURLOPT_HTTPHEADER => [
                'Authentication: Basic ' . $signature,
                'Content-Type: application/json',
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
        ]);

        $data = curl_exec($ch);
        $errno = curl_errno($ch);
        $error = curl_error($ch);

        $data = json_decode($data);

        if (! is_object($data)) {
            return json_encode([
                'success' => false,
                'message' => 'Json decode returns a non-object value',
                'data' => $data,
            ]);
        }

        if (json_last_error() !== JSON_ERROR_NONE) {
            return json_encode([
                'success' => false,
                'message' => 'Unable to parse json data',
                'data' => null,
            ]);
        }

        if ($data->rc !== '00' && strtolower($data->rd) !== 'success') {
            return json_encode([
                'success' => false,
                'message' => $data->rd,
                'data' => $data,
            ]);
        }

        return json_encode([
            'success' => true,
            'message' => $data->rd,
            'data' => $data,
        ]);
    }
}