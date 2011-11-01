<?php if (!defined('APPLICATION')) exit();

$PluginInfo['elRTE'] = array(
	'Name' => 'elRTE',
	'Description' => 'Plugin adds a small trigger near textarea (or it\'s label). This trigger allows you transform textarea to a wysiwyg editor (elRTE) with file manager (elFinder).',
	'Version' => '1.2.11',
	'Date' => 'Summer 2011',
	'Author' => 'Hrusha',
	'RegisterPermissions' => array(
		'Plugins.ElRte.Wysiwyg.Allow',
		'Plugins.ElRte.FileManager.Allow',
		'Plugins.ElRte.FileManager.Root',
		// TODO: Group permissions
		//'Plugins.ElRte.FileManager.Group',
		//'Plugins.ElRte.FileManager.GroupFiles',
		'Plugins.ElRte.FileManager.Files.Read',
		'Plugins.ElRte.FileManager.Files.Write',
		'Plugins.ElRte.FileManager.Files.Remove'
	)
);

// TODO: 
// 1. CDN Jquery and jQUERY UI

class ElRtePlugin extends Gdn_Plugin {
	
	protected static function LocaleLanguageCode() {
		$T = preg_split('/[_-]/', Gdn::Locale()->Current());
		return ArrayValue(0, $T, 'en');
	}
	
	public function PluginController_ElFinderConnector_Create($Sender) {
		require_once dirname(__FILE__).'/class.filemanager.php';
		$Options = self::GetOptions();
		$FileManager = new ElFinderFileManager($Options);
		$FileManager->Run();
	}
	
	/**
	* 
	*/
	protected static function GetOptions() {
		
		$DS = DIRECTORY_SEPARATOR;
		$Options = array();
		$Defaults = array('read'  => True, 'write' => False, 'rm' => False);
		$DefaultOptions = array(
			'root' => Null,
			'tmbDir' => '.thumbnails',
			'defaults' => $Defaults,
			'perms' => array()
		);
		// TODO: $CustomPermissions
		// $All = array('read'  => True, 'write' => True, 'rm' => True);
/*		$NowYear = date('Y');
		$CustomPermissions = array(
			"~$NowYear{$DS}.*~" => $All
		);*/
		
		$Session = Gdn::Session();
	
		if ($Session->CheckPermission('Plugins.ElRte.Wysiwyg.Allow')) {
			$Options['defaults'] = $Defaults;
			$Names = array('Read', 'Write', 'Remove');
			$Names = array_merge(array_combine($Names, $Names), array('Remove' => 'rm'));
			foreach ($Names as $Name => $Perm) {
				$Check = $Session->CheckPermission('Plugins.ElRte.FileManager.Files.'.$Name);
				$Options['defaults'][strtolower($Perm)] = (bool)$Check;
			}
			
			if ($Session->CheckPermission('Plugins.ElRte.FileManager.Root')) {
				$Options['root'] = 'uploads';
				$Options['URL'] = Url('uploads'). '/';
			} elseif ($Session->CheckPermission('Plugins.ElRte.FileManager.Allow')) {
				$UserID = GetValueR('User.UserID', $Session, 0);
				if (!$UserID || $UserID <= 0) throw new Exception('UserID is none.');
				$Folder = 'uploads/userfiles/'.$UserID;
				if (!is_dir($Folder)) mkdir($Folder, 0777, True);
				$Options['root'] = $Folder;
				$Options['URL'] = Url($Folder). '/';
			}
		}
		$Options = array_merge($DefaultOptions, $Options);
		return $Options;
	}
	
	public function Base_Render_Before($Sender) {
		if (!($Sender->DeliveryType() == DELIVERY_TYPE_ALL 
			&& $Sender->SyndicationMethod == SYNDICATION_NONE)) return;
		
		$Session = Gdn::Session();
		if ($Session->CheckPermission('Plugins.ElRte.Wysiwyg.Allow')) {
			$Sender->AddDefinition('LocaleLanguageCode', self::LocaleLanguageCode());
			
			if ($Session->CheckPermission('Plugins.ElRte.FileManager.Allow')) {
				$Sender->AddDefinition('FileManagerAllow', 1);
			}
			$Sender->AddJsFile('plugins/elRTE/vendors/dowhen/jquery.dowhen.min.js');
			$Sender->AddJsFile('plugins/elRTE/elrte.functions.js');
			$Sender->AddCssFile('plugins/elRTE/design/elrte.plugin.css');
		}

	}
	
	public function Setup() {
	}
}