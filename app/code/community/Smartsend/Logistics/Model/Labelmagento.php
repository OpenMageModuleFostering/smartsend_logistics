<?php

/**
 * Smartsend_Logistics Label Magento class
 *
 * The label class is used to handle requests and responses from the Smart Send Logistics API
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
 * @class 		Smartsend_Logistics_Model_Labelmagento
 * @folder		/app/code/community/Smartsend/Logistics/Model/Labelmagento.php
 * @category	Smart Send
 * @package		Smartsend_Logistics
 * @author 		Smart Send ApS
 * @url			http://smartsend.dk/
 * @copyright	Copyright (c) Smart Send ApS (http://www.smartsend.dk)
 * @license		http://smartsend.dk/license
 * @since		Class available since Release 7.1.0
 * @version		Release: 7.1.3.0
 */

class Smartsend_Logistics_Model_Labelmagento extends Smartsend_Logistics_Model_Label {

	protected $apikey;
	protected $smartsend_licensekey;
	protected $smartsend_username;
	protected $cms_system;
	protected $cms_version;
	protected $module_version;
	protected $cms_language;
	protected $settings;

	/*
	 * Set all the variables when class is initiated
	 *
	 * @return void
	 */
	public function __construct() {
		$this->setApiKey();
		$this->setSmartsendLicensekey();
		$this->setSmartsendUsername();
		$this->setCmsVersion();
		$this->setCmsSystem();
		$this->setModuleVersion();
		$this->setCmsLanguage();
		$this->setMessageStringArray();
		$this->setSettings();
	}
	
	/*
	 * Set the strings that are shown (and translated) for succes, error and notification messages
	 *
	 * @return void
	 */
	protected function setMessageStringArray() {
		$this->message_string_array = array(
			2000	=> 'Order',
			//Notifications
			2101	=> 'Download combined PDF label',
			2102	=> 'Link to print labels',
			2103	=> 'Download PDF label',
			2104	=> 'Link to print label',
			2105	=> 'Label generated with Smart Send Logistics',
			2106	=> 'Tracecode',
			//Errors
			2201	=> 'Unknown API method',
			2202	=> 'Trying to send empty order array',
			2203	=> 'Error from server',
			2204	=> 'No orders returned from server',
			2205	=> 'No parcels returned from server',
			2206	=> 'Unknown order was returned from server',
			2207	=> 'No PDF file or link returned from server',
			2208	=> 'Failed to insert tracecode. No tracecode available.',
			2209	=> 'Failed to change order status. Status unchanged.',
			2210	=> 'Failed to send shipment mail. No mail sent.',
			2211	=> 'Failed to add order comment',
			2212	=> 'Please enter a username in the modules settings',
			2213	=> 'Please enter a license key in the modules settings',
			);
		
		// Translate the error string of the $_error array
		foreach($this->message_string_array as $key=>$value) {
			$this->message_string_array[$key] = Mage::helper('logistics')->__($value);
		}
	}
	
	/*
	 * Set the apikey used for API calls
	 *
	 * @return void
	 */
	protected function setApiKey() {
		$this->apikey = 'yL18TETUVQ7E9pgVb6JeV1erIYHAMcwe';
	}
	
	/*
	 * Set the licensekey used for API calls
	 *
	 * @return void
	 */
	protected function setSmartsendLicensekey() {
		if(Mage::getStoreConfig('carriers/smartsend/username') == '' && Mage::helper('core')->isModuleEnabled('Vconnect_Postnord')) {
        	$this->smartsend_licensekey = Mage::getStoreConfig('carriers/vconnect_postnord/licensekey');
        } else {
        	$this->smartsend_licensekey = Mage::getStoreConfig('carriers/smartsend/licensekey');
        }
	}
	
	/*
	 * Set the username used for API calls
	 *
	 * @return void
	 */
	protected function setSmartsendUsername() {
		if(Mage::getStoreConfig('carriers/smartsend/username') == '' && Mage::helper('core')->isModuleEnabled('Vconnect_Postnord')) {
        	$this->smartsend_username = Mage::getStoreConfig('carriers/vconnect_postnord/username');
        } else {
        	$this->smartsend_username = Mage::getStoreConfig('carriers/smartsend/username');
        }
	}
	
