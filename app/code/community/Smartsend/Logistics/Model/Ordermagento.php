<?php

/**
 * Smartsend_Logistics Order Magento class
 *
 * Create order objects that is included in the final Smart Send label API callout.
 *
 * LICENSE
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
 * @class		Smartsend_Logistics_Model_Ordermagento
 * @folder		/app/code/community/Smartsend/Logistics/Model/Ordermagento.php
 * @category	Smart Send
 * @package		Smartsend_Logistics
 * @author 		Smart Send ApS
 * @url			http://smartsend.dk/
 * @copyright	Copyright (c) Smart Send ApS (http://www.smartsend.dk)
 * @license		http://smartsend.dk/license
 * @since		Class available since Release 7.1.0
 * @version		Release: 7.1.3.0
 *
 *	// Order
 *	public function getShippingId()
 *	public function getOrderId()
 *	public function getOrderReference()
 *	public function getOrderPriceTotal()
 *	public function getOrderPriceShipping()
 *	public function getOrderPriceCurrency()
 *	public function getCustomerComment()
 *	public function getShippingAddress()
 *	public function getBillingAddress()
 *	public function getPickupDataSmartsend()
 *	public function getPickupDataVconnect()
 *	public function getFlexDeliveryNote()
 *	
 *	// Settings
 *	public function getSettingsPostdanmark()
 *	public function getSettingsPosten()
 *	public function getSettingsGls()
 *	public function getSettingsBring()
 *	protected function getSettingIncludeOrderComment
 *
 *	// Shipments
 *	public function getShipments()
 *	public function getShipmentTrace($shipment)
 *	public function getShipmentWeight($shipment)
 *	protected function getUnshippedItems()
 *	public function createShipment($tracking_number)
 *	protected function addShipment($shipment)
 *	protected function addParcelWithUnshippedItems()
 *	protected function addItem($item)
 *	
 */
 
class Smartsend_Logistics_Model_Ordermagento extends Smartsend_Logistics_Model_Order {

	public $_errors = array(
		// Shipping
		2301	=>	'Unable to determine the shipping method used for return parcels',
		2302	=>	'Unable to determine the shipping carrier',
		2303	=>	'Unable to determine the shipping method',
		2304	=>	'Unable to determine carrier for pickup shipping method',
		2305	=>	'Unsupported carrier',
		2306	=>	'Unable to determine shipping method for carrier',
		2307	=>	'Unable to determine shipping carrier',
		2308	=>	'Unknown shipping carrier',
		2309	=>	'Unable to determine shipping method',
		// Order set
		2401	=>	'All packages have been shipped. No parcels without trace code exists. Remove existing tracecodes to re-generate labels.',
		2402	=>	'No items to ship',
		2403	=>	'No parcels to ship',
		// Order get
		2501	=>	'Trying to access pickup data for an order that is not a pickup point order',
		);
		
	public function __construct() {
	
		// Translate the error string of the $_error array
		foreach($this->_errors as $key=>$value) {
			$this->_errors[$key] = Mage::helper('logistics')->__($value);
		}
	}

/*****************************************************************************************
 * Order
 ****************************************************************************************/

	/**
	* 
	* Get shipping name/id
	* @return string
	*/
	public function getShippingId() {
	
		$shipMethod_id = $this->_order->getShippingMethod();
		
		return $shipMethod_id; //return unique id of shipping method
	
	}
 
 	/**
	* 
	* Get the order id (SQL id)
	* @return string
	*/
 	public function getOrderId() {
 	
		return $this->_order->getId();
		
 	}
 	
 	/**
	* 
	* Get the order refernce (the one the customer sees)
	* @return string
	*/
 	public function getOrderReference() {
 	
 		return $this->_order->getIncrementId();
 	
 	}
 	
 	/**
	* 
	* Get total price of order including tax
	* @return float
	*/
 	public function getOrderPriceTotal() {
 	
		return $this->_order->getGrandTotal();
		
 	}
 	
