<?php if (!defined('APPLICATION')) exit();

$PluginInfo['PageSpeed'] = array(
	'Name' => 'Page Speed',
	'Description' => 'Minimizes payload size (compressing css/js files), minimizes round-trip times (loads JQuery library from CDN, combines external JavaScript/CSS files). Inspired by Google Page Speed rules. See readme for details.',
	'Version' => '1.85',
	'Date' => '7 Aug 2011',
	'Updated' => 'Autumn 2011',
	'Author' => 'WebDeveloper',
	'AuthorUrl' => 'https://github.com/search?type=Repositories&language=php&q=PageSpeed',
	'RequiredApplications' => array('Dashboard' => '>=2.0.17'),
	'RequiredPlugins' => array('UsefulFunctions' => '>=2.3.60'),
	'SettingsUrl' => '/settings/pagespeed'
);

class PageSpeedPlugin implements Gdn_IPlugin {
	
	private $bRenderInitialized = False;
	private $Configuration = array();
	protected $DeferJavaScriptFiles = array();
	
	public function __construct() {
		$this->Configuration = C('Plugins.PageSpeed');
	}
	
	protected static function CleanCache() {
		if (!(file_exists('cache/ps') && is_dir('cache/ps'))) return;
		$Directory = new RecursiveDirectoryIterator('cache/ps');
		foreach (new RecursiveIteratorIterator($Directory) as $File) {
			$CachedFile = $File->GetRealPath();
			unlink($CachedFile);
		}
	}
	
	public function SettingsController_AfterEnablePlugin_Handler() {
		self::CleanCache();
	}
	
	public function SettingsController_AfterDisablePlugin_Handler() { 
		self::CleanCache();
	}
	
	public function SettingsController_PageSpeed_Create($Sender) {
		$Sender->Permission('Garden.Plugins.Manage');
		
		if ('cleancache' == GetValue(0, $Sender->RequestArgs)) {
			self::CleanCache();
			Redirect('settings/pagespeed');
		} elseif ('disable' == GetValue(0, $Sender->RequestArgs)) {
			SaveToConfig('EnabledPlugins.PageSpeed', False);
			Redirect('/dashboard/settings/plugins');
		}
		
		$Sender->AddSideMenu();
		$Sender->Title('Page Speed');
		
		$Validation = new Gdn_Validation();
		$ConfigurationModel = new Gdn_ConfigurationModel($Validation);
		$Sender->Form->SetModel($ConfigurationModel);
		$ConfigurationModel->SetField(array(
			'Plugins.PageSpeed.AllInOne',
			'Plugins.PageSpeed.DeferJavaScript',
			'Plugins.PageSpeed.ParallelizeEnabled',
			'Plugins.PageSpeed.ParallelizeHosts',
			'Plugins.PageSpeed.CDN.jquery',
			'Plugins.PageSpeed.CDN.jqueryui',
			'Plugins.PageSpeed.CDN.jqueryui-theme'
		));
		
		
		if ($Sender->Form->AuthenticatedPostBack()) {
			//$Validation->ApplyRule('Plugin.Example.RenderCondition', 'Required');
			
			$FormValues = $Sender->Form->FormValues();
			$Integer = array('Plugins.PageSpeed.AllInOne', 'Plugins.PageSpeed.DeferJavaScript');
			foreach ($Integer as $Name) settype($FormValues[$Name], 'int');
			settype($FormValues['Plugins.PageSpeed.ParallelizeEnabled'], 'bool');
			$ParallelizeHosts = SplitUpString($FormValues['Plugins.PageSpeed.ParallelizeHosts'], ',', 'trim strtolower');
			if (count($ParallelizeHosts) == 0) {
				SetValue('Plugins.PageSpeed.ParallelizeHosts', $FormValues, Null);
				SetValue('Plugins.PageSpeed.ParallelizeEnabled', $FormValues, False);
			} else {
				SetValue('Plugins.PageSpeed.ParallelizeHosts', $FormValues, implode(', ', $ParallelizeHosts));
			}
			$Sender->Form->FormValues($FormValues);
			$Sender->Form->Save();
			$Sender->InformMessage(T('Saved'), array('Sprite' => 'Check', 'CssClass' => 'Dismissable AutoDismiss'));

		} else {
			$Sender->Form->SetData($ConfigurationModel->Data);
		}

		$Sender->SetData('GroupingItems', array(
			'Three groups (library, applications, plugins)',
			'All css and javascript files combined into one file',
			'Minify javascript only'
		));
		$Sender->SetData('DeferJavaScriptItems', array(
			0 => 'Disabled',
			1 => htmlspecialchars('Just put <script> tags at bottom'),
			2 => 'Dynamic loading (Dangerous! Something may not work)'
		));
		
		$Sender->View = dirname(__FILE__) . DS . 'views' . DS . 'settings.php';
		$Sender->Render();
	}
	
