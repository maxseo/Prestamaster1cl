{**
 * 2012 - 2020 Prestashop Master
 *
 * MODULE Buy in 1-click
 *
 * @author    Prestashop Master <dev@prestashopmaster.com>
 * @copyright Copyright (c) permanent, Prestashop Master
 * @license   https://opensource.org/licenses/GPL-3.0  GNU General Public License version 3
 * @version   1.0.0
 * @link      https://www.prestashopmaster.com
 *
 * NOTICE OF LICENSE
 *
 * Don't use this module on several shops. The license provided by PrestaShop Master
 * for all its modules is valid only once for a single shop.
 *}
<button type="button" class="close" data-dismiss="modal" aria-label="Close">
    <i class="material-icons">close</i>
</button>
<div class="pm_one_click_popup_content">
    <div class="pm_one_click_popup_content_wrapper">
        <div class="oneclickPopup__title">
            {$product_o->name} 
        </div>
        <div class="oneclickPopup__attr">
            {foreach from=$combinations item=combination}
                {if $id_product_attribute == $combination.id_product_attribute}<input type="hidden" name="id_product_attribute" value="{$combination.id_product_attribute}">{/if}
                {if $id_product_attribute == $combination.id_product_attribute}{$combination.attributes|escape:'htmlall':'UTF-8'} - {$combination.price_tax_incl|escape:'htmlall':'UTF-8'} {$currency->sign|escape:'htmlall':'UTF-8'}{/if}
                <div class="break"></div>
            {/foreach}
        </div>
        
        {if !empty($link->getImageLink($product_o->link_rewrite, (int)$comb_image['id_image'], 'large_default'))}
        <img
          class="img-fluid"
          src="{$link->getImageLink($product_o->link_rewrite, (int)$comb_image['id_image'], 'large_default')}"
        >
        {/if}
        
        <div class="oneclickPopup__form">
            {if $combinations && $page.page_name == 'category'}
                <div class="form__row">
                    <select name="id_product_attribute" class="dropdown__caption">
                        {foreach from=$combinations item=combination}
                            <option {if $id_product_attribute == $combination.id_product_attribute}selected{/if} value="{$combination.id_product_attribute}">{$combination.attributes|escape:'htmlall':'UTF-8'} - {$combination.price_tax_incl|escape:'htmlall':'UTF-8'} {$currency->sign|escape:'htmlall':'UTF-8'}</option>
                        {/foreach}
                    </select>
                </div>
            {/if}
            {if !isset($customer->email)}
                <div class="form__row">
                    <div class="input"><input class="pm_oneclick_email" name="pm_oneclick_email" placeholder="{l s='E-mail' mod='pmoneclick'}" value="{$customer->email|escape:'htmlall':'UTF-8'}" type="text"></div>
                </div>
            {/if}
            {if $oneclick_settings->name == 1}
                <div class="form__row">
                    <div class="input"><input class="pm_oneclick_name" name="pm_oneclick_name" placeholder="{l s='Name' mod='pmoneclick'}" value="{$customer->firstname|escape:'htmlall':'UTF-8'}" type="text"></div>
                </div>
            {/if}
            {if $oneclick_settings->phone == 1}
                
                <div class="form__row">
                    <div class="input">
                        <input class="pm_oneclick_country" name="pm_oneclick_country" value="{$default_country.id_country}" type="hidden">
                        <div class="pmoc-dropdown-wrapper">
                            <div class="pmoc-dropdown-container">
                                <div class="{if $countries|@count > 1}pmoc-dropdown-header{else}pmoc-single{/if}">
                                    {if $countries|@count > 1}
                                        <span class="material-icons pmoc-open-dropdown">
                                     {/if}
                                    {foreach from = $countries item=country}
                                        {if $country.id_country == $default_country.id_country}
                                            <span class="iconify"  data-icon="cif:{$default_country.iso_code|lower}" data-inline="false"></span>
                                        {/if}
                                    {/foreach}
                                    {if $countries|@count > 1}
                                    arrow_drop_down</span>
                                    {/if}
                                </div>
                                <ul class="pmoc-dropdown-dropdown">
                                    {foreach from = $countries item=country}
                                        <li data-id_country="{$country.id_country}" data-iso="{$country.iso_code}" data-call_prefix="+{$country.call_prefix}">
                                            <div class="flag-wrapper">
                                                <span class="iconify" data-icon="cif:{$country.iso_code|lower}" data-inline="true" data-width="25"></span>
                                            </div>
                                            <span>{$country.name}</span><i>+{$country.call_prefix}</i>
                                        </li>
                                    {/foreach}
                                </ul>
                            </div>
                        </div>
                        {*<select name="id_country" class="country_select">
                            {foreach from = $countries item=country}
                                <option data-call_prefix="+{$country.call_prefix}" data-imagesrc="https://demo.prestashopmaster.com/img/{$country.id_country}.png" value="{$country.id_country}">+{$country.call_prefix}</option>
                            {/foreach}
                        </select>*}
                       
                        <input class="pm_oneclick_phone" name="pm_oneclick_phone" placeholder="{l s='Phone' mod='pmoneclick'}" value="" type="text">
                    </div>
                </div>
            {/if}
            {if $oneclick_settings->promo_code == 1}
                <div class="form__row">
                    <div class="input"><input class="pm_oneclick_promo_code" name="pm_oneclick_promo_code" placeholder="{l s='Promo code' mod='pmoneclick'}" value="" type="text"></div>
                </div>
            {/if}
            {if $customer->id > 0}
                <input type="hidden" name="id_customer" value="{$customer->id|intval}">
            {/if}
            <input type="hidden" name="id_product" value="{$product_o->id|intval}">
            
            <div>
                <button class="btn send_one_click" type="button" >{l s='Confirm' mod='pmoneclick'}</button>
            </div>
            <input type="hidden" name="contr_url" value="{$module_url|escape:'htmlall':'UTF-8'}" />
            <input type="hidden" class="pm_one_click_call_prefix" name="call_prefix" value="+{$default_prefix|escape:'htmlall':'UTF-8'}" />
            <input type="hidden" class="pm_one_click_phone_mask" name="phone_mask" value="{$oneclick_settings->phone_mask|escape:'htmlall':'UTF-8'}" />
        </div>
    </div>
</div>