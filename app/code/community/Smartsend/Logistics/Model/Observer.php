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
 * @folder		/app/code/community/Smartsend/Logistics/Model/Observer.php
 * @category	Smartsend
 * @package		Smartsend_Logistics
 * @author		Anders Bilfeldt
 * @url			www.smartsend.dk
 */
class Smartsend_Logistics_Model_Observer extends Varien_Object {

    public function saveShippingMethod($evt) {             //saving shipping method
        $request = $evt->getRequest();
        $quote = $evt->getQuote();
        $shippingMethod = $quote->getShippingAddress()->getShippingMethod();       //getting shipping method

        $pickup_t = $request->getParam('shipping_pickup', false);
        if ($shippingMethod == 'smartsendswipbox_pickup') {
            $pickup['store'] = $pickup_t['store']['smartsendswipbox_pickup'];
        } elseif ($shippingMethod == 'smartsendpostdanmark_pickup') {
            $pickup['store'] = $pickup_t['store']['smartsendpostdanmark_pickup'];
        } elseif ($shippingMethod == 'smartsendposten_pickup') {
            $pickup['store'] = $pickup_t['store']['smartsendposten_pickup'];
        } elseif ($shippingMethod == 'smartsendgls_pickup') {
            $pickup['store'] = $pickup_t['store']['smartsendgls_pickup'];
        } elseif ($shippingMethod == 'smartsendbring_pickup') {
            $pickup['store'] = $pickup_t['store']['smartsendbring_pickup'];
        } elseif ($shippingMethod == 'smartsendpickup_pickup') {
            $pickup['store'] = $pickup_t['store']['smartsendpickup_pickup'];
        }

        $quote_id = $quote->getId();
        $data = array($quote_id => $pickup);

        if ($pickup) {
            Mage::getSingleton('checkout/session')->setPickup($data);                //setting pickup
        }
    }

    public function saveOrderAfter($evt) {
        $order = $evt->getOrder();                  //getting order
        $quote = $evt->getQuote();
        $quote_id = $quote->getId();
        $pickup = Mage::getSingleton('checkout/session')->getPickup();         //getting picup
        $shippingMethod = $quote->getShippingAddress()->getShippingMethod(); //getting shipping method

        if (isset($pickup[$quote_id])) {

            $temp = $pickup[$quote_id];
            $store = unserialize($temp['store']);
            if ($shippingMethod == 'smartsendswipbox_pickup') {
                $pickupModel = Mage::getModel('logistics/swipbox');
                $data['pick_up_id'] = $store['pick_up_id'];
            } elseif ($shippingMethod == 'smartsendpostdanmark_pickup') {
                $pickupModel = Mage::getModel('logistics/postdanmark');
                $data['pick_up_id'] = $store['pick_up_id'];
            } elseif ($shippingMethod == 'smartsendposten_pickup') {
                $pickupModel = Mage::getModel('logistics/posten');
                $data['pick_up_id'] = $store['pick_up_id'];
            } elseif ($shippingMethod == 'smartsendgls_pickup') {
                $pickupModel = Mage::getModel('logistics/gls');
                $data['pick_up_id'] = $store['pick_up_id'];
            } elseif ($shippingMethod == 'smartsendbring_pickup') {
                $pickupModel = Mage::getModel('logistics/bring');
                $data['pick_up_id'] = $store['pick_up_id'];
            } elseif ($shippingMethod == 'smartsendpickup_pickup') {
                $pickupModel = Mage::getModel('logistics/pickup');
                $data['pick_up_id'] = $store['pick_up_id'];
            }

            $data['store'] = Mage::app()->getStore()->getFrontendName();
            $data['company'] = $store['company'];
            $data['street'] = $store['street'];
            $data['city'] = $store['city'];
            $data['zip'] = $store['zipcode'];
            $data['country'] = $store['country'];
            $data['carrier'] = $store['shippingMethod'];
            $data['order_id'] = $order->getId();

            if (isset($pickupModel)) {
                $pickupModel->setData($data);
                $pickupModel->save();
            }
        } elseif(strstr($shippingMethod,"smartsend")!="") {
            
            $carrier_name=explode("_",$shippingMethod);
            $carrier = substr($carrier_name[0], 9);

            $pickupModel = Mage::getModel('logistics/'.$carrier);
            $data['carrier'] = ucfirst($carrier);
            $data['pick_up_id'] = '000';

            $data['store'] = Mage::app()->getStore()->getFrontendName();
            $data['company'] = '';
            $data['street'] = '';
            $data['city'] = '';
            $data['zip'] = '';
            $data['country'] = 'DK';
            $data['order_id'] = $order->getId();

            if (isset($pickupModel)) {
                $pickupModel->setData($data);
                $pickupModel->save();
            }
        }
    }

    public function loadOrderAfter($evt) {
        $order = $evt->getOrder();
        if ($order->getId()) {
            $order_id = $order->getId();
            $pickupCollection = Mage::getModel('logistics/pickup')->getCollection();          //get pickup collection from the pickup table
            $pickupCollection->addFieldToFilter('order_id', $order_id);
            $pickup = $pickupCollection->getFirstItem();
            $order->setPickupObject($pickup);
        }
    }

    public function loadQuoteAfter($evt) {
        $quote = $evt->getQuote();
        if ($quote->getId()) {
            $data = $quote->getData();

            $weight = 0;
            foreach ($quote->getAllItems() as $item) {
                $product = $item->getProduct();
                if ($product->getTypeId() == 'simple') {
                    $weight = $weight + $product->getWeight() * $product->getStockItem()->getOrderedItems();         //calculating total weight of the ordered items
                }
            }

            Mage::unregister('ordsubtotal');
            Mage::unregister('ordweight');

            Mage::register('ordsubtotal', $data['subtotal']);
            Mage::register('ordweight', $weight);

            $quote_id = $quote->getId();
            $pickup = Mage::getSingleton('checkout/session')->getPickup();
            if (isset($pickup[$quote_id])) {
                $data = $pickup[$quote_id];
                $quote->setPickupData($data);
            }
        }
    }
    
    /* Used to add a button to the top menu of the order_view_info page */
    public function adminhtmlWidgetContainerHtmlBefore($event) {
		$block = $event->getBlock();

		if ($block instanceof Mage_Adminhtml_Block_Sales_Order_View) {
			$message = Mage::helper('sales')->__('Are you sure you want to do this?');
			$block->addButton('generate_label', array(
				'label'     => Mage::helper('sales')->__('Generate label'),
				'onclick'   => "setLocation('{$block->getUrl('logistics/logistics/label')}')",//if message use: "confirmSetLocation('{$message}', '{$block->getUrl('*/yourmodule/crazy')}')",
				'class'     => 'go'
			));
		}
	}
	
	/* Used to add a mass action to the sales order grid */
	public function addMassAction($event) {
		$block = $event->getEvent()->getBlock();
		if(get_class($block) =='Mage_Adminhtml_Block_Widget_Grid_Massaction'
			&& $block->getRequest()->getControllerName() == 'sales_order')
		{
			// Add 'generate label' action
			$block->addItem('mass_generate_label', array(
				'label' => Mage::helper('sales')->__('Generate label'),
				'url' => $block->getUrl('logistics/logistics/labelMass')
			));
			
			// Add 'generate return label' action
			$block->addItem('mass_generate_return_label', array(
				'label' => Mage::helper('sales')->__('Generate return label'),
				'url' => $block->getUrl('logistics/logistics/labelreturnMass')
			));
		}
	}

}