	public function Base_Render_Before($Sender) {
		$EnablePostProcessing = 
			GetValue('ParallelizeEnabled', $this->Configuration)
			&& $Sender->DeliveryMethod() == DELIVERY_METHOD_XHTML
			&& $Sender->DeliveryType() == DELIVERY_TYPE_ALL 
			&& $Sender->SyndicationMethod == SYNDICATION_NONE;
		if ($EnablePostProcessing) {
			ob_start();
			$this->bRenderInitialized = True;
		}
	}
	
	public function Base_Render_After($Sender) {
		if ($this->bRenderInitialized) {
			$String = ob_get_contents();
			ob_end_clean();
			//DebugCheckPoint('StaticParallelizeDownloads 2s');
			self::StaticParallelizeDownloads($String);
			//DebugCheckPoint('StaticParallelizeDownloads 2e');
			echo $String;
		}
	}
	
	/**
	* Parallelize downloads across hostnames (version 2).
	*/
	protected static function StaticParallelizeDownloads(&$String) {
		preg_match_all("/src=\"(.*)\"/U", $String, $Images);
		preg_match_all("/background:\s*url\((\"|')(.*)\\1\)/U", $String, $Backgrounds);
		preg_match_all("/background-image:\s*url\((\"|')(.*)\\1\)/U", $String, $BackgroundImages);

		$Images = array_merge($Images[1], $Backgrounds[2], $BackgroundImages[2]);

		$RequestHost = Gdn::Request()->Host();
		$Domain = Gdn::Request()->Domain();
		$Scheme = parse_url($Domain, PHP_URL_SCHEME);
		$ParallelizeHosts = C('Plugins.PageSpeed.ParallelizeHosts');
		if (!is_array($ParallelizeHosts)) $ParallelizeHosts = array_map('trim', explode(',', $ParallelizeHosts));
		if (count($ParallelizeHosts) == 0) throw new RuntimeException('ParallelizeHosts is not properly configured.');
		$ParallelizeHostsCount = count($ParallelizeHosts);
		$Replace = array();
		
		for ($Count = count($Images), $i = 0; $i < $Count; $i++) {
			$Src =& $Images[$i];
			if (substr($Src, 0, 2) == '//') $Src = $Scheme . ':' . $Src;
			$ParseUrl = parse_url($Src);
			if (!isset($ParseUrl['host']) || $RequestHost == $ParseUrl['host']) {
				$Path = $ParseUrl['path'];
				$Hash = sprintf('%u', crc32($Path));
				$ServerNum = $Hash % $ParallelizeHostsCount;
				$NewSrc = GetValue('scheme', $ParseUrl, $Scheme) . '://' . $ParallelizeHosts[$ServerNum] . $Path;
				if (!isset($Replace[$Src])) $Replace[$Src] = $NewSrc;
			}
		}
		if (count($Replace) > 0) {
			$String = str_replace(array_keys($Replace), array_values($Replace), $String);
		}
		return $String;
	}
	
