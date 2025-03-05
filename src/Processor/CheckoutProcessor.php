<?php
/**
 *NOTICE OF LICENSE
 *
 *This source file is subject to the Open Software License (OSL 3.0)
 *that is bundled with this package in the file LICENSE.txt.
 *It is also available through the world-wide-web at this URL:
 *http://opensource.org/licenses/osl-3.0.php
 *If you did not receive a copy of the license and are unable to
 *obtain it through the world-wide-web, please send an email
 *to license@prestashop.com so we can send you a copy immediately.
 *
 *DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 *versions in the future. If you wish to customize PrestaShop for your
 *needs please refer to http://www.prestashop.com for more information.
 *
 *@author INVERTUS UAB www.invertus.eu  <support@invertus.eu>
 *@copyright SIX Payment Services
 *@license   SIX Payment Services
 */

namespace Invertus\SaferPay\Processor;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Cart;
use Invertus\SaferPay\Api\Enum\TransactionStatus;
use Invertus\SaferPay\Config\SaferPayConfig;
use Invertus\SaferPay\Core\Payment\DTO\CheckoutData;
use Invertus\SaferPay\EntityBuilder\SaferPayOrderBuilder;
use Invertus\SaferPay\Exception\Api\SaferPayApiException;
use Invertus\SaferPay\Exception\CouldNotProcessCheckout;
use Invertus\SaferPay\Factory\ModuleFactory;
use Invertus\SaferPay\Logger\LoggerInterface;
use Invertus\SaferPay\Repository\SaferPayOrderRepository;
use Invertus\SaferPay\Service\SaferPayInitialize;
use Invertus\SaferPay\Utility\ExceptionUtility;
use Order;
use PrestaShopException;
use SaferPayOrder;

class CheckoutProcessor
{
    const FILE_NAME = 'CheckoutProcessor';

    /** @var \SaferPayOfficial */
    private $module;

    /** @var SaferPayOrderBuilder */
    private $saferPayOrderBuilder;

    /** @var SaferPayInitialize */
    private $saferPayInitialize;

    /** @var SaferPayOrderRepository */
    private $saferPayOrderRepository;

    public function __construct(
        ModuleFactory $module,
        SaferPayOrderBuilder $saferPayOrderBuilder,
        SaferPayInitialize $saferPayInitialize,
        SaferPayOrderRepository $saferPayOrderRepository
    ) {
        $this->module = $module->getModule();
        $this->saferPayOrderBuilder = $saferPayOrderBuilder;
        $this->saferPayInitialize = $saferPayInitialize;
        $this->saferPayOrderRepository = $saferPayOrderRepository;
    }

    public function run(CheckoutData $data)
    {
        $cart = new Cart($data->getCartId());

        /** @var LoggerInterface $logger */
        $logger = $this->module->getService(LoggerInterface::class);

        if (!$cart) {
            $logger->debug(sprintf('%s - Cart not found', self::FILE_NAME), [
                'context' => [
                    'cartId' => $data->getCartId(),
                ],
            ]);

            throw CouldNotProcessCheckout::failedToFindCart($data->getCartId());
        }

        if (!$data->getCreateAfterAuthorization()) {
            $this->processCreateOrder($cart, $data->getPaymentMethod());
        }

        $authorizedStates = [
            TransactionStatus::AUTHORIZED,
            TransactionStatus::CAPTURED,
        ];

        if (in_array($data->getOrderStatus(), $authorizedStates)) {
            $this->processAuthorizedOrder($data, $cart);
            return '';
        }

        try {
            $response = $this->processInitializePayment(
                $data->getPaymentMethod(),
                $data->getIsBusinessLicense(),
                $data->getSelectedCard(),
                $data->getFieldToken(),
                $data->getSuccessController(),
                $data->getIsWebhook()
            );
        } catch (\Exception $exception) {
            throw new SaferPayApiException('Failed to initialize payment API', SaferPayApiException::INITIALIZE);
        }

        try {
            $this->processCreateSaferPayOrder(
                $response,
                $cart->id,
                $cart->id_customer,
                $data->getIsTransaction()
            );
        } catch (\Exception $exception) {
            $logger->error($exception->getMessage(), [
                'context' => [
                    'cartId' => $data->getCartId(),
                ],
                'exceptions' => ExceptionUtility::getExceptions($exception)
            ]);

            throw CouldNotProcessCheckout::failedToCreateSaferPayOrder($data->getCartId());
        }

        return $response;
    }

