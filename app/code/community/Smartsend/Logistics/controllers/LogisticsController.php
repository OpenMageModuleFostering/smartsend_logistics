<?php

class Smartsend_Logistics_LogisticsController extends Mage_Adminhtml_Controller_Action {

	protected $request=array();
	protected $response;

	/*
	$label = Mage::getModel('logistics/shippingMethods')->getLabel();  //calling label method in: /app/code/community/Smartsend/Logistics/Model/ShippingMethods.php
	$returnlabel = Mage::getModel('logistics/shippingMethods')->getReturnLabel();   //calling label method in: /app/code/community/Smartsend/Logistics/Model/ShippingMethods.php
	$this->_getSession()->addSuccess('Success message 2');
	$this->_getSession()->addNotice('Notice message 2');
	$this->_getSession()->addError('Error message 2');
	*/
					

/***********
 * Functions called from action buttons
 ***********/
 
 
 	/*
 	 * Action: Create single order
 	 */
    public function labelAction() {   //label action 
    	$orderId = $this->getRequest()->getParam('order_id');
    	$order = Mage::getModel('sales/order')->load($orderId);
    	
    	if((Mage::getStoreConfig('carriers/smartsend/username') == '' || Mage::getStoreConfig('carriers/smartsend/licencekey') == '') &&
    		(Mage::getStoreConfig('vconnect_postnord/general/username') == '' || Mage::getStoreConfig('vconnect_postnord/general/license_key') == '')) {
    			$this->_getSession()->addError($this->__("Username and licencekey must be entered in settings"));
    	} else {
			$this->createOrder($order,false);
			
			if($this->isRequest()) {
				try{
					$this->postRequest(true);
					$this->handleRequest();
				} catch(Exception $e) {
					$this->_getSession()->addError($e->getMessage()); 
				}
			}
		}
		
        $this->_redirectReferer();
        return ;
    }
    
    /*
 	 * Action: Create array of orders
 	 */
    public function labelMassAction() {   //label mass action
    
    	if((Mage::getStoreConfig('carriers/smartsend/username') == '' || Mage::getStoreConfig('carriers/smartsend/licencekey') == '') &&
    		(Mage::getStoreConfig('vconnect_postnord/general/username') == '' || Mage::getStoreConfig('vconnect_postnord/general/license_key') == '')) {
    			$this->_getSession()->addError($this->__("Username and licencekey must be entered in settings"));
    	} else {
    	
			$orderIds = $this->getRequest()->getPost('order_ids');
			if (!empty($orderIds)) {
				foreach($orderIds as $orderId) {
					$order = Mage::getModel('sales/order')->load($orderId);
					$this->createOrder($order,false);
				} 
			} else {
				$this->_getSession()->addError($this->__('No orders selected')); 
			}
			
			if($this->isRequest()) {
				try{
					$this->postRequest(false);
					$this->handleRequest();
				} catch(Exception $e) {
					$this->_getSession()->addError($e->getMessage()); 
				}
			}
			
		}
		
        $this->_redirectReferer();
        return ;
    }

	/*
 	 * Action: Create single return order
 	 */
    public function labelreturnAction() {         // return  label action
    	$orderId = $this->getRequest()->getParam('order_id');
    	$order = Mage::getModel('sales/order')->load($orderId);
    	
    	if((Mage::getStoreConfig('carriers/smartsend/username') == '' || Mage::getStoreConfig('carriers/smartsend/licencekey') == '') &&
    		(Mage::getStoreConfig('vconnect_postnord/general/username') == '' || Mage::getStoreConfig('vconnect_postnord/general/license_key') == '')) {
    			$this->_getSession()->addError($this->__("Username and licencekey must be entered in settings"));
    	} else {
			$this->createOrder($order,true);
			
			if($this->isRequest()) {
				try{
					$this->postRequest(true);
					$this->handleRequest();
				} catch(Exception $e) {
					$this->_getSession()->addError($e->getMessage()); 
				}
			}
		}
		
        $this->_redirectReferer();
        return ;
    }
    
    /*
 	 * Action: Create array of return orders
 	 */
    public function labelreturnMassAction() {   //label return mass action
    
    	if((Mage::getStoreConfig('carriers/smartsend/username') == '' || Mage::getStoreConfig('carriers/smartsend/licencekey') == '') &&
    		(Mage::getStoreConfig('vconnect_postnord/general/username') == '' || Mage::getStoreConfig('vconnect_postnord/general/license_key') == '')) {
    			$this->_getSession()->addError($this->__("Username and licencekey must be entered in settings"));
    	} else {
    	
			$orderIds = $this->getRequest()->getPost('order_ids');
			if (!empty($orderIds)) {
				foreach($orderIds as $orderId) {
					$order = Mage::getModel('sales/order')->load($orderId);
					$this->createOrder($order,true);
				} 
			} else {
				$this->_getSession()->addError($this->__('No orders selected')); 
			}
			
			if($this->isRequest()) {
				try{
					$this->postRequest(false);
					$this->handleRequest();
				} catch(Exception $e) {
					$this->_getSession()->addError($e->getMessage()); 
				}
			}
			
		}
		
        $this->_redirectReferer();
        return ;
    }
    
    
    
/***********
 * Functions used to generate labels
 ***********/
 
