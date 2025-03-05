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

namespace Invertus\SaferPay\Service;

use Cart;
use Customer;
use Exception;
use Invertus\SaferPay\Adapter\LegacyContext;
use Invertus\SaferPay\Api\Enum\TransactionStatus;
use Invertus\SaferPay\Api\Request\CancelService;
use Invertus\SaferPay\Api\Request\CaptureService;
use Invertus\SaferPay\Api\Request\RefundService;
use Invertus\SaferPay\Config\SaferPayConfig;
use Invertus\SaferPay\DTO\Request\PendingNotification;
use Invertus\SaferPay\Enum\ControllerName;
use Invertus\SaferPay\Exception\Api\SaferPayApiException;
use Invertus\SaferPay\Factory\ModuleFactory;
use Invertus\SaferPay\Logger\LoggerInterface;
use Invertus\SaferPay\Repository\SaferPayOrderRepository;
use Invertus\SaferPay\Service\Request\CancelRequestObjectCreator;
use Invertus\SaferPay\Service\Request\CaptureRequestObjectCreator;
use Invertus\SaferPay\Service\Request\RefundRequestObjectCreator;
use Invertus\SaferPay\Utility\ExceptionUtility;
use Order;
use SaferPayAssert;
use SaferPayOfficial;
use SaferPayOrder;

if (!defined('_PS_VERSION_')) {
    exit;
}

class SaferPayOrderStatusService
{
    const FILE_NAME = 'SaferPayOrderStatusService';
    /**
     * @var CaptureService
     */
    private $captureService;
    /**
     * @var CaptureRequestObjectCreator
     */
    private $captureRequestObjectCreator;
    /**
     * @var SaferPayOrderRepository
     */
    private $orderRepository;
    /**
     * @var CancelService
     */
    private $cancelService;
    /**
     * @var CancelRequestObjectCreator
     */
    private $cancelRequestObjectCreator;
    /**
     * @var RefundService
     */
    private $refundService;
    /**
     * @var RefundRequestObjectCreator
     */
    private $refundRequestObjectCreator;

    /**
     * @var LegacyContext
     */
    private $context;

