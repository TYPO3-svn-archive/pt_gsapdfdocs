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
 
require_once t3lib_extMgm::extPath('pt_gsashop').'pi4/class.tx_ptgsashop_pi4.php';
require_once t3lib_extMgm::extPath('pt_gsashop').'res/class.tx_ptgsashop_lib.php';  // GSA shop library class with static methods and config constants
require_once t3lib_extMgm::extPath('pt_tools').'res/objects/class.tx_pttools_exception.php'; // general exception class

require_once t3lib_extMgm::extPath('pt_tools').'res/staticlib/class.tx_pttools_debug.php'; // debugging class with trace() function
require_once t3lib_extMgm::extPath('pt_tools').'res/staticlib/class.tx_pttools_div.php'; // general static library class
require_once t3lib_extMgm::extPath('pt_tools').'res/objects/class.tx_pttools_sessionStorageAdapter.php';

require_once t3lib_extMgm::extPath('pt_gsapdfdocs').'res/class.tx_ptgsapdfdocs_invoice.php';
require_once t3lib_extMgm::extPath('pt_gsapdfdocs').'res/class.tx_ptgsapdfdocs_manifest.php';
require_once t3lib_extMgm::extPath('pt_gsapdfdocs').'res/class.tx_ptgsapdfdocs_div.php';





class tx_ptgsapdfdocs_hooks_ptgsashop_pi4 extends tx_ptgsashop_pi4 {  // "extends" needed to access the FE plugin's protected properties and methods

	public function displayOrdersList_MarkerArrayHook(tx_ptgsashop_pi4 $pObj, array $markerArray) {
	
		if (is_array($markerArray['ordersArr'])) {
			foreach ($markerArray['ordersArr'] as &$ordersArr) {
				$ordersArr['orderRelDocNo'] = '<a href="'.tx_ptgsapdfdocs_div::urlToInvoice($ordersArr['orderRelDocNo']).'">' . $ordersArr['orderRelDocNo'] . '</a>';
				
			}
		}
		
		return $markerArray;
	}

} // end class



/*******************************************************************************
 *   TYPO3 XCLASS INCLUSION (for class extension/overriding)
 ******************************************************************************/
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pt_euwidshop/res/class.tx_pteuwidshop_hooks_ptgsashop_pi1.php'])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pt_euwidshop/res/class.tx_pteuwidshop_hooks_ptgsashop_pi1.php']);
}

?>
