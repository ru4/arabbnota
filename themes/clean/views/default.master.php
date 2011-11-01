<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html dir="rtl" xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-ca">
	<head>
		<?php $this->RenderAsset('Head'); ?>
		<!--[if lte IE 7]>
			<style>
				html
				{
					direction:ltr;
				}
			</style>
		<![endif]-->
		
	<!-- elRTE 
	<script src="/plugins/elrte/js/jquery-ui-1.8.13.custom.min.js" type="text/javascript" charset="utf-8"></script>
	<link rel="stylesheet" href="/plugins/elrte/css/smoothness/jquery-ui-1.8.13.custom.css" type="text/css" media="screen" charset="utf-8">

	
	<script src="/plugins/elrte/js/elrte.full.js" type="text/javascript" charset="utf-8"></script>
	<link rel="stylesheet" href="/plugins/elrte/css/elrte.min.css" type="text/css" media="screen" charset="utf-8">

	
	<script src="/plugins/elrte/js/i18n/elrte.ar.js" type="text/javascript" charset="utf-8"></script>

	<script type="text/javascript" charset="utf-8">

	$().ready(function() {
		$("#Form_PostDiscussion,#Form_PostComment,.CommentTabs li,.PostFormControlPanel,#Form_SaveComment,#Form_SendMessage,#Form_StartConversation").hover(function()
		{
			$('textarea#Form_Body').elrte('updateSource');

		});
			var opts = {
				cssClass : 'el-rte',
				lang     : 'ar',
				height   : 450,
				toolbar  : 'complete',
				cssfiles : ['/plugins/elrte/css/elrte-inner.css']
			}
			$('#Form_Body,#Form_Plugin-dot-Signatures-dot-Sig').elrte(opts);
			

			if ($('#Form_Body').text() !="")
			{
			var cont =$('#Form_Body').text;
			$('#Form_Body').elrte('val', cont);
			}
			
			if ($('#Form_Plugin-dot-Signatures-dot-Sig').text() !="")
			{
			var cont =$('#Form_Plugin-dot-Signatures-dot-Sig').text;
			$('#Form_Plugin-dot-Signatures-dot-Sig').elrte('val', cont);
			}

		})
	</script> -->
	</head>
	<body id="<?php echo $BodyIdentifier; ?>" class="<?php echo $this->CssClass; ?>">
		<div id="Wrapper">
			<div id="Main">
				<div id="ContentHolder">
					<div id="Content">
						<?php $this->RenderAsset('Content'); ?>
					</div>
				</div>
				<div id="PanelHolder">
					<div id="Panel"><?php $this->RenderAsset('Panel'); ?></div>
				</div>
				<div class="clear">&nbsp;</div>
			</div>
			
			<div id="Header">
				<h1><a class="Title" href="<?php echo Url('/'); ?>"><span><?php echo Gdn_Theme::Logo(); ?></span></a></h1>
			</div>
			
			<div id="Nav">
				<?php
				  $Session = Gdn::Session();
					if ($this->Menu) {
						$this->Menu->AddLink('Dashboard', T('Dashboard'), '/dashboard/settings', array('Garden.Settings.Manage'));
						// $this->Menu->AddLink('Dashboard', T('Users'), '/user/browse', array('Garden.Users.Add', 'Garden.Users.Edit', 'Garden.Users.Delete'));
						$this->Menu->AddLink('Activity', T('Activity'), '/activity');
						$this->Menu->AddLink('All Categories',T('All Categories'), '/categories/all' );
					 $Authenticator = Gdn::Authenticator();
						if ($Session->IsValid()) {
							$Name = $Session->User->Name;
							$CountNotifications = $Session->User->CountNotifications;
							if (is_numeric($CountNotifications) && $CountNotifications > 0)
								$Name .= ' <span>'.$CountNotifications.'</span>';
								
							$this->Menu->AddLink('User', $Name, '/profile/{UserID}/{Username}', array('Garden.SignIn.Allow'), array('class' => 'UserNotifications'));
							$this->Menu->AddLink('SignOut', T('Sign Out'), $Authenticator->SignOutUrl(), FALSE, array('class' => 'NonTab SignOut'));
						} else {
							$Attribs = array();
							if (C('Garden.SignIn.Popup') && strpos(Gdn::Request()->Url(), 'entry') === FALSE)
								$Attribs['class'] = 'SignInPopup';
								
							$this->Menu->AddLink('Entry', T('Sign In'), $Authenticator->SignInUrl($this->SelfUrl), FALSE, array('class' => 'NonTab'), $Attribs);
						}
						echo $this->Menu->ToString();
					}
				?>
				<div style="position:absolute;left:300px;margin-top:-13px;" class="rss-new-dis">
					<p style="margin-top:10px; margin-bottom:0; padding-bottom:0; text-align:center; line-height:0"><a target="_blank" href="http://feeds.feedburner.com/~r/arabbnota/~6/1"><img src="http://feeds.feedburner.com/arabbnota.1.gif" alt="ÇÎÑ ÇáãæÇÖíÚ Úáì ÚÑÈ ÈäæÊÇÊ" style="border:0"></a></p><p style="margin-top:5px; padding-top:0; font-size:x-small; text-align:center"></p>
				</div>
				<div id="Search">
					<?php
						$Form = Gdn::Factory('Form');
						$Form->InputPrefix = '';
						echo 
							$Form->Open(array('action' => Url('/search'), 'method' => 'get')),
							$Form->TextBox('Search'),
							$Form->Button('Search', array('Name' => '')),
							$Form->Close();
					?>
				</div>
			</div>
			
			<div id="Footer">
				<ul>
					<?php
						$this->RenderAsset('Foot');
						echo Wrap('Arabbnota');
					?>
				</ul>
			</div>
		</div>

		<?php $this->FireEvent('AfterBody'); ?>
		<script type="text/javascript">

		  var _gaq = _gaq || [];
		  _gaq.push(['_setAccount', 'UA-20173259-1']);
		  _gaq.push(['_trackPageview']);

		  (function() {
			var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
			ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
			var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
		  })();

		</script>
	</body>
</html>
