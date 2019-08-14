<?php

/**
 * Smartsend_Logistics
 *
 * This source file is subject to the GNU General Public License v3.0
 * that is bundled with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@smartsend.dk so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the plugin to newer
 * versions in the future. If you wish to customize the plugin for your
 * needs please refer to http://www.smartsend.dk
 *
 * @folder		/app/code/community/Smartsend/Logistics/Model/Carrier/Gls.php
 * @category	Smartsend
 * @package		Smartsend_Logistics
 * @author		Anders Bilfeldt
 * @url			www.smartsend.dk
 */
class Smartsend_Logistics_Model_Carrier_Gls extends Mage_Shipping_Model_Carrier_Abstract implements Mage_Shipping_Model_Carrier_Interface {

    protected $_code = 'smartsendgls';

    public function _post($street, $city, $postcode, $country) {
        $ch = curl_init();

        /* Script URL */
        //$url = 'http://private-2d17c-smartsend.apiary-mock.com/pickup/';
        $url = 'https://smartsend-prod.apigee.net/v7/pickup/';

        $carriers = 'gls';

        /* $_GET Parameters to Send */
        $params = array('country' => $country, 'zip' => $postcode, 'city' => $city, 'street' => $street);

        /* Update URL to container Query String of Paramaters */
        $url .= $carriers . '?' . http_build_query($params);

        curl_setopt($ch, CURLOPT_URL, $url);                //curl url
        curl_setopt($ch, CURLOPT_HTTPGET, true);           //curl request method
        //curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        	'apikey:yL18TETUVQ7E9pgVb6JeV1erIYHAMcwe',
        	'smartsendmail:'.Mage::getStoreConfig('carriers/smartsend/username'),
        	'smartsendlicence:'.Mage::getStoreConfig('carriers/smartsend/licencekey'),
        	'cmssystem:Magento',
        	'cmsversion:'.Mage::getVersion(),
        	'appversion:'.Mage::getConfig()->getNode('modules/Smartsend_Logistics')->version
        	));    //curl request header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = new StdClass();                 //declare new class
        $result->response = curl_exec($ch);               //executing curl
        $result->code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $result->meta = curl_getinfo($ch);
        $curl_error = ($result->code > 0 ? null : curl_error($ch) . ' (' . curl_errno($ch) . ')');    //getting curl error if their are any

        curl_close($ch);               //closing curl

        if ($curl_error) {
            throw new Exception('An error occurred while connecting to Chargify: ' . $curl_error);    //showing error
        }
        return $result;
    }
    
    public function isTrackingAvailable() {
    	return true;
	}

    public function getFormBlock() {
        return 'logistics/gls';
    }

    public function collectRates(Mage_Shipping_Model_Rate_Request $request) {
        if (!Mage::getStoreConfig('carriers/' . $this->_code . '/active')) {             //checking if carrier is active or not
            return false;
        }

        $handling = Mage::getStoreConfig('carriers/' . $this->_code . '/handling');
        $result = Mage::getModel('shipping/rate_result');      //shipping rate price model
        $glsShippingFee = $this->getGlsShippingFee();          //getting gls shipping prices
        $carrier_label = 'Gls';
        $shipping_country = Mage::getSingleton('checkout/type_onepage')->getQuote()->getShippingAddress()          ///get shipping address from the checkout quote
                ->getCountryId();
        $show = true;
        if ($show && $glsShippingFee!=NULL) { // This if condition is just to demonstrate how to return success and error in shipping methods
            $carrier = $this->_code;
            $check_method = Mage::getModel('logistics/shippingMethods')->checkShippingFee($carrier, $shipping_country);       //checking the shipping price for the carrier gls

            foreach ($check_method as $key => $value) {

                $title = '';
                foreach (unserialize($this->getConfigData('price')) as $val) {

                    if ($val['methods'] == $key && $val['pickupshippingfee'] == $value) {
                        $title = $val['method_name'];
                        $method_code = Mage::getModel('logistics/shippingMethods')->getMethodName();           //getting the shipping method name 

                        $method_code = $method_code[$val['methods']];
                    }
                }

                $method = Mage::getModel('shipping/rate_result_method');             //saving the shipping method
                $method->setCarrier($this->_code);
                $method->setMethod($method_code);
                $method->setCarrierTitle($this->getConfigData('title'));
                $method->setMethodTitle($title);
                $method->setPrice($value);
                $method->setCost($value);
                $result->append($method);
            }
        } else {
            $error = Mage::getModel('shipping/rate_result_error');
            $error->setCarrier($this->_code);
            $error->setCarrierTitle($this->getConfigData('name'));
            $error->setErrorMessage($this->getConfigData('specificerrmsg'));
            $result->append($error);
        }
        return $result;
    }

    public function getAllowedMethods() {
        return array('gls' => $this->getConfigData('name'));
    }

    public function getGlsShippingFee() {
        //$orderPrice = Mage::registry('ordsubtotal') + Mage::registry('ordtaxtotal');    //adding the tax amount with the order subtotal
        $orderPrice = Mage::getModel('checkout/session')->getQuote()->getGrandTotal();        // get the total amount of the order
        $orderWeight = Mage::registry('ordweight');                //getting the order weight 

        if (Mage::getStoreConfig('carriers/smartsendgls/price') != "") {                                  //checking the shipping price 
            $pickupShippingRates = unserialize(Mage::getStoreConfig('carriers/smartsendgls/price', Mage::app()->getStore()));
        }

        $rates = array();
        if ($pickupShippingRates) {

            foreach ($pickupShippingRates as $pickupShippingRate) {
                if ($pickupShippingRate['orderminprice'] <= $orderPrice && $pickupShippingRate['ordermaxprice'] >= $orderPrice && $pickupShippingRate['orderminweight'] <= $orderWeight && $pickupShippingRate['ordermaxweight'] >= $orderWeight) {            //if weight and price is correct
                    $rates[] = $pickupShippingRate['pickupshippingfee'];                   //add rate to array of rates
                }
            }

            $cheapestexpensive = Mage::getStoreConfig('carriers/smartsendgls/cheapestexpensive', Mage::app()->getStore());       //get the 'Cheapest or most expensive' setting from the admin system config
            if (!$cheapestexpensive) {
                $cheapestexpensive = 0;   //default is the cheapest
            }

            if (!empty($rates)) {                                   //if at least one rate matches the order
                if ($cheapestexpensive == 0) {
                    return min($rates);               //return min price 
                } elseif ($cheapestexpensive == 1) {
                    return max($rates);                //return max price 
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

}
