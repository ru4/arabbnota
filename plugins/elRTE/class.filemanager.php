<?php if (!defined('APPLICATION')) exit();

require_once dirname(__FILE__).'/vendors/elfinder/connectors/php/elFinder.class.php';

class ElFinderFileManager extends elFinder {
	
	protected function _checkName($n) {
		$Result = parent::_checkName($n);
		$Extension = pathinfo($Result, 4);
		$Name = pathinfo($Result, 8);
		$Result = Gdn_Format::Clean($Name) . '.' . Gdn_Format::Clean($Extension);
		$Result = trim($Result, '.');
		return $Result;
	}
	
}