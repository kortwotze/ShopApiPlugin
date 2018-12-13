<?php
/**
 * @author Peter Ukena <peter.ukena@brille24.de>
 */
declare(strict_types=1);

namespace Sylius\ShopApiPlugin\Controller\Checkout;

use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandlerInterface;
use League\Tactician\CommandBus;
use spec\Sylius\Bundle\ResourceBundle\Controller\ViewHandlerSpec;
use Sylius\Component\Mailer\Sender\SenderInterface;
use Sylius\ShopApiPlugin\Exceptions\PaymentPaidException;
use Sylius\ShopApiPlugin\Mailer\Emails;
use Sylius\ShopApiPlugin\Provider\LoggedInUserProviderInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sylius\ShopApiPlugin\Command\PayPayment;

final class PayPaymentAction
{
    /** @var ViewHandlerInterface */
    private $viewHandler;

    /** @var CommandBus */
    private $bus;

    /** @var LoggedInUserProviderInterface */
    private $loggedInUserProvider;

    /** @var SenderInterface */
    private $sender;

    /**
     * @param ViewHandlerInterface              $viewHandler
     * @param CommandBus                        $bus
     * @param LoggedInUserProviderInterface     $tokenStorage
     * @param SenderInterface                   $sender
     */
    public function __construct(
        ViewHandlerInterface $viewHandler,
        CommandBus $bus,
        LoggedInUserProviderInterface $loggedInUserProvider,
        SenderInterface $sender
    ) {
        $this->viewHandler          = $viewHandler;
        $this->bus                  = $bus;
        $this->loggedInUserProvider = $loggedInUserProvider;
        $this->sender               = $sender;
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function __invoke(Request $request): Response
    {
        try {
            /** @var ShopUserInterface $user */
            $user = $this->loggedInUserProvider->provide();
        } catch (TokenNotFoundException $exception) {
            return $this->viewHandler->handle(View::create(null, Response::HTTP_UNAUTHORIZED));
        }

        try {
            $this->bus->handle(
                new PayPayment(
                    $user,
                    $request->attributes->get('token'),
                    $request->attributes->get('paymentId')
                )
            );
        } catch (PaymentPaidException $paymentPaidException) {
            $this->sender->send(
                Emails::EMAIL_PAY_PAYMENT_ERROR,
                'developer@brille24.de',
                ['order' => $paymentPaidException->getOrder(), 'payment' => $paymentPaidException->getPayment()]
            );
        }

        return $this->viewHandler->handle(View::create(null, Response::HTTP_NO_CONTENT));
    }
}