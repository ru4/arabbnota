<?php if (!defined('APPLICATION')) exit();

// Define the plugin:
$PluginInfo['Liked'] = array(
   'Name' => 'Liked',
   'Description' => 'Adds the facebook like feature to your discussions.',
   'Version' => '1.4',
   'Author' => "Gary Mardell",
   'AuthorEmail' => 'gary@vanillaplugins.com',
   'AuthorUrl' => 'http://garymardell.co.uk'
);

class LikedPlugin extends Gdn_Plugin {
	
	private $Code = '<div style="float: left; margin: -40px 0 0 40px; z-index: 999; position: relative"><fb:like href="%s" layout="button_count" width="60" show_faces="false" font="lucida grande"></fb:like></div>';
	   
	public function DiscussionController_Render_Before(&$Sender) {
		$Discussion = GetValue('Discussion', $Sender, FALSE);
		if (is_object($Discussion)) {
			$Sender->Head->AddTag('meta', array('content' => Gdn_Format::Text($Sender->Discussion->Name), 'property' => 'og:title'));
			$Sender->Head->AddTag('meta', array('content' => Gdn_Url::Request(true, true, true), 'property' => 'og:url'));
			$Sender->Head->AddTag('meta', array('content' => C('Garden.Title'), 'property' => 'og:site_name'));
			$Sender->Head->AddTag('meta', array('content' => 'article', 'property' => 'og:type'));
			$Sender->AddJsFile('http://connect.facebook.net/ar_AR/all.js#xfbml=1');
		}
	}
	
	public function DiscussionController_BeforeDiscussion_Handler(&$Sender) {
		echo sprintf($this->Code, Gdn_Url::Request(true, true, true));
	}

   public function Setup() {
      // No setup required.
   }
}