    /**
     * @var SaferPayOfficial
     */
    private $module;
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        CaptureService $captureService,
        CaptureRequestObjectCreator $captureRequestObjectCreator,
        SaferPayOrderRepository $orderRepository,
        CancelService $cancelService,
        CancelRequestObjectCreator $cancelRequestObjectCreator,
        RefundService $refundService,
        RefundRequestObjectCreator $refundRequestObjectCreator,
        LegacyContext $context,
        ModuleFactory $module,
        LoggerInterface $logger
    ) {
        $this->captureService = $captureService;
        $this->captureRequestObjectCreator = $captureRequestObjectCreator;
        $this->orderRepository = $orderRepository;
        $this->cancelService = $cancelService;
        $this->cancelRequestObjectCreator = $cancelRequestObjectCreator;
        $this->refundService = $refundService;
        $this->refundRequestObjectCreator = $refundRequestObjectCreator;
        $this->context = $context;
        $this->module = $module->getModule();
        $this->logger = $logger;
    }

    public function setPending(Order $order)
    {
        $saferPayOrder = $this->orderRepository->getByOrderId($order->id);
        $saferPayOrder->pending = 1;

        $saferPayOrder->update();
        $order->setCurrentState(_SAFERPAY_PAYMENT_PENDING_);
    }

    public function setComplete(Order $order)
    {
        $saferPayOrder = $this->orderRepository->getByOrderId($order->id);
        $saferPayOrder->captured = 1;

        $saferPayOrder->update();

        //NOTE: Older PS versions does not handle same state change, so we need to check if state is already set
        if ($order->getCurrentState() == _SAFERPAY_PAYMENT_COMPLETED_) {
            return;
        }

        $this->logger->debug('Order set completed (setComplete) SaferPayStatusService.php');

        $order->setCurrentState(_SAFERPAY_PAYMENT_COMPLETED_);
    }

    /** TODO extract capture api code to different service like Assert for readability */
    public function capture(Order $order, $refundedAmount = 0, $isRefund = false)
    {
        $saferPayOrderId = $this->orderRepository->getIdByOrderId($order->id);
        $saferPayOrder = new SaferPayOrder($saferPayOrderId);
        $cart = new Cart($order->id_cart);
        $transactionId = $saferPayOrder->transaction_id;
        $totalPrice = $order->total_paid_tax_incl * SaferPayConfig::AMOUNT_MULTIPLIER_FOR_API;
        $totalPrice = (int) (round($totalPrice));
        if ($isRefund) {
            $transactionId = $saferPayOrder->refund_id;
            $totalPrice = $refundedAmount;
        }

        $captureRequest = $this->captureRequestObjectCreator->create($cart, $transactionId, $totalPrice);

        try {
            $captureResponse = $this->captureService->capture($captureRequest);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage(), [
                'context' => [],
                'exceptions' => ExceptionUtility::getExceptions($e),
            ]);

            throw new SaferPayApiException('Capture API failed', SaferPayApiException::CAPTURE);
        }

        $assertId = $this->orderRepository->getAssertIdBySaferPayOrderId($saferPayOrder->id);
        $saferPayAssert = new SaferPayAssert($assertId);
        if ($isRefund) {
            $saferPayAssert->refunded_amount += $refundedAmount;
            $saferPayAssert->update();
            if ((int) $saferPayAssert->refunded_amount === (int) $saferPayAssert->amount) {
                $saferPayOrder->refunded = 1;
                $saferPayOrder->update();
                $order->setCurrentState(_SAFERPAY_PAYMENT_REFUND_);
                $order->update();
            } else {
                $order->setCurrentState(_SAFERPAY_PAYMENT_PARTLY_REFUND_);
                $order->update();
            }

            return;
        }

        if ((int) $order->getCurrentState() == (int) _SAFERPAY_PAYMENT_COMPLETED_ || (bool) $saferPayOrder->captured) {
            $this->logger->debug(sprintf('%s - saferPayAssert object set captured', self::FILE_NAME), [
                'context' => [
                    'orderId' => $order->id,
                ],
                'message' => 'order is already have captured state',
            ]);

            if (!$saferPayOrder->captured) {
                $saferPayOrder->captured = 1;
                $saferPayOrder->update();
                $saferPayAssert->status = $captureResponse->Status;
                $saferPayAssert->update();
            }

            return;
        }

        $this->logger->debug(sprintf('%s - saferPayAssert object set captured', self::FILE_NAME));

        $order->setCurrentState(_SAFERPAY_PAYMENT_COMPLETED_);
        $order->update();
        $saferPayOrder->captured = 1;
        $saferPayOrder->update();
        $saferPayAssert->status = $captureResponse->Status;
        $saferPayAssert->update();
    }

    public function cancel(Order $order)
    {
        $saferPayOrderId = $this->orderRepository->getIdByOrderId($order->id);
        $saferPayOrder = new SaferPayOrder($saferPayOrderId);
        $cancelRequest = $this->cancelRequestObjectCreator->create($saferPayOrder->transaction_id);
        try {
            $this->cancelService->cancel($cancelRequest);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage(), [
                'context' => [
                    'orderId' => $order->id,
                ],
                'exceptions' => ExceptionUtility::getExceptions($e),
            ]);

            throw new SaferPayApiException('Cancel API failed', SaferPayApiException::CANCEL);
        }
        $order->setCurrentState(_SAFERPAY_PAYMENT_CANCELED_);
        $order->update();
        $saferPayOrder->canceled = 1;
        $saferPayOrder->update();

        $assertId = $this->orderRepository->getAssertIdBySaferPayOrderId($saferPayOrder->id);
        $saferPayAssert = new SaferPayAssert($assertId);
        $saferPayAssert->status = TransactionStatus::CANCELED;
        $saferPayAssert->update();
    }

    public function refund(Order $order, $refundedAmount)
    {
        $saferPayOrderId = $this->orderRepository->getIdByOrderId($order->id);
        $saferPayOrder = new SaferPayOrder($saferPayOrderId);

        $assertId = $this->orderRepository->getAssertIdBySaferPayOrderId($saferPayOrder->id);
        $saferPayAssert = new SaferPayAssert($assertId);

        $refundAmount = $refundedAmount * SaferPayConfig::AMOUNT_MULTIPLIER_FOR_API;
        $refundAmount = (int) (round($refundAmount));

        $isRefundValid = ($saferPayAssert->amount >= $saferPayAssert->refunded_amount + $refundAmount);
        if (!$isRefundValid) {
            throw new SaferPayApiException(SaferPayApiException::REFUND);
        }

        $cart = new Cart($order->id_cart);
        $pendingNotification = null;
        if ($saferPayAssert->payment_method === SaferPayConfig::PAYMENT_WLCRYPTOPAYMENTS ||
            $saferPayAssert->payment_method === SaferPayConfig::PAYMENT_PAYDIREKT) {
            $pendingNotify = $this->context->getLink()->getModuleLink(
                $this->module->name,
                ControllerName::PENDING_NOTIFY,
                [
                    'success' => 1,
                    'cartId' => $cart->id,
                    'orderId' => Order::getOrderByCartId($cart->id),
                    'secureKey' => $cart->secure_key,
                ],
                true
            );
            $customer = new Customer($order->id_customer);
            $pendingNotification = new PendingNotification($pendingNotify, [$customer->email]);
        }
        $refundRequest = $this->refundRequestObjectCreator->create(
            $cart,
            $saferPayOrder->transaction_id,
            $refundAmount,
            $pendingNotification
        );

        try {
            $refundResponse = $this->refundService->refund($refundRequest);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage(), [
                'exceptions' => ExceptionUtility::getExceptions($e),
            ]);

            throw new SaferPayApiException('Refund API failed', SaferPayApiException::REFUND);
        }
        $saferPayOrder->refund_id = $refundResponse->Transaction->Id;
        $saferPayOrder->update();

        if ($refundResponse->Transaction->Status === TransactionStatus::AUTHORIZED) {
            $this->capture($order, $refundAmount, true);
        }

        if ($refundResponse->Transaction->Status === TransactionStatus::CAPTURED) {
            $saferPayAssert->refunded_amount += $refundAmount;
            $saferPayAssert->update();
            if ((int) $saferPayAssert->refunded_amount === (int) $saferPayAssert->amount) {
                $saferPayOrder->refunded = 1;
                $saferPayOrder->update();
                $order->setCurrentState(_SAFERPAY_PAYMENT_REFUND_);
                $order->update();
            }
        }

        if ($refundResponse->Transaction->Status === TransactionStatus::PENDING) {
            $saferPayAssert->pending_refund_amount += $refundAmount;
            $saferPayAssert->update();
            $orderState = $order->getCurrentState();
            if ($orderState !== _SAFERPAY_PAYMENT_PARTLY_REFUND_ && $orderState !== _SAFERPAY_PAYMENT_PENDING_REFUND_) {
                $order->setCurrentState(_SAFERPAY_PAYMENT_PENDING_REFUND_);
                $order->update();
            }
        }

        $saferpayOrderRefund = new \SaferPayOrderRefund();
        $saferpayOrderRefund->id_saferpay_order = $saferPayOrderId;
        $saferpayOrderRefund->id_order = $order->id;
        $saferpayOrderRefund->transaction_id = $refundResponse->Transaction->Id;
        $saferpayOrderRefund->status = $refundResponse->Transaction->Status;
        $saferpayOrderRefund->amount = $refundResponse->Transaction->Amount->Value;
        $saferpayOrderRefund->currency = $refundResponse->Transaction->Amount->CurrencyCode;

        $saferpayOrderRefund->add();
    }
}
