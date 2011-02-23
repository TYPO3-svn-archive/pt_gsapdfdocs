<?php

########################################################################
# Extension Manager/Repository config file for ext: "pt_gsapdfdocs"
#
# Auto generated 05-10-2007 14:45
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'GSA PDF Documents',
	'description' => 'PDF document generation for pt_gsashop',
	'category' => 'General Shop Applications',
	'author' => 'Fabrizio Branca',
	'author_email' => 'branca@punkt.de',
	'shy' => '',
	'dependencies' => 'pt_gsashop,fpdf,pt_xml2pdf,smarty,pt_tools,pt_mvc',
	'conflicts' => '',
	'priority' => '',
	'module' => '',
	'state' => 'alpha',
	'internal' => '',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
	'author_company' => 'punkt.de GmbH',
	'version' => '0.0.1dev',
	'constraints' => array(
		'depends' => array(
			'pt_gsashop' => '',
			'fpdf' => '0.1.2-',
			'pt_xml2pdf' => '',
            'smarty' => '',
            'pt_tools' => '',
			'pt_mvc' => '',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:5:{s:9:"ChangeLog";s:4:"4f16";s:10:"README.txt";s:4:"ee2d";s:12:"ext_icon.gif";s:4:"1bdc";s:19:"doc/wizard_form.dat";s:4:"f317";s:20:"doc/wizard_form.html";s:4:"acfe";}',
);

?>