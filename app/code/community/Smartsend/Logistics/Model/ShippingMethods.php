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
 * @folder		/app/code/community/Smartsend/Logistics/Model/Status.php
 * @category	Smartsend
 * @package		Smartsend_Logistics
 * @author		Anders Bilfeldt
 * @url			www.smartsend.dk
 */
class Smartsend_Logistics_Model_ShippingMethods extends Varien_Object {

    static public function getOptionArray($carrier) {


        $carrier_name = explode('groups[', $carrier);          //getting carrier name

        $carrier_name = explode(']', $carrier_name[1]);

        $carrier_name = $carrier_name[0];

        if ($carrier_name == 'smartsendbring') {

			//Bring shipping methods
            $shipping_methods = array(
            	'private',
            	'privatehome',
            	'commercial',
            	'commercial_bulksplit',
            	'private_bulksplit',
            	'privatehome_bulksplit'
            	);
            if(Mage::helper('logistics/data')->isVconnetEnabled() == false) {
            	array_unshift($shipping_methods,'pickup');
            }

            $method_names = array_flip(Mage::getModel('logistics/shippingMethods')->getMethodName());

            $methods_array = array();
            foreach ($shipping_methods as $key => $value) {

                if (array_key_exists($value, $method_names)) {
                    $methods_array[$method_names[$value]] = $method_names[$value];
                }
            }

            return $methods_array;
        } elseif ($carrier_name == 'smartsendpostdanmark') {

			//Post Danmark shipping methods
            $shipping_methods = array(
            	'private',
            	'privatehome',
            	'commercial',
            	'dpdclassic',
            	'dpdguranty',
            	'valuemail',
            	'privatesamsending',
            	'privatepriority',
            	'privateeconomy'
            	);
            if(Mage::helper('logistics/data')->isVconnetEnabled() == false) {
            	array_unshift($shipping_methods,'pickup');
            }

            $method_names = array_flip(Mage::getModel('logistics/shippingMethods')->getMethodName());

            $methods_array = array();
            foreach ($shipping_methods as $key => $value) {

                if (array_key_exists($value, $method_names)) {
                    $methods_array[$method_names[$value]] = $method_names[$value];
                }
            }

            return $methods_array;
        } elseif ($carrier_name == 'smartsendposten') {

			//Posten shipping methods
            $shipping_methods = array(
            	'private',
            	'privatehome',
            	'commercial',
            	'valuemail',
            	'valuemailfirstclass',
            	'valuemaileconomy',
            	'maximail'
            	);
            if(Mage::helper('logistics/data')->isVconnetEnabled() == false) {
            	array_unshift($shipping_methods,'pickup');
            }

            $method_names = array_flip(Mage::getModel('logistics/shippingMethods')->getMethodName());

            $methods_array = array();
            foreach ($shipping_methods as $key => $value) {

                if (array_key_exists($value, $method_names)) {
                    $methods_array[$method_names[$value]] = $method_names[$value];
                }
            }

            return $methods_array;
        } elseif ($carrier_name == 'smartsendswipbox') {

			//SwipBox shipping methods
            $shipping_methods = array(
            	'pickup'
            	);

            $method_names = array_flip(Mage::getModel('logistics/shippingMethods')->getMethodName());

            $methods_array = array();
            foreach ($shipping_methods as $key => $value) {

                if (array_key_exists($value, $method_names)) {
                    $methods_array[$method_names[$value]] = $method_names[$value];
                }
            }

            return $methods_array;
        } elseif ($carrier_name == 'smartsendpickup') {

			//Closest shipping methods
            $shipping_methods = array();
            if(Mage::helper('logistics/data')->isVconnetEnabled() == false) {
            	array_unshift($shipping_methods,'pickup');
            }

            $method_names = array_flip(Mage::getModel('logistics/shippingMethods')->getMethodName());

            $methods_array = array();
            foreach ($shipping_methods as $key => $value) {

                if (array_key_exists($value, $method_names)) {
                    $methods_array[$method_names[$value]] = $method_names[$value];
                }
            }

            return $methods_array;
        } elseif ($carrier_name == 'smartsendgls') {

			//GLS shipping methods
            $shipping_methods = array(
            	'private',
            	'privatehome',
            	'commercial'
            	);
            if(Mage::helper('logistics/data')->isVconnetEnabled() == false) {
            	array_unshift($shipping_methods,'pickup');
            }

            $method_names = array_flip(Mage::getModel('logistics/shippingMethods')->getMethodName());

            $methods_array = array();
            foreach ($shipping_methods as $key => $value) {

                if (array_key_exists($value, $method_names)) {
                    $methods_array[$method_names[$value]] = $method_names[$value];
                }
            }

            return $methods_array;
        }
    }

