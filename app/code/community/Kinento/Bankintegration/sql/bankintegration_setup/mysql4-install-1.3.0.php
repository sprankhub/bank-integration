<?php
$installer = $this;
$installer->startSetup();

$installer->run( "
CREATE TABLE {$this->getTable( 'bankintegration' )} (
  `entry_id` int(10) unsigned NOT NULL auto_increment,
  `date` varchar(10) NOT NULL default '',
  `name` varchar(1023) NOT NULL default '',
  `account` varchar(255) NOT NULL default '',
  `code` varchar(2) NOT NULL default '',
  `type` varchar(100) NOT NULL default '',
  `amount` decimal(12,4) NOT NULL,
  `mutation` varchar(255) NOT NULL default '',
  `remarks` varchar(1023) NOT NULL default '',
  `identifier` varchar(32) NOT NULL default '',
  `bindorder` varchar(255) NOT NULL default '',
  `bindname` varchar(255) NOT NULL default '',
  `status` enum('processed','certain','guess','unbound','neglected') NOT NULL default 'unbound',
  `bindamount` decimal(12,4) NULL,
  PRIMARY KEY  (`entry_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
  " );

$installer->endSetup();
?>
