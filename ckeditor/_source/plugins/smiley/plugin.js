/*
Copyright (c) 2003-2010, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

CKEDITOR.plugins.add( 'smiley',
{
	requires : [ 'dialog' ],

	init : function( editor )
	{
		editor.config.smiley_path = editor.config.smiley_path || ( this.path + 'images/' );
		editor.addCommand( 'smiley', new CKEDITOR.dialogCommand( 'smiley' ) );
		editor.ui.addButton( 'Smiley',
			{
				label : editor.lang.smiley.toolbar,
				command : 'smiley'
			});
		CKEDITOR.dialog.add( 'smiley', this.path + 'dialogs/smiley.js' );
	}
} );

/**
 * The base path used to build the URL for the smiley images. It must end with
 * a slash.
 * @name CKEDITOR.config.smiley_path
 * @type String
 * @default {@link CKEDITOR.basePath} + 'plugins/smiley/images/'
 * @example
 * config.smiley_path = 'http://www.example.com/images/smileys/';
 * @example
 * config.smiley_path = '/images/smileys/';
 */

/**
 * The file names for the smileys to be displayed. These files must be
 * contained inside the URL path defined with the
 * {@link CKEDITOR.config.smiley_path} setting.
 * @type Array
 * @default (see example)
 * @example
 * // This is actually the default value.
 * config.smiley_images = [
 *     'regular_smile.gif','sad_smile.gif','wink_smile.gif','teeth_smile.gif','confused_smile.gif','tounge_smile.gif',
 *     'embaressed_smile.gif','omg_smile.gif','whatchutalkingabout_smile.gif','angry_smile.gif','angel_smile.gif','shades_smile.gif',
 *     'devil_smile.gif','cry_smile.gif','lightbulb.gif','thumbs_down.gif','thumbs_up.gif','heart.gif',
 *     'broken_heart.gif','kiss.gif','envelope.gif'];
 */


 CKEDITOR.config.smiley_images = [ 'acute.gif', 'aggressive.gif', 'air_kiss.gif', 'angel.gif', 'angel_smile.gif', 'angry_smile.gif', 'ar10.gif', 'ar11.gif', 'ar13.gif', 'ar14.gif', 'ar15.gif', 'ar16.gif', 'ar18.gif', 'ar19.gif', 'ar2.gif', 'ar20.gif', 'ar21.gif', 'ar22.gif', 'ar25.gif', 'ar3.gif', 'ar33.gif', 'ar4.gif', 'ar5.gif', 'ar6.gif', 'ar7.gif', 'ar8.gif', 'ar9.gif', 'bad.gif', 'bb.gif', 'beach.gif', 'beee.gif', 'biggrin.gif', 'big_boss.gif', 'blum.gif', 'blum3.gif', 'blush.gif', 'boast.gif', 'bomb.gif', 'boredom.gif', 'broken_heart.gif', 'buba.gif', 'buba_phone.gif', 'bye.gif', 'clapping.gif', 'confused_smile.gif', 'cray.gif', 'crazy.gif', 'cry_smile.gif', 'curtsey.gif', 'dance.gif', 'dance2.gif', 'dance3.gif', 'dance4.gif', 'dash1.gif', 'dash2.gif', 'dash3.gif', 'declare.gif', 'devil_smile.gif', 'diablo.gif', 'dirol.gif', 'don-t_mention.gif', 'download.gif', 'drinks.gif', 'embaressed_smile.gif', 'english_en.gif', 'envelope.gif', 'feminist.gif', 'feminist_en.gif', 'first_move.gif', 'flirt.gif', 'focus.gif', 'fool.gif', 'friends.gif', 'gamer1.gif', 'gamer2.gif', 'gamer3.gif', 'gamer4.gif', 'girl_blum.gif', 'girl_cray.gif', 'girl_cray2.gif', 'girl_cray3.gif', 'girl_crazy.gif', 'girl_dance.gif', 'girl_devil.gif', 'girl_drink1.gif', 'girl_drink3.gif', 'girl_drink4.gif', 'girl_haha.gif', 'girl_hide.gif', 'girl_hospital.gif', 'girl_impossible.gif', 'girl_in_love.gif', 'girl_mad.gif', 'girl_pinkglassesf.gif', 'girl_prepare_fish.gif', 'girl_sad.gif', 'girl_sigh.gif', 'girl_smile.gif', 'girl_to_take_umbrage.gif', 'girl_to_take_umbrage2.gif', 'girl_wacko.gif', 'girl_wink.gif', 'girl_witch.gif', 'give_heart.gif', 'give_heart2.gif', 'give_rose.gif', 'good.gif', 'hang1.gif', 'hang2.gif', 'hang3.gif', 'heart.gif', 'heat.gif', 'help.gif', 'hi.gif', 'hunter.gif', 'hysteric.gif', 'i-m_so_happy.gif', 'ireful1.gif', 'ireful2.gif', 'ireful3.gif', 'king.gif', 'kiss.gif', 'kiss2.gif', 'kiss3.gif', 'laugh1.gif', 'laugh2.gif', 'laugh3.gif', 'laugh4.gif', 'lazy.gif', 'lightbulb.gif', 'lol.gif', 'lol2.gif', 'mail1.gif', 'mamba.gif', 'man_in_love.gif', 'mda.gif', 'mega_shok.gif', 'moil.gif', 'mosking.gif', 'music.gif', 'music2.gif', 'nea.gif', 'new_russian.gif', 'ok.gif', 'omg_smile.gif', 'on_the_quiet.gif', 'on_the_quiet2.gif', 'padonak.gif', 'paint2.gif', 'paint3.gif', 'paratrooper.gif', 'paratrooper_girl.gif', 'pardon.gif', 'parting.gif', 'party.gif', 'party2.gif', 'pilot.gif', 'pioneer.gif', 'pioneer_smoke.gif', 'pleasantry.gif', 'popcorm1.gif', 'popcorm2.gif', 'prankster2.gif', 'preved.gif', 'punish.gif', 'regular_smile.gif', 'rofl.gif', 'rtfm.gif', 'russian_ru.gif', 'sad.gif', 'sad_smile.gif', 'sarcastic.gif', 'sarcastic_blum.gif', 'sarcastic_hand.gif', 'scare.gif', 'scaut.gif', 'scaut_en.gif', 'scratch_one-s_head.gif', 'search.gif', 'secret.gif', 'sensored.gif', 'SHABLON_padonak_01.gif', 'SHABLON_padonak_02.gif', 'SHABLON_padonak_03.gif', 'shades_smile.gif', 'shok.gif', 'shout.gif', 'slow.gif', 'smile.gif', 'smiley-cry.gif', 'smiley-embarassed.gif', 'smiley-foot-in-mouth.gif', 'smiley-frown.gif', 'smiley-innocent.gif', 'smiley-kiss.gif', 'smiley-laughing.gif', 'smiley-money-mouth.gif', 'smiley-sealed.gif', 'smiley-smile.gif', 'smiley-surprised.gif', 'smiley-tongue-out.gif', 'smiley-undecided.gif', 'smiley-wink.gif', 'smiley-yell.gif', 'smoke.gif', 'soldier.gif', 'soldier_girl.gif', 'sorry.gif', 'spiteful.gif', 'spruce_up.gif', 'stinker.gif', 'suicide2.gif', 'sun_bespectacled.gif', 'superstition.gif', 'swoon.gif', 'tease.gif', 'teeth_smile.gif', 'tender.gif', 'thank_you2.gif', 'this.gif', 'thumbs_down.gif', 'thumbs_up.gif', 'tounge_smile.gif', 'to_babruysk.gif', 'to_become_senile.gif', 'to_pick_ones_nose.gif', 'to_pick_ones_nose2.gif', 'to_take_umbrage.gif', 'training1.gif', 'treaten.gif', 'umnik2.gif', 'unknw.gif', 'vampire.gif', 'vava.gif', 'victory.gif', 'wacko.gif', 'wacko2.gif', 'whatchutalkingabout_smile.gif', 'whistle3.gif', 'wink.gif', 'wink2.gif', 'wink3.gif', 'wink_smile.gif', 'wizard.gif', 'yahoo.gif', 'yes.gif', 'yes3.gif', 'yess.gif', 'yu.gif' ]
 

