<?php if (!defined('APPLICATION')) exit();

class LatestCommentModule extends Gdn_Module {
	protected $_LatestComments;
	public function __construct(&$Sender = '') {
		parent::__construct($Sender);
	}
	public function GetAllDiscussion(){
		$SQL = Gdn::SQL();
		return $SQL->Select('p.DiscussionID, p.CategoryID, p.Name, p.Body, p.DateLastComment, p.LastCommentUserID, p.CountComments')->From('Discussion p')->OrderBy('p.DateLastComment', 'desc')->Get()->ResultArray();         
	}
	public function GetData() {
		$SQL = Gdn::SQL();		
		$Limit = Gdn::Config('LatestComment.Limit');
		$LatestOrMost = Gdn::Config('LatestComment.LatestOrMost');
		$Limit = (!$Limit || $Limit ==0)?10:$Limit;
		$Session = Gdn::Session();
		if($LatestOrMost == "YES")
		{
		$this->_LatestComments = $SQL->Query('SELECT DiscussionID, CategoryID, Name, Body, DateLastComment, LastCommentUserID, CountComments From '.$SQL->Database->DatabasePrefix.'Discussion order by DateLastComment desc LIMIT '.$Limit);
		}
		else
		{
		$this->_LatestComments = $SQL->Query('SELECT DiscussionID, CategoryID, Name, Body, DateLastComment, LastCommentUserID, CountComments From '.$SQL->Database->DatabasePrefix.'Discussion order by DateLastComment desc LIMIT '.$Limit);
		}
	}
	
	public function getLatestComments(){
		return $this->_LatestComments;
	}

	public function AssetTarget() {
		return 'Panel';
	}

	public function ToString() {
		$String = '';
		$Session = Gdn::Session();
		ob_start();
		$LatestOrMost = Gdn::Config('LatestComment.Show.LatestComment');
		//Hide the top poster box id there's no post greater than 0
		if($this->_LatestComments->NumRows() > 0) {
		?>		
			<div id="LatestComment" class="Box BoxLatestComment">
				<h4><?php if($LatestOrMost == "YES") echo Gdn::Translate("Latest Commented"); else echo Gdn::Translate("Most Commented"); ?></h4>
				<ul class="PanelInfo PanelLatestComment">
				<?php
					$i =1;
					foreach($this->_LatestComments->Result() as $Discussion) {					
				?>
					<li><span><strong>
		    			<a href="/Discussion/<?php echo $Discussion->DiscussionID; ?>/<?php echo str_replace(" ", "-",$Discussion->Name); ?>"><?php echo $Discussion->Name; ?></a>
					</span></strong></li>
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