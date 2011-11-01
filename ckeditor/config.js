/*
Copyright (c) 2003-2010, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

CKEDITOR.editorConfig = function( config )
{
 config.removePlugins = 'contextmenu';
 config.extraPlugins = 'MediaEmbed';
 
 
 config.filebrowserBrowseUrl = 'kcfinder/browse.php?type=files';
 	config.filebrowserImageBrowseUrl = 'kcfinder/browse.php?type=images';
 	config.filebrowserFlashBrowseUrl = 'kcfinder/browse.php?type=flash';

 	config.filebrowserUploadUrl = 'kcfinder/upload.php?type=files';
 	config.filebrowserImageUploadUrl = 'kcfinder/upload.php?type=images';
 	config.filebrowserFlashUploadUrl = 'kcfinder/upload.php?type=flash';
	
	
 config.toolbar = 'MyToolbar';

config.toolbar_MyToolbar =
[
    ['Source','-','NewPage','Preview','-','Templates'],
    ['Cut','Copy','Paste','PasteText','PasteFromWord','-','Print', 'SpellChecker', 'Scayt'],
    ['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],
    ['Form', 'Checkbox', 'Radio', 'TextField', 'Textarea', 'Select', 'Button', 'ImageButton', 'HiddenField'],
    ['BidiLtr', 'BidiRtl'],
   
    ['Bold','Italic','Underline','Strike','-','Subscript','Superscript'],
    ['NumberedList','BulletedList','-','Outdent','Indent','Blockquote','CreateDiv'],
    ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
	
   
    ['Image','Flash','MediaEmbed','Table','HorizontalRule','Smiley','SpecialChar','PageBreak'],
    ['Link','Unlink','Anchor'],
	'/',
    ['Styles','Format','Font','FontSize'],
    ['TextColor','BGColor'],
	'/',
    ['Maximize', 'ShowBlocks']
];

 
 };
