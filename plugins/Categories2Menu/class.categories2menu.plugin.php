<?php if (!defined('APPLICATION')) exit();

/**
 * Define the plugin:
 */
$PluginInfo['Categories2Menu'] = array(
	'Name'			=> 'Categories → Menu',
	'Description'	=> 'Adds all Categories as Sub-Menu into the Discussions Menu in the navigation bar.',
	'Version'		=> '1.7.2',
	'Author'		=> 'Oliver Raduner',
	'AuthorEmail'	=> 'vanilla@raduner.ch',
	'AuthorUrl'		=> 'http://raduner.ch/',
	'License'		=> 'Free',
	'RequiredPlugins' => FALSE,
	'HasLocale'		=> FALSE,
	'RegisterPermissions' => FALSE,
	'SettingsUrl'	=> FALSE,
	'SettingsPermission' => FALSE,
	'MobileFriendly' => FALSE,
	'RequiredApplications' => array('Vanilla' => '>=2.0.17')
);


/**
 * Categories into Discussions Menu Plugin
 *
 * Adds all Categories as Sub-Menu to the Discussions Menu in the navigation bar.
 *
 * @version 1.7
 * @date 12-JAN-2011
 * @author Oliver Raduner <vanilla@raduner.ch>
 */
class Categories2MenuPlugin extends Gdn_Plugin
{
	
	protected $_CategoryData;
	
	
	/**
	 * Hack the Base Render in order to achieve our goal
	 * 
	 * @since 1.0
	 * @version 1.7.2
	 */
	public function Base_Render_Before(&$Sender)
	{
		// Attach the Plugin's CSS to the site
		$Sender->AddCssFile($this->GetResource('categories2menu.css', FALSE, FALSE));
		
		$Cat2MenuJQuerySource =
'<script type="text/javascript">
var ddmenuitem = 0;
var menustyles = { "visibility":"visible", "display":"block", "z-index":"9"}

function Menu_close()
{  if(ddmenuitem) { ddmenuitem.css("visibility", "hidden"); } }

function Menu_open()
{  Menu_close();
   ddmenuitem = $(this).find("ul").css(menustyles);
}

jQuery(document).ready(function()
{  $("ul#Menu > li").bind("mouseover", Menu_open);
   $("ul#Menu > li").bind("mouseout", Menu_close);
});

document.onclick = Menu_close;</script>
';
		// Add the jQuery JavaScript to the page
		$Sender->Head->AddString($Cat2MenuJQuerySource);
		
		
		if ($Sender->Menu)
		{
			// Set this to FALSE|TRUE whether you want to display the Discussion-Counter next to each Category or not
			$DisplayCounter = TRUE;
			
			// Build the Categories Model & load Categories Data
			$CategoryModel = new CategoryModel();
			$_CategoryData = $CategoryModel->GetFull();
			
			// If there are any Categories...
			if ($_CategoryData != FALSE)
			{
				// Add a link to the Category overview as first menuitem
				$Sender->Menu->AddLink('Discussions', T('→ All Categories'), '/categories/all');
				
				// If $DisplayCounter is set to TRUE, get Count discussions per Category separately
				$CountDiscussions = 0;
				foreach ($_CategoryData->Result() as $Category) {
					// (will ignore root node)
					if ($Category->Name <> 'Root') $CountDiscussions = $CountDiscussions + $Category->CountDiscussions;
				}
				
				// Fetch every single Category...
				foreach ($_CategoryData->Result() as $Category)
				{
					if ($Category->Name <> 'Root')
					{
						if ($DisplayCounter == TRUE)
						{
							// Build the Categories-Menu with Discussions-Counter
							$Sender->Menu->AddLink('Discussions', $Category->Name.' <span>'.$Category->CountDiscussions.'</span>', '/categories/'.$Category->UrlCode, FALSE);
						} else {
							// Build the Categories-Menu
							$Sender->Menu->AddLink('Discussions', $Category->Name, '/categories/'.$Category->UrlCode, FALSE);
						}
					}
				}
			}
		}
	}
	
	
	/**
	 * Initialize required data
	 *
	 * @since 1.0
	 * @version 1.0
	 */
	public function Setup() { }	
		
}

?>