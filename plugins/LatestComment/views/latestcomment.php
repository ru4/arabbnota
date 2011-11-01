<?php if (!defined('APPLICATION')) exit();
echo $this->Form->Open();
echo $this->Form->Errors();
?>
<style>ul.CheckBoxList{	width:95%;overflow:auto;height:500px;}
ul.CheckBoxList li{	width:25%;line-height:1.5em;border-bottom:1px solid #ccc;float:left;display:inline;}
</style>
<h1><?php echo Gdn::Translate("Latest Comment"); ?></h1>
      <div class="Info"><?php echo Gdn::Translate('Where should the plugin be shown?'); ?></div>
      <table class="AltRows">
         <thead>
            <tr>
               <th><?php echo Gdn::Translate('Display Latest Comments or Most Commented'); ?></th>
               <th class="Alt"><?php echo Gdn::Translate('Default : Latest Comment'); ?></th>
            </tr>
         </thead>
		 <tbody>
            <tr>
               <th><?php echo Gdn::Translate('Select what to display'); ?></th>
               <td class="Alt"><?php
                     echo $this->Form->Radio('LatestComment.Show.LatestComment', "Latest Comment", array('value' => 'YES', 'selected' => 'selected'));
					 echo $this->Form->Radio('LatestComment.Show.LatestComment', "Most Comments", array('value' => 'NO'));
                  ?></td>
            </tr>
         </tbody>
		 </table>
		<table>
		 <thead>
            <tr>
               <th><?php echo Gdn::Translate('Sections'); ?></th>
               <th class="Alt"><?php echo Gdn::Translate('Description'); ?></th>
            </tr>
         </thead>
         <tbody>
               <tr>
                  <th><?php
                     echo $this->Form->Radio('LatestComment.Location.Show', "Every", array('value' => 'every', 'selected' => 'selected'));
                  ?></th>
                  <td class="Alt"><?php echo Gdn::Translate("This will show the panel on every page."); ?></td>
               </tr>
                <tr>
                     <th><?php
                        echo $this->Form->Radio('LatestComment.Location.Show', "Discussion", array('value' => "discussion"));
                     ?></th>
                     <td class="Alt"><?php echo Gdn::Translate("This show the plugin on only selected discussion pages"); ?></td>
                </tr>
				<tr>
                     <th><?php
                        echo $this->Form->Radio('LatestComment.Location.Show', "Activity", array('value' => "activity"));
                     ?></th>
                     <td class="Alt"><?php echo Gdn::Translate("This show the plugin on only Activity pages"); ?></td>
                </tr>
				<tr>
                     <th><?php
                        echo $this->Form->Radio('LatestComment.Location.Show', "Profile", array('value' => "profile"));
                     ?></th>
                     <td class="Alt"><?php echo Gdn::Translate("This show the plugin on only Profile pages"); ?></td>
                </tr>
         </tbody>
      </table>			
      <table class="AltRows">
         <thead>
            <tr>
               <th><?php echo Gdn::Translate('Limit Settings'); ?></th>
               <th class="Alt"><?php echo Gdn::Translate('Default : 10'); ?></th>
            </tr>
         </thead>
         <tbody>
            <tr>
               <th><?php echo Gdn::Translate('Number of Latest Comments to show'); ?></th>
               <td class="Alt"><?php echo $this->Form->TextBox('LatestComment.Limit'); ?></td>
            </tr>
         </tbody>		 
      </table>
	 <!-- <table class="AltRows">    
		<thead>
            <tr>
               <th><?php echo Gdn::Translate('Display User with the topic'); ?></th>
			   <th>Default: Yes</th>
            </tr>
         </thead>	  
		 <tbody>
            <tr>
               <th><?php echo Gdn::Translate('Display User with the Topic'); ?></th>
               <td class="Alt"><?php
                     echo $this->Form->Radio('LatestComment.Show.User', "Yes", array('value' => 'YES', 'selected' => 'selected'));
					 echo $this->Form->Radio('LatestComment.Show.User', "No", array('value' => 'NO'));
                  ?></td>
            </tr>
         </tbody>
      </table>-->

<?php echo $this->Form->Close('Save');
