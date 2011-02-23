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

require_once t3lib_extMgm::extPath('pt_gsapdfdocs').'res/class.tx_ptgsapdfdocs_documentAccessor.php';

require_once t3lib_extMgm::extPath('pt_xml2pdf').'res/class.tx_ptxml2pdf_generator.php';

require_once t3lib_extMgm::extPath('pt_tools').'res/staticlib/class.tx_pttools_assert.php';
require_once t3lib_extMgm::extPath('pt_tools').'res/objects/class.tx_pttools_exception.php';



/**
 * Abstract document class
 *
 * $Id$
 *  
 * @author	Fabrizio Branca <branca@punkt.de>
 * @since	2008-07-09
 */
abstract class tx_ptgsapdfdocs_document extends tx_ptxml2pdf_generator {
	
	/**
	 * @var 	integer	uid of Document 
	 */
	protected $uid;
	
	/**
	 * @var 	string	document type (set in inheriting class) 
	 */
	protected $documenttype;
	
	/**
	 * @var 	integer	  uid of the related order wrapper object 
	 */
	protected $orderWrapperUid;
    
    /**
     * @var     integer   ID of the related customer in the GSA DB
     */
    protected $gsaCustomerId;    
    
    /**
     * @var     boolean flag if Pdf signing is in process
     */
    protected $signProcessing;
    
    /**
     * @var     boolean flag if PPF is signed
     */
    protected $signed;
    
    /**
     * Class constructor - fills object's properties with param array data
     *
     * @param   integer     (optional) ID of the gsapdfdocs document Record. Set to 0 if you want to use the 2nd param.
     * @param   array       Array containing gsapdfdocs document data to set as tx_gsapdfdocs_document object's properties; array keys have to be named exactly like the proprerties of this class and it's parent class. This param has no effect if the 1st param is set to something other than 0.
     * @return  void   
     * @throws  tx_pttools_exception   if the first param is not numeric  
     * @author  Dorit Rottner <rottner@punkt.de>
     * @since   2008-09-16
     */
    public function __construct($uid=0, $dataArr=array()) {
    
        trace('***** Creating new '.__CLASS__.' object. *****');
        
        tx_pttools_assert::isValidUid($uid, true, array('message' => __CLASS__.'No valid ptgsapdfdocs_documents uid: '.$uid.'!'));
        
        // if a customer record ID is given, retrieve customer array from database accessor (and overwrite 2nd param)
        if ($uid  > 0) {
            $dataArr = tx_ptgsapdfdocs_documentAccessor::getInstance()->selectDocumentByUid($uid);
        }
        
        if (!empty($dataArr)) {
            $this->setFromGivenArray($dataArr);
        }
           
        trace($this);
        
    }
    
    
    
    /**
     * Returns the uid
     *
     * @param 	void
     * @return 	int 	uid
     * @author	Fabrizio Branca <branca@punkt.de>
     * @since	2008-10-31
     */
    public function get_uid() {
    	return $this->uid;
    }
    
    
    
    /**
     *  Sets the properties using data given by param array
     *
     * @param   array   Array containing gsapdfdocs document data to set as tx_gsapdfdocs_document object's properties
     * @return  void   
     * @author  Dorit Rottner <rottner@punkt.de>
     * @since   2008-09-16
     */
    protected function setFromGivenArray(array $dataArr) {
        trace('[METHOD] '.__METHOD__);    
        $this->uid = $dataArr['uid'];
        $this->documenttype = $dataArr['documenttype'];
        $this->orderWrapperUid = $dataArr['ow_uid'];
        $this->signProcessing = $dataArr['signProcessing'];
        $this->signed = $dataArr['signed'];
        $this->file = $dataArr['file'];
        
        trace($this);
    }
    
    
    
    /**
     *  Sets signProcessing signed in object and database
     *
     * @param   boolean signProcessing
     * @return  void   
     * @author  Dorit Rottner <rottner@punkt.de>
     * @since   2008-09-17
     */
    public function setSignProcessing($signProcessing) {
        trace('[METHOD] '.__METHOD__);    
        trace($this,0,'this '.__CLASS__);
        $this->set_signProcessing($signProcessing);
        tx_ptgsapdfdocs_documentAccessor::getInstance()->updateDocument(
            array(
                'uid' => $this->uid,
                'signProcessing' => $this->signProcessing,
            )
        );      
     }
    
     
     
