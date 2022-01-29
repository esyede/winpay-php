<?php

declare(strict_types=1);

namespace Esyede\Winpay\V3\Reports;

class BaseEndpoint
{
    const MOCK_SERVER = 'https://private-anon-09a5006298-winpayapiv3.apiary-mock.com/api/v3/payment/';
    const DEBUGGING_PROXY = 'https://private-anon-09a5006298-winpayapiv3.apiary-proxy.com/api/v3/payment/';
    const PRODUCTION = 'https://to.winpay.id/api/v3/payment/';
}
