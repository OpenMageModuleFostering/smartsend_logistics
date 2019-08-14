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
 * @folder		/app/code/community/Smartsend/Logistics/Model/Rewrite/Sales/Total/Quote/Shipping.php
 * @category	Smartsend
 * @package		Smartsend_Logistics
 * @author		Anders Bilfeldt
 * @url			www.smartsend.dk
 */

class Smartsend_Logistics_Model_Rewrite_Sales_Total_Quote_Shipping  extends Mage_Tax_Model_Sales_Total_Quote_Shipping
{
    
    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        
        $this->_setAddress($address);
        /**
         * Reset amounts
         */
        $this->_setAmount(0);
        $this->_setBaseAmount(0);
       
        
        $calc               = $this->_calculator;
        $store              = $address->getQuote()->getStore();
        $storeTaxRequest    = $calc->getRateOriginRequest($store);
        $addressTaxRequest  = $calc->getRateRequest(
            $address,
            $address->getQuote()->getBillingAddress(),
            $address->getQuote()->getCustomerTaxClassId(),
            $store
        );

        $shippingTaxClass = $this->_config->getShippingTaxClass($store);
        
        
        //custom code - start
        $shipping_method = Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress()->getShippingMethod();
        
        if(substr($shipping_method, 0, strlen('smartsend')) === 'smartsend') {
        	$carrier = explode('_',$shipping_method);
        	$smartsend_carrier = $carrier['0'];
        
        	$exclude_tax = Mage::getStoreConfig("carriers/".$smartsend_carrier."/excludetax");
    
			if($exclude_tax) {
			  $shippingTaxClass=0; 
			}
		}
        //custom code - end
     
        $storeTaxRequest->setProductClassId($shippingTaxClass);
        $addressTaxRequest->setProductClassId($shippingTaxClass);

        $priceIncludesTax = $this->_config->shippingPriceIncludesTax($store);
        if ($priceIncludesTax) {
            $this->_areTaxRequestsSimilar = $calc->compareRequests($addressTaxRequest, $storeTaxRequest);
        }

        $shipping           = $taxShipping = $address->getShippingAmount();
        $baseShipping       = $baseTaxShipping = $address->getBaseShippingAmount();
        $rate               = $calc->getRate($addressTaxRequest);
        if ($priceIncludesTax) {
            if ($this->_areTaxRequestsSimilar) {
                $taxExact       = $calc->calcTaxAmount($shipping, $rate, true, false);
                $baseTaxExact   = $calc->calcTaxAmount($baseShipping, $rate, true, false);
                $taxShipping    = $shipping;
                $baseTaxShipping = $baseShipping;
                $shippingExact  = $shipping - $taxExact;
                $baseShippingExact = $baseShipping - $baseTaxExact;
                $taxable        = $taxShipping;
                $baseTaxable    = $baseTaxShipping;
                $isPriceInclTax = true;
                $address->setTotalAmount('shipping', $shippingExact);
                $address->setBaseTotalAmount('shipping', $baseShippingExact);
            } else {
                $storeRate      = $calc->getStoreRate($addressTaxRequest, $store);
                $storeTax       = $calc->calcTaxAmount($shipping, $storeRate, true, false);
                $baseStoreTax   = $calc->calcTaxAmount($baseShipping, $storeRate, true, false);
                $shipping       = $calc->round($shipping - $storeTax);
                $baseShipping   = $calc->round($baseShipping - $baseStoreTax);
                $tax            = $this->_round($calc->calcTaxAmount($shipping, $rate, false, false), $rate, false);
                $baseTax        = $this->_round(
                    $calc->calcTaxAmount($baseShipping, $rate, false, false), $rate, false, 'base');
                $taxShipping    = $shipping + $tax;
                $baseTaxShipping = $baseShipping + $baseTax;
                $taxable        = $shipping;
                $baseTaxable    = $baseShipping;
                $isPriceInclTax = false;
                $address->setTotalAmount('shipping', $shipping);
                $address->setBaseTotalAmount('shipping', $baseShipping);
            }
        } else {
            $tax            = $this->_round($calc->calcTaxAmount($shipping, $rate, false, false), $rate, false);
            $baseTax        = $this->_round(
                $calc->calcTaxAmount($baseShipping, $rate, false, false), $rate, false, 'base');
            $taxShipping    = $shipping + $tax;
            $baseTaxShipping = $baseShipping + $baseTax;
            $taxable        = $shipping;
            $baseTaxable    = $baseShipping;
            $isPriceInclTax = false;
            $address->setTotalAmount('shipping', $shipping);
            $address->setBaseTotalAmount('shipping', $baseShipping);
        }
        $address->setShippingInclTax($taxShipping);
        $address->setBaseShippingInclTax($baseTaxShipping);
        $address->setShippingTaxable($taxable);
        $address->setBaseShippingTaxable($baseTaxable);
        $address->setIsShippingInclTax($isPriceInclTax);
        if ($this->_config->discountTax($store)) {
            $address->setShippingAmountForDiscount($taxShipping);
            $address->setBaseShippingAmountForDiscount($baseTaxShipping);
        }
        return $this;
       
    }

}