    /**
     * @param Cart $cart
     * @param $paymentMethod
     * @return void
     * @throws PrestaShopException
     */
    private function processCreateOrder(Cart $cart, $paymentMethod)
    {
        /** @var \Invertus\SaferPay\Adapter\Cart $cartAdapter */
        $cartAdapter = $this->module->getService(\Invertus\SaferPay\Adapter\Cart::class);

        /** @var LoggerInterface $logger */
        $logger = $this->module->getService(LoggerInterface::class);

        // Notify and return webhooks triggers together leading into order created previously
        if ($cartAdapter->orderExists($cart->id)) {
            $logger->debug(sprintf('%s - Order already exists, returning', self::FILE_NAME), [
                'context' => [
                    'cartId' => $cart->id,
                ],
            ]);

            return;
        }

        $customer = new \Customer($cart->id_customer);

        $logger->debug(sprintf('%s - Creating order', self::FILE_NAME), [
            'context' => [
                'cartId' => $cart->id,
            ],
        ]);

        $this->module->validateOrder(
            $cart->id,
            \Configuration::get(SaferPayConfig::SAFERPAY_ORDER_STATE_CHOICE_AWAITING_PAYMENT),
            (float) $cart->getOrderTotal(),
            $paymentMethod,
            null,
            [],
            null,
            false,
            $customer->secure_key
        );
    }

    /**
     * @param $paymentMethod
     * @param $isBusinessLicense
     * @param $selectedCard
     * @param $fieldToken
     * @param $successController
     * @return array|null
     */
    private function processInitializePayment(
        $paymentMethod,
        $isBusinessLicense,
        $selectedCard,
        $fieldToken,
        $successController,
        $isWebhook
    ) {
        $request = $this->saferPayInitialize->buildRequest(
            $paymentMethod,
            $isBusinessLicense,
            $selectedCard,
            $fieldToken,
            $successController,
            $isWebhook
        );

        return $this->saferPayInitialize->initialize($request, $isBusinessLicense);
    }

    /**
     * @param $initializeBody
     * @param $cartId
     * @param $customerId
     * @param $isTransaction
     * @return void
     */
    private function processCreateSaferPayOrder($initializeBody, $cartId, $customerId, $isTransaction)
    {
        $this->saferPayOrderBuilder->create(
            $initializeBody,
            $cartId,
            $customerId,
            $isTransaction
        );
    }

    private function processAuthorizedOrder(CheckoutData $data, Cart $cart)
    {
        /** @var LoggerInterface $logger */
        $logger = $this->module->getService(LoggerInterface::class);
        $logger->debug(sprintf('%s - Processing authorized order', self::FILE_NAME), [
            'context' => [
                'id_order' => $this->getOrder($cart->id)->id,
            ],
        ]);

        try {
            $this->processCreateOrder($cart, $data->getPaymentMethod());
            $order = $this->getOrder($cart->id);
            $saferPayOrder = new SaferPayOrder($this->saferPayOrderRepository->getIdByCartId($cart->id));

            if (
                $order->getCurrentState() == _SAFERPAY_PAYMENT_AUTHORIZED_
                || $order->getCurrentState() == _SAFERPAY_PAYMENT_COMPLETED_
            ) {
                return;
            }

            if ($data->getOrderStatus() === TransactionStatus::AUTHORIZED) {
                if ($order->getCurrentState() == (int) _SAFERPAY_PAYMENT_AUTHORIZED_) {
                    return;
                }

                $saferPayOrder->authorized = true;
                $data->setIsAuthorizedOrder(true);
                $order->setCurrentState(_SAFERPAY_PAYMENT_AUTHORIZED_);
            } else {
                if ($order->getCurrentState() == _SAFERPAY_PAYMENT_COMPLETED_) {
                    return;
                }

                $saferPayOrder->captured = true;
                $logger->debug('Order set completed CheckoutProcessor.php');
                $order->setCurrentState(_SAFERPAY_PAYMENT_COMPLETED_);
            }

            $saferPayOrder->id_order = $order->id;
            $saferPayOrder->update();
        } catch (\Exception $exception) {
            /** @var LoggerInterface $logger */
            $logger = $this->module->getService(LoggerInterface::class);
            $logger->error($exception->getMessage(), [
                'context' => [],
                'cartId' => $data->getCartId(),
            ]);

            throw CouldNotProcessCheckout::failedToCreateOrder($data->getCartId());
        }
    }

    /**
     * @param int $cartId
     *
     * @return Order
     */
    private function getOrder($cartId)
    {
        if (method_exists('Order', 'getIdByCartId')) {
            return new Order(Order::getIdByCartId($cartId));
        }
        // For PrestaShop 1.6 use the alternative method
        return new Order(Order::getOrderByCartId($cartId));
    }
}
