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
 * @folder		/app/code/community/Smartsend/Logistics/sql/logistics_setup/mysql4-install-7.0.1.php
 * @category	Smartsend
 * @package		Smartsend_Logistics
 * @author		Anders Bilfeldt
 * @url			www.smartsend.dk
 */
$installer = $this;

$installer->startSetup();            //start setup

$installer->run("
    DROP TABLE IF EXISTS {$this->getTable('order_shipping_pickup')};               
    DROP TABLE IF EXISTS {$this->getTable('order_shipping_postdanmark')};
	DROP TABLE IF EXISTS {$this->getTable('order_shipping_posten')};
    DROP TABLE IF EXISTS {$this->getTable('order_shipping_swipbox')};
    DROP TABLE IF EXISTS {$this->getTable('order_shipping_bring')};
    DROP TABLE IF EXISTS {$this->getTable('order_shipping_gls')};
	CREATE TABLE IF NOT EXISTS {$this->getTable('smartsend_pickup')} (
	  `id` int(11) unsigned NOT NULL auto_increment,
	  `order_id` int(11) NOT NULL,
	  `store` varchar(255) NOT NULL default '',
          `pick_up_id` varchar(255) NOT NULL default '',
          `company` varchar(255) NOT NULL default '',
          `street` varchar(255) NOT NULL default '',
          `city` varchar(255) NOT NULL default '',
          `zip` varchar(255) NOT NULL default '',
          `country` varchar(255) NOT NULL default '',
          `carrier` varchar(255) NOT NULL default '',
	  PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");                       //creating table

/* standard values for the table rate */

for ($i = 0; $i < 6; $i++) {

    switch ($i) {                      //shipping method case
        case 0:
            $carrier = "groups[smartsendpickup]";
            break;
        case 1:
            $carrier = "groups[smartsendpostdanmark]";
            break;
        case 2:
            $carrier = "groups[smartsendbring]";
            break;
        case 3:
            $carrier = "groups[smartsendswipbox]";
            break;
        case 4:
            $carrier = "groups[smartsendgls]";
            break;
        case 5:
            $carrier = "groups[smartsendposten]";
            break;
    }

    $shippingmethods = Mage::getModel('logistics/shippingMethods')->getOptionArray($carrier);    //getting shipping methods  for carriers

    $priceResult = array();

    $shippingArray_NotStandard = array('DPDguranty', 'Valuemail', 'Valuemailfirstclass', 'Valuemaileconomy', 'Maximail');        //shipping methods not having standard settings

    foreach ($shippingmethods as $key => $value) {

        if (!in_array($value, $shippingArray_NotStandard)) {

            $millisecond = round(microtime(true) * 1000);
            $id = '_' . $millisecond . '_' . rand(100, 999);        //ids for the values

            $defaultResult = array();
            $defaultResult['orderminprice'] = 0;
            $defaultResult['ordermaxprice'] = 100000;
            $defaultResult['orderminweight'] = 0;
            $defaultResult['ordermaxweight'] = 100000;
            $defaultResult['pickupshippingfee'] = 10;
            $defaultResult['countries'] = 'DK';
            $defaultResult['_id'] = $id;
            $defaultResult['methods'] = $key;

            if ($i != 0) {
                $Title = Mage::getModel('logistics/shippingMethods')->getTitleValue($key);          //getting title value of the shipping method
                $defaultResult['method_name'] = $Title;
            }

            $priceResult[$defaultResult['_id']] = $defaultResult;

		}
	}
	
	if ($i == 0) {
		$path = 'carriers/smartsendpickup/price';
	} elseif ($i == 1) {
		$path = 'carriers/smartsendpostdanmark/price';
	} elseif ($i == 2) {
		$path = 'carriers/smartsendbring/price';
	} elseif ($i == 3) {
		$path = 'carriers/smartsendswipbox/price';
	} elseif ($i == 4) {
		$path = 'carriers/smartsendgls/price';
	} elseif ($i == 5) {
		$path = 'carriers/smartsendposten/price';
	}

	$data = array();
	$data['value'] = serialize($priceResult);
	$data['path'] = $path;
	$data['scope'] = 'default';
	$data['scope_id'] = 0;

	$table = Mage::getSingleton('core/resource')->getTableName('core_config_data');    //checking the table rate values in the table
	$writeAdapter = Mage::getSingleton('core/resource')->getConnection('core_write');
	$readAdapter = Mage::getSingleton('core/resource')->getConnection('core_read');
	$query = 'SELECT * FROM ' . $table . ' where path = "' . $data["path"] . '"';
	$results = $readAdapter->fetchAll($query);

    if (!$results && !empty($priceResult)) {              //if table rate value is empty for that carrier
        $writeAdapter->insert($table, $data);
    }
}
$installer->endSetup();        //end setup
