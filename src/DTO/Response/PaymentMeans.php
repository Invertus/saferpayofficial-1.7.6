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

namespace Invertus\SaferPay\DTO\Response;

if (!defined('_PS_VERSION_')) {
    exit;
}

class PaymentMeans
{
    /**
     * @var Brand
     */
    private $brand;

    /**
     * @var string
     */
    private $displayText;

    /**
     * @var Card
     */
    private $card;
    /**
     * @var string
     */
    private $wallet;

    /**
     * PaymentMeans constructor.
     * @param Brand|null $brand
     * @param string $displayText
     * @param Card|null $card
     */
    public function __construct(Brand $brand = null, $displayText = null, Card $card = null, $wallet = null)
    {
        $this->brand = $brand;
        $this->displayText = $displayText;
        $this->card = $card;
        $this->wallet = $wallet;
    }

    /**
     * @return Brand
     */
    public function getBrand()
    {
        return $this->brand;
    }

    /**
     * @param Brand $brand
     */
    public function setBrand($brand)
    {
        $this->brand = $brand;
    }

    /**
     * @return mixed
     */
    public function getDisplayText()
    {
        return $this->displayText;
    }

    /**
     * @param mixed $displayText
     */
    public function setDisplayText($displayText)
    {
        $this->displayText = $displayText;
    }

    /**
     * @return Card
     */
    public function getCard()
    {
        return $this->card;
    }

    /**
     * @return string
     */
    public function getWallet()
    {
        return $this->wallet;
    }

    /**
     * @param Card $card
     */
    public function setCard($card)
    {
        $this->card = $card;
    }

    public function setWallet($wallet)
    {
        $this->wallet = $wallet;
    }
}