	/*
	 * Set the version number of the CMS system
	 *
	 * @return void
	 */
	protected function setCmsVersion() {
		$this->cms_version = Mage::getVersion();
	}
	
	/*
	 * Set the language used by the CMS system
	 *
	 * @return void
	 */
	protected function setCmsLanguage() {
		$this->cms_language = Mage::app()->getLocale()->getLocaleCode();
	}
	
	/*
	 * Set the CMS system
	 *
	 * @return void
	 */
	protected function setCmsSystem() {
		$this->cms_system = 'Magento';
	}
	
	/*
	 * Set the version number of this module
	 *
	 * @return void
	 */
	protected function setModuleVersion() {
		$this->module_version = Mage::getConfig()->getNode('modules/Smartsend_Logistics')->version;
	}
	
	/*
	 * Set the general settings from the module
	 *
	 * @return void
	 */
	protected function setSettings() {
		$this->settings = array(
			'combine_pdf_labels'	=> Mage::getStoreConfig('carriers/smartsend/combinepdf'),
			'change_order_status'	=> Mage::getStoreConfig('carriers/smartsend/status'),
			'send_shipment_mail'	=> Mage::getStoreConfig('sales_email/shipment/enabled')
			);
	}
	
	/*
	 * Load an order model class
	 *
	 * @return object
	 */
	protected function loadOrderModel($order_id) {
		return Mage::getModel('sales/order')->load($order_id);
	}
	
	/*
	 * Load a Smart Send order model class
	 *
	 * @return object
	 */
	protected function loadSmartsendOrderModel() {
		return Mage::getModel('logistics/ordermagento');
	}
	
	/*
	 * Send an email with the shipping informations
	 *
	 * @param string $order_number is the id of the order
	 * @param array $parcels_succes_array is an array of parcel id numbers
	 * @param string $customer_email_comments is a string that can be sendt with the email
	 *
	 * @return void
	 */
	protected function sendShipmentEmail($order_number,$parcels_succes_array,$customer_email_comments=null) {
	// An email will automatically be send from the system when a shipment is created
	/*
		if(is_array($parcels_succes_array)) {
			foreach($parcels_succes_array as $parcel) {
			
				if($parcel['reference'] == $order_number && count($parcels_succes_array) == 1) {
					//The shipment is created with all unshipped items
					$order = Mage::getModel('sales/order')->load($order_number);
					$shipment = $order->getShipmentsCollection()->getFirstItem();
				} else {
					//The shipment was created by the user
					$shipment = Mage::getModel('sales/order_shipment')->load($parcel['reference']);
				}
				
				if($shipment->getId() != '') {
					// Only send email if it has not been send already
					if($shipment->getEmailSent() == false ) {
						$shipment->sendEmail(true, $customer_email_comments);
						$shipment->setEmailSent(true);
						$shipment->save();
					}
				} else {
					throw new Exception( $this->getMessageString(2210) );
				}
			}
		} else {
			throw new Exception( $this->getMessageString(2210) );
		}
		*/
	}
	
	/*
	 * Set the order status
	 *
	 * @param string $order_number is the id of the order
	 * @param string $order_status is the status that the order should be updated to
	 *
	 * @return void
	 */
	protected function setOrderStatus($order_number,$order_status) {
		$order = Mage::getModel('sales/order')->load($order_number);
		if($order->getId() != '') {
	
			// Array containing all status codes as KEY and labels as VALUE.
			$status_array = array();

			// Create status array
			$all_status = Mage::getModel('sales/order_status')->getResourceCollection()->getData();
			foreach($all_status as $single_status) {
				$status_array[$single_status["status"]] = $single_status["label"];
			}

			// If the status is in Magento
			if(array_key_exists($order_status,$status_array)) {
				// Change the status if is is not already that status
				if($order->getStatus() != $order_status) {
					$order->setStatus($order_status);
					$order->save();
				}
			}
		} else {
			throw new Exception( $this->getMessageString(2209) );
		}
	}
	
