<?php
/**
 * @author Peter Ukena <peter.ukena@brille24.de>
 */

declare(strict_types=1);

namespace Sylius\ShopApiPlugin\Handler;

use SM\Factory\FactoryInterface as StateMachineFactory;
use spec\Sylius\InvoicingPlugin\EventProducer\OrderPaymentPaidProducerSpec;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\ShopApiPlugin\Command\PayPayment;
use Sylius\ShopApiPlugin\Exceptions\PaymentPaidException;
use Webmozart\Assert\Assert;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\OrderInterface;

final class PayPaymentHandler
{
    /** @var OrderRepositoryInterface  */
    private $orderRepository;

    /** @var StateMachineFactory */
    private $stateMachineFactory;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        StateMachineFactory $stateMachineFactory
    ) {
        $this->orderRepository = $orderRepository;
        $this->stateMachineFactory = $stateMachineFactory;
    }

    /**
     * @param PayPayment $payPayment
     *
     * @throws PaymentPaidException
     */
    public function handle(PayPayment $payPayment): void
    {
        /** @var OrderInterface|null $order */
        $order = $this
            ->orderRepository
            ->findOneBy(['tokenValue' => $payPayment->orderToken(), 'customer' => $payPayment->shopUser()->getCustomer()]);
        Assert::notNull($order);

        /** @var PaymentInterface $payment */
        $payment = $order->getPayments()->get($payPayment->paymentId());
        Assert::notNull($payPayment, 'Payment not found!');

        $paymentStateMachine = $this->stateMachineFactory->get($payment, 'sylius_payment');

        if ($paymentStateMachine->can('complete')) {
            try {
                $paymentStateMachine->apply('complete');
            } catch (\Throwable $throwable) {
                throw new PaymentPaidException(
                    $order,
                    $payment,
                    sprintf('State transition failed: %s', $throwable->getMessage()),
                    $throwable->getCode(),
                    $throwable
                );
            }
        } else {
            throw new PaymentPaidException($order, $payment, 'Payment could not be completed.');
        }
    }
}