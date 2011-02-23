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
 
 
require_once t3lib_extMgm::extPath('pt_gsashop').'res/class.tx_ptgsashop_articleAccessor.php';
 
require_once t3lib_extMgm::extPath('pt_gsashop').'pi3/class.tx_ptgsashop_pi3.php';
require_once t3lib_extMgm::extPath('pt_gsashop').'res/class.tx_ptgsashop_lib.php';  // GSA shop library class with static methods and config constants

require_once t3lib_extMgm::extPath('pt_tools').'res/objects/class.tx_pttools_exception.php'; // general exception class
require_once t3lib_extMgm::extPath('pt_tools').'res/staticlib/class.tx_pttools_debug.php'; // debugging class with trace() function
require_once t3lib_extMgm::extPath('pt_tools').'res/staticlib/class.tx_pttools_div.php'; // general static library class
require_once t3lib_extMgm::extPath('pt_tools').'res/objects/class.tx_pttools_sessionStorageAdapter.php';

require_once t3lib_extMgm::extPath('pt_gsapdfdocs').'res/class.tx_ptgsapdfdocs_invoice.php';
require_once t3lib_extMgm::extPath('pt_gsapdfdocs').'res/class.tx_ptgsapdfdocs_manifest.php';
require_once t3lib_extMgm::extPath('pt_gsapdfdocs').'res/class.tx_ptgsapdfdocs_div.php';





class tx_ptgsapdfdocs_hooks_ptgsashop_pi3 extends tx_ptgsashop_pi3 {  // "extends" needed to access the FE plugin's protected properties and methods

	
	/**
	 * Process order submission hook
	 *
	 * @param 	tx_ptgsashop_pi3 	reference to the calling plugin
	 * @param 	string				related ERP document number
	 * @param 	tx_ptgsashop_orderWrapper	order wrapper object
	 */
    public function processOrderSubmission_fixFinalOrderHook(tx_ptgsashop_pi3 $pObj, $relatedErpDocNo, tx_ptgsashop_orderWrapper $orderWrapperObj){
    	
    	$conf = tx_pttools_div::getTS('config.pt_gsapdfdocs.');
    	
    	tx_pttools_assert::isArray($conf, array('message' => 'No configuration found at "config.pt_gsapdfdocs."'));
    	
    	if ($conf['generateInvoiceAfterOrderSubmit']) {
    	
	        if (substr($relatedErpDocNo, 0, 2) == 'RE') {
	            $GLOBALS['TT']->push('Generating Invoice PDF');
	        	// try {
	            
	        		$replace = array(
	        			'###GSAUID###' => $pObj->customerObj->get_gsaCustomerObj()->get_gsauid(),
	        			'###GSAUIDMOD10###' => $pObj->customerObj->get_gsaCustomerObj()->get_gsauid() % 10, 
	        			'###GSAUIDMOD100###' => $pObj->customerObj->get_gsaCustomerObj()->get_gsauid() % 100,
	        			'###GSAUIDMOD1000###' => $pObj->customerObj->get_gsaCustomerObj()->get_gsauid() % 1000,
	        			'###DAY###' => strftime('%d'),
		        		'###MONTH###' => strftime('%m'),
		        		'###YEAR###' => strftime('%Y'),
	        			'###RELATEDERPDOCNO###' => ereg_replace('[^a-zA-Z0-9._-]', '_', $relatedErpDocNo)
	        		);
	        		
	        		$path = str_replace(array_keys($replace), array_values($replace), $conf['invoicePath']);
	        		
	        		t3lib_div::mkdir_deep(PATH_site, dirname($path));
	        		
	        		$additionalMarkers = $conf['additionalMarkers.']; // TODO: stdWrap
		            $additionalMarkers['relatedErpDocNo'] = $relatedErpDocNo;

		            $pdfInvoice = new tx_ptgsapdfdocs_invoice();
		            $pdfInvoice
		            	->set_customerObj($pObj->customerObj)
		            	->set_orderObj($pObj->orderObj)
		            	->fillMarkerArray()
		            	->set_xmlSmartyTemplate($conf['xmlSmartyTemplate'])
		            	->set_languageFile($conf['languageFile'])
		            	// ->set_languageKey($conf['languageKey'])
		            	->addMarkers($additionalMarkers)
		            	->createXml()
		            	->renderPdf($path);
		            	
		            // save to database
					$pdfInvoice->set_orderWrapperUid($orderWrapperObj->get_uid());
                    $pdfInvoice->set_gsaCustomerId($pObj->customerObj->get_gsaCustomerObj()->get_gsauid());
					$pdfInvoice->storeSelf();
					
					// store relatedErpDocNo to session
					tx_pttools_sessionStorageAdapter::getInstance()->store('pt_gsapdfdocs_lastGeneratedInvoice', $relatedErpDocNo);
					
	        	/*
	        	} catch (Exception $exception) {
	
					if (method_exists($exception, 'handle')) {
						$exception->handle();
	        		}
					
					echo tx_pttools_debug::exceptionToHTML($exception);
					die();
					
					if (tx_pttools_debug::inDevContext()) {
						if (t3lib_extMgm::isLoaded('cc_debug') && is_object($GLOBALS['errorList'])) {
							$GLOBALS['errorList']->add(array(
								'level'		=> E_ERROR,			
								'message'	=> tx_pttools_debug::exceptionToHTML($exception),
								'file'		=> $exception->getFile(),
								'line'		=> $exception->getLine(),
								'variables'	=> array(),
								'signature'	=> mt_rand(),
							));
						} elseif(tx_pttools_div::outputToPopup(tx_pttools_debug::exceptionToHTML($exception))) {
						} else {
							echo tx_pttools_debug::exceptionToHTML($exception);
						}
					}		
				}
				*/
				$GLOBALS['TT']->pull();
	        }
	        
    	}
    }

} // end class



/*******************************************************************************
 *   TYPO3 XCLASS INCLUSION (for class extension/overriding)
 ******************************************************************************/
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pt_euwidshop/res/class.tx_pteuwidshop_hooks_ptgsashop_pi1.php'])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pt_euwidshop/res/class.tx_pteuwidshop_hooks_ptgsashop_pi1.php']);
}

?>
