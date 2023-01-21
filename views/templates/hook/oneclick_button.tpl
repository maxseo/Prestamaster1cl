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
{if $product.add_to_cart_url} 
    <button class="btn btn-primary oneClickButton" data-uniqid="{$uniqid|escape:'htmlall':'UTF-8'}" data-url="{$module_url}" data-id_product="{$id_product}" title="{l s='Buy now' mod='pmoneclick'}">
        {l s='Buy now' mod='pmoneclick'}
    </button>
{/if}
<div class="modal fade pmoneclick_modal" id="pmoneclick_modal_{$uniqid|escape:'htmlall':'UTF-8'}" tabindex="-1" role="dialog" aria-labelledby="pmoneclick_modal_{$uniqid|escape:'htmlall':'UTF-8'}" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
    <div class="modal-content">

    </div>
  </div>
</div>