 	/**
	* 
	* Get shipping costs including tax
	* @return float
	*/
 	public function getOrderPriceShipping() {
 	
		return $this->_order->getShippingAmount();
		
	}
 	
 	/**
	* 
	* Get the currency used for the order
	* @return string
	*/
 	public function getOrderPriceCurrency() {
 	
		return $this->_order->getOrderCurrencyCode();
		
 	}
 	
 	/**
	* 
	* Get the comment that the user provided during checkout
	* @return string
	*/
 	public function getCustomerComment() {
 		$comments_collection_object = $this->_order->getStatusHistoryCollection(true);
		return $comments_collection_object->getLastItem()->getComment();
 	}
 	
 	/**
	* 
	* Get the shipping address information
	* @return array
	*/
 	public function getShippingAddress() {
 	
		return array(
			'receiverid'=> ($this->_order->getShippingAddress()->getId() != '' ? $this->_order->getShippingAddress()->getId() : 'guest-'.rand(100000,999999)),
			'company'	=> $this->_order->getShippingAddress()->getCompany(),
			'name1' 	=> $this->_order->getShippingAddress()->getName(),
			'name2'		=> null,
			'address1'	=> $this->_order->getShippingAddress()->getStreet(1),
			'address2'	=> $this->_order->getShippingAddress()->getStreet(2),
			'city'		=> $this->_order->getShippingAddress()->getCity(),
			'zip'		=> $this->_order->getShippingAddress()->getPostcode(),
			'country'	=> $this->_order->getShippingAddress()->getCountry_id(),
			'sms'		=> $this->_order->getShippingAddress()->getTelephone(),
			'mail'		=> $this->_order->getShippingAddress()->getEmail()
			);
			
 	}
 	
 	/**
	* 
	* Get the shipping address information
	* @return array
	*/
 	public function getBillingAddress() {
 	
		return array(
			'receiverid'=> ($this->_order->getBillingAddress()->getId() != '' ? $this->_order->getBillingAddress()->getId() : 'guest-'.rand(100000,999999)),
			'company'	=> $this->_order->getBillingAddress()->getCompany(),
			'name1' 	=> $this->_order->getBillingAddress()->getName(),
			'name2'		=> null,
			'address1'	=> $this->_order->getBillingAddress()->getStreet(1),
			'address2'	=> $this->_order->getBillingAddress()->getStreet(2),
			'city'		=> $this->_order->getBillingAddress()->getCity(),
			'zip'		=> $this->_order->getBillingAddress()->getPostcode(),
			'country'	=> $this->_order->getBillingAddress()->getCountry_id(),
			'sms'		=> $this->_order->getBillingAddress()->getTelephone(),
			'mail'		=> $this->_order->getBillingAddress()->getEmail()
			);
				
 	}
 	
 	/**
	* 
	* Get pickup data for a SmartSend delivery point
	* @return array
	*/	
	public function getPickupDataSmartsend() {
	
		$carrier = $this->getShippingCarrier();
		switch ($carrier) {
			case 'postdanmark':
				$pickupModel = Mage::getModel('logistics/postdanmark');
				break;
			case 'posten':
				$pickupModel = Mage::getModel('logistics/posten');
				break;
			case 'gls':
				$pickupModel = Mage::getModel('logistics/gls');
				break;
			case 'bring':
				$pickupModel = Mage::getModel('logistics/bring');
				break;
			default:
				throw new Exception( $this->_errors[2302] );
		}

		$order_id = $this->_order->getId();	//order id
		$pickupData = $pickupModel->getCollection()->addFieldToFilter('order_id', $order_id)->getFirstItem();        //pickup data 

		if ($pickupData->getData()) {
		
			return array(
				'id' 		=> $pickupData->getPickUpId()."-".time()."-".rand(9999,10000),
				'agentno'	=> $pickupData->getPickUpId(),
				'agenttype'	=> ($this->getShippingCarrier() == 'postdanmark' ? 'PDK' : null),
				'company' 	=> $pickupData->getCompany(),
				'name1' 	=> null,
				'name2' 	=> null,
				'address1'	=> $pickupData->getStreet(),
				'address2' 	=> null,
				'city'		=> $pickupData->getCity(),
				'zip'		=> $pickupData->getZip(),
				'country'	=> $pickupData->getCountry(),
				'sms' 		=> null,
				'mail' 		=> null,
				);

		} else {
			return null;
		}
		
	}
	
