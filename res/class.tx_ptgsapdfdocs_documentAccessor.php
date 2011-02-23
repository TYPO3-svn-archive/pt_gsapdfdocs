<?php
/***************************************************************
*  Copyright notice
*  
*  (c) 2007 Fabrizio Branca (branca@punkt.de)
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



require_once t3lib_extMgm::extPath('pt_tools').'res/staticlib/class.tx_pttools_debug.php'; // debugging class with trace() function
require_once t3lib_extMgm::extPath('pt_tools').'res/objects/class.tx_pttools_exception.php'; // general exception class
require_once t3lib_extMgm::extPath('pt_tools').'res/staticlib/class.tx_pttools_div.php'; // general helper library class
require_once t3lib_extMgm::extPath('pt_tools').'res/abstract/class.tx_pttools_iSingleton.php'; // interface for Singleton design pattern




/** 
 * Database accessor class for pdf document records
 *
 * $Id$
 *
 * @author  	Fabrizio Branca <branca@punkt.de>
 * @since   	2008-07-08
 * @package     TYPO3
 * @subpackage  tx_ptgsashop
 */ 
class tx_ptgsapdfdocs_documentAccessor implements tx_pttools_iSingleton {
    
    /**
     * Properties
     */
	
	/**
	 * @var tx_ptgsapdfdocs_documentAccessor  Singleton unique instance
	 */
    private static $uniqueInstance = NULL; 
    
    /**
     * @var int     pid where to store image data
     */
    protected $storagePid;
    
    
    
    /***************************************************************************
     *   CONSTRUCTOR & OBJECT HANDLING METHODS
     **************************************************************************/
    
    /**
     * Private class constructor: must not be called directly in order to use getInstance() to get the unique instance of the object.
     *
     * @param   void
     * @return  void
     * @author  Fabrizio Branca <branca@punkt.de>
     * @since   2008-07-09
     */
    private function __construct() {
    
        $conf = tx_pttools_div::returnExtConfArray('pt_gsapdfdocs');
        $this->storagePid = tx_pttools_div::returnTyposcriptSetup($conf['tsConfigurationPid'], 'config.tx_ptgsapdfdocs.documentStoragePid');
        $this->storagePid = tx_pttools_div::getPid($this->storagePid);
        
        tx_pttools_assert::isValidUid($this->storagePid, false, array('message' => __METHOD__.'StoragePid: '.$this->storagePid.'!'));
    }
    
    
    
    /**
     * Returns a unique instance (Singleton) of the object. Use this method instead of the private/protected class constructor.
     *
     * @param   void
     * @return  tx_ptgsapdfdocs_documentAccessor      unique instance of the object (Singleton) 
     * @author  Fabrizio Branca <branca@punkt.de>
     * @since   20008-07-08
     */
    public static function getInstance() {
        
        if (self::$uniqueInstance === NULL) {
            $className = __CLASS__;
            self::$uniqueInstance = new $className;
        }
        return self::$uniqueInstance;
        
    }
    
    
    
    /**
     * Final method to prevent object cloning (using 'clone'), in order to use only the unique instance of the Singleton object.
     * 
     * @param   void
     * @return  void
     * @author  Fabrizio Branca <branca@punkt.de>
     * @since   20008-07-08
     */
    public final function __clone() {
        trigger_error('Clone is not allowed for '.get_class($this).' (Singleton)', E_USER_ERROR);
    }
    
    
    /**
     * Returns an array with Id of all documents given parameters
     *
     * @param   string     type of Document
     * @param   boolean    signing in process
     * @param   boolean    signed
     * @return  array       array with all buy record IDs 
     * @author  Dorit Rottner <rottner@punkt.de>
     * @since   2008-09-16
     */
    public function getDocumentIdArr($documentType, $signProcessing, $signed) {
        trace('[METHOD] '.__METHOD__);
        trace('parameters:'.$documentType.', '.$signProcessing.', '.$signed);
        $select = 'uid';
        $from = 'tx_ptgsapdfdocs_documents';
        $where = ' documenttype = '.$GLOBALS['TYPO3_DB']->fullQuoteStr($documentType, $from);
        $where .= ' AND signProcessing = '.$signProcessing;
        $where .= ' AND signed = '.$signed;
        $where .= ' ' . tx_pttools_div::enableFields($from);
        $groupBy = '';
        $orderBy = '';
        $limit   = '';
        
        // exec query using TYPO3 DB API
        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $from, $where, $groupBy, $orderBy, $limit);
        trace(tx_pttools_div::returnLastBuiltSelectQuery($GLOBALS['TYPO3_DB'], $select, $from, $where, $groupBy, $orderBy, $limit));
        
