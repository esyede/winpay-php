<?php

require_once '../src/Payloads.php';
require_once '../src/VirtualAccount.php';
require_once '../src/Exceptions/InvalidWinpayEnvironmentException.php';

use Esyede\Winpay\Payloads;
use Esyede\Winpay\Environment;
use Esyede\Winpay\VirtualAccount;

$payloads = (new Payloads())
    ->setChannel('BSI')
    ->setAmount(10000)
    ->setExpiredTime(3)
    ->setSuffix(0)
    ->setDisplayName('PAY - BSI')
    ->setDescription('Pembayaran Kaos Oblong')
    ->setCallbackUrl('https://tripay.co.id/calback/winpay-bsi');

$va = (new VirtualAccount())
    ->setApiKey('4d0cba482565a4380286a886')
    ->setSecretKey('48fac6002005607b7ba79d210ef38d1c36b433cc')
    ->setEnvironment('development')
    ->setPayloads($payloads);

// One-off payment
var_dump($va->payOneOff()); die;

// Recurring payment
// var_dump($va->payRecurring()); die;

// Check payment status
// $referenceNumber = 'T1234567890'; // Reference numbers from winpay transaction
// var_dump($va->checkPaymentStatus($referenceNumber)); die;