	protected static function ChangeBackgroundUrl(&$CssText, $FilePath) {
		// Change background image url in css
		if (preg_match_all('/url\((.+?)\)/', $CssText, $Match)) {
			$Replace = array();
			foreach ($Match[1] as $N => $UrlImage) {
				$UrlImage = trim($UrlImage, '"\'');
				if ($UrlImage[0] == '/' || self::IsUrl($UrlImage) || substr($UrlImage, 0, 5) == 'data:') continue;
				$File = dirname($FilePath).'/'.$UrlImage;
				if (!file_exists($File)) {
					if (Debug()) trigger_error("Error while fix background image url path. No such file ($File).");
				}
				$Asset = Asset(substr($File, strlen(PATH_ROOT)+1));
				$Replace[$Match[0][$N]] = "url('$Asset')";
			}
			if (count($Replace) > 0) {
				$CssText = str_replace(array_keys($Replace), array_values($Replace), $CssText);
			}
		}
		$ParallelizeEnabled = C('Plugins.PageSpeed.ParallelizeEnabled');
		if ($ParallelizeEnabled) self::StaticParallelizeDownloads($CssText);
	}
	
	public function HeadModule_BeforeToString_Handler($Head) {

		$Configuration =& $this->Configuration;

		$Tags = $Head->Tags();
		usort($Tags, array('HeadModule', 'TagCmp')); // BeforeToString fires before sort
		
		$CombinedJavascript = array('library' => array());
		$CombinedCss = array();
		$RemoveIndex = array();
		
		$AllInOne = ArrayValue('AllInOne', $Configuration);
		$DeferJavaScript = ArrayValue('DeferJavaScript', $Configuration);
		
		foreach ($Tags as $Index => &$Tag) {
			// JavaScript (script tag)
			if (GetValue(HeadModule::TAG_KEY, $Tag) == 'script') {
				if (!isset($JsTag)) $JsTag = $Tag;
				$CachedFilePath = $this->GetCachedFilePath($Tag, 'src', $FilePath);
				if ($CachedFilePath === False) {
					if ($DeferJavaScript) {
						$this->DeferJavaScriptFiles[] = $Tag['src'];
						$RemoveIndex[] = $Index;
					}
					continue;
				}
				
				if (!file_exists($CachedFilePath)) {
					if (!isset($Snoopy)) $Snoopy = Gdn::Factory('Snoopy');

					$Snoopy->Submit('http://marijnhaverbeke.nl/uglifyjs', array(
						'code_url' => '',
						'download' => '',
						'js_code' => file_get_contents($FilePath)
					));
					
					file_put_contents($CachedFilePath, trim($Snoopy->results));
				}
				if (!$AllInOne) {
					$GroupName = self::GetGroupName($FilePath);
					if ($GroupName == 'js' || $GroupName == 'themes') $GroupName = 'library';
					elseif (!in_array($GroupName, array('plugins', 'applications', 'library'))) {
						// Unknown group, move it to application group.
						$GroupName = 'applications';
					}
					$CombinedJavascript[$GroupName][$Index] = $CachedFilePath;
				} else {
					$CombinedJavascript[$Index] = $CachedFilePath;
				}
				
			} elseif (GetValue(HeadModule::TAG_KEY, $Tag) == 'link' && GetValue('rel', $Tag) == 'stylesheet') {

				$CachedFilePath = $this->GetCachedFilePath($Tag, 'href', $FilePath);
				if ($CachedFilePath === False) continue;

				if (!file_exists($CachedFilePath)) {
					$Css = file_get_contents($FilePath);
					$CssText = self::ProcessImportCssText($Css, $FilePath);
					if ($CssText === False) $CssText = $Css;
					if (GetValue('MinifyCss', $Configuration, True)) $CssText = self::MinifyCssText($CssText);
					self::ChangeBackgroundUrl($CssText, $FilePath);
					// TODO: COMBINE CSS (WE MUST CHECK MEDIA)
					// style.css + custom.css, admin.css + customadmin.css
					// TODO: MORE EFFECTIVE COMBINE colorbox.css + custom-colorbox.css
					file_put_contents($CachedFilePath, $CssText);
				}
				
				if (!$AllInOne) {
					$GroupName = self::GetGroupName($FilePath);
					// combine in two group applications and plugins
					// TODO: REMOVE if (!isset($CssTag)) $CssTag = $Tag;
					if (!isset($CssTag)) $CssTag = $Tag;
					if (!in_array($GroupName, array('plugins', 'applications'))) $GroupName = 'applications';
					$CombinedCss[$GroupName][$Index] = $CachedFilePath;
				} else {
					$CombinedCss[$Index] = $CachedFilePath;
				}
			}
		}
		
		if ($AllInOne == 2) {
			// Js
			unset($CombinedJavascript['library']);
			foreach ($CombinedJavascript as $Index => $Src) {
				$NewSrc = Asset($Src, False, False);
				if ($DeferJavaScript) {
					$RemoveIndex[] = $Index;
					$this->DeferJavaScriptFiles[] = $NewSrc;
				} else $Tags[$Index]['src'] = $NewSrc;
			}
			// Css
			foreach ($CombinedCss as $Index => $Src) {
				$Tags[$Index]['href'] = Asset($Src, False, False);
			}
		} elseif ($AllInOne) {
			$RemoveIndex[] = array_keys($CombinedCss);
			// Css
			$CombinedCss = array_unique($CombinedCss);
			$CachedFilePath = 'cache/ps/style.' . self::HashSumFiles($CombinedCss) . '.css';
			if (!file_exists($CachedFilePath)) {
				$Combined = '';
				//$IncludeBasename = Gdn::Session()->CheckPermission('Garden.Admin.Only');
				foreach ($CombinedCss as $File) {
					//if ($IncludeBasename) $Combined .= '/*' . basename($File) . "*/\n";
					$Combined .= file_get_contents($File) . "\n";
				}
				file_put_contents($CachedFilePath, $Combined);
			}
			
			$Tags[] = array(
				HeadModule::TAG_KEY => 'link',
				'rel' => 'stylesheet',
				'type' => 'text/css',
				'href' => Asset($CachedFilePath, False, False),
				'media' => 'all',
				HeadModule::SORT_KEY => 98
			);

			// Js
			unset($CombinedJavascript['library']);
			$RemoveIndex[] = array_keys($CombinedJavascript);
			$CombinedJavascript = array_unique($CombinedJavascript);
			$CachedFilePath = 'cache/ps/functions.' . self::HashSumFiles($CombinedJavascript) . '.js';
			if (!file_exists($CachedFilePath)) {
				$Combined = '';
				foreach ($CombinedJavascript as $File) $Combined .= file_get_contents($File) . ";\n";
				file_put_contents($CachedFilePath, $Combined);
			}
			$Src = Asset($CachedFilePath, False, False);
			// Defer loading of JavaScript
			if ($DeferJavaScript) {
				$this->DeferJavaScriptFiles[] = $Src;
			} else {
				$Tags[] = array(
					HeadModule::TAG_KEY => 'script',
					'type' => 'text/javascript',
					'src' => $Src,
					//'_path' => $CachedFilePath,
					HeadModule::SORT_KEY => 99
				);
			}
			//d($RemoveIndex, Flatten($RemoveIndex), @$CombinedCss, @$CombinedJavascript);
		} else {
			if (count($CombinedCss) > 0) {
				foreach ($CombinedCss as $Group => $Files) {
					$RemoveIndex[] = array_keys($Files);
					$Files = array_unique($Files);
					$Hash = self::HashSumFiles($Files);
					$CachedFilePath = "cache/ps/{$Group}.{$Hash}.css";
					if (!file_exists($CachedFilePath)) {
						$Combined = '';
						foreach ($Files as $Index => $File) {
							$Combined .= '/*' . basename($File) . "*/\n" . file_get_contents($File) . "\n";
						}
						file_put_contents($CachedFilePath, $Combined);
					}
					$CssTag[HeadModule::SORT_KEY] += 1;
					// This is not works...
					// $CssTag['href'] = Asset($CachedFilePath);
					// $Tags[] = $CssTag;
					// This works...
					$Tags[] = array_merge($CssTag, array('href' => Asset($CachedFilePath, False, False)));
				}
			}
			// TODO: IF ONE FILE IN GROUP NO NEED TO PARSE/COMBINE IT
			if (count($CombinedJavascript) > 1) {
				// TODO: array_unique
				foreach ($CombinedJavascript as $Group => $Files) {
					$RemoveIndex[] = array_keys($Files);
					$Files = array_unique($Files);
					$Hash = self::HashSumFiles($Files);
					$CachedFilePath = "cache/ps/{$Group}.{$Hash}.js";
					if (!file_exists($CachedFilePath)) {
						$Combined = '';
						foreach ($Files as $Index => $File) {
							$Combined .= '//' . basename($File) . "\n" . file_get_contents($File) . ";\n";
						}
						file_put_contents($CachedFilePath, $Combined);
					}
					
					$Src = Asset($CachedFilePath, False, False);
					if ($DeferJavaScript) {
						$this->DeferJavaScriptFiles[] = $Src;
					} else {
						$JsTag['src'] = $Src;
						$JsTag[HeadModule::SORT_KEY] += 1;
						$Tags[] = $JsTag;
					}
				}
			}
		}
		
		if (count($RemoveIndex) > 0) $Tags = self::RemoveKeyFromArray($Tags, Flatten($RemoveIndex));

		$Head->Tags($Tags);
	}
	
