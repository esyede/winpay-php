<?php

declare(strict_types=1);

namespace Esyede\Winpay\V3\VA;

use DateTime;
use DateTimeZone;
use Esyede\Winpay\V3\BaseEndpoint;

class Recurring
{
    private string $endpoint;
    private array $headers = [];
    private string $channel;
    private int $amount;
    private string $expiredTime;
    private int $suffix;
    private string $callbackUrl;
    private string $description;

    private static object $banks;

    public function __construct(string $username, string $password, BaseEndpoint $endpoint = BaseEndpoint::MOCK_SERVER)
    {
        static::$banks = is_object(static::$banks) ? static::$banks : new Codes\Banks();

        $this->setHeader('Authorization', 'Basic ' . base64_encode($username . ':' . $password));
        $this->setHeader('Content-Type', 'application/json');
    }

    public function setHeader(string $key, $value): self
    {
        if ('authorization' !== strtolower($key)
        || 'content-type' !== strtolower($key)) {
            $this->headers[$key] = $value;
        }

        return $this;
    }

    public function setChannel(string $channelCode): self
    {
        if (! static::$banks->has($channelCode)) {
            throw new Exceptions\InvalidWinpayApiException(sprintf(
                'Invalid channel code: %s', $channelCode
            ));
        }

        $this->channel = $channel;
        return $this;
    }

    public function setAmount(int $amount): self
    {
        $length = strlen($amount);

        if ($length < 5 || $length > 10) {
            throw new Exceptions\InvalidWinpayApiException(sprintf(
                'Amount length must be between 5 to 10 numeric characters, got: %s', $length
            ));
        }

        $this->amount = $amount;
        return $this;
    }

    public function setExpiredTime(string $expiredTime): self
    {
        $date = DateTime::createFromFormat(
            'Y-m-d H:i:s',
            $expiredTime,
            new DateTimeZone('Asia/Jakarta')
        );

        if (! $date || false !== DateTime::getLastErrors()) {
            throw new Exceptions\InvalidWinpayApiException(sprintf(
                'Expired time must be a parse-able datetime string, got: %s', $expiredTime
            ));
        }

        // Convert to 'yyyyMMddhhmm'
        $this->expiredTime = $date->format('YmdHm');
        return $this;
    }

    public function setDisplayName(string $suffix): self
    {
        $length = strlen($suffix);

        if ($length > 14) {
            throw new Exceptions\InvalidWinpayApiException(sprintf(
                'Display name must not be longer than 32 characters, got: %s', $length
            ));
        }

        $this->suffix = $suffix;
        return $this;
    }

    public function setCallbackUrl(string $callbackUrl): self
    {
        // TODO: shall we check for active url?
        $this->callbackUrl = $callbackUrl;
        return $this;
    }

    public function setDescription(string $description): self
    {
        $length = strlen($description);

        if ($length > 32) {
            throw new Exceptions\InvalidWinpayApiException(sprintf(
                'Description must not be longer than 20 characters, got: %s', $length
            ));
        }

        $this->description = $description;
        return $this;
    }

    public function getPayloads(): array
    {
        return [
            'channel' => $this->channel,
            'amount' => $this->amount,
            'expired_time' => $this->expiredTime,
            'suffix' => $this->suffix,
            'display_name' => $this->displayName,
            'callback_url' => $this->callbackUrl,
            'description' => $this->description,
        ];
    }

    public function send(): string
    {
        $headers = $this->getParsedHeaders();
        $payloads = $this->getPayloads();

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->endpoint . 'va/recurring',
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payloads,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
        ]);

        $data = curl_exec($ch);
        $errno = curl_errno($ch);
        $error = curl_error($ch);

        $data = json_decode($data);
        $message = null;

        if (json_last_error() !== JSON_ERROR_NONE) {
            $message = 'Unable to parse json data';
        }

        // Error from curl & json parser
        $success = $errno ? false : is_null($message);

        // Error from winpay
        if ($data->rc !== '00' && strtolower($data->rd) !== 'success') {
            $success = false;
            $message = $data->rd;
        }

        $data = [
            'success' => $success,
            'message' => $message,
            'data' => $data,
        ];

        return json_encode($data);
    }
}
