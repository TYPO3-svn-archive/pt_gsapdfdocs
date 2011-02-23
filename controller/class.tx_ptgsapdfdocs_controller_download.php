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

require_once t3lib_extMgm::extPath('pt_gsapdfdocs').'res/class.tx_ptgsapdfdocs_div.php';



require_once t3lib_extMgm::extPath('pt_tools').'res/objects/exceptions/class.tx_pttools_exceptionAuthentication.php';
require_once t3lib_extMgm::extPath('pt_tools').'res/staticlib/class.tx_pttools_assert.php';
require_once t3lib_extMgm::extPath('pt_tools').'res/staticlib/class.tx_pttools_debug.php';
require_once t3lib_extMgm::extPath('pt_tools').'res/staticlib/class.tx_pttools_div.php';

require_once t3lib_extMgm::extPath('pt_mvc').'classes/class.tx_ptmvc_controllerEid.php';


/**
 * Controller "download"
 * 
 * @author	Fabrizio Branca <branca@punkt.de>
 * @since	2008-10-02
 * @version $Id$
 */
class tx_ptgsapdfdocs_controller_download extends tx_ptmvc_controllerEid {
	
	/**
	 * @var string	this prefix will be used to prefix parameters passed to this controller (via GET/POST)
	 */
	public $prefixId = 'download';
	
	/**
	 * @var tslib_feUserAuth current frontend user
	 */
	protected $feUserObj;
	
	
	
	/**
	 * Init
	 * 
	 * @param 	void
	 * @return 	void
	 * @author	Fabrizio Branca <branca@punkt.de>
	 * @since	2008-10-16
	 */
	public function init() {
		$this->feUserObj = tslib_eidtools::initFeUser();
	}
	
	
	
	/**
	 * Action "downloadInvoice"
	 * 
	 * @param 	void
	 * @return 	string 	HTML output
	 * @author	Fabrizio Branca <branca@punkt.de>
	 * @since	2008-10-16
	 */
	public function downloadInvoiceAction() {
				
		tx_pttools_assert::isNotEmptyString($this->params['redn'], array('message' => 'No valid "redn" set!'));
		
		$relatedErpDocNo = urldecode($this->params['redn']);
		
		$invoice = new tx_ptgsapdfdocs_invoice();
		$invoice->loadByRelatedErpDocNo($relatedErpDocNo);
		
		// check if current user is allowed to download this file!
		
		if (!$this->checkAccessAllowed($invoice)) {
            throw new tx_pttools_exceptionAuthentication('No access right for the requested document', 
                                                         'Currently logged-in user (uid "'.$this->feUserObj->user['uid'].'") is not allowed to access requested document '.'("'.$relatedErpDocNo.'")'
                                                        );
		} else {
			$this->_downloadFile(PATH_site . $invoice->get_file());
		}
		
	}
	
	/**
	 * check if download access is allowed
	 * 
	 * @return boolean true if curent user is allowed to download the invoice
	 * @author Daniel Lienert <lienert@punkt.de>
	 * @since 15.07.2010
	 */
	protected function checkAccessAllowed($invoice) {
		$orderwrapper = new tx_ptgsashop_orderWrapper((int)$invoice->get_orderWrapperUid(), 0, array(), false);
		$feCustomer = new tx_ptgsauserreg_feCustomer($this->feUserObj->user['uid']);
		
		$allowed = false;
		$allowed = ($orderwrapper->get_customerId() == $feCustomer->get_gsaMasterAddressId());
		
		// HOOK: allow multiple hooks to add change the previous decision
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['pt_gsapdfdocs']['tx_ptgsapdfdocs_controller_download']['checkAccessAllowedHook'])) {
			foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['pt_gsapdfdocs']['tx_ptgsapdfdocs_controller_download']['checkAccessAllowedHook'] as $className) {
				$hookObj = &t3lib_div::getUserObj($className); 
				$allowed = $hookObj->checkAccessAllowedHook($allowed, $invoice); 
			}
		}	
		
		return $allowed;
	}
	
	
	/**
	 * Sends a file to the browser
	 *
	 * @param 	string 	file path
	 * @param 	bool	(optional) if true the file will be downloaded, if false the file will be embedded if this is available in the client
	 * @author	Fabrizio Branca <branca@punkt.de>
	 * @since	2008-10-16
	 */
	public static function _downloadFile($filepath, $saveDocument = true) {
		
		tx_pttools_assert::isFilePath($filepath, array('message' => 'Download file not found'));
	
		$path_parts = pathinfo($filepath);
		$filename = $path_parts['basename'];
		
		if ($saveDocument) {
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename="'.$filename.'"');
		} else {
			header('Content-type: application/pdf');
		}
		header('Content-Length: ' .(string)(filesize($filepath)) );
		header("Content-Transfer-Encoding: binary\n");
				
		readfile($filepath);
		
		exit();
	}
	
}

?>