/**
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
 */
 
var pm_oneclick = {
    
    initialize: function(){
        
        $(document).off('click', '.oneClickButton');
        $(document).on('click', '.oneClickButton', pm_oneclick.getOneclickPopup);
        
        $(document).off('click', '.send_one_click');
        $(document).on('click', '.send_one_click', pm_oneclick.oneclickSave);
        
        $(document).off('change', '.country_select');
        $(document).on('change', '.country_select', pm_oneclick.changeMask);
        
        $(document).off('click', '.pmoc-open-dropdown');
        $(document).on('click', '.pmoc-open-dropdown', pm_oneclick.switchDropdown);
        
        $(document).off('click', '.pmoc-dropdown-dropdown li');
        $(document).on('click', '.pmoc-dropdown-dropdown li', pm_oneclick.changeMask);
    },
    
    getOneclickPopup: function(e){
        e.preventDefault();
        var self = $(this),
            id_product = self.data('id_product'),
            uniqid = self.data('uniqid'),
            form_data = '',
            url = self.data('url');
        if ($('#add-to-cart-or-refresh').length) {
            form_data = $('#add-to-cart-or-refresh').serialize();
        }
        
        $.ajax({
                method: 'POST',
                url: url,
                data: form_data+'&uniqid='+uniqid+'&id_product='+id_product+'&action=getOneclickPopup',
                dataType: 'json'
            }).then(function (resp) {
                var modal_one_click = $('#pmoneclick_modal_'+resp.uniqid);
                modal_one_click.find('.modal-content').html(resp.popup);
                modal_one_click.modal('show');
                var phone_mask = modal_one_click.find('.pm_one_click_phone_mask').val(),
                    prefix = modal_one_click.find('.pm_one_click_call_prefix').val();
                if (phone_mask.length > 0) {
                    modal_one_click.find('.pm_oneclick_phone').inputmask("mask", {"mask": prefix+" "+phone_mask});
                }
               // $('#modal-oneclick input[name="phone"]').inputmask("mask", {"mask": "+38 (099) 999-99-99", 'showMaskOnHover': false});
                
            });
    },
    
    oneclickSave: function(e){
        e.preventDefault();
        var self = $(this),
            form = self.closest('.pm_one_click_popup_content'),
            id_product = form.find('input[name="id_product"]').val(),
            id_product_attribute = 0,
            email = '',
            phone = '',
            name = '',
            promo_code = '',
            valid_email = true,
            valid_phone = true,
            valid_name = true,
            url = form.find('input[name="contr_url"]').val(),
            name_field = form.find('input[name="pm_oneclick_name"]'),
            id_country = form.find('.pm_oneclick_country').val(),
            email_field = form.find('input[name="pm_oneclick_email"]'),
            phone_field = form.find('input[name="pm_oneclick_phone"]'),
            promo_code_filed = form.find('input[name="pm_oneclick_promo_code"]');
        
        if(form.find('input[name="id_product_attribute"]').length){
            id_product_attribute = form.find('input[name="id_product_attribute"]').val();
        }
        if(email_field.length){
            email = email_field.val();
            valid_email = pm_oneclick.validateEmail(email_field);
        }
        
        if(name_field.length){
            name = name_field.val();
            valid_name = pm_oneclick.validateLength(name_field, 2);
        }
        if(phone_field.length){
            phone = phone_field.val();
            valid_phone = pm_oneclick.validatePhone(phone_field, form)
        }
        if(form.find('input[name="id_customer"]').length){
            var id_customer = form.find('input[name="id_customer"]').val();
        }
        if(promo_code_filed.length){
            promo_code = promo_code_filed.val();
        }
        
        if (valid_phone && valid_name && valid_email) {
            form.find('.send_one_click').attr('disabled', 'disabled');
            $.ajax({
                method: 'POST',
                url: url,
                data: {
                    id_product: id_product,
                    id_product_attribute: id_product_attribute,
                    name: name,
                    phone: phone,
                    email: email,
                    id_customer: id_customer,
                    action: 'saveOneclick',
                    promo_code: promo_code,
                    id_country: id_country
                },
                dataType: 'json'
            }).then(function (resp) {
                form.closest('.pmoneclick_modal .modal-content').html($('<p>'+resp.message+'</p>'))
            });
        }
    },
    
    validatePhone: function(elem, form){
        var valid = true,
            phone_mask = form.find('input[name="phone_mask"]').val(),
            phone_field = form.find('input[name="pm_oneclick_phone"]');
        if(phone_mask){
            if(!phone_field.inputmask("isComplete")){
                valid = false;
                phone_field.addClass('nonvalid');
                phone_field.removeClass('valid');
            } else {
                phone_field.removeClass('nonvalid');
                phone_field.addClass('valid');
            }
        } else {
            if(!pm_oneclick.validateLength(phone_field, 2)){
                valid = false;
            }
        }
        return valid;
    },
    
    validateLength: function(field, length){
        var valid = true;
        if (field.val().length < length){
            valid = false;
            field.addClass('nonvalid');
            field.removeClass('valid');
        } else {
            field.removeClass('nonvalid');
            field.addClass('valid');
        }
        return valid;
    },
    
    validateEmail: function(elem){
        if(elem.length > 0){
            var value = elem.val(),
            email_reg = /^[-a-z0-9~!$%^&*_=+}{\'?]+(\.[-a-z0-9~!$%^&*_=+}{\'?]+)*@([a-z0-9_][-a-z0-9_]*(\.[-a-z0-9_]+)*\.(aero|arpa|biz|com|coop|edu|gov|info|int|mil|museum|name|net|org|pro|travel|mobi|[a-z][a-z])|([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}))(:[0-9]{1,5})?$/i;
            if(value.match(email_reg) || value.length != 0)  
            {  
                elem.removeClass('nonvalid');
                elem.addClass('valid');
                return true;   
            }  
            else  
            {  
                elem.addClass('nonvalid');
                elem.removeClass('valid');
                return false;
            } 
        } else {
            elem.addClass('nonvalid');
            elem.removeClass('valid');
            return false;
        }
    },
    
    changeMask: function(e){
        var self = $(this),
            prefix = self.data('call_prefix'),
            iso = self.data('iso').toLowerCase(),
            id_country = self.data('id_country'),
            modal_one_click = self.closest('.modal-content'),
            dropdown_container = modal_one_click.find('.pmoc-dropdown-container'),
            dropdown_arrow = dropdown_container.find('.pmoc-open-dropdown'),
            dropdown = dropdown_container.find('.pmoc-dropdown-dropdown'),
            phone_mask = modal_one_click.find('.pm_one_click_phone_mask').val();
        if (phone_mask.length > 0) {
            modal_one_click.find('.pm_oneclick_phone').inputmask("mask", {"mask": prefix.replace(/9/g, "\\9")+" "+phone_mask});
        }
        if (typeof dropdown_arrow != 'undefined') {
            if (dropdown_arrow.hasClass('active')) {
                pm_oneclick.closeDropdown(dropdown, dropdown_arrow);
            } else {
                pm_oneclick.openDropdown(dropdown, dropdown_arrow);
            }
            dropdown_arrow.closest('.pmoc-dropdown-header').find('.iconify').replaceWith($('<span class="iconify" data-icon="cif:'+iso+'" data-inline="false"></span>'));
            $('.pm_oneclick_country').val(id_country);
            Iconify.preloadImages([
               'cif:'+iso
            ]);
        }
    },
    
    switchDropdown: function(e){
        var self = $(this),
            parent = self.closest('.pmoc-dropdown-container'),
            dropdown = parent.find('.pmoc-dropdown-dropdown');
        if (self.hasClass('active')) {
            pm_oneclick.closeDropdown(dropdown, self);
        } else {
            pm_oneclick.openDropdown(dropdown, self);
        }
    },
    
    closeDropdown: function(dropdown, arrow){
        dropdown.slideUp();
        arrow.removeClass('active');
    },
    
    openDropdown: function(dropdown, arrow){
        dropdown.slideDown();
        arrow.addClass('active');
    }
} 
$(document).ready(function(){
    pm_oneclick.initialize();
});