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
 * @folder		/app/code/community/Smartsend/Logistics/Model/System/Listformat.php
 * @category	Smartsend
 * @package		Smartsend_Logistics
 * @author		Anders Bilfeldt
 * @url			www.smartsend.dk
 */

class Smartsend_Logistics_Model_System_Listformat extends Mage_Core_Model_Config_Data {

    public function toOptionArray() {                 //address list format for the admin system config
        $opt[1] = array('value' => 1, 'label' => Mage::helper('adminhtml')->__("#Company, #Street, #Zipcode #City"));
        $opt[2] = array('value' => 2, 'label' => Mage::helper('adminhtml')->__("#Company, #Street, #Zipcode"));
        $opt[3] = array('value' => 3, 'label' => Mage::helper('adminhtml')->__("#Company, #Street, #City"));
        $opt[4] = array('value' => 4, 'label' => Mage::helper('adminhtml')->__("#Company, #Street"));
        return $opt;
    }

}