	/**
	* 
	* Get pickup data for a vConnect delivery point
	* @return array
	*/	
	public function getPickupDataVconnect() {

		if($this->_order->hasData('vconnect_postnord_data')){
        	$postnordShippingInfoArray = Mage::helper('core')->jsonDecode($this->_order->getData('vconnect_postnord_data'));
        	if(isset($postnordShippingInfoArray['data']['servicePointId']) && $postnordShippingInfoArray['data']['servicePointId'] != ''){
        		$pickuppoint = $postnordShippingInfoArray['data'];
        		
        		return array(
					'id' 		=> $pickuppoint['servicePointId']."-".time()."-".rand(9999,10000),
					'agentno'	=> $pickuppoint['servicePointId'],
					'agenttype'	=> ($this->getShippingCarrier() == 'postdanmark' ? 'PDK' : null),
					'company' 	=> $pickuppoint['name'],
					'name1' 	=> null,
					'name2' 	=> null,
					'address1'	=> $pickuppoint['deliveryAddress']['streetName'].($pickuppoint['deliveryAddress']['streetNumber'] != '' ? ' '.$pickuppoint['deliveryAddress']['streetNumber'] : ''),
					'address2' 	=> null,
					'city'		=> $pickuppoint['deliveryAddress']['city'],
					'zip'		=> $pickuppoint['deliveryAddress']['postalCode'],
					'country'	=> $pickuppoint['deliveryAddress']['countryCode'],
					'sms' 		=> null,
					'mail' 		=> null,
					);
					
        	} else {
        		return null;
        	}
		} else {
			$billing_address = $this->getShippingAddress();

			$pacsoftServicePoint 		= str_replace(' ', '', $billing_address['address2']); 	//remove spaces
			$pacsoftServicePointArray 	= explode(":",$pacsoftServicePoint); 			//devide into a array by :

			if ( isset($pacsoftServicePointArray) && ( strtolower($pacsoftServicePointArray[0]) == strtolower('ServicePointID') ) ||  strtolower($pacsoftServicePointArray[0]) == strtolower('Pakkeshop') ){
				$pickupData = array(
					'id' 		=> $pacsoftServicePointArray[1]."-".time()."-".rand(9999,10000),
					'agentno'	=> $pacsoftServicePointArray[1],
					'agenttype'	=> ($this->getShippingCarrier() == 'postdanmark' ? 'PDK' : null),
					'company' 	=> $billing_address['company'],
					'name1' 	=> $billing_address['name1'],
					'name2' 	=> $billing_address['name2'],
					'address1'	=> $billing_address['address1'],
					'address2' 	=> null,
					'city'		=> $billing_address['city'],
					'zip'		=> $billing_address['zip'],
					'country'	=> $billing_address['country'],
					'sms' 		=> null,
					'mail' 		=> null,
					);
		
				return $pickupData;
		
			} else {
				return null;
			}
		}
	
	}
	
	/**
	* 
	* Get the Flex delivery comment (where to place the parcel) from Mysql
	* @return string / null
	*/
	public function getFlexDeliveryNote() {
		if( $this->isSmartsend() ) {
			//This is a Smart Send order
			$flexModel = Mage::getModel('logistics/flex');
			$flexData = $flexModel->getCollection()->addFieldToFilter('order_id', $this->_order->getId())->getFirstItem();        //pickup data 
			if ($flexData->getData()) {
				if($flexData->getFlexnote() != '') {
					return $flexData->getFlexnote();
				} else {
					return null;
				}
			} else {
				return null;
			}
		} elseif( $this->isVconnect() ) {
			if($this->_order->hasData('vconnect_postnord_data')){
        		$postnordShippingInfoArray = Mage::helper('core')->jsonDecode($this->_order->getData('vconnect_postnord_data'));
			}
			if(isset($postnordShippingInfoArray['flexdelivery']) && $postnordShippingInfoArray['flexdelivery'] != '') {
				return $postnordShippingInfoArray['flexdelivery'];
			} else {
				return null;
			}
		} else {
			return null;
		}
	}
	