	/**
	* This is place before </body> tag.
	*/
	public function Base_AfterBody_Handler($Sender) {
		$DeferJavaScript = ArrayValue('DeferJavaScript', $this->Configuration);
		if ($DeferJavaScript && count($this->DeferJavaScriptFiles) > 0) {
			switch ($DeferJavaScript) {
				case 2: $this->RenderDeferJavaScriptFiles(); break;
				case 1: 
				default: $this->RenderScriptTags(); break;
			}
		}
	}

	/**
	* If DeferJavaScript = TRUE, there is the place where all javascript file loaded
	* Using snippet: code.google.com/speed/page-speed/docs/payload.html#DeferLoadingJS
	*/	
	protected function RenderDeferJavaScriptFiles() {
		$ArrayCode = "['" . implode("', '", $this->DeferJavaScriptFiles) . "']";
		echo <<<SCRIPT
<script type="text/javascript">
window.onload = function() {
	var include = function(files) {
		var onload;
		var script = document.createElement('script');
		var file = files.shift();
		if (typeof(files) == 'string') files = [files];
		script.setAttribute('type', 'text/javascript');
		script.setAttribute('src', file);
		document.body.appendChild(script);
		if (files.length > 0) {
			onload = function() { include(files); }
			script.onreadystatechange = onload;
			script.onload = onload;
		}
	}
	include($ArrayCode);
}
</script>
SCRIPT;
	}
	