 	/*
 	 * Function: is there a requerst?
 	 * both for single and mass generation
 	 */
 	private function isRequest() {
 		if(empty($this->request)) {
 			return false;
 		} else {
 			return true;
 		}
 	}
 
 
 	/*
 	 * Function: Get JSON request
 	 * both for single and mass generation
 	 */
 	private function getJsonRequest() {
 		if(empty($this->request)) {
 			throw new Exception($this->__("Trying to send empty order array"));
 		} else {
 			return json_encode($this->request);
 		}
 	}
 
 	/*
 	 * Function: Create an order request
 	 * both for single and mass generation
 	 */
 	private function createOrder($order,$return=false) {
 	
 		$smartsendorder = Mage::getModel('logistics/order');
 		$smartsendorder->setOrderObject($order);
 		$smartsendorder->setReturn($return);
 		try {	
			$smartsendorder->setInfo();
			$smartsendorder->setReceiver();
			$smartsendorder->setSender();
			$smartsendorder->setAgent();
			$smartsendorder->setService();
			$smartsendorder->setParcels();
			
			//All done. Add to request.
			$this->request[] = $smartsendorder->getFinalOrder();

			//$this->_getSession()->addNotice("Order " . $order->getIncrementId() .": ".json_encode($smartsendorder->getFinalOrder()));
		} catch(Exception $e) {
			$this->_getSession()->addError("Order #" . $order->getIncrementId() .": ".$e->getMessage()); 
		}
 	}
 	
