<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');


/*******************************************************************************
 * FRONTEND HOOKS   - !!IMPORTANT: clear conf cache to activate changes!!
 ******************************************************************************/

if (TYPO3_MODE == 'FE') { // WARNING: do not remove this condition since this may stop the backend from working!
    
    
    /*
     * pt_gsashop orderProcessor_hooks
     */
	$emConf = tx_pttools_div::returnExtConfArray('pt_gsapdfdocs', true);
	if ($emConf['generatePdfsInFixFinalOrderHook']) {
    	$TYPO3_CONF_VARS['EXTCONF']['pt_gsashop']['orderProcessor_hooks']['fixFinalOrderHook'][] = 'EXT:pt_gsapdfdocs/res/pt_gsashop_hooks/class.tx_ptgsapdfdocs_hooks_ptgsashop_orderProcessor.php:tx_ptgsapdfdocs_hooks_ptgsashop_orderProcessor->fixFinalOrderHook';     // hook array (loop processing)
	}
    
	require(t3lib_extMgm::extPath('pt_gsapdfdocs').'res/pt_gsashop_hooks/class.tx_ptgsapdfdocs_hooks_ptgsashop_pi4.php');
	$TYPO3_CONF_VARS['EXTCONF']['pt_gsashop']['pi4_hooks']['displayOrdersList_MarkerArrayHook'][] = 'tx_ptgsapdfdocs_hooks_ptgsashop_pi4';
        
}

// eID-Skript for downloading the files
$GLOBALS['TYPO3_CONF_VARS']['FE']['eID_include']['tx_ptgsapdfdocs_download'] = 'EXT:pt_gsapdfdocs/eID/download.php';

// only for testing
// $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['pt_mvc']['controller_actions']['tx_ptgsapdfdocs_controller_download']['hookTestAction'] = 'EXT:pt_gsapdfdocs/res/class.tx_ptgsapdfdocs_controller_download_hook.php:tx_ptgsapdfdocs_controller_download_hook->hookTestAction';

?>