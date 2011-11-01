<?php if (!defined('APPLICATION')) exit();
echo $this->Form->Open();
echo $this->Form->Errors();
?>
<style>
ul.CheckBoxList{
	width:95%;
	overflow:auto;
	height:500px;
}
ul.CheckBoxList li{
	width : 25%;
	line-height:1.5em;
	border-bottom:1px solid #ccc;
	float:left;
	display:inline;
}
</style>
<h1><?php echo Gdn::Translate("Top Posters"); ?></h1>
      <div class="Info"><?php echo Gdn::Translate('Where should the plugin be shown?'); ?></div>
      <table class="AltRows">
         <thead>
            <tr>
               <th><?php echo Gdn::Translate('Sections'); ?></th>
               <th class="Alt"><?php echo Gdn::Translate('Description'); ?></th>
            </tr>
         </thead>
         <tbody>
               <tr>
                  <th><?php
                     echo $this->Form->Radio('TopPosters.Location.Show', "Every", array('value' => 'every', 'selected' => 'selected'));
                  ?></th>
                  <td class="Alt"><?php echo Gdn::Translate("This will show the panel on every page."); ?></td>
               </tr>
                <tr>
                     <th><?php
                        echo $this->Form->Radio('TopPosters.Location.Show', "Discussion", array('value' => "discussion"));
                     ?></th>
                     <td class="Alt"><?php echo Gdn::Translate("This show the plugin on only selected discussion pages"); ?></td>
                </tr>
				<tr>
					
                     <td class="Alt" colspan="2" ><?php echo Gdn::Translate("Select the users you want to hide in the Top Poster List"); ?><?php
					 $arrDataset = Array();
					 foreach($this->AllUsers as $arrvals){
						$arrDataset[$arrvals['Name']] = $arrvals['UserID'];						
					 }
                     echo $this->Form->CheckBoxList("TopPosters.Excluded", $arrDataset);
                     ?></td>                     
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
               <th><?php echo Gdn::Translate('Number of top posters to show'); ?></th>
               <td class="Alt"><?php echo $this->Form->TextBox('TopPosters.Limit'); ?></td>
            </tr>
         </tbody>		 
      </table>
	  <table class="AltRows">    
		<thead>
            <tr>
               <th><?php echo Gdn::Translate('Medal for Top posters'); ?></th>
               <th class="Alt"><?php echo Gdn::Translate('Change the image at /plugins/TopPosters/badges/'); ?></th>
            </tr>
         </thead>	  
		 <tbody>
            <tr>
               <th><?php echo Gdn::Translate('Display Medals at'); ?></th>
               <td class="Alt"><?php
                     echo $this->Form->Radio('TopPosters.Show.Medal', "Thread only", array('value' => 'thread', 'selected' => 'selected'));
					 echo $this->Form->Radio('TopPosters.Show.Medal', "Side panel only", array('value' => 'side', 'selected' => 'selected'));
					 echo $this->Form->Radio('TopPosters.Show.Medal', "Both", array('value' => 'both', 'selected' => 'selected'));
                  ?></td>
            </tr>
         </tbody>
		 <tbody>
            <tr>
               <th></th>
               <td class="Alt"><?php echo Gdn::Translate('[Note]:<br>you can customize the remaining icons by uploading your desired 16x16 png file and<br> filenaming convention for the image should be like this... 3.png, 4.png, so on'); ?></td>
            </tr>
         </tbody>
      </table>

<?php echo $this->Form->Close('Save');
