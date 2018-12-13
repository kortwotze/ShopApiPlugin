<?php

declare(strict_types=1);

namespace Sylius\ShopApiPlugin\Mailer;

final class Emails
{
    const EMAIL_VERIFICATION_TOKEN = 'api_verification_token';
    const EMAIL_RESET_PASSWORD_TOKEN = 'api_reset_password_token';
    const EMAIL_PAY_PAYMENT_ERROR  = 'api_order_pay_payment_error';

    private function __construct()
    {
    }
}