	/**
	* Write <script> tags.
	*/
	protected function RenderScriptTags() {
		foreach ($this->DeferJavaScriptFiles as $Src) {
			echo "\n", Wrap('', 'script', array('src' => $Src, 'type' => 'text/javascript'));
		}
	}
	
	public function Setup() {
		if (!is_dir('cache/ps')) mkdir('cache/ps', 0777, True);
	}
	
	protected function GetCachedFilePath(&$Tag, $FileKey, &$FilePath) {
		$Url =& $Tag[$FileKey];
		$FilePath = Null;
		if (self::IsUrl($Url)) return False;
		// Since 2.0.18 we have _path key.
		if (array_key_exists('_path', $Tag)) $FilePath = $Tag['_path'];
		else $FilePath = PATH_ROOT . parse_url($Url, PHP_URL_PATH);
		$Basename = pathinfo($FilePath, PATHINFO_BASENAME);
		// TODO: custom-
		// if (substr($Basename, 0, 6) == 'custom') d($Tags, $Basename);	
		switch ($Basename) {
			case 'jquery.js': {
				$Version = GetValueR('CDN.jquery', $this->Configuration, '1.6');
				$Url = 'http://ajax.googleapis.com/ajax/libs/jquery/'.$Version.'/jquery.min.js';
				return False;
			}
			//case 'jquery.ui.packed.js': // TODO: MAYBE
			case 'jqueryui.js': {
				$Version = GetValueR('CDN.jqueryui', $this->Configuration, '1.8');
				$Url = 'http://ajax.googleapis.com/ajax/libs/jqueryui/'.$Version.'/jquery-ui.min.js';
				return False;
			}
			case 'jquery-ui.css': {
				$Version = GetValueR('CDN.jqueryui', $Configuration, '1.8');
				$Theme = GetValueR('CDN.jqueryui-theme', $Configuration, 'smoothness');
				$Url = 'http://ajax.googleapis.com/ajax/libs/jqueryui/'.$Version.'/themes/'.$Theme.'/jquery-ui.css';
				return False;
			}
			default: // Nothing
		}
		
		$CachedFilePath = 'cache/ps/' . sprintf('%u', crc32($Url)) . '.' . $Basename;
		return $CachedFilePath;
	}
	
