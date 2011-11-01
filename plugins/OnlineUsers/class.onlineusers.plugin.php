<?php if (!defined('APPLICATION')) exit();

// Define the plugin:
$PluginInfo['OnlineUsers'] = array(
   'Name' => 'Online Users',
   'Description' => "This plugin requires whosonline 1.3 plugin to run. displays an online/offline image in the CommentInfo section",
   'Version' => '1.1',
   'MobileFriendly' => TRUE,
   'RequiredApplications' => FALSE,
   'RequiredTheme' => FALSE, 
   'RequiredPlugins' => 'whosonlineV1.3',   
   'RegisterPermissions' => FALSE,
   'Author' => "Adrian Lee",
   'AuthorEmail' => 'L33.adrian@gmail.com',
   'AuthorUrl' => 'http://pinoyau.info'
);

class OnlineUsersPlugin extends Gdn_Plugin {  
   
   
   public function DiscussionController_BeforeDiscussionRender_Handler(&$Sender) {
      $this->_CacheOnlineUserss($Sender);
	  $Sender->AddJsFile('/plugins/OnlineUsers/tt.js');
   }
   
   public function PostController_BeforeCommentRender_Handler(&$Sender) {
      $this->_CacheOnlineUserss($Sender);
   }
   
   protected function _CacheOnlineUserss(&$Sender) {
   //logic taken from Who's Online plugin
   $SQL = Gdn::SQL();
		// $this->_OnlineUsers = $SQL
		// insert or update entry into table
		$Session = Gdn::Session();

		$Invisible = ($Invisible ? 1 : 0);

		if ($Session->UserID)
			$SQL->Replace('Whosonline', array(
				'UserID' => $Session->UserID,
				'Timestamp' => Gdn_Format::ToDateTime(),
				'Invisible' => $Invisible),
				array('UserID' => $Session->UserID)
			);     

		$Frequency = C('WhosOnline.Frequency', 4);
		$History = time() - $Frequency;

		$SQL
			->Select('u.UserID, u.Name, w.Timestamp, w.Invisible')
			->From('Whosonline w')
			->Join('User u', 'w.UserID = u.UserID')
			->Where('w.Timestamp >=', date('Y-m-d H:i:s', $History))
			->OrderBy('u.Name');

		if (!$Session->CheckPermission('Plugins.WhosOnline.ViewHidden'))
			$SQL->Where('w.Invisible', 0);

		$OnlineUsers = $SQL->Get();
		
	$arrOnline = Array();
	
	 if($OnlineUsers->NumRows() > 0) {	
	   foreach($OnlineUsers->Result() as $User) {
		  $arrOnline[] =$User->UserID;			  
	   }
	 }
      $Sender->SetData('Plugin-OnlineUsers-Marker', $arrOnline);
   }
   
   public function DiscussionController_CommentInfo_Handler(&$Sender) {
      $this->_AttachOnlineUsers($Sender);
   }
   
   public function PostController_CommentInfo_Handler(&$Sender) {
      $this->_AttachOnlineUsers($Sender);
   }
   
   protected function _AttachOnlineUsers(&$Sender) {
      if(in_array($Sender->EventArguments['Author']->UserID, $Sender->Data('Plugin-OnlineUsers-Marker'))){
		echo "<img src='".Url('/plugins/OnlineUsers/status-online-icon.png')."' style='vertical-align:middle' title='Online'> ";
	  }else{
		echo "<img src='".Url('/plugins/OnlineUsers/status-offline-icon.png')."' style='vertical-align:middle' title='Offline'> ";
	  }
   }

   public function Setup() {
      // Nothing here!
   }
   
   public function Structure() {
      // Nothing here!
   }
         
}