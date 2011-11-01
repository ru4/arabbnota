<?php if (!defined('APPLICATION')) exit();

$PluginInfo['RoleBadges'] = array(
   'Name' => 'RoleBadges',
   'Description' => "Adds user's role badges under their name in comments.",
   'Version' => '0.1',
   'RequiredApplications' => array('Vanilla' => '2.0.17'),
   'RequiredTheme' => FALSE,
   'RequiredPlugins' => FALSE,
   'HasLocale' => FALSE,
//   'SettingsUrl' => 'plugins/RoleBadges',
   'SettingsPermission' => 'Garden.AdminUser.Only',
   'RegisterPermissions' => FALSE,
   'Author' => "Thomas Martin",
   'AuthorEmail' => 'thomas@thescoundrels.net',
   'AuthorUrl' => 'http://thescoundrels.net'
);

class RoleBadgesPlugin extends Gdn_Plugin {

   public function Base_Render_Before( $sender ) {
            $sender->AddCssFile( $this->GetResource( 'design/RoleBadges.css', FALSE, FALSE ) );
   }

   public function DiscussionController_CommentInfo_Handler( $sender ) {
      $this->attachBadge( $sender );
   }

   public function PostController_CommentInfo_Handler( $sender ) {
      $this->attachBadge( $sender );
   }

   protected function attachBadge( $sender ) {
      $roles = array();
      $userModel = new UserModel();
      $roleData = $userModel->GetRoles( $sender->EventArguments['Author']->UserID );
      if( $roleData !== FALSE && $roleData->NumRows(DATASET_TYPE_ARRAY) > 0 )
         $roles = ConsolidateArrayValuesByKey( $roleData->Result(), 'Name' );
      foreach( $roles as $role ) {
      	echo '<span class="RoleBadges ' . $role . 'Badge"></span>';
      }
   }
}
