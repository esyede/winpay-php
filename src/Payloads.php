<?php

declare(strict_types=1);

namespace Esyede\Winpay;

use DateTime;
use DateTimeZone;
use DateInterval;

class Payloads
{
    private string $channel;
    private int $amount;
    private string $expiredTime;
    private string $suffix;
    private string $displayName;
    private string $callbackUrl;
    private string $description;
    private string $refNum;

    public function setChannel(string $channel): self
    {
        $this->channel = strtoupper($channel);
        return $this;
    }

    public function setAmount(int $amount): self
    {
        $this->amount = $amount;
        return $this;
    }

    public function setExpiredTime(int $hours): self
    {
        $hours = ($hours < 1) ? 1 : (($hours > 3) ? 3 : $hours);
        $minutes = $hours * 60;

        $date = (new DateTime('now', new DateTimeZone('Asia/Jakarta')))
            ->add(new DateInterval('PT' . $minutes . 'M'))
            ->format('Ymdhi');

        $this->expiredTime = $date;
        return $this;
    }

    public function setSuffix(string $suffix): self
    {
        $this->suffix = $suffix;
        return $this;
    }

    public function setDisplayName(string $displayName): self
    {
        $this->displayName = $displayName;
        return $this;
    }

    public function setCallbackUrl(string $callbackUrl): self
    {
        $this->callbackUrl = $callbackUrl;
        return $this;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function setRefNum(string $refNum): self
    {
        $this->refNum = $refNum;
        return $this;
    }

    public function toArray(): array
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

    public function getRefNum(): string
    {
        return $this->refNum;
    }
}