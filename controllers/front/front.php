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
 
require_once _PS_MODULE_DIR_.'pmoneclick/models/pmOneclcikSettings.php';
use PrestaShop\PrestaShop\Core\Product\ProductExtraContentFinder;

class PmoneclickFrontModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();
        if (Tools::isSubmit('action')) {
            $action = Tools::getValue('action');
            switch ($action) {
                case 'getOneclickPopup':
                    $id_product = (int)Tools::getValue('id_product');
                    $uniqid = Tools::getValue('uniqid');
                    $idProductAttribute = 0;
                    $groups = Tools::getValue('group');
                    if (!empty($groups)) {
                        $idProductAttribute = (int) self::getIdProductAttributeByIdAttributes(
                            $id_product,
                            $groups,
                            true
                        );
                    } else {
                        $idProductAttribute = Product::getDefaultAttribute($id_product);
                    }
                    
                    $id_oneclick_settings = $this->getIdSettingsFront();
                    $oneclick_settings = new pmOneclcikSettings($id_oneclick_settings);
                    $product_o = new Product($id_product, false, $this->context->language->id);
                    $combinations = array();
                    $attributes = $product_o->getAttributesGroups((int)$this->context->language->id);
                    $currency = new Currency((int)$this->context->currency->id);
                    
                    foreach ($attributes as $attribute) {
                        if (!isset($combinations[$attribute['id_product_attribute']]['attributes'])) {
                            $combinations[$attribute['id_product_attribute']]['attributes'] = '';
                        }
                        $combinations[$attribute['id_product_attribute']]['attributes'] .= $attribute['attribute_name'].' - ';
                        $combinations[$attribute['id_product_attribute']]['id_product_attribute'] = $attribute['id_product_attribute'];
                        $combinations[$attribute['id_product_attribute']]['default_on'] = $attribute['default_on'];
                        if (!isset($combinations[$attribute['id_product_attribute']]['price'])) {
                            $price_tax_incl = Product::getPriceStatic((int)$product_o->id, true, $attribute['id_product_attribute']);
                            $price_tax_excl = Product::getPriceStatic((int)$product_o->id, false, $attribute['id_product_attribute']);
                            $combinations[$attribute['id_product_attribute']]['price_tax_incl'] = Tools::ps_round(Tools::convertPrice($price_tax_incl, $currency), 2);
                            $combinations[$attribute['id_product_attribute']]['price_tax_excl'] = Tools::ps_round(Tools::convertPrice($price_tax_excl, $currency), 2);
                            $combinations[$attribute['id_product_attribute']]['formatted_price'] = Tools::displayPrice(Tools::convertPrice($price_tax_excl, $currency), $currency);
                        }
                        if (!isset($combinations[$attribute['id_product_attribute']]['qty_in_stock'])) {
                            $combinations[$attribute['id_product_attribute']]['qty_in_stock'] = StockAvailable::getQuantityAvailableByProduct((int)$product_o->id, $attribute['id_product_attribute'], (int)$this->context->shop->id);
                        }
                    }
    
                    foreach ($combinations as $key => &$combination) {
                        if (Product::getQuantity($id_product, $combination['id_product_attribute'], null, $this->context->cart) > 0) {
                            $combination['attributes'] = rtrim($combination['attributes'], ' - ');
                        } else {
                            unset($combinations[$key]);
                        }
                    }
                    if ($idProductAttribute) {
                        $comb_image = Product::getCombinationImageById($idProductAttribute, $this->context->language->id);
                    } else {
                        $comb_image = Product::getCover($id_product, $this->context);
                    }
                    
                    $countries = Country::getCountries($this->context->language->id, true);
                    foreach ($countries as $country) {
                        if ($country['id_country'] == $oneclick_settings->id_country) {
                            $default_country = $country;
                            $default_prefix = str_replace(9, '\\9', $country['call_prefix']);
                        }
                    }
                    
                    $this->context->smarty->assign([
                        'product_o' => $product_o,
                        'customer' => $this->context->customer,
                        'id_product_attribute' => $idProductAttribute,
                        'combinations' => $combinations,
                        'oneclick_settings' => $oneclick_settings,
                        'currency' => $currency,
                        'comb_image' => $comb_image,
                        'default_prefix' => $default_prefix,
                        'countries' => $countries,
                        'default_country' => $default_country,
                        'module_url' => $this->context->link->getModuleLink('pmoneclick', 'front')
                        ]);
                    $popup = $this->context->smarty->fetch(_PS_MODULE_DIR_.'pmoneclick/views/templates/front/popup.tpl');
                    die(Tools::jsonEncode(['popup' => $popup, 'uniqid' => $uniqid]));
                case 'saveOneclick':
                    $id_product = (int)Tools::getValue('id_product');
                    $id_product_attribute = (int)Tools::getValue('id_product_attribute');
                    
                    $id_customer = (int)Tools::getValue('id_customer');
                    $email = Tools::getValue('email');
                    $name = Tools::getValue('name');
                    $phone = Tools::getValue('phone');
                    $id_oneclick_settings = $this->getIdSettingsFront();
                    $oneclick_settings = new pmOneclcikSettings($id_oneclick_settings);
                    
                    if (!$id_customer) {
                        if (isset($email) && $email) {
                            $id_customer = $this->getIdCustomerByEmail($email);
                        }
                    }
                    if (!$id_customer) {
                        $id_customer = $this->createCustomer($email, $name, $phone);
                    }
                    
                    $customer = new Customer((int)$id_customer);
                    
                    
                    /*if (!count($addresses)) {*/
                        $this->createAddress($customer, $oneclick_settings);
                    /*} else {
                        $this->updateAddress($addresses[0]['id_address']);
                    }*/
                    $addresses = array_reverse($customer->getAddresses((int)$this->context->cart->id_lang));
                    $this->context->customer = $customer;
                    
                    if (!$this->context->cart->id) {
                        $this->context->cart->recyclable = 0;
                        $this->context->cart->gift = 0;
                    }
            
                    if (!$this->context->cart->id_customer) {
                        $this->context->cart->id_customer = $id_customer;
                    }
                    
                    if (Validate::isLoadedObject($this->context->cart) && $this->context->cart->OrderExists()) {
                        return;
                    }
                    
                    if (!$this->context->cart->secure_key) {
                        $this->context->cart->secure_key = $this->context->customer->secure_key;
                    }
                    if (!$this->context->cart->id_shop) {
                        $this->context->cart->id_shop = (int)$this->context->shop->id;
                    }
                    if (!$this->context->cart->id_lang) {
                        $this->context->cart->id_lang = (($id_lang = (int)Tools::getValue('id_lang')) ? $id_lang : Configuration::get('PS_LANG_DEFAULT'));
                    }
                    if (!$this->context->cart->id_currency) {
                        $this->context->cart->id_currency = (($id_currency = (int)Tools::getValue('id_currency')) ? $id_currency : Configuration::get('PS_CURRENCY_DEFAULT'));
                    }
            
                    
                    $id_address_delivery = (int)Tools::getValue('id_address_delivery');
                    $id_address_invoice = (int)Tools::getValue('id_address_delivery');
            
                    if (!$this->context->cart->id_address_invoice && isset($addresses[0])) {
                        $this->context->cart->id_address_invoice = (int)$addresses[0]['id_address'];
                    } elseif ($id_address_invoice) {
                        $this->context->cart->id_address_invoice = (int)$id_address_invoice;
                    }
                    if (!$this->context->cart->id_address_delivery && isset($addresses[0])) {
                        $this->context->cart->id_address_delivery = $addresses[0]['id_address'];
                    } elseif ($id_address_delivery) {
                        $this->context->cart->id_address_delivery = (int)$id_address_delivery;
                    }
                    $this->context->cart->setNoMultishipping();
                    $this->context->cart->save();
                    
                    
                    $currency = new Currency((int)$this->context->cart->id_currency);
                    $this->context->currency = $currency;
                    
                    $this->context->cart->deleteProduct((int)$id_product, (int)$id_product_attribute);
                    $this->context->cart->updateQty(1, (int)$id_product, (int)$id_product_attribute, null, 'up');
                    
                    if (!$this->context->cart->id_carrier) {
                        $this->context->cart->setDeliveryOption(array($this->context->cart->id_address_delivery => (int)$oneclick_settings->carrier.','));
                        $this->context->cart->save();
                    }
                    
                    $module_name = $this->getPaymentModuleName($oneclick_settings->payment);
                    $id_order_state = $oneclick_settings->status;
                    $id_cart_before_save_order = (int)$this->context->cart->id;
                    
                    $this->_updateMessage(Tools::getValue('promo_code'));
                    
                    if (Validate::isModuleName($module_name)) {
                        $payment_module = Module::getInstanceByName($module_name);
                        $payment_module->validateOrder(
                            (int)$this->context->cart->id,
                            (int)$id_order_state,
                            $this->context->cart->getOrderTotal(true, Cart::BOTH),
                            $payment_module->displayName,
                            $this->module->l('One click order:'),
                            array(),
                            null,
                            false,
                            $this->context->cart->secure_key
                        );
                    }
                    $order_reference = $this->getOrderReference($id_cart_before_save_order);
                    $this->context->smarty->assign([
                        'order_reference' => $order_reference
                    ]);
                    $message = $this->context->smarty->fetch(_PS_MODULE_DIR_.'pmoneclick/views/templates/front/confirm_popup.tpl');
                    //$messagew = $this->module->l('Your order has been accepted! Order reference: ', 'front');
                    die(Tools::jsonEncode(['message' => $message]));
                    break;
                default:
                    break;
            }
        }
    }
    
    private function _updateMessage($messageContent)
    {
        if ($messageContent) {
            if ($oldMessage = Message::getMessageByCartId((int)$this->context->cart->id)) {
                $message = new Message((int)$oldMessage['id_message']);
                $message->message = $messageContent;
                $message->update();
            } else {
                $message = new Message();
                $message->message = $messageContent;
                $message->id_cart = (int)$this->context->cart->id;
                $message->id_customer = (int)$this->context->cart->id_customer;
                $message->add();
            }
        } else {
            if ($oldMessage = Message::getMessageByCartId($this->context->cart->id)) {
                $message = new Message($oldMessage['id_message']);
                $message->delete();
            }
        }

        return true;
    }
    
    private function getOrderReference($id_cart)
    {
        return  Db::getInstance()->getValue('
    		SELECT reference
    		FROM `'._DB_PREFIX_.'orders`
    		WHERE `id_cart` = '.(int)$id_cart);
    }
    
    public function getIdCustomerByEmail($email)
    {
        return  Db::getInstance()->getValue('
		SELECT id_customer
		FROM `'._DB_PREFIX_.'customer`
		WHERE `email` = \''.pSQL($email).'\'');
    }
    
    public function getPaymentModuleName($id_module)
    {
        return  Db::getInstance()->getValue('
    		SELECT name
    		FROM `'._DB_PREFIX_.'module`
    		WHERE `id_module` = '.(int)$id_module);
    }
    
    public function createAddress($customer, $oneclick_settings)
    {
        $address = new Address();
        $address->id_customer = (int)$customer->id;
        if ($oneclick_settings->ignore_country || !Tools::isSubmit('id_country')) {
            $address->id_country = $oneclick_settings->id_country;
        } else {
            $address->id_country = Tools::getValue('id_country');
        }
        $address->alias = $this->module->l('One click address alias');
        $address->lastname = $customer->lastname;
        $address->firstname = $customer->firstname;
        $address->phone = pSql(Tools::getValue('phone'));
        $address->address1 = $this->module->l('One click address');
        $address->city = $this->module->l('One click city');
        $address->add();
    }
    
    private function updateAddress($id_address)
    {
        $address = new Address($id_address);
        $address->phone = pSql(Tools::getValue('phone'));
        $address->update();
    }
    
    private function getIdSettingsFront()
    {
        $id_shop = $this->context->shop->id;
        $id_shop_group = $this->context->shop->id_shop_group;
        return pmOneclcikSettings::getId($id_shop, $id_shop_group);
    }
    
    public function createCustomer($email, $name, $phone)
    {
        $customer = new Customer();
        $customer->email = pSql($email);
        if ($name) {
            $customer->firstname = $name;
        } else {
            $customer->firstname = '-';
        }
        if ($phone) {
            $customer->phone = $phone;
        } else {
            $customer->phone = '-';
        }
        $customer->lastname = '-';
        $passwd = $this->generatePassword();
        $value = $this->get('hashing')->hash($passwd, _COOKIE_KEY_);
        $customer->passwd = $value;
        $customer->add();
        $language = new Language((int) $this->context->language->id);
        $vars = array(
            '{firstname}' => $customer->firstname,
            '{passwd}' => $passwd,
            '{email}' => $email,
        );
        Mail::Send(
            (int) $this->context->language->id,
            'account',
            Context::getContext()->getTranslator()->trans(
                'Welcome!',
                array(),
                'Emails.Subject',
                $language->locale
            ),
            $vars,
            $email,
            null,
            null,
            null,
            null,
            null,
            _PS_MODULE_DIR_.'pmoneclick/mails',
            false,
            (int) $this->context->shop->id
        );
        return $customer->id;
    }
    
    public function generatePassword($length = 8)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $count = mb_strlen($chars);
    
        for ($i = 0, $result = ''; $i < $length; $i++) {
            $index = rand(0, $count - 1);
            $result .= mb_substr($chars, $index, 1);
        }
    
        return $result;
    }

    public static function getIdProductAttributeByIdAttributes($idProduct, $idAttributes, $findBest = false)
    {
        $idProduct = (int) $idProduct;

        if (!is_array($idAttributes) && is_numeric($idAttributes)) {
            $idAttributes = array((int) $idAttributes);
        }

        if (!is_array($idAttributes) || empty($idAttributes)) {
            throw new PrestaShopException(
                sprintf(
                    'Invalid parameter $idAttributes with value: "%s"',
                    print_r($idAttributes, true)
                )
            );
        }

        $idAttributesImploded = implode(',', array_map('intval', $idAttributes));
        $idProductAttribute = Db::getInstance()->getValue(
            '
            SELECT
                pac.`id_product_attribute`
            FROM
                `' . _DB_PREFIX_ . 'product_attribute_combination` pac
                INNER JOIN `' . _DB_PREFIX_ . 'product_attribute` pa ON pa.id_product_attribute = pac.id_product_attribute
            WHERE
                pa.id_product = ' . $idProduct . '
                AND pac.id_attribute IN (' . $idAttributesImploded . ')
            GROUP BY
                pac.`id_product_attribute`
            HAVING
                COUNT(pa.id_product) = ' . count($idAttributes)
        );

        if ($idProductAttribute === false && $findBest) {
            //find the best possible combination
            //first we order $idAttributes by the group position
            $orderred = array();
            $result = Db::getInstance()->executeS(
                '
                SELECT
                    a.`id_attribute`
                FROM
                    `' . _DB_PREFIX_ . 'attribute` a
                    INNER JOIN `' . _DB_PREFIX_ . 'attribute_group` g ON a.`id_attribute_group` = g.`id_attribute_group`
                WHERE
                    a.`id_attribute` IN (' . $idAttributesImploded . ')
                ORDER BY
                    g.`position` ASC'
            );

            foreach ($result as $row) {
                $orderred[] = $row['id_attribute'];
            }

            while ($idProductAttribute === false && count($orderred) > 0) {
                array_pop($orderred);
                $idProductAttribute = Db::getInstance()->getValue(
                    '
                    SELECT
                        pac.`id_product_attribute`
                    FROM
                        `' . _DB_PREFIX_ . 'product_attribute_combination` pac
                        INNER JOIN `' . _DB_PREFIX_ . 'product_attribute` pa ON pa.id_product_attribute = pac.id_product_attribute
                    WHERE
                        pa.id_product = ' . (int) $idProduct . '
                        AND pac.id_attribute IN (' . implode(',', array_map('intval', $orderred)) . ')
                    GROUP BY
                        pac.id_product_attribute
                    HAVING
                        COUNT(pa.id_product) = ' . count($orderred)
                );
            }
        }

        if (empty($idProductAttribute)) {
            throw new PrestaShopObjectNotFoundException('Can not retrieve the id_product_attribute');
        }

        return $idProductAttribute;
    }
}
