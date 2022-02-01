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
    ->setDisplayName('MERCHANT NAME')
    ->setDescription('T1234567890')
    ->setCallbackUrl('https://mysite.com/calback/winpay-bsi');

$va = (new VirtualAccount())
    ->setApiKey('your_api_key')
    ->setSecretKey('your_secret_key')
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

