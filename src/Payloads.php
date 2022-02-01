<?php

declare(strict_types=1);

namespace Esyede\Winpay;

use DateTime;
use DateTimeZone;
use DateInterval;

class Payloads
{
    private ?string $channel = null;
    private int $amount = 0;
    private ?string $expiredTime = null;
    private ?string $suffix = null;
    private ?string $displayName = null;
    private ?string $callbackUrl = null;
    private ?string $description = null;
    private ?string $refNum = null;

    /**
     * Set channel pembayaran (e.g: BSI)
     *
     * @param string $channel
     */
    public function setChannel(string $channel): self
    {
        $this->channel = strtoupper($channel);
        return $this;
    }

    /**
     * Set nominal pembayaran (e.g: 10000)
     * Panjang: 5 - 10 karakter
     *
     * @param int $amount
     */
    public function setAmount(int $amount): self
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * Set waktu kedaluwarsa invoice.
     * Dalam hitungan jam, min. 1, max. 3.
     *
     * @param int $hours
     */
    public function setExpiredTime(int $hours): self
    {
        $hours = ($hours < 1) ? 1 : (($hours > 3) ? 3 : $hours);
        $minutes = $hours * 60;

        $date = (new DateTime('now', new DateTimeZone('Asia/Jakarta')))
            ->add(new DateInterval('PT' . $minutes . 'M'))
            ->format('YmdHi');

        $this->expiredTime = $date;
        return $this;
    }

    /**
     * Set suffix: nilai yang dapat ditambahkan sebagai akhiran
     * dari nomor VA yang dihasilkan (tergantung kebijakan dari setiap bank)
     * Panjang maks: 14 karakter.
     *
     * @param string $suffix
     */
    public function setSuffix(string $suffix): self
    {
        $this->suffix = $suffix;
        return $this;
    }

    /**
     * Set nama VA yang akan ditampilkan ke user.
     * Panjang maks: 32 karakter.
     *
     * @param string $displayName
     */
    public function setDisplayName(string $displayName): self
    {
        $this->displayName = $displayName;
        return $this;
    }

    /**
     * Set URL callback.
     * Jika kosong maka akan memakai url yg terdaftar di dashboard.
     *
     * @param string $callbackUrl
     */
    public function setCallbackUrl(string $callbackUrl): self
    {
        $this->callbackUrl = $callbackUrl;
        return $this;
    }

    /**
     * Set deskripsi pembayaran.
     * Panjang maks: 20 karakter
     *
     * @param string $description
     */
    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Set target nomor referensi pembayaran
     * Method ini hanya perlu dipanggil ketika melakukan:
     *
     * Cek status pembayaran yaitu sebelum memanggil method checkStatus(), atau
     * Membatakan pembayaran yaitu sebelum memanggil method cancelPayment()
     *
     * @param string $refNum
     */
    public function setRefNum(string $refNum): self
    {
        $this->refNum = $refNum;
        return $this;
    }

    /**
     * Ambil payload dalam bentuk array.
     *
     * @return array
     */
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
            'ref_num' => $this->refNum,
        ];
    }

    /**
     * Ambil reference number (untuk debugging)
     *
     * @return string
     */
    public function getRefNum(): string
    {
        return $this->refNum;
    }
}