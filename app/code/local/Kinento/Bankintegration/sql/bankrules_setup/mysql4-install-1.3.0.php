<?php
$installer = $this;
$installer->startSetup();

$installer->run( "
CREATE TABLE {$this->getTable( 'bankrules' )} (
  `entry_id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(250) NOT NULL default '',
  `field` varchar(250) NOT NULL default '',
  `filter` varchar(250) NOT NULL default '',
  `type` enum('exact','partial') NOT NULL default 'exact',
  PRIMARY KEY  (`entry_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
  " );

$installer->endSetup();
