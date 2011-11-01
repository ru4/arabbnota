<?php if (!defined('APPLICATION')) exit();
/*
Copyright 2008, 2009 Vanilla Forums Inc.
This file is part of Garden.
Garden is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
Garden is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
You should have received a copy of the GNU General Public License along with Garden.  If not, see <http://www.gnu.org/licenses/>.
Contact Vanilla Forums Inc. at support [at] vanillaforums [dot] com
*/

// Define the plugin:
$PluginInfo['Participated'] = array(
   'Name' => 'Participated Discussions',
   'Description' => "This plugin adds a tab to the main discussions view that displays a list of the logged-in user's participated discussions.",
   'Version' => '1.0.0',
   'MobileFriendly' => TRUE,
   'RequiredApplications' => FALSE,
   'RequiredTheme' => FALSE, 
   'RequiredPlugins' => FALSE,
   'HasLocale' => TRUE,
   'RegisterPermissions' => FALSE,
   'Author' => "Tim Gunter",
   'AuthorEmail' => 'tim@vanillaforums.com',
   'AuthorUrl' => 'http://www.vanillaforums.com'
);

class ParticipatedPlugin extends Gdn_Plugin {

   protected $Participated = NULL;
   protected $CountParticipated = NULL;
   
   public function DiscussionsController_AfterInitialize_Handler($Sender) {
      $this->GetCountParticipated();
   }
   
   protected function GetCountParticipated() {
      if (is_null($this->CountParticipated)) {
         $DiscussionModel = new DiscussionModel();
         try {
            $this->CountParticipated = $DiscussionModel->GetCountParticipated(NULL);
         } catch (Exception $e) {
            $this->CountParticipated = FALSE;
         }
      }
      
      return $this->CountParticipated;
   }
   
   // CONTEXT: DiscussionModel
   public function DiscussionModel_GetParticipated_Create(&$Sender) {
      
      $UserID = GetValue(0, $Sender->EventArguments);
      $Offset = GetValue(1, $Sender->EventArguments);
      $Limit = GetValue(2, $Sender->EventArguments);
      
      if (is_null($UserID)) {
         if (!Gdn::Session()->IsValid()) throw new Exception(T("Could not get participated discussions for non logged-in user."));
         $UserID = Gdn::Session()->UserID;
      }
      
      $Sender->SQL->Reset();
      $Sender->DiscussionSummaryQuery();
      
      return $Sender->SQL->Select('d.*')
         ->Join('Comment c','d.DiscussionID = c.DiscussionID')
         ->Where('c.InsertUserID', $UserID)
         ->GroupBy('c.DiscussionID')
         ->Limit($Limit, $Offset)
         ->Get();
   }
   
   public function DiscussionModel_GetCountParticipated_Create(&$Sender) {
      
      $UserID = GetValue(0, $Sender->EventArguments);
      
      if (is_null($UserID)) {
         if (!Gdn::Session()->IsValid()) throw new Exception(T("Could not get participated discussions for non logged-in user."));
         $UserID = Gdn::Session()->UserID;
      }
      
      $Count = Gdn::SQL()->Select('c.DiscussionID','DISTINCT','NumDiscussions')
         ->From('Comment c')
         ->Where('c.InsertUserID', $UserID)
         ->GroupBy('c.DiscussionID')
         ->Get();
      
      return ($Count instanceof Gdn_Dataset) ? $Count->NumRows() : FALSE;
   }
   
   // CONTEXT: DiscussionsController
   public function DiscussionsController_AfterDiscussionTabs_Handler(&$Sender) {
      $Count = $this->GetCountParticipated();
      if ($Count > 0) {
         $MyParticipated = T('Participated Discussions');
         $MyParticipated .= '<span>'.$Count.'</span>';
         echo '<li '.(($Sender->RequestMethod == 'participated') ? ' class="Active"' : '').'>'.Anchor($MyParticipated, '/discussions/participated', 'MyParticipated').'</li>';
      }
   }
   
   // CONTEXT: DiscussionsController
   public function DiscussionsController_Participated_Create(&$Sender, $Args) {
      $Sender->Permission('Garden.SignIn.Allow');
      
      $Page = GetValue(0, $Args);
      $Limit = GetValue(1, $Args);
      
      list($Offset, $Limit) = OffsetLimit($Page, Gdn::Config('Vanilla.Discussions.PerPage', 30));
         
      // Get Discussions
      $DiscussionModel = new DiscussionModel();
      
      $Sender->DiscussionData = $DiscussionModel->GetParticipated(Gdn::Session()->UserID, $Offset, $Limit);
      $Sender->SetData('Discussions', $Sender->DiscussionData);
      
      $CountDiscussions = $DiscussionModel->GetCountParticipated(Gdn::Session()->UserID);
      $Sender->SetData('CountDiscussions', $CountDiscussions);
      
      // Build a pager
      $PagerFactory = new Gdn_PagerFactory();
		$Sender->EventArguments['PagerType'] = 'Pager';
		$Sender->FireEvent('BeforeBuildPager');
      $Sender->Pager = $PagerFactory->GetPager($Sender->EventArguments['PagerType'], $Sender);
      $Sender->Pager->ClientID = 'Pager';
      $Sender->Pager->Configure(
         $Offset,
         $Limit,
         $CountDiscussions,
         'discussions/participated/%1$s'
      );
		$Sender->FireEvent('AfterBuildPager');
      
      // Deliver JSON data if necessary
      if ($Sender->DeliveryType() != DELIVERY_TYPE_ALL) {
         $Sender->SetJson('LessRow', $Sender->Pager->ToString('less'));
         $Sender->SetJson('MoreRow', $Sender->Pager->ToString('more'));
         $Sender->View = 'discussions';
      }
      
      // Add modules
      $Sender->AddModule('NewDiscussionModule');
      $Sender->AddModule('CategoriesModule');
      $BookmarkedModule = new BookmarkedModule($Sender);
      $BookmarkedModule->GetData();
      $Sender->AddModule($BookmarkedModule);
      
      $Sender->Render($this->GetView('participated.php'));
   }
   
   public function Setup() {
      // Nothing to do here!
   }
   
   public function Structure() {
      // Nothing to do here!
   }
         
}