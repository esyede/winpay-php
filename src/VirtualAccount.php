<?php

declare(strict_types=1);

namespace Esyede\Winpay;

use stdClass;

class VirtualAccount
{
    private $apiKey;
    private $secretKey;
    private $headers = [];
    private $environment;
    private $payloads;
    // ----------------------------------------------------------------------------------
    // Setters
    // ----------------------------------------------------------------------------------

    /**
     * Set api key.
     *
     * @param string $apiKey
     */
    public function setApiKey(string $apiKey): self
    {
        $this->apiKey = $apiKey;
        return $this;
    }

    /**
     * Set secret key.
     *
     * @param string $secretKey
     */
    public function setSecretKey(string $secretKey): self
    {
        $this->secretKey = $secretKey;
        return $this;
    }

    /**
     * Set environment (isi dengan 'development' atau 'production')
     *
     * @param string $environment
     */
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

    /**
     * Set payload yang akan dikirim.
     *
     * @param Payloads $payloads
     */
    public function setPayloads(Payloads $payloads)
    {
        $this->payloads = $payloads;
        return $this;
    }

    // ----------------------------------------------------------------------------------
    // Actual requests
    // ----------------------------------------------------------------------------------

    /**
     * Eksekusi pemnbayaran one-off (sekali bayar / closed payment).
     *
     * @return string
     */
    public function payOneOff(): string
    {
        return $this->send('payment/va/oneoff');
    }

    /**
     * Eksekusi pembayaran recurring (berkelanjutan / open payment)
     *
     * @return string
     */
    public function payRecurring(): string
    {
        return $this->send('payment/va/recurring');
    }

    /**
     * Cek status transaksi pembayaran.
     *
     * @param  string $refNum
     *
     * @return string
     */
    public function checkStatus(string $refNum): string
    {
        $payloads = (new Payloads())->setRefNum($refNum);
        $this->setPayloads($payloads);

        return $this->send('payment/status');
    }

    /**
     * Batalkan transaksi pembayaran.
     *
     * @param  string $refNum
     *
     * @return string
     */
    public function cancelPayment(string $refNum): string
    {
        $payloads = (new Payloads())->setRefNum($refNum);
        $this->setPayloads($payloads);

        return $this->send('payment/cancel');
    }

    // ----------------------------------------------------------------------------------
    // Helper methods
    // ----------------------------------------------------------------------------------

    /**
     * Helper method untuk mengirim request.
     *
     * @param  string $endpoint
     *
     * @return string
     */
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

        if ($errno) {
            $errors = new stdClass();
            $errors->errors = ['curl' => $error];
            return json_encode([
                'success' => false,
                'results' => $errors,
                'payloads' => $payloads,
                'url' => $url,
            ], JSON_PRETTY_PRINT);
        }

        $results = is_string($results) ? $results : strval($results);
        $results = json_decode($results);

        if (! is_object($results)) {
            $errors = new stdClass();
            $errors->errors = ['json' => 'Json decode returns a non-object value'];
            return json_encode([
                'success' => false,
                'results' => $errors,
                'payloads' => $payloads,
                'url' => $url,
            ], JSON_PRETTY_PRINT);
        }

        if (json_last_error() !== JSON_ERROR_NONE) {
            $errors = new stdClass();
            $errors->errors = ['json' => 'Unable to parse json data'];
            return json_encode([
                'success' => false,
                'results' => $errors,
                'payloads' => $payloads,
                'url' => $url,
            ], JSON_PRETTY_PRINT);
        }

        // Handle: payment errors
        if (isset($results->errors)) {
            return json_encode([
                'success' => false,
                'results' => $results,
                'payloads' => $payloads,
                'url' => $url,
            ], JSON_PRETTY_PRINT);
        }

        // Handle: Check payment status
        if (isset($results->ref_num) && isset($results->status)) {
            return json_encode([
                // Success wiil be TRUE only when the status is 'paid'
                'success' => (isset($results->status) && strtolower(strval($results->status)) === 'paid'),
                'results' => $results,
                'payloads' => $payloads,
                'url' => $url,
            ], JSON_PRETTY_PRINT);
        }

        // Handle: Transaction requests
        if (isset($results->rc) && isset($results->rd)) {
            return json_encode([
                'success' => ($results->rc === '00' && strtolower(strval($results->rd)) === 'sukses'),
                'results' => $results,
                'payloads' => $payloads,
                'url' => $url,
            ], JSON_PRETTY_PRINT);
        }

        // Handle: cancel payment (error)
        if (count(get_object_vars($results)) === 1 && isset($results->message)) {
            return json_encode([
                'success' => false,
                'results' => $results,
                'payloads' => $payloads,
                'url' => $url,
            ], JSON_PRETTY_PRINT);
        }

        // Handle: cancel payment success
        if (count(get_object_vars($results)) === 2
        && isset($results->ref_num)
        && isset($results->message)) {
            return json_encode([
                'success' => (false !== strpos(strtolower(strval($results->message)), 'successfully cancelled')),
                'results' => $results,
                'payloads' => $payloads,
                'url' => $url,
            ], JSON_PRETTY_PRINT);
        }

        return json_encode([
            'success' => false,
            'results' => $results,
            'payloads' => $payloads,
            'url' => $url,
        ], JSON_PRETTY_PRINT);
    }
}