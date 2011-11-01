<?php if (!defined('APPLICATION')) exit();

class TopPostersModule extends Gdn_Module {

	protected $_TopPosters;

	public function __construct(&$Sender = '') {
		parent::__construct($Sender);
	}
	
	public function GetAllUsers(){		
		$SQL = Gdn::SQL();
		return $SQL->Select('p.UserID, p.Name')->From('User p')->OrderBy('p.Name', 'asc')->Get()->ResultArray();         
	}
	
	
	public function GetData() {
		$SQL = Gdn::SQL();		
		$Limit = Gdn::Config('TopPosters.Limit');		
		$Limit = (!$Limit || $Limit ==0)?10:$Limit;		
		$Session = Gdn::Session();
		/*
		$SQL
			->Select('u.Name, u.CountComments, u.CountDiscussions')
			->From('User u')			
			->Where('u.CountComments >','0')
			->Where('u.CountComments is not null') 
			->OrderBy('u.CountComments','desc')
			->Limit($Limit);		
			
		*/		
		$arrExcludedUsers = Gdn::Config('TopPosters.Excluded');
		$usersExcluded = (is_array($arrExcludedUsers))?' AND UserID not in ('.Implode(',',$arrExcludedUsers).')':"";
		$this->_TopPosters = $SQL->Query('SELECT UserID, Name, if(CountDiscussions is NULL,0,CountDiscussions) + if(CountComments is NULL,0,CountComments) as AllPosted FROM '.$SQL->Database->DatabasePrefix.'User WHERE 1 '.$usersExcluded.' HAVING AllPosted > 0  order by AllPosted desc, Name asc LIMIT '.$Limit);
		
	}
	
	public function getTopPosters(){
		return $this->_TopPosters;
	}

	public function AssetTarget() {
		return 'Panel';
	}

	public function ToString() {
		$String = '';
		$Session = Gdn::Session();
		ob_start();
		//Hide the top poster box id there's no post greater than 0
		if($this->_TopPosters->NumRows() > 0) {
		?>		
			<div id="TopPosters" class="Box">
				<h4><?php echo Gdn::Translate("Top Posters"); ?></h4>
				<ul class="PanelInfo">
				<?php
					$i =1;
					foreach($this->_TopPosters->Result() as $User) {					
				?>
					<li>
						<?php echo $User->AllPosted; ?>
						<?php							
							if(Gdn::Config('TopPosters.Show.Medal') == "both" || Gdn::Config('TopPosters.Show.Medal') == "side"){							
						?>
						<img src="<?php echo str_replace("index.php?p=","",Url('/plugins/TopPosters/badges/'.(file_exists( 'plugins/TopPosters/badges/'.$i.'.png')? $i.'.png':'medal-icon.png'))); ?>">
						<?php
							}
						?>
		 				<strong>
		    				<?php echo UserAnchor($User); ?>
		 				</strong>
		 				
					</li>
				<?php
					$i++;
					}				
				?>
			</ul>
		</div>
		<?php
		}
		$String = ob_get_contents();
		@ob_end_clean();
		return $String;
	}
}