	/**
	* 
	* Get the start time for Smart Delivery from MySQL
	* @return string / null
	*/
	public function getSmartDeliveryTimeStart() {
		return null;
	}
	
	/**
	* 
	* Get the end time for Smart Delivery from MySQL
	* @return string / null
	*/
	public function getSmartDeliveryTimeEnd() {
		return null;
	}
	
/*****************************************************************************************
 * Settings
 ****************************************************************************************/
	
	/**
	* 
	* Get the settings for Post Danmark
	* @return array
	*/
	public function getSettingsPostdanmark() {
		
		return array(
			'notemail'			=> Mage::getStoreConfig('carriers/smartsendpostdanmark/notemail'),
			'notesms'			=> Mage::getStoreConfig('carriers/smartsendpostdanmark/notesms'),
			'prenote'			=> Mage::getStoreConfig('carriers/smartsendpostdanmark/prenote'),
			'prenote_from'		=> Mage::getStoreConfig('carriers/smartsendpostdanmark/prenote_sender'),
			'prenote_receiver'	=> Mage::getStoreConfig('carriers/smartsendpostdanmark/prenote_receiver'),
			'prenote_message'	=> Mage::getStoreConfig('carriers/smartsendpostdanmark/prenote_message'),
			'flex'				=> null,
			'format'			=> Mage::getStoreConfig('carriers/smartsendpostdanmark/format'),
			'quickid'			=> Mage::getStoreConfig('carriers/smartsendpostdanmark/quickid'),
			'waybillid'			=> Mage::getStoreConfig('carriers/smartsendpostdanmark/waybillid'),
			'return'			=> Mage::getStoreConfig('carriers/smartsendpostdanmark/return'),
			);
			
	}
	
	/**
	* 
	* Get the settings for Posten
	* @return array
	*/
	public function getSettingsPosten() {
	
		return array(
			'notemail'			=> Mage::getStoreConfig('carriers/smartsendposten/notemail'),
			'notesms'			=> Mage::getStoreConfig('carriers/smartsendposten/notesms'),
			'prenote'			=> Mage::getStoreConfig('carriers/smartsendposten/prenote'),
			'prenote_from'		=> Mage::getStoreConfig('carriers/smartsendposten/prenote_sender'),
			'prenote_receiver'	=> Mage::getStoreConfig('carriers/smartsendposten/prenote_receiver'),
			'prenote_message'	=> Mage::getStoreConfig('carriers/smartsendposten/prenote_message'),
			'flex'				=> null,
			'format'			=> Mage::getStoreConfig('carriers/smartsendposten/format'),
			'quickid'			=> Mage::getStoreConfig('carriers/smartsendposten/quickid'),
			'waybillid'			=> Mage::getStoreConfig('carriers/smartsendposten/waybillid'),
			'return'			=> Mage::getStoreConfig('carriers/smartsendposten/return'),
			);
			
	}
	
	/**
	* 
	* Get the settings for GLS
	* @return array
	*/
	public function getSettingsGls() {
	
		return array(
			'notemail'			=> Mage::getStoreConfig('carriers/smartsendgls/notemail'),
			'notesms'			=> Mage::getStoreConfig('carriers/smartsendgls/notesms'),
			'prenote'			=> null,
			'prenote_from'		=> null,
			'prenote_receiver'	=> null,
			'prenote_message'	=> null,
			'flex'				=> null,
			'format'			=> null,
			'quickid'			=> null,
			'waybillid'			=> null,
			'return'			=> Mage::getStoreConfig('carriers/smartsendgls/return'),
			);
			
	}
	
