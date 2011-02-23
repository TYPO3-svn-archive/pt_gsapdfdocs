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


/**
 * Static methods
 *
 * $Id$
 *  
 * @author	Fabrizio Branca <branca@punkt.de>
 * @since	2008-07-09
 */
class tx_ptgsapdfdocs_div {

	/**
	 * Generates an url to the invoice (to the eID script)
	 *
	 * @param string related erp document number
	 * @return string url
	 * @author Fabrizio Branca <branca@punkt.de>
	 * @since 2008-07-09
	 */
	public static function urlToInvoice($relatedErpDocNo) {
		return 'index.php?eID=tx_ptgsapdfdocs_download&download[action]=downloadInvoice&download[redn]='.urlencode($relatedErpDocNo);
		
	}
	
}

/*******************************************************************************
 *   TYPO3 XCLASS INCLUSION (for class extension/overriding)
 ******************************************************************************/
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pt_gsapdfdocs/res/class.tx_ptgsapdfdocs_div.php']) {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pt_gsapdfdocs/res/class.tx_ptgsapdfdocs_div.php']);
}

?>