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
 
$sql = array();


$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'pm_oneclick_settings` (
    `id` int(1) NOT NULL AUTO_INCREMENT,
    `name` int(1),
    phone int(1),
    ignore_country int(1),
    promo_code int(1),
    id_country int,
    id_shop int,
    id_shop_group int,
    phone_mask varchar(255),
    carrier varchar(255),
    payment varchar(255),
    status varchar(255),
    PRIMARY KEY  (`id`, id_shop, id_shop_group)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';


foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
