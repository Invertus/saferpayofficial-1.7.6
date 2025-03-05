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

namespace Invertus\SaferPay\Exception;

if (!defined('_PS_VERSION_')) {
    exit;
}

// NOTE class to define most used exception codes for our development.
class ExceptionCode
{
    // Payment related codes starts from 5***
    const PAYMENT_FAILED_TO_FIND_CART = 5001;
    const PAYMENT_FAILED_TO_CREATE_ORDER = 5002;
    const CANNOT_USE_CARD = 5003;

    // Order related codes starts from 7***
    const ORDER_FAILED_TO_FIND_ORDER = 7001;
    const ORDER_UNHANDLED_TRANSACTION_STATUS = 7002;

    // Any other unhandled codes should start with 9***
    const UNKNOWN_ERROR = 9001;
}
