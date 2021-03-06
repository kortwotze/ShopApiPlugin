<?php

declare(strict_types=1);

namespace Sylius\ShopApiPlugin\Request;

use Symfony\Component\HttpFoundation\Request;

final class EstimateShippingCostRequest
{
    /** @var string */
    private $cartToken;

    /** @var string */
    private $countryCode;

    /** @var string */
    private $provinceCode;

    public function __construct(Request $request)
    {
        $this->cartToken = $request->attributes->get('token');
        $this->countryCode = $request->query->get('countryCode');
        $this->provinceCode = $request->query->get('provinceCode');
    }

    public function cartToken(): string
    {
        return $this->cartToken;
    }

    public function countryCode(): string
    {
        return $this->countryCode;
    }

    public function provinceCode(): ?string
    {
        return $this->provinceCode;
    }
}
