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
 * @folder		/app/code/community/Smartsend/Logistics/Block/Adminhtml/Shipping.php
 * @category	Smartsend
 * @package		Smartsend_Logistics
 * @author		Anders Bilfeldt
 * @url			www.smartsend.dk
 */
class Smartsend_Logistics_Block_Adminhtml_Shipping extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract {

    public function __construct() {      // create columns for the table rate for the carriers(other than pickup) system config 
        $this->addColumn('methods', array(
            'label' => $this->__('Methods'),
            'size' => 6,
        ));
        $this->addColumn('orderminprice', array(
            'label' => $this->__('Min Price'),
            'size' => 6,
        ));
        $this->addColumn('ordermaxprice', array(
            'label' => $this->__('Max Price'),
            'size' => 6,
        ));
        $this->addColumn('orderminweight', array(
            'label' => $this->__('Min Weight'),
            'size' => 6,
        ));
        $this->addColumn('ordermaxweight', array(
            'label' => $this->__('Max Weight'),
            'size' => 6,
        ));
        $this->addColumn('pickupshippingfee', array(
            'label' => $this->__('Shipping Fee'),
            'size' => 6,
        ));

        $this->addColumn('countries', array(
            'label' => $this->__('Countries'),
            'size' => 6,
        ));

        $this->addColumn('method_name', array(
            'label' => $this->__('Method Name'),
            'size' => 6,
        ));

        $this->_addAfter = false;
        $this->_addButtonLabel = $this->__('Add Rate');

        parent::__construct();
        $this->setTemplate('logistics/array_dropdown.phtml');    //get the dropdown for the price shipping table for carriers other than pickup carrier
    }

    protected function _renderCellTemplate($columnName) {  //inserts the value to the created columns above
        if (empty($this->_columns[$columnName])) {          //checking the column names 
            throw new Exception('Wrong column name specified.');
        }
        $column = $this->_columns[$columnName];
        $inputName = $this->getElement()->getName() . '[#{_id}][' . $columnName . ']';

        if ($columnName == 'methods') {                    //for column name methods
            $shippingmethods = Mage::getModel('logistics/shippingMethods')->getOptionArray($inputName);   //getting the list of shipping methods for the column methods

            $rendered = '<select style="width:100px;" name="' . $inputName . '">';

            foreach ($shippingmethods as $att => $name) {
                $rendered .= '<option value="' . $att . '">' . $name . '</option>';
            }
            $rendered .= '</select>';
        } else {                       //for other column than methods
            return '<input type="text" class="required-entry" name="' . $inputName . '" value="#{' . $columnName . '}" ' . ($column['size'] ? 'size="' . $column['size'] . '"' : '') . '/>';
        }
        return $rendered;
    }

}
