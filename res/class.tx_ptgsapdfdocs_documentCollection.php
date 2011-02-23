<?php
/***************************************************************
*  Copyright notice
*  
*  (c) 2008 Dorit Rottner (rottner@punkt.de)
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
 * PDF Documents collection class for the 'pt_gsapdfdocs' extension
 *
 * $Id$
 *
 * @author	Dorit Rottner <rottner@punkt.de>
 * @since   2008-09-16
 */ 
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 */



/**
 * Inclusion of extension specific resources
 */
require_once t3lib_extMgm::extPath('pt_gsapdfdocs').'res/class.tx_ptgsapdfdocs_document.php';// extension specific document class
require_once t3lib_extMgm::extPath('pt_gsapdfdocs').'res/class.tx_ptgsapdfdocs_documentAccessor.php';// Accessor Class for document object

/**
 * Inclusion of external resources
 */
require_once t3lib_extMgm::extPath('pt_tools').'res/staticlib/class.tx_pttools_debug.php'; // debugging class with trace() function
require_once t3lib_extMgm::extPath('pt_tools').'res/objects/class.tx_pttools_exception.php'; // general exception class
require_once t3lib_extMgm::extPath('pt_tools').'res/staticlib/class.tx_pttools_div.php'; // general static library class
require_once t3lib_extMgm::extPath('pt_tools').'res/abstract/class.tx_pttools_objectCollection.php'; // abstract object Collection class



/**
 * GSA dtabuch collection class
 *
 * @author	    Dorit Rottner <rottner@punkt.de>
 * @since       2008-09-16
 * @package     TYPO3
 * @subpackage  tx_ptgsapdfdocs
 */
class tx_ptgsapdfdocs_documentCollection extends tx_pttools_objectCollection {
    
    /**
     * Properties
     */
    
    
    
	/***************************************************************************
     *   CONSTRUCTOR
     **************************************************************************/
     
    /**
     * Class constructor: creates a collection of document objects. 
     *
     * @param   string     type of Document
     * @param   boolean    signing in process
     * @param   boolean    signed
     * @return  void
 	 * @author	Dorit Rottner <rottner@punkt.de>
 	 * @since   2008-09-16
     */
    public function __construct($documentType='invoice',$signProcessing=0, $signed=0) { 
		require_once t3lib_extMgm::extPath('pt_gsapdfdocs').'res/class.tx_ptgsapdfdocs_'.$documentType.'.php';// Class for pdf document 
    	trace('***** Creating new '.__CLASS__.' object. *****');
        trace('parameters:'.$documentType.', '.$signProcessing.', '.$signed);
        // load collection from database
        $idArr = tx_ptgsapdfdocs_documentAccessor::getInstance()->getDocumentIdArr($documentType,$signProcessing, $signed);
		$class = 'tx_ptgsapdfdocs_'.$documentType;
        foreach ($idArr as $id) {
			$this->addItem(new $class($id),$id);
		}
    }   
    
    /***************************************************************************
     *   extended collection methods
     **************************************************************************/
    
 
    /***************************************************************************
     *   GENERAL METHODS
     **************************************************************************/
    
    /***************************************************************************
     *   PROPERTY GETTER/SETTER METHODS
     **************************************************************************/
     

} // end class



/*******************************************************************************
 *   TYPO3 XCLASS INCLUSION (for class extension/overriding)
 ******************************************************************************/
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pt_gsapdfdocs/res/class.tx_ptgsapdfdocs_documentCollection.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pt_gsapdfdocs/res/class.tx_ptgsapdfdocs_documentCollection.php']);
}

?>