	protected static function GetGroupName($FilePath) {
		static $WebRootLength;
		if (is_null($WebRootLength)) $WebRootLength = strlen(Gdn_Url::WebRoot());
		$GroupName = GetValue(1, explode('/', substr($FilePath, $WebRootLength)));
		return $GroupName;
	}
	
	protected static function StaticMinify($css) {
		//$css = str_replace('  ', ' ', $css);
		// credit: http://www.phpsnippets.info/compress-css-files-using-php
		/* remove comments */
		$css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
		/* remove tabs, spaces, newlines, etc. */
		//$css = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $css);
		$css = preg_replace('/\s+/', ' ', $css); // my
		$css = str_replace( '; ', ';', $css );
		$css = str_replace( ': ', ':', $css );
		$css = str_replace( ' {', '{', $css );
		$css = str_replace( '{ ', '{', $css );
		$css = str_replace( ', ', ',', $css );
		$css = str_replace( '} ', '}', $css );
		$css = str_replace( ';}', "}\n", $css );
		$css = trim($css);
		return $css;
	}
	
	protected static function ProcessImportCssText($CssText = '', $Filepath) {
		if (!$CssText) $CssText = file_get_contents($Filepath);
		preg_match_all('/(@import\s+url\(((.+?)\)).*)/i', $CssText, $Match);
		if (!(isset($Match[3]) && count($Match[3]) > 0)) return False;
		$CssFiles = $Match[3];
		//$Url = Url('/', True);
		$DocRoot = Gdn::Request()->GetValue('DOCUMENT_ROOT');
		$Replace = array();
		foreach ($CssFiles as $N => $Filename) {
			$Filename = trim($Filename, '"\'');
			if ($Filename{0} == '/') {
				$ImportFilepath = PrefixString($DocRoot, $Filename);
			} else {
				// relative path
				$ImportFilepath = dirname($Filepath) . DS . $Filename;
			}			
			if (!file_exists($ImportFilepath)) trigger_error("File not found ($ImportFilepath)");
			$ImportedCss = file_get_contents($ImportFilepath);
			self::ChangeBackgroundUrl($ImportedCss, $ImportFilepath);
			$ImportMatch = $Match[0][$N];
			$Replace[$ImportMatch] = "\n".$ImportedCss;
		}
		
		$Result = str_replace(array_keys($Replace), array_values($Replace), $CssText);
		
		return $Result;
	}
	
	
	protected static function IsUrl($Url) {
		return (strpos($Url, '//') !== False);
	}
	
	protected static function MinifyCssFile($Filepath) {
		return self::MinifyCssText(file_get_contents($Filepath));
	}
	
	protected static function MinifyCssText($Text) {
		return self::StaticMinify($Text);
	}
	
	protected static function RemoveKeyFromArray($Array, $Keys) {
		// RemoveKeyFromArray in functions.general.php doesnt work as expected
		if (!is_array($Keys)) $Keys = array($Keys);
		if (is_array($Array)) foreach ($Keys as $Key) unset($Array[$Key]);
		return $Array;
	}
	
