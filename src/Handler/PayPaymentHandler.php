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
use Webmozart\Assert\Assert;

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

    public function handle(PayPayment $payPayment): void
    {
        /** @var OrderInterface|null $order */
        $order = $this
            ->orderRepository
            ->findOneBy(['tokenValue' => $payPayment->orderToken(), 'customer' => $payPayment->shopUser()->getCustomer()]);
        Assert::notNull($order);

        $payment = $order->getPayments()->get($payPayment->paymentId());
        Assert::notNull($payPayment, 'Payment not found!');

        $paymentStateMachine = $this->stateMachineFactory->get($payment, 'sylius_payment');

        if ($paymentStateMachine->can('complete')) {
            $paymentStateMachine->apply('complete');
        } else {
            throw new \Exception('Payment cannot be completed!');
        }
    }
}