/**
 * The description to be used for each of the smileys defined in the
 * {@link CKEDITOR.config.smiley_images} setting. Each entry in this array list
 * must match its relative pair in the {@link CKEDITOR.config.smiley_images}
 * setting.
 * @type Array
 * @default  The textual descriptions of smiley.
 * @example
 * // Default settings.
 * config.smiley_descriptions =
 *     [
 *         'smiley', 'sad', 'wink', 'laugh', 'frown', 'cheeky', 'blush', 'surprise',
 *         'indecision', 'angry', 'angel', 'cool', 'devil', 'crying', 'enlightened', 'no',
 *         'yes', 'heart', 'broken heart', 'kiss', 'mail'
 *     ];
 * @example
 * // Use textual emoticons as description.
 * config.smiley_descriptions =
 *     [
 *         ':)', ':(', ';)', ':D', ':/', ':P', ':*)', ':-o',
 *         ':|', '>:(', 'o:)', '8-)', '>:-)', ';(', '', '', '',
 *         '', '', ':-*', ''
 *     ];
 */
CKEDITOR.config.smiley_descriptions =
	[
		'smiley', 'sad', 'wink', 'laugh', 'frown', 'cheeky', 'blush', 'surprise',
		'indecision', 'angry', 'angel', 'cool', 'devil', 'crying', 'enlightened', 'no',
		'yes', 'heart', 'broken heart', 'kiss', 'mail'
	];

/**
 * The number of columns to be generated by the smilies matrix.
 * @name CKEDITOR.config.smiley_columns
 * @type Number
 * @default 8
 * @since 3.3.2
 * @example
 * config.smiley_columns = 6;
 */
