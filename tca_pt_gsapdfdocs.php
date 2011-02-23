<?php

if (!defined ('TYPO3_MODE')) die ('Access denied.');

$TCA['tx_ptgsapdfdocs_documents'] = array(
    'ctrl' => $TCA['tx_ptgsapdfdocs_documents']['ctrl'],
    'interface' => array(
        'showRecordFieldList' => 'hidden,documenttype,file,ow_uid,gsa_customer_id, signProcessing, signed'
    ),
    'feInterface' => $TCA['tx_ptgsapdfdocs_documents']['feInterface'],
    'columns' => array(
        'hidden' => array(
            'exclude' => 1,
            'label' => 'LLL:EXT:lang/locallang_general.php:LGL.hidden',
            'config' => array(
                'type' => 'check',
                'default' => '0'
            )
        ),
        'documenttype' => array(
            'exclude' => 1,
            'label' => 'LLL:EXT:pt_gsapdfdocs/locallang_db.xml:tx_ptgsapdfdocs_documents.documenttype',
        	'config' => array (
                'type' => 'select',
                'items' => array(
                    array('LLL:EXT:pt_gsapdfdocs/locallang_db.xml:tx_ptgsapdfdocs_documents.documenttype.invoice', 'invoice'),
                    array('LLL:EXT:pt_gsapdfdocs/locallang_db.xml:tx_ptgsapdfdocs_documents.documenttype.manifest', 'manifest'),
                    ),
                'maxitems' => 1,
                'size' => 1,
			),
        ),
        'file' => array(
            'exclude' => 1,
            'label' => 'LLL:EXT:pt_gsapdfdocs/locallang_db.xml:tx_ptgsapdfdocs_documents.file',
            'config' => array(
                'type' => 'input',
                'size' => '30',
                'max' => '30',
            )
        ),
        'ow_uid' => array(
        	'exclude' => 1,
        	'label' => 'LLL:EXT:pt_gsapdfdocs/locallang_db.xml:tx_ptgsapdfdocs_documents.ow_uid',
            'config' => array(
                'type' => 'group',
                'internal_type' => 'db',
                'allowed' => 'tx_ptgsashop_order_wrappers',
                'size' => 1,
                'minitems' => 1,
                'maxitems' => 1,
                'eval' => 'required,int,nospace',
            )
        ),
        'gsa_customer_id' => array(
            'exclude' => 1,
            'label' => 'LLL:EXT:pt_gsapdfdocs/locallang_db.xml:tx_ptgsapdfdocs_documents.gsa_customer_id',
            'config' => array(
                'type' => 'input',
                'size' => '30',
                'max' => '11',
                'eval' => 'required,int',
            )
        ),
        'signProcessing' => array(
            'exclude' => 1,
            'label' => 'LLL:EXT:pt_gsapdfdocs/locallang_db.xml:tx_ptgsapdfdocs_documents.signProcessing',
            'config' => array(
                'type' => 'check',
                'default' => '0'
            )
        ),
        'signed' => array(
            'exclude' => 1,
            'label' => 'LLL:EXT:pt_gsapdfdocs/locallang_db.xml:tx_ptgsapdfdocs_documents.signed',
            'config' => array(
                'type' => 'check',
                'default' => '0'
            )
        ),
    ),
    'types' => array(
        '0' => array('showitem' => 'documenttype, file, ow_uid, gsa_customer_id, signProcessing, signed')
    ),
);



?>