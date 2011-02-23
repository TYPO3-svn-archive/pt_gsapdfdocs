<?php
/***************************************************************
*  Copyright notice
*  
*  (c) 2008 Fabrizio Branca (branca@punkt.de)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is 
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
* 
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
* 
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

require_once t3lib_extMgm::extPath('pt_tools').'res/staticlib/class.tx_pttools_assert.php';
require_once t3lib_extMgm::extPath('pt_gsapdfdocs').'res/class.tx_ptgsapdfdocs_invoice.php';



class tx_ptgsapdfdocs_manifest extends tx_ptgsapdfdocs_document {
	
	/**
	 * @var string	document type
	 */
	protected $documenttype = 'manifest';
		
	/**
	 * @var int		delivery number
	 */
	protected $deliveryNumber;	
	
	/**
	 * @var tx_ptgsashop_order
	 */
	protected $orderObj;
	
	/**
	 * @var tx_ptgsauserreg_feCustomer
	 */
	protected $customerObj;
	
	
	



	
	/**
	 * Set property value
	 * 
	 * @param 	tx_ptgsauserreg_feCustomer $customerObj
	 * @return 	tx_ptgsapdfdocs_invoice $this
	 */
	public function set_customerObj(tx_ptgsauserreg_feCustomer $customerObj) {

		$this->customerObj = $customerObj;
		
		return $this;
	}



	/**
	 * Set property value
	 * 
	 * @param 	tx_ptgsashop_order $orderObj
	 * @return 	tx_ptgsapdfdocs_invoice $this
	 */
	public function set_orderObj(tx_ptgsashop_order $orderObj) {

		$this->orderObj = $orderObj;
		
		return $this;
	}
	
	
	
	/**
	 * Set property value
	 *
	 * @param 	int	delivery number
	 * @return	void
	 * @author	Fabrizio Branca <branca@punkt.de>
	 * @since	2008-06-12
	 */
	public function set_deliveryNumber($deliveryNumber) {
		
		tx_pttools_assert::isInstanceOf($this->orderObj, 'tx_ptgsashop_order', array('message' => 'No order object found. Please set one before setting the delivery number!'));
		tx_pttools_assert::isInstanceOf($this->orderObj->getDelivery($deliveryNumber), 'tx_ptgsashop_delivery', array('message' => sprintf('Could not find delivery "%s" in orderObj!', $deliveryNumber)));
		
		$this->deliveryNumber = $deliveryNumber;
		
		$this->markerArray['deliveryNumber'] = $deliveryNumber;
		return $this;
	}
	
	
	
	/**
	 * Fill marker array
	 *
	 * @param 	void
	 * @return 	tx_ptgsapdfdocs_manifest	returns itself
	 * @author	Fabrizio Branca <branca@punkt.de>
	 * @since	2008-10-31
	 */
	public function fillMarkerArray() {
		
		tx_pttools_assert::isType($this->orderObj, 'tx_ptgsashop_order');
    	tx_pttools_assert::isType($this->customerObj, 'tx_ptgsauserreg_feCustomer');
    	
    	$orderPresentator = new tx_ptgsashop_orderPresentator($this->orderObj);
    	
    	$this->markerArray = $orderPresentator->getMarkerArray();
    	
    	// add some customer data, that is not in the order object
        $this->markerArray['username'] = $this->customerObj->get_feUserObj()->get_username();
        $this->markerArray['vatId'] = $this->customerObj->get_gsaCustomerObj()->get_euVatId();
        $this->markerArray['isForeignCountry'] = tx_ptgsauserreg_countrySpecifics::isForeignCountry($this->customerObj->get_gsaCustomerObj()->get_country());
     	$this->markerArray['afterTableSnippets'] = array();
     	
        // HOOK: allow multiple hooks to manipulate $markerArray
        if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['pt_gsapdfdocs']['tx_ptgsapdfdocs_manifest']['markerArrayHook'])) {
            foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['pt_gsapdfdocs']['tx_ptgsapdfdocs_manifest']['markerArrayHook'] as $className) {
                $hookObj = t3lib_div::getUserObj($className);
                $this->markerArray = $hookObj->displayShoppingCart_MarkerArrayHook($this, $this->markerArray);
            }
        }
                
		tx_pttools_assert::isNotEmptyArray($this->markerArray['deliveries'][$this->deliveryNumber], array('message' => 'Delivery not found!'));
		$this->markerArray['delivery'] = $this->markerArray['deliveries'][$this->deliveryNumber];
		
		return $this;
	}
	
	
	
	/**
	 * Load by related erp doc number
	 *
	 * @param	string	related erp doc number
	 * @param 	int		number of the delivery ("0" is the first one!)
	 * @return 	void
	 * @author	Fabrizio Branca <branca@punkt.de>
	 * @since	2008-12-02
	 */
	public function loadByRelatedErpDocNo($relatedErpDocNo, $number) {
		tx_pttools_assert::isIntegerish($number);
		
		tx_pttools_assert::isNotEmptyString($relatedErpDocNo);
		
		$documents = tx_ptgsapdfdocs_documentAccessor::getInstance()->selectDocumentsByRelatedErpDocNo($relatedErpDocNo);
		
		tx_pttools_assert::isNotEmptyArray($documents, array('message' => sprintf('No documents found in database for relatedErpDocNo "%s"!', $relatedErpDocNo)));
		
		$this->loadOutOfDocumentsArray($documents, $number);
	}
	
	
	
	/**
	 * Load by order wrapper uid
	 *
	 * @param	string	order wrapper uid
	 * @param 	int		number of the delivery ("0" is the first one!)
	 * @return 	void
	 * @author	Fabrizio Branca <branca@punkt.de>
	 * @since	2008-12-02
	 */
	public function loadByOrderWrapperUid($ow_uid, $number) {
		tx_pttools_assert::isIntegerish($number);
		
		tx_pttools_assert::isValidUid($ow_uid);
		
		$documents = tx_ptgsapdfdocs_documentAccessor::getInstance()->selectDocumentsByOrderWrapperUid($ow_uid);
		
		tx_pttools_assert::isNotEmptyArray($documents, array('message' => sprintf('No documents found in database for orderwrapper uid "%s"!', $ow_uid)));
		
		$this->loadOutOfDocumentsArray($documents, $number);
	}
	
	
	
	/**
	 * Searches for a manifest in the given documents array (an array of document records))
	 *
	 * @param 	array 	array of document rows
	 * @param 	int		number of the delivery ("0" is the first one!)
	 * @return 	void
	 * @author	Fabrizio Branca <branca@punkt.de>
	 * @since	2008-11-03
	 */
	protected function loadOutOfDocumentsArray(array $documents, $number) {
		tx_pttools_assert::isIntegerish($number);
		
		tx_pttools_assert::isNotEmptyArray($documents, array('message' => 'Empty documents array!'));
		
		// search all documents for an invoice
		$manifests = array();
		foreach ($documents as $document) {
			if ($document['documenttype'] == 'manifest') {
				$manifests[] = $document;
			}
		}
		$manifestCount = count($manifests);
		tx_pttools_assert::isTrue($manifestCount > 0, array('message' => 'Did not find any manifests'));
		
		$manifest = $manifests[$number];
		
		tx_pttools_assert::isNotEmptyArray($manifest, array('message' => sprintf('Did not find any manifest number "%s" in document array', $number)));
		
		$this->setFromGivenArray($manifest);
	}
	
    
}


/*******************************************************************************
 *   TYPO3 XCLASS INCLUSION (for class extension/overriding)
 ******************************************************************************/
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pt_gsapdfdocs/res/class.tx_ptgsapdfdocs_manifest.php']) {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pt_gsapdfdocs/res/class.tx_ptgsapdfdocs_manifest.php']);
}



?>