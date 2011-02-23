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

require_once t3lib_extMgm::extPath('pt_gsapdfdocs').'res/class.tx_ptgsapdfdocs_document.php';
require_once t3lib_extMgm::extPath('pt_gsapdfdocs').'res/class.tx_ptgsapdfdocs_documentAccessor.php';


require_once t3lib_extMgm::extPath('pt_gsashop').'res/class.tx_ptgsashop_orderPresentator.php';

require_once t3lib_extMgm::extPath('pt_gsashop').'res/class.tx_ptgsashop_order.php';  // GSA Shop order class
require_once t3lib_extMgm::extPath('pt_gsashop').'res/class.tx_ptgsashop_orderWrapper.php';// GSA Shop order wrapper class
require_once t3lib_extMgm::extPath('pt_gsashop').'res/class.tx_ptgsashop_orderWrapperAccessor.php';  // GSA Shop database accessor class for order wrappers
require_once t3lib_extMgm::extPath('pt_gsashop').'res/class.tx_ptgsashop_orderPresentator.php';// GSA shop order presentator class
require_once t3lib_extMgm::extPath('pt_gsauserreg').'res/class.tx_ptgsauserreg_user.php';  // // TYPO3 FE user class
require_once t3lib_extMgm::extPath('pt_gsauserreg').'res/class.tx_ptgsauserreg_feCustomer.php';  // TYPO3 FE user class


/**
 * Invoice class
 *
 * $Id$
 *  
 * @author	Fabrizio Branca <branca@punkt.de>
 * @since	2008-07-09
 */
class tx_ptgsapdfdocs_invoice extends tx_ptgsapdfdocs_document {
	
	/**
	 * @var tx_ptgsashop_order
	 */
	protected $orderObj;
	
	/**
	 * @var tx_ptgsauserreg_feCustomer
	 */
	protected $customerObj;
	
	/**
	 * @var string	document type
	 */
	protected $documenttype = 'invoice';
	
	
	
	/**
	 * Load by related erp doc number
	 *
	 * @param	string	related erp doc number
	 * @return 	void
	 * @author	Fabrizio Branca <branca@punkt.de>
	 * @since	2008-11-03
	 */
	public function loadByRelatedErpDocNo($relatedErpDocNo) {
		
		tx_pttools_assert::isNotEmptyString($relatedErpDocNo);
		
		$documents = tx_ptgsapdfdocs_documentAccessor::getInstance()->selectDocumentsByRelatedErpDocNo($relatedErpDocNo);
		
		tx_pttools_assert::isNotEmptyArray($documents, array('message' => sprintf('No documents found in database for relatedErpDocNo "%s"!', $relatedErpDocNo)));
		
		$this->loadOutOfDocumentsArray($documents);
	}
	
	
	
	/**
	 * Load by order wrapper uid
	 *
	 * @param	string	order wrapper uid
	 * @return 	void
	 * @author	Fabrizio Branca <branca@punkt.de>
	 * @since	2008-11-03
	 */
	public function loadByOrderWrapperUid($ow_uid) {
		
		tx_pttools_assert::isValidUid($ow_uid);
		
		$documents = tx_ptgsapdfdocs_documentAccessor::getInstance()->selectDocumentsByOrderWrapperUid($ow_uid);
		
		tx_pttools_assert::isNotEmptyArray($documents, array('message' => sprintf('No documents found in database for orderwrapper uid "%s"!', $ow_uid)));
		
		$this->loadOutOfDocumentsArray($documents);

	}
	
	
	
	/**
	 * Searches for an invoices in the given documents array (an array of document records))
	 *
	 * @param 	array 	array of document rows
	 * @return 	void
	 * @author	Fabrizio Branca <branca@punkt.de>
	 * @since	2008-11-03
	 */
	protected function loadOutOfDocumentsArray(array $documents) {
		
		tx_pttools_assert::isNotEmptyArray($documents, array('message' => 'Empty documents array!'));
		
		// search all documents for an invoice
		$invoices = array();
		foreach ($documents as $document) {
			if ($document['documenttype'] == 'invoice') {
				$invoices[] = $document;
			}
		}
		$invoiceCount = count($invoices);
		tx_pttools_assert::isTrue($invoiceCount > 0, array('message' => 'Did not find any invoices'));
		tx_pttools_assert::isTrue($invoiceCount == 1, array('message' => sprintf('Found more than one invoices. Found: "%s"', $invoiceCount)));
		
		$invoice = $invoices[0];
		
		tx_pttools_assert::isNotEmptyArray($invoice, array('message' => 'No valid invoice document found!'));
		
		$this->setFromGivenArray($invoice);
	}


	
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
	 * Get the order object
	 * 
	 * @author Christoph Ehscheidt <ehscheidt@punkt.de>
	 * @return tx_ptgsashop_order
	 */
	public function get_orderObj() {
		return $this->orderObj;
	}
	
	
    /**
     * Fill the marker array
     *
     * @param	void
	 * @return 	tx_ptgsapdfdocs_invoice $this
     * @author	Fabrizio Branca <branca@punkt.de>
     * @global	$TYPO3_CONF_VARS for the markerArrayHook
     * @since 	2007-10-10
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
        if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['pt_gsapdfdocs']['tx_ptgsapdfdocs_invoice']['markerArrayHook'])) {
            foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['pt_gsapdfdocs']['tx_ptgsapdfdocs_invoice']['markerArrayHook'] as $className) {
                $hookObj = t3lib_div::getUserObj($className);
                $this->markerArray = $hookObj->displayShoppingCart_MarkerArrayHook($this, $this->markerArray);
            }
        }
                
        return $this;
    }
    
    
    
    /**
     * Check if invoice already exists before storing self to database
 	 *
     * @param 	void
     * @throws	tx_pttools_exception 	if invoice already exists 
     * @author	Fabrizio Branca <branca@punkt.de>
     * @since	2008-12-04
     */
    public function storeSelf() {
    	// check if an invoice already exists
    	$documents = tx_ptgsapdfdocs_documentAccessor::getInstance()->selectDocumentsByOrderWrapperUid($this->orderWrapperUid);
		foreach ($documents as $document) {
			if ($document['documenttype'] == 'invoice') {
				throw new tx_pttools_exception(sprintf('Invoice (uid: "%s", documenttype: "%s") already exists for orderWrapperUid "%s"', $document['uid'], $document['documenttype'], $this->orderWrapperUid));
			}
		}
    	parent::storeSelf();
    }
    
}

/*******************************************************************************
 *   TYPO3 XCLASS INCLUSION (for class extension/overriding)
 ******************************************************************************/
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pt_gsapdfdocs/res/class.tx_ptgsapdfdocs_invoice.php']) {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pt_gsapdfdocs/res/class.tx_ptgsapdfdocs_invoice.php']);
}


?>