<?php
/***************************************************************
*  Copyright notice
*  
*  (c) 2008 Dorit Rottner <rottner@punkt.de>
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
/** 
 * Hooking class of the 'pt_gsapdfdocs' extension for hooks in tx_ptgsashop_orderPresentator
 *
 * $Id$
 *
 * @author  Dorit Rottner <rottner@punkt.de>
 * @since   2008-11-20
 */ 
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 */



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


/**
 * Class being included by pt_gsashop using hooks in tx_ptgsashop_orderProcessor
 *
 * @author      Dorit Rottner <rottner@punkt.de>
 * @since       2008-11-20
 * @package     TYPO3
 * @subpackage  tx_ptgsapdfdocs
 */
class tx_ptgsapdfdocs_hooks_ptgsashop_orderProcessor {
    
	
    /**
     * This method is called by a hook in tx_ptgsashop_orderProcessor::fixFinalOrderHook 
     *
     * @param   array      params contains order Wrapper
     * @author  Dorit Rottner <rottner@punkt.de>
     * @since   2008-11-20
     */
    public function fixFinalOrderHook(array $params /*, tx_ptgsashop_orderProcessor $pObj*/ ) {
        trace('[METHOD] '.__METHOD__);
    	$orderWrapperObj = $params['orderWrapperObj'];
    	tx_pttools_assert::isInstanceOf($orderWrapperObj,'tx_ptgsashop_orderWrapper',array('message'=>'No valid orderWrapper Object'));
        if (TYPO3_DLOG) t3lib_div::devLog('Entering "'.__METHOD__.'"', 'pt_gsapdfdocs', 1, $this->pdfdocsConf);
                
        $relatedErpDocNo = $orderWrapperObj->get_relatedDocNo();
        $gsaUid = $orderWrapperObj->get_customerId();
        $conf = tx_pttools_div::getTS('config.pt_gsapdfdocs.');
        
        tx_pttools_assert::isArray($conf, array('message' => 'No configuration found at "config.pt_gsapdfdocs."'));
        
        if ($conf['generateInvoiceAfterOrderSubmit']) {
        
            if (substr($relatedErpDocNo, 0, 2) == 'RE') {
                $GLOBALS['TT']->push('Generating Invoice PDF');
                // try {
                
                    $replace = array(
                        '###GSAUID###' => $gsaUid,
                        '###GSAUIDMOD10###' => $gsaUid % 10, 
                        '###GSAUIDMOD100###' => $gsaUid % 100,
                        '###GSAUIDMOD1000###' => $gsaUid % 1000,
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
                        ->set_customerObj($orderWrapperObj->get_feCustomerObj())
                        ->set_orderObj($orderWrapperObj->get_orderObj())
                        ->fillMarkerArray()
                        ->set_xmlSmartyTemplate($conf['xmlSmartyTemplate'])
                        ->set_languageFile($conf['languageFile'])
                        // ->set_languageKey($conf['languageKey'])
                        ->addMarkers($additionalMarkers)
                        ->createXml()
                        ->renderPdf($path);
                        
                    // save to database
                    $pdfInvoice->set_orderWrapperUid($orderWrapperObj->get_uid());
                    $pdfInvoice->set_gsaCustomerId($gsaUid);
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
                                'level'     => E_ERROR,         
                                'message'   => tx_pttools_debug::exceptionToHTML($exception),
                                'file'      => $exception->getFile(),
                                'line'      => $exception->getLine(),
                                'variables' => array(),
                                'signature' => mt_rand(),
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
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pt_gsapdfdocs/res/pt_gsashop_hooks/class.tx_ptgsapdfdocs_hooks_ptgsashop_orderProcessor.php'])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pt_gsapdfdocs/res/pt_gsashop_hooks/class.tx_ptgsapdfdocs_hooks_ptgsashop_orderProcessor.php']);
}

?>
