<?php
 /**
 * GoMage LightCheckout Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2015 GoMage (http://www.gomage.com)
 * @author       GoMage
 * @license      http://www.gomage.com/license-agreement/  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 5.9
 * @since        Class available since Release 2.0
 */

class Smartsend_Logistics_Model_System_Validateuser extends Mage_Adminhtml_Block_System_Config_Form_Field {

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
    	
    	$r = Mage::getStoreConfig('gomage_activation/lightcheckout/ar');
    	
    	$validation = false;
    	
    	if($validation) {
    		return sprintf('<strong class="required">%s</strong>', $this->__('User account verified'));
    	
    	} else {
    		return sprintf('<strong class="required">%s</strong>', $this->__('Please enter a valid key'));
    	}
    	
    }
}