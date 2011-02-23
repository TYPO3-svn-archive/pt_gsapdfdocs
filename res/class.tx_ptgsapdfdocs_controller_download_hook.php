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

require_once t3lib_extMgm::extPath('pt_gsapdfdocs').'controller/class.tx_ptgsapdfdocs_controller_download.php';

/**
 * Hook class for "tx_ptgsapdfdocs_controller_download"
 * 
 * @version $Id$
 * @author	Fabrizio Branca <branca@punkt.de>
 * @since	2008-10-16
 */
class tx_ptgsapdfdocs_controller_download_hook extends tx_ptgsapdfdocs_controller_download {
	
	/**
	 * Action method for action "hookTest"
	 *
	 * @param 	array 	$params
	 * @param 	tx_ptgsapdfdocs_controller_download 	calling parent object
	 * @return 	strin	HTML Output
	 * @author	Fabrizio Branca <branca@punkt.de>
	 * @since	2008-10-16
	 */
	public function hookTestAction(array $params, tx_ptgsapdfdocs_controller_download $pObj) {
		return 'This action was processed within a hook action method!';
	}
	
}

?>