	/*
	 * Add a trace code to the parcel.
	 * If order has no parcels, create a parcel with alll unshipped items
	 *
	 * @param string $order_number is the id of the order
	 * @param string $shipment_number is the id of the shipment
	 * @param string $tracking_number is the tracking number to add
	 * @param string $tracelink is the linked used to crack the parcel
	 *
	 * @return void
	 */
	protected function addTracecodeToParcel($order_number,$shipment_number,$tracking_number,$tracelink) {
	
		$order = Mage::getModel('sales/order')->load($order_number);
		$smartsendorder = Mage::getModel('logistics/ordermagento');
 		$smartsendorder->setOrderObject($order);
 		
 		$carrier_code = 'smartsend'.$smartsendorder->getShippingCarrier();
 		
 		//Get all shipments for the order
		$shipments = $smartsendorder->getShipments();
		
		if(empty($shipments)) {
			// Create a shipment
			$smartsendorder->createShipment($tracking_number);
		} else {
			$shipment = Mage::getModel('sales/order_shipment')->load($shipment_number);
			
			if($shipment->getId() != '') {
				$track = Mage::getModel('sales/order_shipment_track')
					->setShipment($shipment)
					->setData('title', $order->getShippingDescription())
					->setData('number', $tracking_number)
					->setData('carrier_code', $carrier_code)
					->setData('order_id', $shipment->getData('order_id'))
					->save();
				
				//An email is only send if a tracking number is returned
				$email = Mage::getStoreConfig('sales_email/shipment/enabled'); //Setting from: System->Configuration->Sales Emails->Shipment->Enabled
				$includeComment = false;
				$comment = "";
				
				//Email the customer that the order is sent
				if(!$shipment->getEmailSent() && $email){
					$shipment->sendEmail(true,($includeComment ? $comment : ''));
					$shipment->setEmailSent(true);
					$shipment->save();                          
				}
			} else {
				throw new Exception( $this->getMessageString(2208) );
			}
			
		}
		
	}
	
	/*
	 * Add a comment to the order
	 *
	 * @param string $order_number is the id of the order
	 * @param string $order_comment is the comment to add
	 *
	 * @return void
	 */
	protected function addCommentToOrder($order_number,$order_comment) {
		$order = Mage::getModel('sales/order')->load($order_number);
		if($order->getId() != '') {
			//Add order history comment
			$order->addStatusHistoryComment($order_comment, false); // no sense to set $status again
			$order->save();
		} else {
			throw new Exception( $this->getMessageString(2211) );
		}
	}
	
	/*
	 * Show the messages that has been added during the API call
	 *
	 * @return void
	 */
	public function showResult() {
	
		foreach($this->getMessages() as $message) {
		
			switch ($message['type']) {
				case 'error':
					//Add an error
					Mage::getSingleton('core/session')->addError( $message['text'] );
					break;
				case 'warning':
					//Add a warning
					Mage::getSingleton('core/session')->addWarning( $message['text'] );
					break;
				case 'success':
					//Add a success
					Mage::getSingleton('core/session')->addSuccess( $message['text'] );
					break;
				default:
					//Add an information
					Mage::getSingleton('core/session')->addNotice( $message['text'] );
			}

		}
	}
	
	/**
 	 * The metchods adds an order to the API request
 	 * both for single and mass generation
 	 *
 	 * @param object $order
 	 * @param bool $return indicates if the order is a return order (true) or a normal order (false)
 	 *
 	 * @return void
 	 */
 	public function addOrderToRequest($order,$return=false) {
		
		$smartsendorder = Mage::getModel('logistics/ordermagento');
		$smartsendorder->setOrderObject($order);
		$smartsendorder->setReturn($return);

		$smartsendorder->setInfo();
		$smartsendorder->setReceiver();
		$smartsendorder->setSender();
		$smartsendorder->setAgent();
		$smartsendorder->setService();
		$smartsendorder->setParcels();

		//All done. Add to request.
		switch ($this->getRequestType()) {
			case 'bulk':
				//Label was created from order list
				$this->request[] = $smartsendorder->getFinalOrder();
				break;
			case 'single':
				//Label was created from order info page
				$this->request = $smartsendorder->getFinalOrder();
				break;
			default:
				throw new Exception( $this->getMessageString(2201) );
		}
 	}
	
}