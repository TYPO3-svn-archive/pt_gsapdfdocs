#
# Table structure for table 'tx_ptgsapdfdocs_documents'
#
CREATE TABLE tx_ptgsapdfdocs_documents (
    uid int(11) NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,
    tstamp int(11) DEFAULT '0' NOT NULL,
    crdate int(11) DEFAULT '0' NOT NULL,
    cruser_id int(11) DEFAULT '0' NOT NULL,
    deleted tinyint(4) DEFAULT '0' NOT NULL,
    hidden tinyint(4) DEFAULT '0' NOT NULL,
	
	documenttype tinytext NOT NULL,
    file tinytext NOT NULL,
	ow_uid int(11) DEFAULT '0' NOT NULL,
    gsa_customer_id int(11) DEFAULT '0' NOT NULL,
	signProcessing int(3) DEFAULT '0' NOT NULL,
	signed int(3) DEFAULT '0' NOT NULL,

    PRIMARY KEY (uid),
    KEY parent (pid), 
);



#
# Table structure for table 'tx_ptgsashop_order_wrappers'
#
CREATE TABLE tx_ptgsashop_order_wrappers (
	irrePdfDocuments int(11) DEFAULT '0' NOT NULL,
);