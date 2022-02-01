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
    ->setSuffix('0')
    ->setDisplayName('NAMA MERCHANT')
    ->setDescription('T1234567890')
    ->setCallbackUrl('https://tripay.co.id/calback/winpay-bsi');

$va = (new VirtualAccount())
    ->setApiKey('4d0cba482565a4380286a886')
    ->setSecretKey('48fac6002005607b7ba79d210ef38d1c36b433cc')
    ->setEnvironment('development')
    ->setPayloads($payloads);

// One-off payment
// var_dump($va->payOneOff()); die;

// Recurring payment
// var_dump($va->payRecurring()); die;

// Check payment status
// $referenceNumber = 'sia788575c-8f4a-4dbf-a0e8-f32db5f21c2a'; // Reference numbers from winpay transaction
// var_dump($va->checkStatus($referenceNumber)); die;


// Cancel payment
$referenceNumber = 'sia788575c-8f4a-4dbf-a0e8-f32db5f21c2a'; // Reference numbers from winpay transaction
var_dump($va->cancelPayment($referenceNumber)); die;

