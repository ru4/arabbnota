<?php if (!defined('APPLICATION')) exit();

$PluginInfo['QuickConfigSave'] = array(
	'Name' => 'Quick SaveToConfig()',
	'Description' => 'Admins can edit config here.',
	'Version' => '1.0.0',
	'Date' => '26 Oct 2011',
	'Updated' => 'Autumn 2011',
	'Author' => 'Gerry Perry',
	'AuthorUrl' => 'http://www.youtube.com/watch?v=VlT7xV3VgaY',
	'SettingsPermission' => 'Only.For.Admins',
	'SettingsUrl' => '/dashboard/plugin/conf'
);

class QuickConfigSavePlugin extends Gdn_Plugin {
	
	public function PluginController_Conf_Create($Sender) {
		$Sender->Permission('Only.For.Admins');
		$this->Dispatch($Sender);
	}
	
	private static function InformMessage($Message, $Sprite = 'Check') {
		$Controller = Gdn::Controller();
		if ($Controller) {
			$Options = array('Sprite' => $Sprite, 'CssClass' => 'Dismissable AutoDismiss');
			$Controller->InformMessage($Message, $Options);
		}
	}
	
	public function Controller_Index($Sender) {
		$Form =& $Sender->Form;
		if ($Form->AuthenticatedPostBack()) {
			$FormValues = $Form->FormValues();
			extract($FormValues, EXTR_SKIP);
			$Options = array('RemoveEmpty' => $RemoveEmpty);
			if ($Name) { // 1) Simple SaveToConfig(Name, Value)
				settype($Value, $Type);
				SaveToConfig($Name, $Value, $Options);
				self::InformMessage(T('Saved'), 'Check');
			} elseif ($Configuration) {
				$NewLines = self::EvalConfigurationCode($Configuration);
				if (!is_array($NewLines)) $Form->AddError($NewLines);
				if ($Form->ErrorCount() == 0) {
					$ValueCode = "\n" . implode("\n", $NewLines);
					file_put_contents(PATH_CONF . '/config.php', $ValueCode, FILE_APPEND | LOCK_EX);
					self::InformMessage(T('Saved'), 'Check');
				}
			}
		}
		$Sender->Title($this->GetPluginKey('Name'));
		$Sender->AddSideMenu('plugin/'.$this->GetPluginIndex());
		$Sender->View = $this->GetView('index'.'.php');
		$Sender->Render();
	}
	
	public function Setup() {
		//if (!Gdn::Router()->GetRoute('conf')) Gdn::Router()->SetRoute('conf', 'dashboard/plugin/conf', 'Internal');
	}
	
	/**
	* Eval code.
	* Returns array() on success.
	* Returns string on failure.
	* 
	* @param mixed $Code
	* @return mixed $Result.
	*/
	private static function EvalConfigurationCode($Code) {
		$Lines = explode("\n", $Code);
		$NewLines = array();
		foreach ($Lines as $Line) {
			$Line = trim($Line);
			if ($Line == '') continue;
			if (substr($Line, 0, 14) != '$Configuration') return 'Unexpected: ' . SliceString($Line, 50);
			$Tokens = token_get_all('<?php '.$Line);
			$Key = array_search(';', $Tokens, True);
			if ($Key === False) continue;
			$Tokens = array_slice($Tokens, 0, $Key + 1);
			$JoinStrings = False;
			$Line = '';
			foreach ($Tokens as $Token) {
				if ($JoinStrings == False) {
					if (is_array($Token) && $Token[0] == 309 && $Token[1] == '$Configuration') {
						$JoinStrings = True;
					}
				}
				if (!$JoinStrings) continue;
				if (is_array($Token)) $Token = $Token[1];
				$Line .= $Token;
			}
			$NewLines[] = $Line;
		}
		if (count($NewLines) == 0) return 'Nothing to write.';
		return $NewLines;
		//if (!$ErrorString) $ErrorString = 'Something funky happened.';
		//return $ErrorString;
	}
}