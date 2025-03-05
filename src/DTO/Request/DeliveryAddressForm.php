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

namespace Invertus\SaferPay\DTO\Request;

if (!defined('_PS_VERSION_')) {
    exit;
}

class DeliveryAddressForm
{
    const ADDRESS_SOURCE = 'NONE';
    const MANDATORY_FIELDS = [
        'CITY',
        'COMPANY',
        'COUNTRY',
        'EMAIL',
        'FIRSTNAME',
        'LASTNAME',
        'PHONE',
        'SALUTATION',
        'STATE',
        'STREET',
        'ZIP',
    ];

    private $addressSource;

    private $mandatoryFields;

    public function __construct($mandatoryFields, $addressSource)
    {
        $this->addressSource = $addressSource;
        $this->mandatoryFields = $mandatoryFields;
    }

    /**
     * @return mixed
     */
    public function getAddressSource()
    {
        return $this->addressSource;
    }


    /**
     * @param string $addressSource
     */
    public function setAddressSource($addressSource)
    {
        $this->addressSource = $addressSource;
    }

    /**
     * @return mixed
     */
    public function getMandatoryFields()
    {
        return $this->mandatoryFields;
    }

    /**
     * @param mixed $mandatoryFields
     */
    public function setMandatoryFields($mandatoryFields)
    {
        $this->mandatoryFields = $mandatoryFields;
    }
}
