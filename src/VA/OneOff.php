<?php

declare(strict_types=1);

namespace Esyede\Winpay\V3\VA;

use DateTime;
use DateTimeZone;

class OneOff
{
    private string $channel;
    private int $amount;
    private string $expiredTime;
    private int $suffix;
    private string $callbackUrl;
    private string $description;

    // TODO: length validation
    public function setChannel(string $channelCode): self
    {
        $this->channel = $channel;
        return $this;
    }

    // TODO: length validation
    public function setAmount(int $amount): self
    {
        $this->amount = $amount;
        return $this;
    }

    // TODO: length validation
    public function setExpiredTime(string $expiredTime): self
    {
        $date = DateTime::createFromFormat(
            'Y-m-d H:i:s',
            $expiredTime,
            new DateTimeZone('Asia/Jakarta')
        );

        if (! $date || false !== DateTime::getLastErrors()) {
            throw new Exceptions\InvalidExpiredTimeException(
                sprintf('Invalid expired time: %s', $expiredTime)
            );
        }

        // Convert to 'yyyyMMddhhmm'
        $this->expiredTime = $date->format('YmdHm');
        return $this;
    }

    // TODO: length validation
    public function setDisplayName(string $suffix): self
    {
        $this->suffix = $suffix;
        return $this;
    }

    // TODO: length validation
    public function setCallbackUrl(string $callbackUrl): self
    {
        $this->callbackUrl = $callbackUrl;
        return $this;
    }

    // TODO: length validation
    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }
}
