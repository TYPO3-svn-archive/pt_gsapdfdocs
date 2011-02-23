<?php

t3lib_extMgm::addStaticFile($_EXTKEY,'static/','GSA PDF Documents');

// extend pt_gsacategories table "tx_ptgsashop_order_wrappers"
$tempColumns = Array (
	'irrePdfDocuments' => Array (
		'exclude' => 1,
		'label' => 'LLL:EXT:pt_gsapdfdocs/locallang_db.xml:irrePdfDocuments',
		'config' => Array (
			'type' => 'inline',
			'foreign_table' => 'tx_ptgsapdfdocs_documents',
			'foreign_field' => 'ow_uid',
			'maxitems' => 1000,
			'appearance' => Array (
				'collapseAll' => true,
				'expandSingle' => true,
                'newRecordLinkPosition' => 'none',
			),
		)
	),
);
t3lib_div::loadTCA('tx_ptgsashop_order_wrappers');
t3lib_extMgm::addTCAcolumns('tx_ptgsashop_order_wrappers',$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes('tx_ptgsashop_order_wrappers','--div--;LLL:EXT:pt_gsapdfdocs/locallang_db.xml:irrePdfDocuments, irrePdfDocuments');


t3lib_extMgm::allowTableOnStandardPages('tx_ptgsapdfdocs_documents');

$TCA['tx_ptgsapdfdocs_documents'] = array(
    'ctrl' => array(
        'title' => 'LLL:EXT:pt_gsapdfdocs/locallang_db.xml:tx_ptgsapdfdocs_documents',
        'label' => 'documenttype',
		'hideTable' => true,
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'default_sortby' => 'ORDER BY crdate',
        'delete' => 'deleted',
        'enablecolumns' => array(
            'disabled' => 'hidden',
        ),
        'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca_pt_gsapdfdocs.php',
        'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_ptgsapdfdocs_documents.png',
    ),
    'feInterface' => array(
        'fe_admin_fieldList' => 'hidden, documenttype, file, ow_uid, signProcessing, signed',
    )
);

t3lib_div::loadTCA('tt_content');

?>