<?php

declare(strict_types=1);

namespace Esyede\Winpay\V3\Codes;

class Responses
{
    private array $responseCodes = [
        '00' => 'Success',
        '01' => 'Invalid authentication',
        '02' => 'User is not active yet',
        '10' => 'Channel is unavailable',
        '17' => 'Invalid identifier/phone number',
        '50' => 'Data not found',
        '51' => 'Payment can not be canceled, either already expired or paid',
        '69' => 'Cut-off time',
        '98' => 'System is under maintenance',
        '99' => 'General error',
    ];

    public function has(string $code): bool
    {
        return isset($this->$responseCodes[$code]);
    }

    public function get(string $code, string $default = 'Unknown'): string
    {
        return $this->has($code) ? $this->responseCodes[$code] : $default;
    }

    public function all(): array
    {
        return $this->responseCodes;
    }
}
