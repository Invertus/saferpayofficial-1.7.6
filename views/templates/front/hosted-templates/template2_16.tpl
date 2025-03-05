{**
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
 *}

{include file="./partials/all_errors_16.tpl"}

<div class="container" id="main">

    <div class="form-group row">
        <label for="fields-holder-name" class="col-sm-4 col-form-label">{l s='Full Name' mod='saferpayofficial'}</label>
        <div class="col-sm-8">
            <input type="input" class="form-control" id="fields-holder-name"
                   placeholder="{l s='Full Name' mod='saferpayofficial'}" readonly>
        </div>
    </div>

    <div class="form-group row">
        <label for="fields-card-number"
               class="col-sm-4 col-form-label">{l s='Card Number' mod='saferpayofficial'}</label>
        <div class="col-sm-8">
            <input class="form-control" id="fields-card-number" placeholder="{l s='Card Number' mod='saferpayofficial'}"
                   readonly>
        </div>
    </div>

    <div class="form-group row">
        <label for="fields-expiration" class="col-sm-4 col-form-label">{l s='Expiration' mod='saferpayofficial'}</label>
        <div class="col-sm-8">
            <input class="form-control" id="fields-expiration" placeholder="{l s='Expiration' mod='saferpayofficial'}"
                   readonly>
        </div>
    </div>

    <div class="form-group row">
        <label for="fields-cvc" class="col-sm-4 col-form-label">{l s='Cvc' mod='saferpayofficial'}</label>
        <div class="col-sm-8">
            <input type="input" class="form-control" id="fields-cvc" placeholder="{l s='Cvc' mod='saferpayofficial'}"
                   readonly>
        </div>
    </div>
    <div class="row">
        <button class="offset-md-8 col-md-4 btn btn-primary" id="submit_hosted_field"
                disabled="disabled">{l s='Pay' mod='saferpayofficial'}</button>
    </div>
    <input class="form-control" id="token" readonly="" type="hidden"/>

    <input type="hidden" name="saferpay_selected_card" value="{$saferpay_selected_card|escape:'htmlall':'UTF-8'}" />
</div>