    public function getMethodName() {

        return array(
            'Pickuppoint' 			=> 'pickup',
            'Private to home' 		=> 'privatehome',
            'Commercial' 			=> 'commercial',
            'Private' 				=> 'private',
            'DPDclassic' 			=> 'dpdclassic',
            'DPDguarantee' 			=> 'dpdguranty',
            'Valuemail' 			=> 'valuemail',
            'Valuemailfirstclass'	=> 'valuemailfirstclass',
            'Valuemaileconomy' 		=> 'valuemaileconomy',
            'Maximail' 				=> 'maximail',
            'Commercial Bulksplit'	=> 'commercial_bulksplit',
            'Private Bulksplit' 	=> 'private_bulksplit',
            'Privatehome Bulksplit' => 'privatehome_bulksplit',
            'Private samsending' 	=> 'privatesamsending',
            'Private priority'		=> 'privatepriority',
            'Private economy'		=> 'privateeconomy'
        );
    }

    public function getTitleValue($opt) {

        if ($opt == "Private") {   //getting title of the shipping method
            $label = "Privat";
        } elseif ($opt == "Commercial") {
            $label = "Erhverv";
        } else {
            $label = $opt;
        }

        return $label;
    }

    public function checkShippingFee($carrier, $shipping_country) {    //cheking shipping fees for the shipping method
        $orderPrice = Mage::getModel('checkout/session')->getQuote()->getGrandTotal();          //getting order total
        $orderWeight = Mage::registry('ordweight');                       //getting order  weight

        if (Mage::getStoreConfig('carriers/' . $carrier . '/price') != "") {
            $pickupShippingRates = unserialize(Mage::getStoreConfig('carriers/' . $carrier . '/price', Mage::app()->getStore()));                   //unserializing the shipping rates from the shipping rate table
        }
        
        $cheapestexpensive = Mage::getStoreConfig('carriers/' . $carrier . '/cheapestexpensive', Mage::app()->getStore());                  // get the Cheapest or most expensive for the admin system config
		if (!$cheapestexpensive) {
    		$cheapestexpensive = 0;
        }
        
        //This array will contain the valid shipping methods                
		$shippingmethods = array();  
        
        if (is_array($pickupShippingRates)) {

            foreach ($pickupShippingRates as $pickupShippingRate) {
                $countries = explode(',', $pickupShippingRate['countries']);
                $countries = array_map("strtoupper", $countries);

				if(in_array(strtoupper($shipping_country), $countries)
					&& (float)$pickupShippingRate['orderminprice'] <= (float)$orderPrice && (float)$pickupShippingRate['ordermaxprice'] >= (float)$orderPrice
					&& (float)$pickupShippingRate['orderminweight'] <= (float)$orderWeight && (float)$pickupShippingRate['ordermaxweight'] >= (float)$orderWeight
					) {
					
					// The shipping rate is valid.
						
					if(isset($shippingmethods[$pickupShippingRate['methods']]) && $shippingmethods[$pickupShippingRate['methods']] != '') {
						//There is already a shipping method with the name in the array of valid shipping methods.
						if ( (int)$cheapestexpensive == 0 && ( (float) $shippingmethods[$pickupShippingRate['methods']] > (float) $pickupShippingRate['pickupshippingfee'] )) {
							//This method is cheaper and will override existing shipping method
							$shippingmethods[$pickupShippingRate['methods']] = $pickupShippingRate['pickupshippingfee'];
						} elseif ( (int)$cheapestexpensive == 1 && ( (float) $shippingmethods[$rates['methods']] < (float) $pickupShippingRate['pickupshippingfee'] )) {
							//This method is more expensive and will override existing shipping method
							$shippingmethods[$pickupShippingRate['methods']] = $pickupShippingRate['pickupshippingfee'];
						}
					} else {
						//Add the shipping method to the array of valid methods.
						$shippingmethods[$pickupShippingRate['methods']] = $pickupShippingRate['pickupshippingfee'];
					}
			
                }
            }
        }
        return $shippingmethods;
    }

    public function getLabel() {     //generate label method
        // Dummy function that is not used
    }

    public function getReturnLabel() {     //generate label method
        // Dummy function that is not used 
    }

}
