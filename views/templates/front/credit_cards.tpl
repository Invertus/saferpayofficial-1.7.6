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

{extends file='customer/page.tpl'}

{block name='page_title'}
        {l s='My credit cards' mod='saferpayofficial'}
{/block}

{block name='page_content'}
    <div class="card">
        <table class="table">
            <thead>
            <tr>
                <th>
                </th>
                <th>
                    {l s='Credit card' mod='saferpayofficial'}
                </th>
                <th>
                    {l s='Added date' mod='saferpayofficial'}
                </th>
                <th>
                    {l s='Valid till' mod='saferpayofficial'}
                </th>
                <th>
                    {l s='Card' mod='saferpayofficial'}
                </th>
                <th>
                    {l s='Action' mod='saferpayofficial'}
                </th>
            </tr>
            </thead>
            {foreach $rows as $row}
                {$row|cleanHtml nofilter}
            {/foreach}
        </table>
    </div>
{/block}