        tx_pttools_assert::isMySQLRessource($res);
        $idArr = array();
        while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
            $idArr[] = $row['uid'];
        }
        $GLOBALS['TYPO3_DB']->sql_free_result($res);

        trace($idArr);
        return $idArr;
    }
    
    
    /**
     * Inserts a document
     *
     * @param 	array 	data array
     * @return 	int		uid of the inserted document
     * @throws	tx_pttools_exception	if insert query fails
     * @author	Fabrizio Branca <branca@punkt.de>
     * @since 	2008-07-08
     */
    public function insertDocument(array $dataArr) { 
        
        // query preparation
        $table = 'tx_ptgsapdfdocs_documents';

        $insertFieldsArr = array();
        $insertFieldsArr['pid']             = $this->storagePid;
        $insertFieldsArr['tstamp']          = time();
        $insertFieldsArr['crdate']          = time();
        $insertFieldsArr['documenttype']  	= $dataArr['documenttype'];
        $insertFieldsArr['file']            = $dataArr['file'];
        $insertFieldsArr['ow_uid'] 			= intval($dataArr['orderWrapperUid']);
        $insertFieldsArr['gsa_customer_id'] = intval($dataArr['gsaCustomerId']);
        
        tx_pttools_assert::isNotEmptyString($insertFieldsArr['file'], array('message' => 'No valid file path!'));
        tx_pttools_assert::isValidUid($insertFieldsArr['ow_uid'], true, array('message' => 'No valid order wrapper uid!'));
        tx_pttools_assert::isValidUid($insertFieldsArr['gsa_customer_id'], false, array('message' => 'No valid GSA customer id!'));
        // exec query using TYPO3 DB API
        $res = $GLOBALS['TYPO3_DB']->exec_INSERTquery($table, $insertFieldsArr);

        tx_pttools_assert::isMySQLRessource($res);
        
        return $GLOBALS['TYPO3_DB']->sql_insert_id();
    }
    
    /**
     * Update a document
     *
     * @param 	array 	data array
     * @return 	void	
     * @throws	tx_pttools_exception	if insert query fails
     * @author	Dorit Rottner <rottnerpunkt.de>
     * @since 	2008-09-16
     */
    public function updateDocument(array $dataArr) { 
        trace('[METHOD] '.__METHOD__);
    	trace($dataArr,0,'$dataArr');
        // query preparation
        $table = 'tx_ptgsapdfdocs_documents';

        $fieldsArr = array();
		$fieldsArr['uid'] = $dataArr['uid'];
        tx_pttools_assert::isValidUid($fieldsArr['uid'], false,array('message' => __METHOD__.'No valid ptgsapdfdocs_documents uid: '.$fieldsArr['uid'].'!'));
		if (array_key_exists('documenttype',$dataArr)) {
			$fieldsArr['documenttype'] = $dataArr['documenttype'];
        }
        if (array_key_exists('file',$dataArr)) {
        	$fieldsArr['file'] = $dataArr['file'];
			tx_pttools_assert::isNotEmptyString($fieldsArr['file'], array('message' => 'No valid file path: '.$fieldsArr['file'].'!'));
        }
        if (array_key_exists('orderWrapperUid',$dataArr)) {
        	$fieldsArr['ow_uid']  = intval($dataArr['orderWrapperUid']);
        	tx_pttools_assert::isValidUid($fieldsArr['ow_uid'],false, array('message' => 'No valid order wrapper uid!'));
        }   
        
        if (array_key_exists('signProcessing',$dataArr)) {
        	$fieldsArr['signProcessing'] = intval($dataArr['signProcessing']);
        }   
        if (array_key_exists('signed',$dataArr)) {
        	$fieldsArr['signed'] = intval($dataArr['signed']);
        }   
        $fieldsArr = tx_pttools_div::expandFieldValuesForQuery($fieldsArr);
        
		$where = 'uid ='.intval($fieldsArr['uid']);		     
        // exec query using TYPO3 DB API
        $res = $GLOBALS['TYPO3_DB']->exec_UPDATEquery($table, $where, $fieldsArr);
        trace(tx_pttools_div::returnLastBuiltUpdateQuery($GLOBALS['TYPO3_DB'], $table, $where, $fieldsArr));
        
        tx_pttools_assert::isMySQLRessource($res);
        
    }
    
    
    /**
     * Returns a document for a given related erp document number
     *
     * @param 	integer	uid of Document
     * @return 	array	database row
     * @author	Dorit Rottner <rottner@punkt.de>
     * @since	2008-09-16
     */
    public function selectDocumentByUid($uid) {
        trace('[METHOD] '.__METHOD__);
    	
    	tx_pttools_assert::isValidUid($uid,true, array('message' => 'No valid ptgsapdfdocs_documents uid!'));
    	
        // query preparation
        $select  = 'tx_ptgsapdfdocs_documents.*';
        $from    = 'tx_ptgsapdfdocs_documents';
        $where   = 'uid='.$uid;
        $where  .=  tx_pttools_div::enableFields('tx_ptgsapdfdocs_documents', 'tx_ptgsapdfdocs_documents') ;
        $groupBy = '';
        $orderBy = '';
        $limit   = '';
        
        // exec query using TYPO3 DB API
        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $from, $where, $groupBy, $orderBy, $limit);
        trace(tx_pttools_div::returnLastBuiltSelectQuery($GLOBALS['TYPO3_DB'], $select, $from, $where, $groupBy, $orderBy, $limit));
        tx_pttools_assert::isMySQLRessource($res);
        
        $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
        
        $GLOBALS['TYPO3_DB']->sql_free_result($res);
        
        return $row;
    }
    
    
    
    
    /**
     * Returns all documents for a given related erp document number
     *
     * @param 	string	$relatedErpDocNo
     * @return 	array	array of database row arrays
     * @author	Fabrizio Branca <branca@punkt.de>
     * @since	2008-07-09
     */
    public function selectDocumentsByRelatedErpDocNo($relatedErpDocNo) {
    	
    	tx_pttools_assert::isNotEmptyString($relatedErpDocNo);
    	
        // query preparation
        $select  = 'doc.*';
        $from    = 'tx_ptgsashop_order_wrappers as ow, tx_ptgsapdfdocs_documents as doc';
        $where   = 'ow.related_doc_no = '.$GLOBALS['TYPO3_DB']->fullQuoteStr($relatedErpDocNo, 'tx_ptgsashop_order_wrappers');
        $where  .= ' AND ow.uid = doc.ow_uid'; 
        $where  .=  tx_pttools_div::enableFields('tx_ptgsashop_order_wrappers', 'ow') ; 
        $where  .=  tx_pttools_div::enableFields('tx_ptgsapdfdocs_documents', 'doc') ;
        $groupBy = '';
        $orderBy = '';
        $limit   = '';
        
        // exec query using TYPO3 DB API
        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $from, $where, $groupBy, $orderBy, $limit);
        tx_pttools_assert::isMySQLRessource($res);
        
        $rows = array();
        while (($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) != false) {
        	$rows[] = $row;
        }
        
        $GLOBALS['TYPO3_DB']->sql_free_result($res);
        
        return $rows;
    }
    
    
    
    
    /**
     * Returns all documents for a given orderwrapper uid
     *
     * @param 	string	$relatedErpDocNo
     * @return 	array	array of database row arrays
     * @author	Fabrizio Branca <branca@punkt.de>
     * @since	2008-07-09
     */
    public function selectDocumentsByOrderWrapperUid($ow_uid) {
    	
    	tx_pttools_assert::isValidUid($ow_uid, false, array('message' => 'No valid orderwrapper uid given!'));
    	
        // query preparation
        $select  = 'doc.*';
        $from    = 'tx_ptgsashop_order_wrappers as ow, tx_ptgsapdfdocs_documents as doc';
        $where   = 'ow.uid = '. $ow_uid;
        $where  .= ' AND ow.uid = doc.ow_uid'; 
        $where  .=  tx_pttools_div::enableFields('tx_ptgsashop_order_wrappers', 'ow') ; 
        $where  .=  tx_pttools_div::enableFields('tx_ptgsapdfdocs_documents', 'doc') ;
        $groupBy = '';
        $orderBy = '';
        $limit   = '';
        
        // exec query using TYPO3 DB API
        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $from, $where, $groupBy, $orderBy, $limit);
        tx_pttools_assert::isMySQLRessource($res);
        
        $rows = array();
        while (($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) != false) {
        	$rows[] = $row;
        }
        
        $GLOBALS['TYPO3_DB']->sql_free_result($res);
        
        return $rows;
    }
    
    
} // end class



/*******************************************************************************
 *   TYPO3 XCLASS INCLUSION (for class extension/overriding)
 ******************************************************************************/
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pt_gsapdfdocs/res/class.tx_ptgsapdfdocs_documentAccessor.php']) {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pt_gsapdfdocs/res/class.tx_ptgsapdfdocs_documentAccessor.php']);
}

?>