<?php if (!defined('APPLICATION')) exit();

// Define the plugin:
$PluginInfo['LatestComment'] = array(
   'Name' => 'LatestComment',
   'Description' => "Lists the Latest Commented or Most Commented Discussions on sidepanel",
   'Version' => '1.0',
   'Author' => "Taran Sahota",
   'AuthorEmail' => 'taran@solgens.com',
   'AuthorUrl' => 'http://www.taransahota.com',   
   'SettingsPermission' => FALSE
);

class LatestCommentPlugin extends Gdn_Plugin {
		public function PluginController_LatestComment_Create(&$Sender) {
		$Sender->AddSideMenu('plugin/latestcomment');
		$Sender->Form = new Gdn_Form();
		$Validation = new Gdn_Validation();
		$ConfigurationModel = new Gdn_ConfigurationModel($Validation);
		$ConfigurationModel->SetField(array('LatestComment.Show.LatestComment','LatestComment.Location.Show', 'LatestComment.Limit', 'LatestComment.Show.User'));
		$Sender->Form->SetModel($ConfigurationModel);
			
		if ($Sender->Form->AuthenticatedPostBack() === FALSE) {    
			$Sender->Form->SetData($ConfigurationModel->Data);    
		} else {
			$Data = $Sender->Form->FormValues();
			$ConfigurationModel->Validation->ApplyRule('LatestComment.Show.LatestComment', 'Required');
			$ConfigurationModel->Validation->ApplyRule('LatestComment.Location.Show', 'Required');
			$ConfigurationModel->Validation->ApplyRule('LatestComment.Limit', array('Required', 'Integer'));
			//$ConfigurationModel->Validation->ApplyRule('LatestComment.Location.Show', 'Required');
			if ($Sender->Form->Save() !== FALSE)
				$Sender->StatusMessage = Gdn::Translate("Your settings have been saved.");
		}
		$LatestCommentModule = new LatestCommentModule($Sender);	  
		$Sender->AllDiscussions = $LatestCommentModule->GetAllDiscussion();
		$Sender->Render($this->GetView('latestcomment.php'));
	}

	public function Base_Render_Before(&$Sender) {
		$ConfigItem = Gdn::Config('LatestComment.Location.Show', 'every');
		$Controller = $Sender->ControllerName;
		$Application = $Sender->ApplicationFolder;
		$Session = Gdn::Session();
		$ShowOnController = array();		
		switch($ConfigItem) {
			case 'every':
				$ShowOnController = array('discussioncontroller','categoriescontroller','discussionscontroller','profilecontroller','activitycontroller','draftscontroller','messagescontroller');
				break;
			case 'discussion':
				$ShowOnController = array('discussioncontroller','discussionscontroller','categoriescontroller','draftscontroller');	
				break;
			case 'profile':
				$ShowOnController = array('profilecontroller','messagescontroller');	
			break;
			case 'activity':
				$ShowOnController = array('activitycontroller');	
			break;
			default:
				$ShowOnController = array('discussioncontroller','categoriescontroller','discussionscontroller','profilecontroller','activitycontroller','draftscontroller','messagescontroller');
		}
		if (!InArrayI($Controller, $ShowOnController)) return;	  
		include_once(PATH_PLUGINS.DS.'LatestComment'.DS.'class.latestcommentmodule.php');
		$LatestCommentModule = new LatestCommentModule($Sender);
		$LatestCommentModule->GetData();
		$Sender->AddModule($LatestCommentModule);
		$Limit = Gdn::Config('LatestComment.Limit', 10);
		if (!is_numeric($Limit)) $Limit = 10;
		$Sender->AddDefinition('LatestComment', $Limit);
	}	
	
	public function Base_GetAppSettingsMenuItems_Handler(&$Sender) {
		$Menu = $Sender->EventArguments['SideMenu'];
		$Menu->AddLink('Add-ons', 'Latest Comments', 'plugin/latestcomment', 'Garden.Themes.Manage');
	}   
	public function Setup() { 
	//nothing to do here
	}
}
