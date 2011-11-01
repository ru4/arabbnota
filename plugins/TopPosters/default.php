<?php if (!defined('APPLICATION')) exit();

// Define the plugin:
$PluginInfo['TopPosters'] = array(
   'Name' => 'TopPosters',
   'Description' => "Lists the users with most posted comment on sidepanel",
   'Version' => '1.4.1',
   'Author' => "Adrian Lee",
   'AuthorEmail' => 'l33.adrian@gmail.com',
   'AuthorUrl' => 'http://www.pinoyau.info',   
   'SettingsPermission' => FALSE
);

class TopPostersPlugin extends Gdn_Plugin {

	public function DiscussionController_BeforeDiscussionRender_Handler(&$Sender) {		
		$this->_CacheBadges($Sender);
	}
  
	public function PostController_BeforeCommentRender_Handler(&$Sender) {
		$this->_CacheBadges($Sender);
	}
   
	protected function _CacheBadges(&$Sender) {	
		$Discussion = $Sender->Data('Discussion');
		$Comments = $Sender->Data('CommentData');
		$TopPostersModule = new TopPostersModule();

		$UserIDList = array();		
		if ($Discussion)
			$UserIDList[$Discussion->InsertUserID] = 1;
		 
		if ($Comments && $Comments->NumRows()) {
			$Comments->DataSeek(-1);
			while ($Comment = $Comments->NextRow())
				$UserIDList[$Comment->InsertUserID] = 1;
		}

		$UserBadges = array();
		if (sizeof($UserIDList)) {
			$Limit = Gdn::Config('TopPosters.Limit');		
			$Limit = (!$Limit || $Limit ==0)?10:$Limit;	
			$arrExcludedUsers = Gdn::Config('TopPosters.Excluded');
			$usersExcluded = (is_array($arrExcludedUsers))?' AND UserID not in ('.Implode(',',$arrExcludedUsers).')':"";
			$TopPostersModule->GetData();
			$Badges = $TopPostersModule->getTopPosters();

			$Badges->DataSeek(-1);

			 $i =1;
			 while ($UserBadge = $Badges->NextRow())
				$UserBadges[$UserBadge->UserID] = $i++;
		}

		$Sender->SetData('Plugin-Badge-Counts', $UserBadges);
	}
   
	public function DiscussionController_CommentInfo_Handler(&$Sender) {		
		if(Gdn::Config('TopPosters.Show.Medal') == "both" || Gdn::Config('TopPosters.Show.Medal') == 'thread')  $this->_AttachBadge($Sender);
	}
   
	public function PostController_CommentInfo_Handler(&$Sender) {
		if(Gdn::Config('TopPosters.Show.Medal') == "both" || Gdn::Config('TopPosters.Show.Medal') == 'thread')  $this->_AttachBadge($Sender);
	}
   
	protected function _AttachBadge(&$Sender) {   
		$badge = ArrayValue($Sender->EventArguments['Author']->UserID, $Sender->Data('Plugin-Badge-Counts'));
		if($badge >0){
			$icon = (file_exists( 'plugins/TopPosters/badges/'.$badge.'.png')? $badge.'.png':'medal-icon.png');
			echo '<span><img src="'.str_replace("index.php?p=","",Gdn::Request()->Domain().Url('plugins/TopPosters/badges/'.$icon)).'" style="width:16px;height:16px;vertical-align:middle"></span>';
		}
	}   
	public function PluginController_TopPosters_Create(&$Sender) {
		$Sender->AddSideMenu('plugin/topposters');
		$Sender->Form = new Gdn_Form();
		$Validation = new Gdn_Validation();
		$ConfigurationModel = new Gdn_ConfigurationModel($Validation);
		$ConfigurationModel->SetField(array('TopPosters.Location.Show', 'TopPosters.Limit', 'TopPosters.Excluded','TopPosters.Show.Medal'));
		$Sender->Form->SetModel($ConfigurationModel);
			
		if ($Sender->Form->AuthenticatedPostBack() === FALSE) {    
			$Sender->Form->SetData($ConfigurationModel->Data);    
		} else {
			$Data = $Sender->Form->FormValues();
			$ConfigurationModel->Validation->ApplyRule('TopPosters.Limit', array('Required', 'Integer'));
			$ConfigurationModel->Validation->ApplyRule('TopPosters.Location.Show', 'Required');
			if ($Sender->Form->Save() !== FALSE)
				$Sender->StatusMessage = Gdn::Translate("Your settings have been saved.");
		}
		$TopPostersModule = new TopPostersModule($Sender);	  
		$Sender->AllUsers = $TopPostersModule->GetAllUsers();
		$Sender->Render($this->GetView('topposters.php'));
	}

	public function Base_Render_Before(&$Sender) {
		$ConfigItem = Gdn::Config('TopPosters.Location.Show', 'every');

		$Controller = $Sender->ControllerName;
		$Application = $Sender->ApplicationFolder;
		$Session = Gdn::Session();     		

		$ShowOnController = array();		
		switch($ConfigItem) {
			case 'every':
				$ShowOnController = array(
					'discussioncontroller',
					'categoriescontroller',
					'discussionscontroller',
					'profilecontroller',
					'activitycontroller',
					'draftscontroller'
				);
				break;
			case 'discussion':
			default:
				$ShowOnController = array(
					'discussioncontroller',
					'discussionscontroller',
					'categoriescontroller',
					'draftscontroller'
				);				
		}
		if (!InArrayI($Controller, $ShowOnController)) return;	  

		include_once(PATH_PLUGINS.DS.'TopPosters'.DS.'class.toppostersmodule.php');
		$TopPostersModule = new TopPostersModule($Sender);
		$TopPostersModule->GetData();	   
		$Sender->AddModule($TopPostersModule);
		$Limit = Gdn::Config('TopPosters.Limit', 4);
		if (!is_numeric($Limit)) $Limit = 4;      
		$Sender->AddDefinition('TopPostersLimit', $Limit);

	}

	public function Base_GetAppSettingsMenuItems_Handler(&$Sender) {
		$Menu = $Sender->EventArguments['SideMenu'];
		$Menu->AddLink('Add-ons', 'Top Posters', 'plugin/topposters', 'Garden.Themes.Manage');
	}   

	public function Setup() { 
	//nothing to do here
	}
}