	/**
	* 
	* Get the settings for Bring
	* @return array
	*/
	public function getSettingsBring() {
	
		return array(
			'notemail'			=> Mage::getStoreConfig('carriers/smartsendbring/notemail'),
			'notesms'			=> Mage::getStoreConfig('carriers/smartsendbring/notesms'),
			'prenote'			=> null,
			'prenote_from'		=> null,
			'prenote_receiver'	=> null,
			'prenote_message'	=> null,
			'flex'				=> null,
			'format'			=> null,
			'quickid'			=> null,
			'waybillid'			=> null,
			'return'			=> Mage::getStoreConfig('carriers/smartsendbring/return'),
			);
			
	}
	
	/**
	* 
	* Should the order comment be included as freetext on label
	*
	* @return boolean
	*/
 	protected function getSettingIncludeOrderComment() {
 		return Mage::getStoreConfig('carriers/smartsend/includeordercomment');
 	}
	
/*****************************************************************************************
 * Shipments
 ****************************************************************************************/

	/**
	* 
	* Get the shipments for the order if any
	* @return array
	*/
	public function getShipments() {

		if( $this->_order->hasShipments() ) {
			return $this->_order->getShipmentsCollection();
		} else {
			return null;
		}
	}

	/**
	* 
	* Get the Track&Trace code for a given shipment
	* @return string
	*/
	public function getShipmentTrace($shipment) {

		$tracknums = array();
		foreach($shipment->getAllTracks() as $tracknum) {
			$tracknums[]=$tracknum->getNumber();
		}

		if(empty($tracknums)) {
			return false;
		} else {
			return true;
		}
			
	}
	
	/**
	* 
	* Get the weight (in kg) of a given shipment
	* @return float
	*/
	public function getShipmentWeight($shipment) {
	
		$weight = 0;
		foreach($shipment->getAllItems() as $eachShipmentItem) {
			$itemWeight = $eachShipmentItem->getWeight();
			$itemQty    = $eachShipmentItem->getQty();
			$rowWeight  = $itemWeight*$itemQty;

			$weight = $weight + $rowWeight;
		}
		
		if(isset($weight) && $weight > 0) {
			return $weight;
		} else {
			return null;
		}
	}
	
	/**
	* 
	* Get the unshipped items of the order
	* @return array
	*/
	protected function getUnshippedItems() {
	
		$items = array();
		foreach($this->_order->getAllItems() as $eachOrderItem){
			$Itemqty = 0;
			$Itemqty = $eachOrderItem->getQtyOrdered()
					- $eachOrderItem->getQtyShipped()
					- $eachOrderItem->getQtyRefunded()
					- $eachOrderItem->getQtyCanceled();
			if($Itemqty > 0) {
				$items[] = array(
					'id'		=> $eachOrderItem->getId(),
					'productid'	=> $eachOrderItem->getProductId(),
					'quantity'	=> $Itemqty
					);
			}
		}

		if(!empty($items)) {
			return $items;
		} else {
			return null;
		}

	}
	
