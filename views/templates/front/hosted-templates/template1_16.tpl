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
    <div class="form-group">
        <input class="form-control" id="fields-holder-name" readonly placeholder="{l s='Holder name' mod='saferpayofficial'}">
    </div>

    <div class="form-group row">
        <div class="col-sm-12">
            <input class="form-control" id="fields-card-number" readonly placeholder="{l s='0000 0000 0000 0000' mod='saferpayofficial'}">
        </div>
    </div>

    <div class="row">
        <div class="col-sm-6 col-xs-12 form-group">
            <input class="form-control" id="fields-expiration" readonly placeholder="{l s='MM/YYYY' mod='saferpayofficial'}">
        </div>
        <div class="col-sm-6 col-xs-12 form-group">
            <input class="form-control" id="fields-cvc" readonly placeholder="{l s='000' mod='saferpayofficial'}">
        </div>
    </div>

    <button class="col-md-4 btn btn-primary" id="submit_hosted_field" disabled="disabled">{l s='Pay' mod='saferpayofficial'}</button>

    <input class="form-control" id="token" readonly="" type="hidden" />

    <input type="hidden" name="saferpay_selected_card" value="{$saferpay_selected_card|escape:'htmlall':'UTF-8'}" />
</div>
