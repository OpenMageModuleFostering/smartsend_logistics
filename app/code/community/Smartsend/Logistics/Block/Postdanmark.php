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
 * @folder		/app/code/community/Smartsend/Logistics/Block/Postdanmark.php
 * @category	Smartsend
 * @package		Smartsend_Logistics
 * @author		Anders Bilfeldt
 * @url			www.smartsend.dk
 */
class Smartsend_Logistics_Block_Postdanmark extends Mage_Checkout_Block_Onepage_Shipping_Method_Available {

    public function __construct() {
        $this->setTemplate('logistics/pickup.phtml');
    }

    public function getPickupData() {
        $checkOut = Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress();         //getting shipping address from checkout quote
        $street = $checkOut->getStreet();
        $street = implode(' ', $street);             // splitting the street by " "(space)
        $city = $checkOut->getCity();
        $postcode = $checkOut->getPostcode();
        $country = $checkOut->getCountry();
        $result = Mage::getSingleton('logistics/carrier_postdanmark')->_post($street, $city, $postcode, $country);           // get the pickup points for the postdanmark carrier

        $output = json_decode($result->response, true);              // decoding the pickup points data into array 
        if (!$output) {                                             // if no pickup data return false
            return FALSE;
        }
        $servicePoints = $output;

        $format = Mage::getStoreConfig('carriers/smartsendpostdanmark/listformat');            //get the address format from the admin system config
        for ($i = 0; $i < count($servicePoints); $i++) {
            if (!isset($servicePoints[$i])) {
                break;
            }
            $addressData = $this->getaddressData($servicePoints[$i]);       //getting address data form the pickup points response 
            switch ($format) {
                case 1:
                    $resultData[$addressData['servicePointId']] = array(
                        'company' => $addressData['company'],
                        'street' => $addressData['street'],
                        'zip_code' => $addressData['zipcode'],
                        'city' => $addressData['city']
                    );
                    break;
                case 2:
                    $resultData[$addressData['servicePointId']] = array(
                        'company' => $addressData['company'],
                        'street' => $addressData['street'],
                        'zipcode' => $addressData['zipcode']
                    );
                    break;
                case 3:
                    $resultData[$addressData['servicePointId']] = array(
                        'company' => $addressData['company'],
                        'street' => $addressData['street'],
                        'city' => $addressData['city']
                    );
                default:
                    $resultData[$addressData['servicePointId']] = array(
                        'company' => $addressData['company'],
                        'street' => $addressData['street'],
                    );
                    break;
            }
        }
        if (!isset($resultData)) {
            $resultData = "";
        }
        return $resultData;
    }

    protected function getaddressData($servicePoint) {
        $data['pick_up_id'] = $servicePoint['pickupid'];
        $data['company'] = implode(" ", array_filter(array($servicePoint['name1'], $servicePoint['name2'])));          //joining the address data 
        $data['city'] = $servicePoint['city'];
        $data['street'] = implode(" ", array_filter(array($servicePoint['address1'], $servicePoint['address2'])));
        $data['zipcode'] = $servicePoint['zip'];
        $data['shippingMethod'] = $servicePoint['carrier'];
        $data['country'] = $servicePoint['country'];
        $ser = serialize($data);
        $data['servicePointId'] = $ser;
        return $data;
    }

}
