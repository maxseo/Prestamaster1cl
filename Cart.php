<?php
 
class Cart extends CartCore
{
    /*
    * module: ets_onepagecheckout
    * date: 2023-10-09 13:46:09
    * version: 2.6.0
    */
    public function getPackageList($flush = false)
    {
        if(($address_type =  Tools::getValue('address_type')) && $address_type=='shipping_address')
            $this->id_address_delivery = (int)Tools::getValue('id_address',$this->id_address_delivery);
        return parent::getPackageList($flush);
    }
    /*
    * module: ets_onepagecheckout
    * date: 2023-10-09 13:46:09
    * version: 2.6.0
    */
    public function getPackageShippingCost($id_carrier = null, $use_tax = true, Country $default_country = null, $product_list = null, $id_zone = null, bool $keepOrderPrices = false)
    {
        if($IDzone = (int)Hook::exec('actionGetIDZoneByAddressID'))
        {
            $id_zone = $IDzone;
        }
        
        return parent::getPackageShippingCost($id_carrier,$use_tax,$default_country,$product_list,$id_zone, $keepOrderPrices);
    }
    /*
    * module: ets_extraoptions
    * date: 2024-03-05 04:00:24
    * version: 1.0.7
    */
    public $_products;
    /*
    * module: ets_extraoptions
    * date: 2024-03-05 04:00:24
    * version: 1.0.7
    */
    public $shouldSplitGiftProductsQuantity ;
    /*
    * module: ets_extraoptions
    * date: 2024-03-05 04:00:24
    * version: 1.0.7
    */
    public function getOrderTotal(
        $withTaxes = true,
        $type = Cart::BOTH,
        $products = null,
        $id_carrier = null,
        $use_cache = false,
        bool $keepOrderPrices = false
    ){
        $total = parent::getOrderTotal($withTaxes,$type,$products,$id_carrier,$use_cache,$keepOrderPrices);
        if($type!=Cart::BOTH && $type!=Cart::ONLY_PRODUCTS)
            return $total;
        if(!$keepOrderPrices && ($type== Cart::BOTH  || $type==Cart::ONLY_PRODUCTS) && Module::isEnabled('ets_extraoptions'))
        {
            $priceCustom  = Module::getInstanceByName('ets_extraoptions')->getPriceAttributeCustom($this,$withTaxes);
            return $total + $priceCustom;
        }
        else
            return $total;
    }
    /*
    * module: ets_extraoptions
    * date: 2024-03-05 04:00:24
    * version: 1.0.7
    */
    public function getProducts($refresh = true, $id_product = false, $id_country = null, $fullInfos = true,bool $keepOrderPrices = false,$default = false)
    {
        if($keepOrderPrices || $default || !Module::isEnabled('ets_extraoptions'))
            return parent::getProducts($refresh,$id_product,$id_country,$fullInfos,$keepOrderPrices);
        else
        {
            $this->_products = Module::getInstanceByName('ets_extraoptions')->getProducts($this,$refresh,$id_product,$id_country,$fullInfos,$keepOrderPrices);
            return $this->_products;
        }
    }
    /*
    * module: ets_extraoptions
    * date: 2024-03-05 04:00:24
    * version: 1.0.7
    */
    public function applyProductCalculations($row, $shopContext, $productQuantity = null, bool $keepOrderPrices = false)
    {
        return parent::applyProductCalculations($row,$shopContext,$productQuantity,$keepOrderPrices);
    }
}