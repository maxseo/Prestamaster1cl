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

class PmOneclcikSettings extends ObjectModel
{
    public $phone;
    public $phone_mask;
    public $name;
    public $payment;
    public $carrier;
    public $id_country;
    public $ignore_country;
    public $status;
    public $id_shop;
    public $id_shop_group;
    public $promo_code;
    
    public static $definition = array(
        'table' => 'pm_oneclick_settings',
        'primary' => 'id',
        'fields' => array(
            'id_country' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'ignore_country' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'id_shop' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'id_shop_group' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'phone' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'promo_code' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'phone_mask' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'name' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'payment' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'carrier' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'status' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
        )
    );

    public function __construct($id_form_group = null, $id_lang = null, $id_shop = null, Context $context = null)
    {
        parent::__construct($id_form_group, $id_lang, $id_shop);
    }

    public function add($autodate = true, $null_values = false)
    {
        parent::add($autodate, $null_values);
        $last_id = Db::getInstance()->Insert_ID();
        return $last_id;
    }

    public function delete()
    {
        $res = parent::delete();

        return $res;
    }
    
    public static function getId($id_shop, $id_shop_group)
    {
        $id = 0;
        if ($id_shop) {
            $id = self::getIdByShopId($id_shop);
        }
        if (!$id && $id_shop_group) {
            $id = self::getIdByShopGroupId($id_shop_group);
        }
        if (!$id) {
            $id = self::getIdWithoutShop();
        }
        return $id;
    }
    
    public static function getAdminId($id_shop = 0, $id_shop_group = 0)
    {
        if ($id_shop) {
            return self::getIdByShopId($id_shop);
        } else if ($id_shop_group) {
            return self::getIdByShopGroupId($id_shop_group);
        } else {
            return self::getIdWithoutShop();
        }
    }
    
    public static function getIdByShopId($id_shop)
    {
        return Db::getInstance()->getValue("SELECT id FROM "._DB_PREFIX_."pm_oneclick_settings WHERE id_shop=".(int)$id_shop);
    }
    
    public static function getIdByShopGroupId($id_shop_group)
    {
        return Db::getInstance()->getValue("SELECT id FROM "._DB_PREFIX_."pm_oneclick_settings WHERE id_shop = 0 AND id_shop_group=".(int)$id_shop_group);
    }
    
    public static function getIdWithoutShop()
    {
        return Db::getInstance()->getValue("SELECT id FROM "._DB_PREFIX_."pm_oneclick_settings WHERE id_shop_group = 0 AND id_shop = 0");
    }
}