    /**
     *  Sets signed in object and database
     *
     * @param   boolean signed
     * @return  void   
     * @author  Dorit Rottner <rottner@punkt.de>
     * @since   2008-09-17
     */
    public function setSigned($signed) {
        trace('[METHOD] '.__METHOD__);    
        $this->set_signed($signed);
        tx_ptgsapdfdocs_documentAccessor::getInstance()->updateDocument(
            array(
                'uid' => $this->uid,
                'signed' => $this->signed,
            )
        );      
     }
     
     
     
    /**
     * Store itself to the database
     *
     * @param 	void
     * @return 	tx_ptgsapdfdocs_document
     * @author	Fabrizio Branca <branca@punkt.de>
     * @since	2008-12-04
     */
	public function storeSelf() {
		
		$this->uid = tx_ptgsapdfdocs_documentAccessor::getInstance()->insertDocument(
			array(
				'documenttype' => $this->documenttype,
				'orderWrapperUid' => $this->orderWrapperUid,
                'gsaCustomerId' => $this->gsaCustomerId,
				'file' => $this->outputFile,
			)
		);
		
		trace($this,0,'ptgsapdfdocs_document');
		return $this;    
	}
	
	
	
	/**
	 * Setter for output file. In general the "outputFile" property will be populated by the parent class after generating a pdf. 
	 * But if you want to import existing pdfs you can set the file path here before storing the record.
	 * User relative paths (relative to PATH_site)!
	 *
	 * @param 	string	file path (relative to PATH_site)
	 * @return 	void
	 * @author	Fabrizio Branca <branca@punkt.de>
	 * @since	2008-11-24
	 */
	public function set_outputFile($file) {
		$this->outputFile = $file;
	}
	
	
	
    /**
     * Gets property value
     *
     * @return   string  name of file
     */
    public function get_file() {
        return (string) $this->file;
    }

    
    
	/**
	 * Sets property value
	 *
	 * @param 	int	order wrapper uid
	 * @return 	tx_ptgsapdfdocs_document	reference to itself
	 */
	public function set_orderWrapperUid($orderWrapperUid) {
		
		tx_pttools_assert::isValidUid($orderWrapperUid, true, array('message' => 'Invalid orderWrapperUid!'));
		
		$this->orderWrapperUid = $orderWrapperUid;
		
		return $this;
	}

    
    
	/**
	 * Returns the property value
	 *
	 * @return	int	order wrapper uid
	 */
	public function get_orderWrapperUid() {
		return $this->orderWrapperUid;
	}
    
    /**
     * Sets property value
     *
     * @param   int order wrapper uid
     * @return  tx_ptgsapdfdocs_document    reference to itself
     */
    public function set_gsaCustomerId($gsaCustomerId) {
        
        tx_pttools_assert::isValidUid($gsaCustomerId, false, array('message' => 'Invalid gsaCustomerId!'));
        
        $this->gsaCustomerId = $gsaCustomerId;
        
        return $this;
    }
	
    /**
     * Gets property value
     *
     * @param   void
     * @return  boolean signProcessing  
     */
    public function get_signProcessing() {
        
        
        return (boolean) $this->signProcessing;
        
    }
    
    /**
     * Sets property value
     *
     * @param   boolean signProcessing
     * @return  tx_ptgsapdfdocs_document    reference to itself
     */
    public function set_signProcessing($signProcessing) {
        
        tx_pttools_assert::isBoolean($signProcessing, array('message' => 'Invalid signProcessing!'));
        
        $this->signProcessing = $signProcessing;
        
    }

    /**
     * Gets property value
     *
     * @param   void
     * @return  boolean signed  
     */
    public function get_signed() {
        
        
        return (boolean) $this->signed;
        
    }
    
    /**
     * Sets property value
     *
     * @param   boolean signProcessing
     * @return  tx_ptgsapdfdocs_document    reference to itself
     */
    public function set_signed($signed) {
        
        tx_pttools_assert::isBoolean($signed, array('message' => 'Invalid signed!'));
        
        $this->signed = $signed;
        
        return $this;
    }
    
}

/*******************************************************************************
 *   TYPO3 XCLASS INCLUSION (for class extension/overriding)
 ******************************************************************************/
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pt_gsapdfdocs/res/class.tx_ptgsapdfdocs_document.php']) {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pt_gsapdfdocs/res/class.tx_ptgsapdfdocs_document.php']);
}
    
?>