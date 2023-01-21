<?php
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
 
if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Core\Module\WidgetInterface;

require_once _PS_MODULE_DIR_.'/pmoneclick/models/pmOneclcikSettings.php';

class Pmoneclick extends Module implements WidgetInterface
{
    public function __construct()
    {
        $this->name = 'pmoneclick';
        $this->author = 'PrestashopMaster';
        $this->tab = 'front_office_features';
        $this->module_key = '26193154aed8de76e5da682d934bbe39';
        $this->version = '1.0.0';
        $this->need_instance = 1;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Buy in 1-click');
        $this->description = $this->l('Allow place a quick order');

        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
    }
    
    public function install()
    {
        include(dirname(__FILE__).'/sql/install.php');
        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('displayProductActions');
    }

    public function uninstall()
    {
        include(dirname(__FILE__).'/sql/uninstall.php');
        return parent::uninstall();
    }

    public function getContent()
    {
        if (((bool)Tools::isSubmit('submitOneclick')) == true) {
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        return $this->postProcess().$this->renderForm();
    }

    protected function renderForm()
    {
        $carriers = array();
        $available_carriers = Carrier::getCarriers($this->context->language->id, true);
        foreach ($available_carriers as $key => $carrier) {
            $carriers[$key+1]['id_carrier'] = $carrier['id_carrier'];
            $carriers[$key+1]['name'] = $carrier['name'];
        }

        $payment_modules = array();
        foreach (PaymentModule::getInstalledPaymentModules() as $key => $p_module) {
            $payment_modules[$key+1]['id_payment'] = $p_module['id_module'];
            $payment_modules[$key+1]['name'] = $p_module['name'];
        }

        $order_states = [];
        foreach (OrderState::getOrderStates((int)Context::getContext()->language->id) as $key => $order_state) {
            $order_states[$key+1]['id_state'] = $order_state['id_order_state'];
            $order_states[$key+1]['name'] = $order_state['name'];
        }
        
        $countries = [];
        /*$id_shop = Shop::getContextShopID(true);
        if ($id_shop) {
            $countries_list = Country::getCountriesByIdShop($id_shop, $this->context->language->id);
            foreach ($countries_list as $key => $country) {
                $countries[$key+1]['id_country'] = $country['id_country'];
                $countries[$key+1]['name'] = $country['name'];
            }
        } else {*/
        $countries_list = Country::getCountries($this->context->language->id, true);
        foreach ($countries_list as $key => $country) {
            $countries[$key+1]['id_country'] = $country['id_country'];
            $countries[$key+1]['name'] = $country['country'];
        }
        //}
        
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->trans('Settings', array(), 'Admin.Global'),
                    'icon' => 'icon-cogs'
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->trans('Show name', array(), 'Modules.pmoneclick.Admin'),
                        'name' => 'name',
                        'class' => 't',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'on',
                                'label' => $this->l('enable'),
                                'value' => 1
                            ),

                            array(
                                'id' => 'off',
                                'lable' => $this->l('disable'),
                                'value' => 0
                            ),
                        )
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->trans('Show phone', array(), 'Modules.pmoneclick.Admin'),
                        'name' => 'phone',
                        'class' => 't',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'on',
                                'label' => $this->l('enable'),
                                'value' => 1
                            ),

                            array(
                                'id' => 'off',
                                'lable' => $this->l('disable'),
                                'value' => 0
                            ),
                        )
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->trans('Show promo code', array(), 'Modules.pmoneclick.Admin'),
                        'name' => 'promo_code',
                        'class' => 't',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'on',
                                'label' => $this->l('enable'),
                                'value' => 1
                            ),

                            array(
                                'id' => 'off',
                                'lable' => $this->l('disable'),
                                'value' => 0
                            ),
                        )
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->trans('Ignore selected country (use only for phone prefix)', array(), 'Modules.pmoneclick.Admin'),
                        'name' => 'ignore_country',
                        'class' => 't',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'on',
                                'label' => $this->l('enable'),
                                'value' => 1
                            ),

                            array(
                                'id' => 'off',
                                'lable' => $this->l('disable'),
                                'value' => 0
                            ),
                        )
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->trans('Phone mask', array(), 'Modules.pmoneclick.Admin'),
                        'name' => 'phone_mask',
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->trans('Default carrier', array(), 'Modules.pmoneclick.Admin'),
                        'name' => 'carrier',
                        'options' => array(
                            'query' => $carriers,
                            'id' => 'id_carrier',
                            'name' => 'name'
                        )
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->trans('Default payment', array(), 'Modules.pmoneclick.Admin'),
                        'name' => 'payment',
                        'options' => array(
                            'query' => $payment_modules,
                            'id' => 'id_payment',
                            'name' => 'name'
                        )
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->trans('Default status', array(), 'Modules.pmoneclick.Admin'),
                        'name' => 'status',
                        'options' => array(
                            'query' => $order_states,
                            'id' => 'id_state',
                            'name' => 'name'
                        )
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->trans('Default country', array(), 'Modules.pmoneclick.Admin'),
                        'name' => 'id_country',
                        'options' => array(
                            'query' => $countries,
                            'id' => 'id_country',
                            'name' => 'name'
                        )
                    )
                ),
                'submit' => array(
                    'title' => $this->trans('Save', array(), 'Admin.Actions'),
                    'name' => 'submitOneclick'
                )
            ),
        );

        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->default_form_language = $lang->id;
        $helper->module = $this;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitStoreConf';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'uri' => $this->getPathUri(),
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );

        return $helper->generateForm(array($fields_form));
    }

    public function renderWidget($hookName = null, array $configuration = [])
    {
        $this->smarty->assign($this->getWidgetVariables($hookName, $configuration));
        return $this->display(__FILE__, 'views/templates/hook/oneclick_button.tpl');
    }

    public function hookdisplayProductActions($params)
    {
        $configuration = [];
        $configuration['id_product'] = $params['product']['id_product'];
        $configuration['id_product_attribute'] = $params['product']['id_product_attribute'];
        $this->context->smarty->assign($this->getWidgetVariables(null, $configuration));
        return $this->display(__FILE__, 'views/templates/hook/oneclick_button.tpl');
    }
    
    public function hookpmoneclick($params)
    {
        return $this->hookdisplayProductActions($params);
    }
    
    public function getWidgetVariables($hookName = null, array $configuration = [])
    {
        $id_oneclick_settings = $this->getIdSettingsFront();
        $settings = new pmOneclcikSettings($id_oneclick_settings);
        return [
            'settings' => $settings,
            'id_product' => $configuration['id_product'],
            'module_url' => $this->context->link->getModuleLink('pmoneclick', 'front'),
            'uniqid' => uniqid()
        ];
    }

    public function getConfigFieldsValues()
    {
        $fields = array();
        $id_shop = Shop::getContextShopID(true);
        $id_shop_group = Shop::getContextShopGroupID(true);
        if (!Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE')) {
            $id_shop = 1;
            $id_shop_group = 1;
        }
        $id_oneclick_settings = $this->getIdSettingsAdmin($id_shop, $id_shop_group);
        $one_click = new pmOneclcikSettings($id_oneclick_settings);
        $fields['name'] = $one_click->name;
        $fields['phone'] = $one_click->phone;
        $fields['promo_code'] = $one_click->promo_code;
        $fields['phone_mask'] = $one_click->phone_mask;
        $fields['id_country'] = $one_click->id_country;
        $fields['carrier'] = $one_click->carrier;
        $fields['payment'] = $one_click->payment;
        $fields['status'] = $one_click->status;
        $fields['ignore_country'] = $one_click->ignore_country;
        return $fields;
    }
    
    private function getIdSettingsAdmin($id_shop, $id_shop_group)
    {
        return pmOneclcikSettings::getAdminId($id_shop, $id_shop_group);
    }
    
    private function getIdSettingsFront()
    {
        $id_shop = $this->context->shop->id;
        $id_shop_group = $this->context->shop->id_shop_group;
        return pmOneclcikSettings::getId($id_shop, $id_shop_group);
    }

    protected function postProcess()
    {
        if (Tools::isSubmit('submitOneclick')) {
            $id_shop = Shop::getContextShopID(true);
            $id_shop_group = Shop::getContextShopGroupID(true);
            if (!Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE')) {
                $id_shop = 1;
                $id_shop_group = 1;
            }
            $id_oneclick_settings = $this->getIdSettingsAdmin($id_shop, $id_shop_group);
            $one_click = new pmOneclcikSettings($id_oneclick_settings);
            $one_click->name = (int)Tools::getValue('name');
            $one_click->phone = (int)Tools::getValue('phone');
            $one_click->promo_code = (int)Tools::getValue('promo_code');
            $one_click->ignore_country = (int)Tools::getValue('ignore_country');
            $one_click->id_country = (int)Tools::getValue('id_country');
            $one_click->phone_mask = pSql(Tools::getValue('phone_mask'));
            $one_click->carrier = pSql(Tools::getValue('carrier'));
            $one_click->payment = pSql(Tools::getValue('payment'));
            $one_click->status = pSql(Tools::getValue('status'));
            $one_click->id_shop = $id_shop;
            $one_click->id_shop_group = $id_shop_group;
            if (!$one_click->id) {
                $one_click->add();
            } else {
                $one_click->update();
            }
            return $this->displayConfirmation($this->trans('The settings have been updated.', array(), 'Admin.Notifications.Success'));
        }
    }

    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path.'/views/js/front.js');
        $this->context->controller->addJS($this->_path.'/views/js/iconify.min.js');
        $this->context->controller->addJS($this->_path.'/views/js/jquery.ddslick.min.js');
        $this->context->controller->addCSS($this->_path.'/views/css/front.css');
        $this->context->controller->addCSS($this->_path.'/views/css/cif.css');
        $this->context->controller->registerJavascript(
            'modules-pmoneclick-input-mask',
            'modules/'.$this->name.'/views/js/jquery.inputmask.bundle.min.js',
            ['position' => 'bottom', 'priority' => 150]
        );
        $this->context->controller->registerJavascript(
            'modules-pmoneclick-input-maskp',
            'modules/'.$this->name.'/views/js/phone.min.js',
            ['position' => 'bottom', 'priority' => 150]
        );
    }
    
}