	/**
	* 
	* Create a parcel containing all unshipped items.
	* Add the parcel to the request.
	*/
	public function createShipment($tracking_number=null) {

		$order = $this->_order;
		$unshipped_items = $this->getUnshippedItems();
		
		// Create array with id's and quantities of unshipped items
		if(is_array($unshipped_items)) {
			$itemsarray = array();
			foreach($unshipped_items as $item) {
				$itemsarray[$item['id']] = $item['quantity'];
			}
		} else {
			$itemsarray = null;
		}

		/* check if order shipment is prossiable or not */
		//An email is only send if a tracking number is returned
		$email = Mage::getStoreConfig('sales_email/shipment/enabled'); //Setting from: System->Configuration->Sales Emails->Shipment->Enabled
		$includeComment = false;
		$comment = "";

		if( $order->canShip() && !empty($itemsarray) ) {
			// @var $shipment Mage_Sales_Model_Order_Shipment
			// prepare to create shipment
			$shipment = Mage::getModel('sales/service_order', $order)->prepareShipment($itemsarray);
			if ($shipment) {
				$shipment->register();
				
				//Add a comment. Second parameter is whether or not to email the comment to the user
				$shipment->addComment($comment, $email && $includeComment);
				
				// Set the order status as 'Processing'
				$order->setIsInProcess($email);
				$order->addStatusHistoryComment(Mage::helper('logistics')->__('Shipment generated by Smart Send Logistics'), false);
				
				try {
					$transactionSave = Mage::getModel('core/resource_transaction')
							->addObject($shipment)
							->addObject($order)
							->save();
					
					//Set order status as complete
					//$order->setData('state', Mage_Sales_Model_Order::STATE_COMPLETE);
					//$order->setData('status', Mage_Sales_Model_Order::STATE_COMPLETE);
					//$order->save();
					
				} catch (Mage_Core_Exception $e) {
					throw new Exception($this->_errors[2402].': '.$e);
				}
				
				if($tracking_number) {
					//Get the carrier code
 					$carrier_code = 'smartsend'.$this->getShippingCarrier();
					
					// Add tracking information to the shipment
					//$shipment = Mage::getModel('sales/order_shipment')->load($shipment_number);
					$track = Mage::getModel('sales/order_shipment_track')
						->setShipment($shipment)
						->setData('title', $order->getShippingDescription())
						->setData('number', $tracking_number)
						->setData('carrier_code', $carrier_code)
						->setData('order_id', $shipment->getData('order_id'))
						->save();
				}
				
				//Email the customer that the order is sent
				if(!$shipment->getEmailSent() && $email){
					$shipment->sendEmail(true,($includeComment ? $comment : ''));
					$shipment->setEmailSent(true);
					$shipment->save();                          
				}
				
			}
		}

	}
	
	/**
	* 
	* Add a shipment to the request
	*/
	protected function addShipment($shipment) {
	
		$parcel = array(
			'shipdate'	=> null,
			'reference' => $shipment->getId(),
			'weight'	=> $this->getShipmentWeight($shipment),
			'height'	=> null,
			'width'		=> null,
			'length'	=> null,
			'size'		=> null,
			'freetext1'	=> $this->getFreetext(),
			'items' 	=> array(),
			);

		$ordered_items = $shipment->getAllItems();	
		foreach($ordered_items as $item) {
			$parcel['items'][] = $this->addItem($item);
		}
	
		$this->_parcels[] = $parcel;

	}
	
	/*
	 * Add a parcel to the request
	 * The parcel contains all unshipped items
	 */
	protected function addParcelWithUnshippedItems() {
		$unshipped_items = $this->getUnshippedItems();
		
		$parcel = array(
			'shipdate'	=> null,
			'reference' => $this->getOrderId(),
			'weight'	=> 0,
			'height'	=> null,
			'width'		=> null,
			'length'	=> null,
			'size'		=> null,
			'freetext1'	=> $this->getFreetext(),
			'items' 	=> array(),
			);
			
		foreach($unshipped_items as $item) {
			$product = Mage::getModel('catalog/product')->load($item['productid']);
			if( $product->getId() ) {
				$parcel['weight'] =+ $item['quantity']*$product->getWeight();
				$parcel['items'][] = $this->addItem($product);
			}
		}
	
		$this->_parcels[] = $parcel;
	}

	/**
	* 
	* Format an item to be added to a parcel
	* @return array
	*/
	protected function addItem($item) {

		return array(
			'sku'		=> $item->getSku(),
			'title'		=> $item->getName(),
			'quantity'	=> $item->getQty(),
			'unitweight'=> $item->getWeight(),
			'unitprice'	=> $item->getPrice(),
			'currency'	=> Mage::app()->getStore()->getCurrentCurrencyCode()
			);
		  //  $item->getItemId(); //product id
	}

}