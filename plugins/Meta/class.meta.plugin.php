<?php if(!defined('APPLICATION')) exit();
/*
 * Copyright 2011 Junaid P V
 * License: GPLv3
 */

$PluginInfo['Meta'] = array(
	'Name'			=>	'Meta',
	'Description' 	=>	'This plugin allow to add meta description and keywords',
	'Version' 		=>	'0.3.0',
	'Author' 		=>	"Junaid",
	'AuthorEmail' 	=>	'junu.pv@gmail.com',
	'AuthorUrl' 	=>	'http://junaidpv.in',
	'HasLocale' => TRUE
);

class DescriptionAndKeywordsPlugin extends Gdn_Plugin {
	public function Base_Render_Before(&$Sender) {
		$MetaDescriptionlimit = 40; // should not be more than 50
		$Description = Gdn::Config('Meta.Description');
		$Keywords = Gdn::Config('Meta.Keywords');

		$Sender->Head->AddTag('meta', array('name' => 'description', 'content'=>$Description));
		$Sender->Head->AddTag('meta', array('name' => 'keywords', 'content'=>$Keywords));

	}
	public function PluginController_Meta_Create(&$Sender) {
		
		$Sender->AddSideMenu('plugin/meta');
		
		$Sender->Form = new Gdn_Form();
		$Validation = new Gdn_Validation();
		$ConfigurationModel = new Gdn_ConfigurationModel($Validation);
		$ConfigurationModel->SetField(array('Meta.Description', 'Meta.Keywords'));
		$Sender->Form->SetModel($ConfigurationModel);
		
		if ($Sender->Form->AuthenticatedPostBack() === FALSE) {
			$Sender->Form->SetData($ConfigurationModel->Data);
		} else {
			$Data = $Sender->Form->FormValues();
			//$ConfigurationModel->Validation->ApplyRule('DescriptionAndKeywords.Description', 'Required');
			//$ConfigurationModel->Validation->ApplyRule('DescriptionAndKeywords.Keywords', 'Required');
			if ($Sender->Form->Save() !== FALSE) {

			}
		}
		$Sender->View = dirname(__FILE__) . DS . 'view' . DS . 'manager.php';
		$Sender->Render();
	}
	public function Base_GetAppSettingsMenuItems_Handler(&$Sender) {
		$Menu = $Sender->EventArguments['SideMenu'];
		$Menu->AddLink('Site Settings', T('Meta'), 'plugin/meta');
	}
	public function Setup() { 
		// No setup required.
	}
}