	protected static function HashSumFiles($Files) {
		$HashSum = array_sum(array_map('crc32', $Files));
		$NewHash = sprintf('%u', crc32($HashSum));
		return $NewHash;
	}
	
	/*
	* Loads/saves image dimension
	*/
	protected static function ImageDimensions($NewImageDimensions = Null) {
		$CacheFile = PATH_CACHE . '/image_dimensions.ini';
		if ($NewImageDimensions !== Null) {
			$PhpCode = "<?php\n\$_ = " . var_export($NewImageDimensions, True) . ';';
			return file_put_contents($CacheFile, $PhpCode);
		}
		$_ = array();
		if (file_exists($CacheFile)) include $CacheFile;
		return $_;
	}
	
	protected static function StaticPhpQueryReplace(&$String) {
		
		$ImageDimensions = self::ImageDimensions();
		$Domain = Gdn::Request()->Domain();
		
		$Doc = PqDocument($String, array('FixHtml' => False));
		foreach (Pq('img[src]') as $ImgNode) {
			$PqImg = Pq($ImgNode);
			if (!$PqImg->Attr('width') && !$PqImg->Attr('height')) {
				// TODO: DONT USE AbsoluteSource() for relative paths
				$Src = AbsoluteSource(trim($PqImg->Attr('src'), '/'), $Domain);
				$CrcKey = crc32($Src);
				if (!array_key_exists($CrcKey, $ImageDimensions)) {
					$ImageSize = getimagesize($Src);
					$ImageDimensions[$CrcKey] = array($ImageSize[0], $ImageSize[1], '_src' => $Src);
				} else {
					$ImageSize = $ImageDimensions[$CrcKey];
				}
				$PqImg->Attr('width', $ImageSize[0]);
				$PqImg->Attr('height', $ImageSize[1]);
			}
		}
		// Save cache.
		self::ImageDimensions($ImageDimensions);
		$String = $Doc->document->saveXML();
	}
	
	protected static function StaticDomDocumentReplace(&$String) {
		
		if (substr(ltrim($String), 0, 5) != '<?xml') $String = '<?xml version="1.0" encoding="UTF-8"?'. ">\n" . $String;
		$DOMDocument = DOMDocument::LoadXML($String, LIBXML_NOERROR);
		if ($DOMDocument === False) return $String;
		
		$ImageDimensions = self::ImageDimensions();
		$BeforeCount = count($ImageDimensions);
		$Domain = Gdn::Request()->Domain();

		$DomNodeList = $DOMDocument->GetElementsByTagName('img');
		for ($i = $DomNodeList->length - 1; $i >= 0; $i--) {
			$Node = $DomNodeList->Item($i);
			$Width = $Node->GetAttribute('width');
			$Height = $Node->GetAttribute('height');
			if ($Width == '' && $Height == '') {
				$Src = $Node->GetAttribute('src');
				if (!$Src) continue;
				$Src = AbsoluteSource($Src, $Domain);
				$CrcKey = crc32($Src);
				if (!array_key_exists($CrcKey, $ImageDimensions)) {
					$ImageSize = getimagesize($Src);
					$ImageDimensions[$CrcKey] = array($ImageSize[0], $ImageSize[1], '_src' => $Src);
				} else {
					$ImageSize = $ImageDimensions[$CrcKey];
				}
				if ($ImageSize[0] && $ImageSize[1]) {
					$Node->SetAttribute('width', $ImageSize[0]);
					$Node->SetAttribute('height', $ImageSize[1]);
				}
			}
		}
		// Save cache.
		if (count($ImageDimensions) != $BeforeCount) self::ImageDimensions($ImageDimensions);
		$String = $DOMDocument->saveXML(); // LIBXML_NOXMLDECL | LIBXML_NOENT 
		//$DOMDocument->formatOutput = True;
		//$String = $DOMDocument->saveXML(Null, LIBXML_NOXMLDECL | LIBXML_NOENT);
		//d($String);
	}
	
}


