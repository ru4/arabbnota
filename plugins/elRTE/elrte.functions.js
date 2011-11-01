jQuery(document).ready(function(){
	if ($.browser.msie) return;
	if (typeof($.livequery) != 'function') return;
	
	var WebRoot = gdn.definition('WebRoot');
	
	// Make elfinder dialog some wider.
	$('body > div.ui-dialog').livequery(function(){
		if ($(this).width() < 700) $(this).width(700).center();
	});
	
	// elRTE settings.
	var ElRteSettings = {
		//doctype: '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">',
		absoluteURLs: false,
		allowSource: true,
		styleWithCSS: false,
		toolbar: 'RtePluginToolbar',
		height: 400,
		fmAllow: !!gdn.definition('FileManagerAllow', false),
		lang: gdn.definition('LocaleLanguageCode', 'en'),
		fmOpen : function(callback) {
			$('<div id="myelfinder" />').elfinder({
				url: gdn.url('plugin/elfinderconnector'),
				lang: gdn.definition('LocaleLanguageCode', 'en'),
				dialog: {width: '95%', modal: true, title: ''},
				closeOnEditorCallback: true,
				editorCallback: callback
			});
		}
	}
	
	var AddCssFile = function(file, webroot) {
		var link = $('<link>', {rel: 'stylesheet', type: 'text/css', media: 'screen', charset:'utf-8'});
		if (typeof(webroot) != 'undefined') file = gdn.combinePaths(webroot, file);
		$(link).attr('href', file);
		$('head').append(link);
	}
	
	var AddJsFile = function(file, webroot) {
		var script = $('<script>', {type: 'text/javascript', charset: 'utf-8'});
		if (typeof(webroot) != 'undefined') file = gdn.combinePaths(webroot, file);
		$(script).attr('src', file);
		$('head').append(script);
	}
	
	var LoadElRte = function() {
		if ($('body').data('bElRteLoaded')) return;
		$('body').data('bElRteLoaded', true);
		var lang = gdn.definition('LocaleLanguageCode', 'en');
		var RteRoot = gdn.combinePaths(WebRoot, 'plugins/elRTE/vendors/elrte/');
		var CdnServer = 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.7.1/';
		AddCssFile('themes/smoothness/jquery-ui.css', CdnServer);
		AddCssFile('css/elrte.min.css', RteRoot);
		AddJsFile('jquery-ui.min.js', CdnServer);
		AddJsFile('js/elrte.min.js', RteRoot);
		AddJsFile('js/i18n/elrte.'+lang+'.js', RteRoot);
		var elfinder = !!gdn.definition('FileManagerAllow', false);
		if (elfinder) {
			var EfRoot = gdn.combinePaths(WebRoot, 'plugins/elRTE/vendors/elfinder/');
			AddCssFile('css/elfinder.css', EfRoot);
			AddJsFile('js/elfinder.min.js', EfRoot);
			AddJsFile('js/i18n/elfinder.'+lang+'.js', EfRoot);			
		}
	}
	
	var ElRteLoading = function(){
		return (typeof(elRTE) != "undefined");
	}
	
	var TransformTextArea = function(TextareaId) {
		var t = $("#"+TextareaId);
		var p = elRTE.prototype.options.panels;
		// Add custom panels.
		p.CopyPaste = ['pastetext', 'pasteformattext', 'removeformat', 'docstructure'];
		p.TextDecoration = ['bold', 'italic', 'underline'];
		p.TextAlign = ['justifyleft', 'justifycenter', 'justifyright'];
		p.FormatHeaders = ['formatblock'];
		p.List = ['insertorderedlist', 'insertunorderedlist'];
		p.Renders = ['link', 'unlink', 'image']; // anchor
		p.Misc = ['elfinder', 'fullscreen'];
		
		elRTE.prototype.options.toolbars.RtePluginToolbar = [
			'CopyPaste', 'undoredo', 'TextDecoration', 'TextAlign', 'FormatHeaders', 'List', 'Renders', 'Misc'
		];
		
		ElRteSettings.height = $(t).height();
		
		t.unbind(); // TODO: FIX ME. LOST AUTOGROW HERE

		if (t.is(':hidden') == false) {
			
			// TODO: FIX ME, CANT REENABLE WYSIWYG
			t.elrte(ElRteSettings);
		
			// Add tab to tabsbar
			var TabsBarReady = function() {
				var tabsbar = $('.tabsbar', t.parent().parent());
				return !!tabsbar;
			}
			$.doWhen(TabsBarReady, function(){
				var tabsbar = $('.tabsbar', t.parent().parent());
				var lasttab = $('div.tab', tabsbar).last();
				var turnoff = $('<div class="tab turnoff rounded-bottom-7">Turn off</div>').insertAfter(lasttab);
				turnoff.click(function(){
					var rte = $(this).parents('div.el-rte')[0];
					$(rte).replaceWith(t);
					t.show();
					//console.log(t);
					TipsySetTrigger.call(t);
				});
			});
		}
		
	}
	
	var TipsyReady = function(){
		return (typeof($.fn.tipsy) == 'function');
	}
	
	if (!TipsyReady()) AddJsFile('plugins/elRTE/vendors/tipsy/jquery.tipsy.min.js', WebRoot);
	var TipsySetTrigger = function() {
		var TextareaId = $(this).attr('id');
		//console.log('TipsySetTrigger', this, TextareaId);
		$(this).tipsy({
			title: function(){
				return '<span class="elRteTrigger" id="elRteTrigger'+TextareaId+'">WYSIWYG</span>';
			},
			fade: true,
			html: true,
			trigger: 'focus',
			opacity: 0.5,
			gravity: 'se'
		});
		// TODO: FIX GRAVITY, FOR ADMIN AND USER
	}
	
	$.doWhen(TipsyReady, function(){
		$("textarea").each(TipsySetTrigger);		
	});
	
	// Tooltip-trigger click handler.
	$('span[id^=elRteTrigger]').livequery(function(){
		$(this).click(function() {
			var TextareaId = this.id.substr(12);
			LoadElRte();		
			jQuery.doWhen(ElRteLoading, TransformTextArea, {data: TextareaId});
		});
	});
	
});

// jQuery center.

if (typeof(jQuery.fn.center) == 'undefined') {
	jQuery.fn.center = function() {
		this.css("position", "absolute");
		var w = $(window);
		var t = (w.height() - this.height()) / 2 + w.scrollTop();
		this.css("top", t + "px");
		this.css("left",(w.width()-this.width())/2+w.scrollLeft() + "px");
		return this;
	}
}