 	/*
 	 * Function: POST final cURL request
 	 * both for single and mass generation
 	 */
 	private function postRequest($single=false) {
 	
 		$ch = curl_init();               //intitiate curl

        /* Script URL */
        if($single == true) {
        	$url = 'https://smartsend-prod.apigee.net/v7/booking/order';
        } elseif($single == false) {
        	$url = 'https://smartsend-prod.apigee.net/v7/booking/orders';
        } else {
        	throw new Exception('Unknown post method: '.$single);
        }
        
        if(Mage::getStoreConfig('carriers/smartsend/username') == '' && Mage::helper('core')->isModuleEnabled('Vconnect_Postnord')) {
        	$username = Mage::getStoreConfig('vconnect_postnord/general/username');
        	$licensekey = Mage::getStoreConfig('vconnect_postnord/general/license_key');
        } else {
        	$username = Mage::getStoreConfig('carriers/smartsend/username');
        	$licensekey = Mage::getStoreConfig('carriers/smartsend/licencekey');
        }

        curl_setopt($ch, CURLOPT_URL, $url);       //curl url
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->getJsonRequest());
        //curl_setopt($ch, CURLOPT_HTTPGET, true);   //curl request method
        //curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        	'apikey:yL18TETUVQ7E9pgVb6JeV1erIYHAMcwe',
        	'smartsendmail:'.$username,
        	'smartsendlicence:'.$licensekey,
        	'cmssystem:Magento',
        	'cmsversion:'.Mage::getVersion(),
        	'appversion:'.Mage::getConfig()->getNode('modules/Smartsend_Logistics')->version,
        	'Content-Type:application/json; charset=UTF-8'
        	));    //curl request header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $this->response = new StdClass();                       //creating new class
        $this->response->body = curl_exec($ch);             //executing the curl
        $this->response->code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $this->response->meta = curl_getinfo($ch);
        $curl_error = ($this->response->code > 0 ? null : curl_error($ch) . ' (' . curl_errno($ch) . ')');      //getting error from curl if any

        curl_close($ch);                          //closing the curl

        if ($curl_error) {
            throw new Exception($this->__('An error occurred while sending order').': ' . $curl_error);
        }
        
        if(!($this->response->code >= '200') || !($this->response->code <= '210')) {
        	throw new Exception($this->__('Response').': ('.$this->response->code.') '.$this->response->body);
        }
 	
 	}
 	
 	
 	/*
	 * Add Track and Trace number to parcels
	 * @string shipment_reference: unique if of shipment
	 * @string tracecode
	 */ 
 	private function addTraceToShipment($shipment_reference,$tracecode) {
 	
	/*	$shipment_collection = Mage::getResourceModel('sales/order_shipment_collection');
		$shipment_collection->addAttributeToFilter('order_id', $order_id);
		
		foreach($shipment_collection as $sc) {
			$shipment = Mage::getModel('sales/order_shipment');
			$shipment->load($sc->getId());
			if($shipment->getId() != '') { 
				$track = Mage::getModel('sales/order_shipment_track')
						 ->setShipment($shipment)
						 ->setData('title', 'ShippingMethodName')
						 ->setData('number', $track_no)
						 ->setData('carrier_code', 'ShippingCarrierCode')
						 ->setData('order_id', $shipment->getData('order_id'))
						 ->save();
			}
		} */
		
		$shipment = Mage::getModel('sales/order_shipment');
		$shipment->load($shipment_reference);
		if($shipment->getId() != '') {
			$order = Mage::getModel('sales/order')->load($shipment->getData('order_id'));
			$smartsendorder = Mage::getModel('logistics/order');
 			$smartsendorder->setOrderObject($order);
		
			$track = Mage::getModel('sales/order_shipment_track')
				->setShipment($shipment)
				->setData('title', $smartsendorder->getShippingMethod())
				->setData('number', $tracecode)
				->setData('carrier_code', $smartsendorder->getShippingCarrierId())
				->setData('order_id', $shipment->getData('order_id'))
				->save();
		} else {
			throw new Exception($this->__('Failed to insert tracecode'));
		}
		
 	}
 	
 	/*
 	 * Function: go through parcels and add trace code
 	 */
 	private function verifyParcels($json) {
 		if(isset($json->parcels) && is_array($json->parcels)) {
			foreach($json->parcels as $parcel) {
				if(isset($parcel->reference) && $parcel->reference != '' && isset($parcel->tracecode) && $parcel->tracecode != '') {
					$this->addTraceToShipment($parcel->reference, $parcel->tracecode);
				}	
			}
		}
	}	
 	
 	
 	/*
 	 * Function: Handle cURL response
 	 * both for single and mass generation
 	 */
 	private function handleRequest() {
 		if(strpos($this->response->meta['content_type'],'json') !== false) {
 			$json = json_decode($this->response->body);
 		/*	$this->_getSession()->addNotice($this->getJsonRequest());
 			$this->_getSession()->addNotice($this->response->body); */
 			
 			//Show a notice if info is given
 			if(isset($json->info)) {
 				if(is_array($json->info)) {
 					foreach($json->info as $info) {
 						$this->_getSession()->addNotice($info);
 					}
 				} else {
 					$this->_getSession()->addNotice($json->info);
 				}
 			}
 			
 			if(isset($json->combine_pdf) && Mage::getStoreConfig('carriers/smartsend/combinepdf') == 1) {
 				$this->_getSession()->addSuccess('<a href="'. $json->combine_pdf .'" target="_blank">Combined PDF labels</a>');
 			}
 			
 			if(isset($json->combine_link) && Mage::getStoreConfig('carriers/smartsend/combinepdf') == 1) {
 				$this->_getSession()->addSuccess('<a href="'. $json->combine_link .'" target="_blank">Combined label links</a>');
 			}
 			
			if(isset($json->orders) && is_array($json->orders)) {
				// An array of orders was returned
				foreach($json->orders as $json_order) {
					if(isset($json_order->pdflink) && !(isset($json->combine_pdf) && Mage::getStoreConfig('carriers/smartsend/combinepdf') == 1)) {
						$this->_getSession()->addSuccess('Order #'.$json_order->orderno.': <a href="'. $json_order->pdflink .'" target="_blank">PDF label</a>');
						// Go through parcels and add trace to shipments
						$this->verifyParcels($json_order);	
					} elseif(isset($json_order->link) && !(isset($json->combine_link) && Mage::getStoreConfig('carriers/smartsend/combinepdf') == 1)) {
						$this->_getSession()->addSuccess('Order #'.$json_order->orderno.': <a href="'. $json_order->link .'" target="_blank">Label link</a>');
						// Go through parcels and add trace to shipments
						$this->verifyParcels($json_order);	
					} elseif( (isset($json_order->pdflink) || isset($json_order->link) ) && Mage::getStoreConfig('carriers/smartsend/combinepdf') == 1) {
						$this->_getSession()->addSuccess('Order #'.$json_order->orderno.': '. $json_order->message);
						$this->verifyParcels($json_order);
					} else {
						if(isset($json_order->status) && $json_order->status != '') {
							$this->_getSession()->addError('Order #'.$json_order->orderno.': '. $json_order->message); 
						} else {
							$this->_getSession()->addError($this->__('Unknown status').': '. $json_order->message);
						}
					}
				}
			
			} else {
				// An array of orders was not returned. Check if just a single order was returned
			
				if(isset($json->pdflink) && !(isset($json->combine_pdf) && Mage::getStoreConfig('carriers/smartsend/combinepdf') == 1)) {
					$this->_getSession()->addSuccess('Order #'.$json->orderno.': <a href="'. $json->pdflink .'" target="_blank">PDF label</a>');
					// Go through parcels and add trace to shipments
					$this->verifyParcels($json);	
				} elseif(isset($json->link) && !(isset($json->combine_link) && Mage::getStoreConfig('carriers/smartsend/combinepdf') == 1)) {
					$this->_getSession()->addSuccess('Order #'.$json->orderno.': <a href="'. $json->link .'" target="_blank">Label link</a>');
					// Go through parcels and add trace to shipments
					$this->verifyParcels($json);	
				} else {
					if(isset($json->status) && $json->status != '') {
						$this->_getSession()->addError('Order #'.$json->orderno.': '. $json->message);
					} else {
						$this->_getSession()->addError($this->__('Unknown status').': '. $json->message);
					}
				}
			}
 			
 		} else {
 			throw new Exception($this->__('Unknown content type').': '.$this->response->meta['content_type']);
 		}
 		
 	}
 	 	
}
