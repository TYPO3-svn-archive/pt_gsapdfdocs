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
 * Main commission Handling module for the 'pt_gsapartners' extension to set status, orderAmount.
 *
 * $Id$
 *
 * @author	Dorit Rottner <rottner@punkt.de>
 */


#require_once(PATH_tslib.'class.tslib_pibase.php');
/**
 * Inclusion of external resources
 */
require_once t3lib_extMgm::extPath('pt_gsashop').'res/class.tx_ptgsashop_gsaTransactionAccessor.php';  // GSA accounting Transaction Accesor class
require_once 'Console/Getopt.php';  // PEAR Console_Getopt: parsing params as options (see http://pear.php.net/manual/en/package.console.console-getopt.php)

require_once t3lib_extMgm::extPath('pt_tools').'res/staticlib/class.tx_pttools_debug.php'; // debugging class with trace() function
require_once t3lib_extMgm::extPath('pt_tools').'res/staticlib/class.tx_pttools_div.php';
require_once t3lib_extMgm::extPath('pt_tools').'res/objects/class.tx_pttools_exception.php';
require_once t3lib_extMgm::extPath('pt_tools').'res/objects/class.tx_pttools_cliHandler.php';

require_once t3lib_extMgm::extPath('pt_gsashop').'res/class.tx_ptgsashop_order.php';             // GSA specific order Class
require_once t3lib_extMgm::extPath('pt_gsashop').'res/class.tx_ptgsashop_orderWrapper.php';      // GSA specific orderWrapper Class

/**
 * Inclusion of extension specific resources
 */
require_once t3lib_extMgm::extPath('pt_gsapdfdocs').'res/class.tx_ptgsapdfdocs_div.php';
require_once t3lib_extMgm::extPath('pt_gsapdfdocs').'res/class.tx_ptgsapdfdocs_documentCollection.php';


class tx_ptgsapdfdocs_pdfsign  {

    /**
     * Constants
     */
    const EXT_KEY     = 'pt_gsapdfdocs';               // (string) the extension key
    const LL_FILEPATH = 'cronmod/locallang.xml';       // (string) path to the locallang file to use within this class
	

    /**
     * Poperties
     */
    public $prefixId = 'tx_gsapdfdocs_pdfsign';		// Same as class name
	public $scriptRelPath = 'cronmod/class.tx_ptgsapdfdocs_pdfsign.php';	// Path to this script relative to the extension dir.
	public $extKey = 'pt_gsapdfdocs';	// The extension key.
	/**
	 * [Put your description here]
	 */
    protected $command;
    protected $documentType;
    
	protected $extConfArr;  //Extension Configuration array 
    
    /***************************************************************************
    *   CONSTRUCTOR & RUN METHOD
    ***************************************************************************/
    
    /**
     * Class constructor: define CLI options, set class properties 
     *
     * @param   void
     * @return  void
     * @author  Dorit Rottner <rottner@punkt.de>
     * @since   2008-09-16
     */
    public function __construct() {
            
            // for TYPO3 3.8.0+: enable storage of last built SQL query in $GLOBALS['TYPO3_DB']->debug_lastBuiltQuery for all query building functions of class t3lib_DB
            $GLOBALS['TYPO3_DB']->store_lastBuiltQuery = true;
            //echo 'TYPO3_MODE: '.TYPO3_MODE;                        
            // define command line options
            $this->shortOptions = 'hc:t:'; // (string) short options for Console_Getopt  (see http://pear.php.net/manual/en/package.console.console-getopt.intro-options.php)
            $this->longOptionsArr = array('help', 'command='); // (array) long options for Console_Getopt (see http://pear.php.net/manual/en/package.console.console-getopt.intro-options.php)
            $this->helpString = "Availabe options:\n".
                                "-h/--help      Help: this list of available options\n".
                                "-c/--command   Name of command to process (required):\n".
                                "-t/--type   	Type of Document (optional): if nothing is specified only invoices are handled\n".
                                "\n";
            
            // start script output
            echo "\n".
                 "---------------------------------------------------------------------\n".
                 "CLI Signing processing started...\n".
                 "---------------------------------------------------------------------\n";
                
            // get extension configuration configured in Extension Manager (from localconf.php) - NOTICE: this has to be placed *before* the first call of $this->cliHandler->cliMessage()!!
            
            $this->extConfArr = tx_pttools_div::returnExtConfArray($this->extKey);
            if (!is_array($this->extConfArr)) {
                fwrite(STDERR, "[ERROR] No extension configuration found!\nScript terminated.\n\n");
                die();
            }
            
            // invoke CLI handler with extension configuration
            $this->cliHandler = new tx_pttools_cliHandler($this->scriptName, 
                                                          $this->extConfArr['cliAdminEmailRecipient'],
                                                          $this->extConfArr['cliEmailSender'], 
                                                          $this->extConfArr['cliHostName'],
                                                          $this->extConfArr['cliQuietMode'],
                                                          $this->extConfArr['cliEnableLogging'],
                                                          $this->extConfArr['cliLogDir']
                                                         );
            $this->cliHandler->cliMessage('Script initialized', false, 2, true); // start new audit log entry
            $this->cliHandler->cliMessage('$this->extConfArr = '.print_r($this->extConfArr, 1));

            echo 'extconf: ';var_dump($this->extConfArr); echo "\n";
            // dev only
            #fwrite(STDERR, "[TRACE] died: STOP \n\n"); die();
    }
    
