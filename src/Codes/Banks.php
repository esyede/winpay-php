<?php

declare(strict_types=1);

namespace Esyede\Winpay\V3\Codes;

class Banks
{
    private array $lists = [
        'BSI',
    ];

    public function has(string $code): bool
    {
        return in_array($code, $this->$lists);
    }

    public function all(): array
    {
        return $this->lists;
    }
}
