<?php
/**
 * @author Peter Ukena <peter.ukena@brille24.de>
 */

declare(strict_types=1);

namespace Sylius\ShopApiPlugin\Command;

use Brille24\Entity\ShopUserInterface;

final class PayPayment
{
    /** @var ShopUserInterface */
    private $shopUser;

    /** @var string */
    private $orderToken;

    /** @var string */
    private $paymentId;

    public function __construct(
        ShopUserInterface $shopUser,
        string $orderToken,
        string $paymentId
    ) {
        $this->shopUser = $shopUser;
        $this->orderToken = $orderToken;
        $this->paymentId = $paymentId;
    }

    /** @return ShopUserInterface */
    public function shopUser(): ShopUserInterface
    {
        return $this->shopUser;
    }

    /** @return string */
    public function orderToken(): string
    {
        return $this->orderToken;
    }

    /** @return string */
    public function paymentId(): string
    {
        return $this->paymentId;
    }
}