    /**
     * Run of the CLI class: executes the business logic 
     * @param   void    
     * @return  boolean	if execution worked or failed
     * @author  Dorit Rottner <rottner@punkt.de>
     * @since   2007-06-30
     */
    public function run() {
        $GLOBALS['trace']=2;
    	#$this->gsaAccountingTransactionHandlerObj = new tx_ptgsapdfdocs_gsaTransactionHandler();
        try {
            
            trace($_SERVER['argv']);
        	if (!$_SERVER['argv'][1]) {
                    echo 
                     "---------------------------------------------------------------------\n".
                     "No options given \n".
                     "---------------------------------------------------------------------\n";
        		return false;
        	}
            $this->processOptions();
            
            trace($this->command,0,'$this->command');
            switch ($this->command) {
                case 'handle_pdfsign':
                    $this->handlePdfsign();
                    echo 
                     "---------------------------------------------------------------------\n".
                     "Command ".$this->command." ended\n".
                     "---------------------------------------------------------------------\n";
                    return false;
                    break;
                default:
                    $this->cliHandler->cliMessage("Invalid command '".$this->command."'.", true, 1);
                    echo 
                     "---------------------------------------------------------------------\n".
                     "Invalid command ".$this->command."\n".
                     "---------------------------------------------------------------------\n";
                    return false;
            }
            
        } catch (tx_pttools_exception $excObj) {
            
            // if an exception has been catched, handle it and display error message
            $this->cliHandler->cliMessage($excObj->__toString()."\n", true, 1);
            
        }
        return true;
        
    }
    
    
    
    /***************************************************************************
    *   BUSINESS LOGIC METHODS
    ***************************************************************************/
    
    /** 
     * Processes the command line arguments as options and sets the resulting class properties
     *
     * @param   void
     * @return  void       
     * @author  Dorit Rottner <rottner@punkt.de>
     * @since   2008-09-16
     */
    private function processOptions() {
        trace('[METHOD] '.__METHOD__);
        
        $console = new Console_Getopt;  // PEAR module (see http://pear.php.net/manual/en/package.console.console-getopt.php)
        trace('before $parsedOptionsArr');
        $parsedOptionsArr = $this->cliHandler->getOptions($console, $this->shortOptions, $this->helpString, $this->longOptionsArr, true);
        
        trace($parsedOptionsArr,0,'$parsedOptionsArr');
        $this->documentType = 'invoice';
        // evaluate options, set properties
        for ($i=0; $i<sizeOf($parsedOptionsArr); $i++) {
            if ($parsedOptionsArr[$i][0] == 'h' || $parsedOptionsArr[$i][0] == '--help') {
                die($this->helpString);
            }
            if ($parsedOptionsArr[$i][0] == 'c' || $parsedOptionsArr[$i][0] == '--command') {
                $this->command = $parsedOptionsArr[$i][1];
            }
            if ($parsedOptionsArr[$i][0] == 't' || $parsedOptionsArr[$i][0] == '--type') {
                $this->documentType = $parsedOptionsArr[$i][1];
            }
        }
        trace($parsedOptionsArr,0,'$parsedOptionsArr');
            
    }
    

    /**
     * handle PDF's for signing Process    
     * @param   void       
     * @return  void
     * @author  Dorit Rottner <rottner@punkt.de>
     * @since   2008-09-17
     */
    
    private function handlePdfsign(){
    	trace('[CMD] '.__METHOD__);
		// get Documents with are not signed and signing is not in process
    	$documents = new tx_ptgsapdfdocs_documentCollection($this->documentType);
    	foreach ($documents as $document) {
    		#trace($document,0,'document');
   	 		if (file_exists(PATH_site.$document->get_file())) {
	    		$filename = end(explode('/',$document->get_file()));
	   	 		trace($filename,0,'filename');
	   	 		$fileout = $this->extConfArr['signPathCopyOut']. $filename;
	   	 		trace($fileout.', copy: '.PATH_site.$document->get_file(),0,'fileout ');
	   	 		copy(PATH_site.$document->get_file(), $fileout);
				if (!file_exists($this->extConfArr['signPathOut'].$filename))	{  	 		
	   	 			link($fileout,$this->extConfArr['signPathOut'].$filename);
				}
	   	 		$document->setSignProcessing(true);
   	 		}
    		trace($document,0,'document');
    	}

   	 	// get Documents with are not signed and signing is in process
    	$documents = new tx_ptgsapdfdocs_documentCollection($this->documentType,true);
    	foreach ($documents as $document) {
   	 		$filename = end(explode('/',$document->get_file()));
   	 		trace($filename,0,'filename');
   	 		$filein = $this->extConfArr['signPathIn']. $filename;
   	 		trace($filein,0,'filein');
   	 		// Falls datei existiert und älter als Minuten
   	 		trace(60*$this->extConfArr['signTime'],0,'sign Time in seconds');
   	 		trace(time(),0,'time');
   	 		if (file_exists($filein)) {
				if(filectime($filein) + 60*$this->extConfArr['signTime'] < time()) {
   	 				rename (PATH_site.$document->get_file(), PATH_site.$document->get_file().'_unsigned');
   	 				link($filein, PATH_site.$document->get_file());
					unlink($filein);
		   	 		#rename(PATH_site.$document->get_file().'writing',PATH_site.$document->get_file());
		   	 		$document->setSignProcessing(false);
		   	 		$document->setSigned(true);
	    			trace($document,0,'document');
				}
   	 		}
   	 	}
    }


}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pt_gsapdfdocs/cronmod/class.tx_ptgsapdfdocs_pdfsign.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pt_gsapdfdocs/cronmod/class.tx_ptgsapdfdocs_pdfsign.php']);
}

?>
