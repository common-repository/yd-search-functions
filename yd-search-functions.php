<?php
/**
 * @package YD_Search_Functions
 * @author Yann Dubois
 * @version 0.4.0
 */

/*
 Plugin Name: YD Search Functions Wordpress plugin
 Plugin URI: http://www.yann.com/en/wp-plugins/yd-search-functions
 Description: Improved search tools and template functions including Google-like search result snippets (on-the-fly contextual abstract), search logging, and hit-highlighting. | Funded by <a href="http://www.nogent-citoyen.com">Nogent Citoyen</a>
 Author: Yann Dubois
 Version: 0.4.0
 Author URI: http://www.yann.com/
 */

/**
 * @copyright 2010  Yann Dubois  ( email : yann _at_ abc.fr )
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */
/**
 Revision 0.1.0:
 - Original beta release
 Revision 0.2.0:
 - Better function interface
 - Simplified function implementation
 - No more debug messages visible
 - Option set for customization
 Revision 0.2.1:
 - Bugfix: caps search string (uppercase) / thanks to Inky for reporting
 Revision 0.3.0:
 - Don't put ellipsis at beginning if not extracted or cut at beginning
 - Default locale  = option + default from blog config
 - Default time format = option
 - Display date = option
 - Link to options/settings page in main plugin list text
 - Make tokenized abstract & highlight work again
 - Make plural form (ending only) work again
 - Plural form = option
 - Improved options/settings page design
 - Linkbackware (instead of silent backlinks...)
 - Translations of new features
 Revision 0.4.0:
 - Implement search logging (option) - ok!
 - Implement most searched list - ok!
 - Search list management (admin pannel) - ok!
 - Most-searched listing function - ok!
 - Most-searched listing-related settings - ok!
 - Display number of different searches today / week / month / year - ok!
 - Display total number of different searches (since first date) - ok!
 - Widget for displaying most searched listing - ok!
 - Widget options panel - ok!
 - Safeguard to automatically count and close <b> tags - ok!
 - Hit-highlighting optional - ok!
 - Accent-aware highlighting optional - ok!
 - Case-sensitive highlighting optional - ok!
 - Advanced / multi-word highlighting optional - ok!
 - Settings update bug - ok!
 - Robot filter - ok!
 - Translations of new features - ok!
 */
/**
 *	TODO:
 *  - Implement rewrite rule get requests
 *  - Most searched RSS
 *  - Implement Google custom search search box function
 *  - Comments search
 *  - Image + attachments search
 *  - Forum search (if bbpress)
 *  - Users search (profile page,...)
 *  - Function + widget for displaying "intelligent" search box
 *  - Function + widget for displaying WP search results list
 *  - Function + widget for displaying GG search results list
 *  - Widget css hierarchy (like other widgets: <ul>, <div>,...)
 *  - Widget bottom link option + default backlinkware
 *  - Screenshots
 *  - Test and final release 
 *  -> version 1.0.0
 *  
 *  phase 2:
 *  - Advanced search options (advanced searchbox)
 *  - Exact search + highlighting (iron != environment)
 *  - Google custom search XML manager
 *  - Priority display (Google custom style)
 *  - Landing page priority display (when people arrive from Google)
 *  - Make priority management directly available from most searched list
 *  - Tabbed presentation
 *  - Google CSS style overriding manager
 *  - Google style page navigation
 *  - Tokenized search word priority
 *  - Post2peer-style most searched sharing (implement most searched rss)
 *  	will eventually re-broadcast most searched words in the network (aggregated rss)
 *  	will manage a customized google search engine to list matching google results
 *  - Search result rss (Wikio-style)
 *  - Optional thumbnail
 *  -> version 2.0.0
 *  
 *  phase 3:
 *  - Related page links widget
 *  - Subscribe to search results e-mail push
 *  - Implement better stemming?
 *  - Better fulltext engine (like Sphinx or Lucene?)
 *  -> version 3.0.0
 */

/** Install or reset plugin defaults **/
function yd_searchfunc_reset( $force ) {
	/** Init values **/
	$yd_searchfunc_version	= "0.4.0";
	$newoption				= 'widget_yd_searchfunc';
	$newvalue				= '';
	$prev_options 			= get_option( $newoption );
	if( !isset( $prev_options['plugin_version'] ) 
		|| $prev_options['plugin_version'] != $yd_searchfunc_version ) {
		yd_searchfunc_db_struct();
		$newvalue = $prev_options;
		$newvalue['plugin_version'] 	= $yd_searchfunc_version;
		update_option( $newoption, $newvalue );
		$newvalue = '';
	}
	if( ( isset( $force ) && $force ) || !isset( $prev_options['plugin_version'] ) ) {
		$def_locale 		= 'en_US';
		$def_charset		= 'utf8';
		$def_time_format	= __( '%b. %e, %Y', 'yd-searchfunc'); // php strftime() format
		if( defined( 'WPLANG' ) )				$def_locale 	= WPLANG;
		if( defined( 'DB_CHARSET' ) ) 			$def_charset 	= DB_CHARSET;
		//if( $tf = get_option('date_format') ) 	$def_time_format= $tf;
		// those default options/settings are set-up at plugin first-install or manual reset only
		// they will not be changed when the plugin is just upgraded or deactivated/reactivated
		$newvalue['plugin_version'] 	= $yd_searchfunc_version;
		$newvalue[0]['role']			= 'administrator';
		$newvalue[0]['cutlength'] 		= 150;	// size of the snippet abstract text
		$newvalue[0]['token_min_len'] 	= 3;	// smaller word units will not be parsed
		$newvalue[0]['default_ellip'] 	= '<b>...</b>';	// default ellipsis string (such as...)
		$newvalue[0]['hit_hiliting'] 	= 1;
		$newvalue[0]['accent_aware'] 	= 1;
		$newvalue[0]['case_sensitive'] 	= 0;
		$newvalue[0]['advanced_hilite']	= 1;
		$newvalue[0]['before_hilite'] 	= '<b>'; 
		$newvalue[0]['after_hilite'] 	= '</b>';
		$newvalue[0]['locale']			= $def_locale;
		$newvalue[0]['charset']			= $def_charset;
		$newvalue[0]['time_format']		= $def_time_format;
		$newvalue[0]['display_date']	= 1;
		$newvalue[0]['debug']			= 0;
		$newvalue[0]['match_plural']	= 1;
		$newvalue[0]['plural_form']		= 's';
		$newvalue[0]['disable_backlink']= 0;
		$newvalue[0]['search_logging']	= 1;
		$newvalue[0]['search_base_url']	= '/?s=';
		$newvalue[0]['dash_top_count']	= 10;
		$newvalue[0]['func_top_count']	= 50;
		$newvalue[0]['display_rownum']	= 1;
		$newvalue[0]['display_count']	= 1;
		$newvalue[1]['widget_title']	= 'Top searches';
		$newvalue[1]['widget_top_count']= 10;
		$newvalue[1]['widget_display_rownum'] = 1;
		$newvalue[1]['widget_display_count'] = 1;
		if( $prev_options ) {
			update_option( $newoption, $newvalue );
		} else {
			add_option( $newoption, $newvalue );
		}
	}
}
register_activation_hook(__FILE__, 'yd_searchfunc_reset');

/** Create Text Domain For Translations **/
add_action('init', 'yd_searchfunc_textdomain');
function yd_searchfunc_textdomain() {
	$plugin_dir = basename( dirname(__FILE__) );
	load_plugin_textdomain(
		'yd-searchfunc',
		PLUGINDIR . '/' . dirname( plugin_basename( __FILE__ ) ),
		dirname( plugin_basename( __FILE__ ) )
	); 
}

/** Create custom admin menu page **/
add_action('admin_menu', 'yd_searchfunc_menu');
function yd_searchfunc_menu() {
	$options = get_option( 'widget_yd_searchfunc' );
	$i = 0;
	$role_to_level= array(
	    'subscriber'	=> 0,
		'contributor'	=> 1,
		'author'		=> 2,
		'editor'		=> 5,
		'administrator'	=> 8 
	);
	$access = intval( $role_to_level[$options[0]['role']] );
	add_options_page(
		__('YD Search Functions Options', 'yd-searchfunc'), 
		__('YD Search Functions', 'yd-searchfunc'),
		$access,
		__FILE__,
		'yd_searchfunc_options'
	);
}

function yd_searchfunc_options() {
	$support_url	= 'http://www.yann.com/en/wp-plugins/yd-search-functions';
	$yd_logo		= 'http://www.yann.com/yd-searchfunc-v040-logo.gif';
	$jstext			= preg_replace( "/'/", "\\'", __( 'This will disable the link in your blog footer. ' .
							'If you are using this plugin on your site and like it, ' .
							'did you consider making a donation?' .
							' -- Thanks.', 'yd-searchfunc' ) );
	?>
	<script type="text/javascript">
	<!--
	function donatemsg() {
		alert( '<?php echo $jstext ?>' );
	}
	//-->
	</script>
	<?php
	echo '<div class="wrap">';
	
	// ---
	// options/settings page header section: h2 title + warnings / updates
	// ---
	
	echo '<h2>' . __('YD Search Functions Options', 'yd-searchfunc') . '</h2>';

	if( isset( $_GET["do"] ) ) {
		echo '<div class="updated">';
		echo '<p>' . __('Action:', 'yd-searchfunc') . ' '
		. __( 'I should now', 'yd-searchfunc' ) . ' ' . __( $_GET["do"], 'yd-searchfunc' ) . '.</p>';
		if(	$_GET["do"] == __('Reset widget options', 'yd-searchfunc') ) {
			yd_searchfunc_reset( 'force' );
			echo '<p>' . __('Widget options are reset', 'yd-searchfunc') . '</p>';
		} elseif(	$_GET["do"] == __('Update widget options', 'yd-searchfunc') ) {
			yd_searchfunc_update_options();
			echo '<p>' . __('Widget options are updated', 'yd-searchfunc') . '</p>';
		}
		echo '</div>'; // / updated
	} else {
		echo '<div class="updated">';
		echo '<p>'
		. '<a href="http://www.yann.com/en/wp-plugins/yd-search-functions" target="_blank" title="Plugin FAQ">';
		echo __('Welcome to YD Search Functions Admin Page.', 'yd-searchfunc')
		. '</a></p>';
		echo '</div>'; // / updated
	}
	$options = get_option( 'widget_yd_searchfunc' ); // need to fetch the options again in case updated.
	$i = 0;
	if( ! is_array( $options ) ) {
		// Something went wrong
		echo '<div class="updated">'; //TODO: Replace with appropriate error / warning class (red/pink)
		echo __( 'Uh-oh. Looks like I lost my settings. Sorry.', 'yd-searchfunc' );
		echo '<form method="get" style="display:inline;">';
		echo '<input type="submit" name="do" value="' . __( 'Reset widget options', 'yd-searchfunc' ) . '"><br/>';
		echo '<input type="hidden" name="page" value="' . $_GET["page"] . '">';
		echo '</form>';
		echo '</div>'; // / updated
		return false;
	}
	
	// ---
	// Right sidebar
	// ---
	
	echo '<div class="metabox-holder has-right-sidebar">';
	echo '<div class="inner-sidebar">';
	echo '<div class="meta-box-sortabless ui-sortable">';

	// == Block 1 ==

	echo '<div class="postbox">';
	echo '<h3 class="hndle">' . __( 'Considered donating?', 'yd-searchfunc' ) . '</h3>';
	echo '<div class="inside" style="text-align:center;"><br/>';
	echo '<a href="' . $support_url . '" target="_blank" title="Plugin FAQ" border="0">'
	. '<img src="' . $yd_logo . '" alt="YD logo" /></a>'
	. '<br/><small>' . __( 'Enjoy this plugin?', 'yd-searchfunc' ) . '<br/>' . __( 'Help me improve it!', 'yd-searchfunc' ) . '</small><br/>'
	. '<form action="https://www.paypal.com/cgi-bin/webscr" method="post">'
	. '<input type="hidden" name="cmd" value="_s-xclick">'
	. '<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----'
	. '">'
	. '<input type="image" src="https://www.paypal.com/' . __( 'en_US', 'yd-searchfunc' ) . '/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">'
	. '<img alt="" border="0" src="https://www.paypal.com/' . __( 'en_US', 'yd-searchfunc' ) . '/i/scr/pixel.gif" width="1" height="1">'
	. '</form>'
	. '<small><strong>' . __( 'Thanks', 'yd-searchfunc' ) . ' - Yann.</strong></small><br/><br/>';
	
	//---
	echo '<form method="get" style="display:inline;">';
	//---
	
	echo '<table style="margin:10px;">';
	echo '<tr><td>' . __( 'Disable backlink in the blog footer:', 'yd-searchfunc' ) .
		'</td><td><input type="checkbox" name="yd_searchfunc-disable_backlink-0" value="1" ';
	if( $options[$i]["disable_backlink"] == 1 ) echo ' checked="checked" ';
	echo ' onclick="donatemsg()" ';
	echo ' /></td></tr>';
	echo '</table>';
	
	echo '</div>'; // / inside
	echo '</div>'; // / postbox
	
	// == Block 2 ==
	
	echo '<div class="postbox">';
	echo '<h3 class="hndle">' . __( 'Most searched', 'yd-searchfunc' ) . '</h3>';
	echo '<div class="inside" style="padding:10px;">';
	
	yd_searchfunc_dashboard();
	
	echo '</div>'; // / inside
	echo '</div>'; // / postbox
	
	// == Block 3 ==
	
	echo '<div class="postbox">';
	echo '<h3 class="hndle">' . __( 'Credits', 'yd-searchfunc' ) . '</h3>';
	echo '<div class="inside" style="padding:10px;">';
	echo '<b>' . __( 'Initial funding', 'yd-searchfunc' ) . '</b>';
	echo '<ul><li><a href="http://www.nogent-citoyen.com">Nogent Citoyen</a></li></ul>';
	echo '<b>' . __( 'Translations', 'yd-searchfunc' ) . '</b>';
	echo '<ul>';
	echo '<li>' . __( 'English:', 'yd-searchfunc' ) . ' <a href="http://www.yann.com">Yann</a></li>';
	echo '<li>' . __( 'French:', 'yd-searchfunc' ) . ' <a href="http://www.yann.com">Yann</a></li>';
	echo '</ul>';
	echo __( 'If you want to contribute to a translation of this plugin, please drop me a line by ', 'yd-searchfunc' );
	echo '<a href="mailto:yann@abc.fr">' . __('e-mail', 'yd-searchfunc' ) . '</a> ';
	echo __( 'or leave a comment on the ', 'yd-searchfunc' );
	echo '<a href="' . $support_url . '">' . __( 'plugin\'s page', 'yd-searchfunc' ) . '</a>. ';
	echo __( 'You will get credit for your translation in the plugin file and the documentation page, ', 'yd-searchfunc' );
	echo __( 'as well as a link on this page and on my developers\' blog.', 'yd-searchfunc' );
		
	echo '</div>'; // / inside
	echo '</div>'; // / postbox
	
	// == Block 4 ==
	
	echo '<div class="postbox">';
	echo '<h3 class="hndle">' . __( 'Support' ) . '</h3>';
	echo '<div class="inside" style="padding:10px;">';
	echo '<b>' . __( 'Free support', 'yd-searchfunc' ) . '</b>';
	echo '<ul>';
	echo '<li>' . __( 'Support page:', 'yd-searchfunc' );
	echo ' <a href="' . $support_url . '">' . __( 'here.', 'yd-searchfunc' ) . '</a>';
	echo ' ' . __( '(use comments!)', 'yd-searchfunc' ) . '</li>';
	echo '</ul>';
	echo '<p><b>' . __( 'Professional consulting', 'yd-searchfunc' ) . '</b><br/>';
	echo __( 'I am available as an experienced free-lance Wordpress plugin developer and web consultant. ', 'yd-searchfunc' );
	echo __( 'Please feel free to <a href="mailto:yann@abc.fr">check with me</a> for any adaptation or specific implementation of this plugin. ', 'yd-searchfunc' );
	echo __( 'Or for any WP-related custom development or consulting work. Hourly rates available.', 'yd-searchfunc' ) . '</p>';
	echo '</div>'; // / inside
	echo '</div>'; // / postbox
	
	echo '</div>'; // / meta-box-sortabless ui-sortable
	echo '</div>'; // / inner-sidebar

	// ---
	// Main content area
	// ---
	
	echo '<div class="has-sidebar sm-padded">';
	echo '<div id="post-body-content" class="has-sidebar-content">';
	echo '<div class="meta-box-sortabless">';
	
	// == Result formatting options ==
	
	echo '<div class="postbox">';
	echo '<h3 class="hndle">' . __( 'Result formatting options:', 'yd-searchfunc' ) . '</h3>';
	echo '<div class="inside">';
	echo '<table style="margin:10px;">';
		echo '<tr><td>' . __('Abstract text length:', 'yd-searchfunc') .
		'</td><td><input type="text" name="yd_searchfunc-cutlength-0" value="' .
		htmlentities( $options[$i]["cutlength"] ) . '" size="4" /></td></tr>';
	//token_min_len
	echo '<tr><td>' . __('Minimum word length:', 'yd-searchfunc') .
		'</td><td><input type="text" name="yd_searchfunc-token_min_len-0" value="' .
		htmlentities( $options[$i]["token_min_len"] ) . '" size="3" /></td></tr>';
	//default_ellip
	echo '<tr><td>' . __('Default ellipsis:', 'yd-searchfunc') .
		'</td><td><input type="text" name="yd_searchfunc-default_ellip-0" value="' .
		htmlentities( $options[$i]["default_ellip"] ) . '" size="10" /></td></tr>';
	//hit_hiliting
	echo '<tr><td>' . __('Hit highlighting:', 'yd-searchfunc') .
		'</td><td><input type="checkbox" name="yd_searchfunc-hit_hiliting-0" value="1" ';
	if( $options[$i]["hit_hiliting"] == 1 ) echo ' checked="checked" ';
	echo ' /></td></tr>';
	//accent_aware
	echo '<tr><td>' . __('Accentuation awareness:', 'yd-searchfunc') .
		'</td><td><input type="checkbox" name="yd_searchfunc-accent_aware-0" value="1" ';
	if( $options[$i]["accent_aware"] == 1 ) echo ' checked="checked" ';
	echo ' /></td></tr>';
	//case_sensitive
	echo '<tr><td>' . __('Case sensitivity:', 'yd-searchfunc') .
		'</td><td><input type="checkbox" name="yd_searchfunc-case_sensitive-0" value="1" ';
	if( $options[$i]["case_sensitive"] == 1 ) echo ' checked="checked" ';
	echo ' /></td></tr>';
	//advanced_hilite
	echo '<tr><td>' . __('Advanced (multi-word) highlighting:', 'yd-searchfunc') .
		'</td><td><input type="checkbox" name="yd_searchfunc-advanced_hilite-0" value="1" ';
	if( $options[$i]["advanced_hilite"] == 1 ) echo ' checked="checked" ';
	echo ' /></td></tr>';
	//before_hilite
	echo '<tr><td>' . __('Before highlight:', 'yd-searchfunc') .
		'</td><td><input type="text" name="yd_searchfunc-before_hilite-0" value="' .
		htmlentities( $options[$i]["before_hilite"] ) . '" size="25" /></td></tr>';
	//after_hilite
	echo '<tr><td>' . __('After highlight:', 'yd-searchfunc') .
		'</td><td><input type="text" name="yd_searchfunc-after_hilite-0" value="' .
		htmlentities( $options[$i]["after_hilite"] ) . '" size="10" /></td></tr>';
	//locale
	echo '<tr><td>' . __('Date localization:', 'yd-searchfunc') .
		'</td><td><input type="text" name="yd_searchfunc-locale-0" value="' .
		htmlentities( $options[$i]["locale"] ) . '" size="10" /></td></tr>';
	//charset
	echo '<tr><td>' . __('Date charset:', 'yd-searchfunc') .
		'</td><td><input type="text" name="yd_searchfunc-charset-0" value="' .
		htmlentities( $options[$i]["charset"] ) . '" size="10" /></td></tr>';
	//time_format
	echo '<tr><td>' . __('Date format:', 'yd-searchfunc') .
		'</td><td><input type="text" name="yd_searchfunc-time_format-0" value="' .
		htmlentities( $options[$i]["time_format"] ) . '" size="10" />' .
		' <i>' . __( 'Uses PHP strftime() format', 'yd-searchfunc' ) . '</i>' .
		'</td></tr>';
	//display_date
	echo '<tr><td>' . __('Display the date:', 'yd-searchfunc') .
		'</td><td><input type="checkbox" name="yd_searchfunc-display_date-0" value="1" ';
	if( $options[$i]["display_date"] == 1 ) echo ' checked="checked" ';
	echo ' /></td></tr>';
	//match_plural
	echo '<tr><td>' . __('Try to match plurals:', 'yd-searchfunc') .
		'</td><td><input type="checkbox" name="yd_searchfunc-match_plural-0" value="1" ';
	if( $options[$i]["match_plural"] == 1 ) echo ' checked="checked" ';
	echo ' /></td></tr>';
	//plural_form
	echo '<tr><td>' . __('Plural form:', 'yd-searchfunc') .
		'</td><td><input type="text" name="yd_searchfunc-plural_form-0" value="' .
		htmlentities( $options[$i]["plural_form"] ) . '" size="3" />' .
		'</td></tr>';	

	echo '</table>';
	echo '</div>'; // / inside
	echo '</div>'; // / postbox
	
	// == Search logging options ==
	
	echo '<div class="postbox">';
	echo '<h3 class="hndle">' . __( 'Search logging options:', 'yd-searchfunc' ) . '</h3>';
	echo '<div class="inside">';
	echo '<table style="margin:10px;">';
	
	//search_logging
	echo '<tr><td>' . __('Enable search logging:', 'yd-searchfunc') .
		'</td><td><input type="checkbox" name="yd_searchfunc-search_logging-0" value="1" ';
	if( $options[$i]["search_logging"] == 1 ) echo ' checked="checked" ';
	echo ' /></td></tr>';
	//search_base_url
	echo '<tr><td>' . __('Search base URL:', 'yd-searchfunc') .
		'</td><td><input type="text" name="yd_searchfunc-search_base_url-0" value="' .
		htmlentities( $options[$i]["search_base_url"] ) . '" size="10" />' .
		'</td></tr>';	
	//dash_top_count
	echo '<tr><td>' . __('List in dashboard:', 'yd-searchfunc') .
		'</td><td><input type="text" name="yd_searchfunc-dash_top_count-0" value="' .
		htmlentities( $options[$i]["dash_top_count"] ) . '" size="3" /> ' .
		__('most searched expressions', 'yd-searchfunc') .
		'</td></tr>';
	//func_top_count
	echo '<tr><td>' . __('List in function:', 'yd-searchfunc') .
		'</td><td><input type="text" name="yd_searchfunc-func_top_count-0" value="' .
		htmlentities( $options[$i]["func_top_count"] ) . '" size="3" /> ' .
		__('most searched expressions', 'yd-searchfunc') . ' ' .
		__('(default value)', 'yd-searchfunc') .
		'</td></tr>';
	//display_rownum
	echo '<tr><td>' . __('Display row number in function listing:', 'yd-searchfunc') .
		'</td><td><input type="checkbox" name="yd_searchfunc-display_rownum-0" value="1" ';
	if( $options[$i]["display_rownum"] == 1 ) echo ' checked="checked" ';
	echo ' /></td></tr>';
	//display_count
	echo '<tr><td>' . __('Display count in function listing:', 'yd-searchfunc') .
		'</td><td><input type="checkbox" name="yd_searchfunc-display_count-0" value="1" ';
	if( $options[$i]["display_count"] == 1 ) echo ' checked="checked" ';
	echo ' /></td></tr>';
			
	echo '</table>';
	echo '</div>'; // / inside
	echo '</div>'; // / postbox
	
	// == Other options ==

	echo '<div class="postbox">';
	echo '<h3 class="hndle">' . __( 'Other options:', 'yd-searchfunc' ) . '</h3>';
	echo '<div class="inside">';
	echo '<table style="margin:10px;">';
		
	//debug
	echo '<tr><td>' . __('Debug mode:', 'yd-searchfunc') .
		'</td><td><input type="checkbox" name="yd_searchfunc-debug-0" value="1" ';
	if( $options[$i]["debug"] == 1 ) echo ' checked="checked" ';
	echo ' /></td></tr>';
			
	//---
	
	echo '</table>';
	
	echo '</div>'; // / inside
	echo '</div>'; // / postbox
	
	echo '<div>';
	echo '<p class="submit">';
	echo '<input type="submit" name="do" value="' . __('Update widget options', 'yd-searchfunc') . '">';
	echo '<input type="hidden" name="page" value="' . $_GET["page"] . '">';
	echo '<input type="hidden" name="time" value="' . time() . '">';
	echo '</p>';
	echo '</form>';
	
	//---
	
	echo '<form method="get" style="display:inline;">';
	echo '<p class="submit">';
	echo '<input type="submit" name="do" value="' . __('Reset widget options', 'yd-searchfunc') . '">';
	echo '<input type="hidden" name="page" value="' . $_GET["page"] . '">';
	echo '</p>'; // / submit
	echo '</form>';
	echo '</div>'; // /
	
	echo '</div>'; // / meta-box-sortabless
	echo '</div>'; // / has-sidebar-content
	echo '</div>'; // / has-sidebar sm-padded
	echo '</div>'; // / metabox-holder has-right-sidebar
	echo '</div>'; // /wrap
}

/** Add links on the plugin page (short description) **/
add_filter( 'plugin_row_meta', 'yd_searchfunc_links' , 10, 2 );
function yd_searchfunc_links( $links, $file ) {
	$base = plugin_basename(__FILE__);
	if ( $file == $base ) {
		$links[] = '<a href="options-general.php?page=yd-search-functions%2Fyd-search-functions.php">' . __('Settings') . '</a>';
		$links[] = '<a href="http://www.yann.com/en/wp-plugins/yd-search-functions">' . __('Support') . '</a>';
	}
	return $links;
}

/** Update display options of the options/settings admin page **/
function yd_searchfunc_update_options(){
	$to_update = Array(
		'cutlength',
		'token_min_len',
		'default_ellip',
		'before_hilite',
		'after_hilite',
		'locale',
		'charset',
		'time_format',
		'display_date',
		'debug',
		'match_plural',
		'plural_form',
		'disable_backlink',
		'search_logging',
		'search_base_url',
		'dash_top_count',
		'func_top_count',
		'display_rownum',
		'display_count',
		'hit_hiliting',
		'accent_aware',
		'case_sensitive',
		'advanced_hilite'
	);
	yd_update_options_nostrip_array( 'widget_yd_searchfunc', 0, $to_update, $_GET, 'yd_searchfunc-' );
}

// ============================ Plugin specific functions start here =================

function yd_search_snippet( 
	$the_content = '',
	$the_search = '',
	$the_time = '', 
	$locale = '', 
	$timeformat = '',
	$display = true
) {
	global $wp_query;
	global $post;
	global $more;
	$old_more = $more;
	$more = 1;
	$options = get_option( 'widget_yd_searchfunc' );
	$i = 0;
	if( $the_content === false ) $display = false;
	if( $the_content === '' )	$the_content	= get_the_content();
	if( $the_search  === '' )	$the_search		= get_query_var( 's' );
	if( $the_time    === '' )	$the_time		= get_the_time('U');
	if( $locale		 === '' )	$locale			= $options[$i]["locale"]
													. '.' . $options[$i]["charset"];
	if( $timeformat	 === '' )	$timeformat		= $options[$i]["time_format"];
	$cutlength = $options[$i]["cutlength"];
	$content = '';
	setlocale( LC_TIME, $locale );
	if( $options[$i]["display_date"] ) $content .= strftime( $timeformat, $the_time ) . ' ';
	$content .= yd_extract_snip( 
		yd_strip_tags(
			yd_strip_sqb( $the_content )
		),
		$the_search
	);
	if( $options[$i]["hit_hiliting"] ) $content = yd_highlight( $content, $the_search );
	if( $display === true ) echo $content;
	$more = $old_more;
	return $content;
}

function yd_highlight( $content, $search_string, $before = false, $after = false ) {
	$options = get_option( 'widget_yd_searchfunc' );
	$i = 0;
	if( $before === false ) $before = $options[$i]["before_hilite"]; // default value
	if( $after === false ) $after = $options[$i]["after_hilite"]; // default value
	//$content = trim( $content );
	$pattern = yd_patternize( $search_string, false );
	//try simple match first.
	$content = preg_replace( $pattern, $before . "$1" . $after, $content, -1, $count );
	if( $count == 0 && $options[$i]["advanced_hilite"] ) {
		//no simple match: try advanced match
		// first try to match full expression
		$content2 = yd_advanced_highlight( $content, $pattern, $before, $after );
		if( $content2 == $content ) {
			// next try to match individual words as well
			$pattern = yd_patternize( $search_string, true );	// will tokenize search string / search for partial matches for each token
			if( $options[$i]["debug"] ) echo 'pattern: ' . $pattern . '<br/>'; 
			$content = yd_advanced_highlight( $content, $pattern, $before, $after );
		} else {
			$content = $content2;
		}
	}
	if( $hitcount = preg_match_all( '/<b>/i', $content, $m ) ) {
		//safeguard
		$closecount = preg_match_all( '|</b>|i', $content, $m );
		if( $closecount != $hitcount ) {
			//we're missing some </b>s here...
			$missing = ( $hitcount - $closecount );
			$content .= str_repeat( '</b>', $missing );
		}
	}
	return $content;
}

function yd_advanced_highlight( $content, $pattern, $before, $after ) {
	$options = get_option( 'widget_yd_searchfunc' );
	$i = 0;
	$idx_content = $content;
	if( $options[$i]['accent_aware'] ) $idx_content = yd_strnormalize( $content );
	if( preg_match_all( $pattern, $idx_content, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE ) ) {
		foreach( $matches as $i => $match ) {
			$matched_string = $match[1][0];
			$begin_pos_idx = $match[1][1];
			$begin_pos = $begin_pos_idx + $tagging_offset; //in fact this count will be approximate due to utf-8 mb conversions
			//try to guess utf-8 / ascii offset
			$mblen = mb_strlen( substr( $content, 0, $begin_pos ) );
			$offset = $begin_pos - $mblen;
			$begin_pos += $offset;
			if( $options[$i]["debug"] ) echo 'pos: ' . $begin_pos . '|offset: ' . $offset . '|matched str: ' . $matched_string . '<br/>';
			//echo '|' . $idx_content . '|' . $content . '|';
			//beware of ending mb utf-8 char: search for next space char
			$str_frag_end = strpos( $content, ' ', $begin_pos + strlen( $matched_string ) );
			$frag_length = $str_frag_end - $begin_pos;
			$str_fragment = substr( $content, $begin_pos, $frag_length );
			$str_fragment_notag = strip_tags( $str_fragment );
			$tagging_offset -= ( strlen( $str_fragment ) - strlen( $str_fragment_notag ) );
					//account for any deleted tag here
			$str_fragment = $str_fragment_notag;
			if( preg_match( "/[<>]/", $str_fragment ) ) {
				//don't tag if it contains tags;
			} else {
				$content = substr_replace( 
					$content, 
					$before . $str_fragment . $after,
					$begin_pos,
					strlen( $str_fragment )
				);
				//take into account offset of added tags compared to indexed string
				$tagging_offset += strlen( $before . $after );
			}
		}
	}
	return $content;
}

function mb_substr_replace($output, $replace, $posOpen, $posClose) {
        return mb_substr($output, 0, $posOpen).$replace.mb_substr($output, $posClose+1);
    } 
    
function yd_extract_snip( $content, $search_string, $sniplength = false, $ellip = false ) {
	$options = get_option( 'widget_yd_searchfunc' );
	$i = 0;
	$token_min_length = $options[$i]["token_min_len"]; //smaller word units will not be parsed
	if( $ellip === false ) $ellip = ' ' . trim( $options[$i]["default_ellip"] ) . ' '; // default value
	if( $sniplength === false ) $sniplength = $options[$i]["cutlength"]; // default value
	$err = '';
	// first search for complete string
	$pattern = yd_patternize( $search_string );
	$search_string = yd_strnormalize( $search_string );
	$idx_content = yd_strnormalize( $content );
	//$content = trim( $content );
	$halflength = floor( ( $sniplength - strlen( $search_string ) ) / 2 );
	//first try stripos...
	$firstmatch = stripos( $idx_content, $search_string );
	// if no match try regexp
	if( $firstmatch === false ) {
		if( preg_match( $pattern, $idx_content, $matches, PREG_OFFSET_CAPTURE ) ) {
			$firstmatch = $matches[1][1];
		}
	}
	if( $firstmatch !== false ) {
		//try to guess utf-8 / ascii offset
		$mblen = mb_strlen( substr( $content, 0, $firstmatch ) );
		$offset = $firstmatch - $mblen;
		$firstmatch += $offset;
		
		$beginsnip = strpos( $content, ' ', $firstmatch - $halflength );
		$leftside = substr( $content, $beginsnip, $sniplength );
		$endsnip = strrpos( $leftside, ' ' );
		$snip = substr( $leftside, 0, $endsnip );
		$bellip = $ellip;
		if( $beginsnip <= 0 ) $bellip = '';
		return $bellip . $snip . $ellip;
	} else {
		// did not find complete search string
		// next: search for individual words (tokens)
		// tokenize search string
		$tokens = preg_split( '/[\s]+/', $search_string );
		// next search for individual tokens
		$firstmatches = array();
		//echo '[' . $idx_content . ']';
		foreach( $tokens as $token ) {
			if( strlen( $token ) < $token_min_length ) continue;
			$err .= $token . ' | ';
			$firstmatch = stripos( $idx_content, $token );
			if( $firstmatch === false ) {
				//$err .= 'cplx';
				if( preg_match( $pattern, $idx_content, $matches, PREG_OFFSET_CAPTURE ) ) {
					$firstmatch = $matches[1][1];
				}
			}
			if( $firstmatch !== false ) {
				//$err .= 'found';
				$firstmatches[] = $firstmatch;
			}
		}
		
		if( count( $firstmatches ) > 0 ) {
			
			//TODO: matchlist sort by token proximity
			// - first sort by offset to get them in order
			// - next loop through the array to add a proximity with previous word key/value
			// - next usort the array by proximity
			// - if equal proximity, take longest words
			// ...something like that...
			
			$firstmatch = $firstmatches[0];
			$beginsnip = strpos( $content, ' ', $firstmatch - $halflength );
			$leftside = substr( $content, $beginsnip, $sniplength );
			$endsnip = strrpos( $leftside, ' ' );
			$snip = substr( $leftside, 0, $endsnip );
			$bellip = $ellip;
			if( $beginsnip <= 0 ) $bellip = '';
			return $bellip . $snip . $ellip;
		} else {
			return yd_clean_cut( $content, $sniplength ) . $ellip;
		}
	}
}

function yd_strip_curly( $string ) {
	// UNUSED right now...
	$string = preg_replace( '/\xe2\x80\x99/s', '\'', $string );
	$string = preg_replace( '/\xe2\x80\x98/s', '\'', $string );
	$string = preg_replace( '/\xe2\x80\x9c/s', '\"', $string );
	$string = preg_replace( '/\xe2\x80\x9d/s', '\"', $string );
	return $string;
}

function yd_strnormalize( $string ) {
	//$string = trim ( $string );
	$string = remove_accents( $string ); // WP function in /wp-includes/formatting.php
	$string = preg_replace( "/[^a-zA-Z0-9]/i", ' ', $string );
	//$string = preg_replace( "/[\s]+/", ' ', $string ); 
	//	don't reduce whitespace: the 2 strings need to stay same length
	return $string;
}

function yd_patternize( $string, $alternation = false ) {
	$options = get_option( 'widget_yd_searchfunc' );
	$i = 0;
	//$string = preg_replace( '/[^a-zA-Z0-9\s]/', '.', $string ); // accents?
	//$string = mb_ereg_replace( '/[^a-zA-Z0-9\s]/', '.', $string ); // accents?
	mb_internal_encoding("UTF-8");
	mb_regex_encoding("UTF-8");
	if( $options[$i]['accent_aware'] ) $string = mb_ereg_replace( '[^a-zA-Z0-9\s]', '..?', $string ); // accents?
	$string2 = $string;
	$string = preg_replace( '/[\s]+/', '[\\s]*', $string ); // rubber space
	$string2 = preg_replace( '/[\s]+/', '|', $string2 ); // alternation
	$string2 = preg_replace( '/\|.{1,2}\|/', '|', $string2 ); // remove single/double char words
	$string2 = preg_replace( '/\|.{1,2}$/', '', $string2 ); // remove single/double chars at end, too!
	if( $options[$i]['match_plural'] ) {
		$p = $options[$i]['plural_form'];
		$string2 = preg_replace( '/([^\|]+)/', '\b' . "$1" . $p . '?\b', $string2 ); // word boundaries + plural
		$string2 = preg_replace( "/" . $p . "\\b([^\?])/", $p . "?\b$1", $string2 ); // plural is optional to get singular form
	} else {
		$string2 = preg_replace( '/([^\|]+)/', '\b' . "$1" . '\b', $string2 ); // word boundaries alone
	}
	$string = preg_replace( '|/|', '\\/', $string ); // escape slashes
	if( $alternation && $string != $string2 ) $string = $string . '|' . $string2;
	$string = '/(' . $string . ')/';
	if( !$options[$i]['case_sensitive'] ) $string .= 'i';
	return $string;
}

function yd_strip_sqb( $content ) {
	// strip square brackets
	return preg_replace( 
		"/\[[^\]]+\]/", 
		"", 
		$content
	);
}

function yd_strip_tags( $text, $flags = '' ) {
	// strip tags but replace with whitespace
	$text = preg_replace( '/</', ' <', $text );
	$text = preg_replace( '/>/', '> ', $text );
	$text = html_entity_decode( strip_tags( $text, $flags ), ENT_QUOTES, "UTF-8" );
	$text = preg_replace( '/[\s]+/', ' ', $text );
	return $text;
}

function yd_searchfunc_linkware() {
	$options = get_option( 'widget_yd_searchfunc' );
	$i = 0;
	if( $options[$i]['disable_backlink'] ) echo "<!--\n";
	echo '<p style="text-align:center" class="yd_linkware"><small><a href="http://www.yann.com/en/wp-plugins/yd-search-functions">Featuring Advanced Search Functions plugin by YD</a></small></p>';
	if( $options[$i]['disable_backlink'] ) echo "\n-->";
}
add_action('wp_footer', 'yd_searchfunc_linkware');

function yd_searchfunc_db_struct() {
	global $wpdb;
	$table_name = $wpdb->prefix . "yd_searchlog";
	if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
		$sql = "
			CREATE TABLE IF NOT EXISTS `" . $table_name . "` (
 				`term` varchar(128) NOT NULL,
  				`slug` varchar(128) NOT NULL,
  				`count` bigint(20) NOT NULL default '1',
  				`first` timestamp NOT NULL default CURRENT_TIMESTAMP,
  				`last` timestamp NOT NULL default '0000-00-00 00:00:00',
  				PRIMARY KEY  (`term`),
  				FULLTEXT KEY `term` (`term`)
			);
		";
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta($sql);
	}
}

function yd_searchfunc_searchlog( $wp ) {
	if( is_search() ) {
		$options = get_option( 'widget_yd_searchfunc' );
		$i = 0;
		if( $options[$i]['search_logging'] && !yd_is_robot() ) {
			global $wpdb;
			$table_name = $wpdb->prefix . "yd_searchlog";
			$search = get_search_query();
			$slug = sanitize_title( $search );
			$slug = preg_replace( "/-/", "+", $slug );
			$query = "INSERT DELAYED INTO `" . $table_name . "` ( term, slug, count, first, last ) VALUES " .
				" ( '" . preg_replace( "/'/", "''", $search ) . "', " .
				" '" . $slug . "', " .
				" 1, CURRENT_TIMESTAMP(), CURRENT_TIMESTAMP() ) " .
				" ON DUPLICATE KEY UPDATE count = if( count > 0, count +1, -1 ), last = CURRENT_TIMESTAMP";
			$wpdb->query( $query );
		}
	}
}
add_action( 'wp', 'yd_searchfunc_searchlog' );

function yd_is_robot() {
	$ua	= $_SERVER['HTTP_USER_AGENT'];
	if( preg_match( '/Googlebot/', $ua ) ) return TRUE;
	if( preg_match( '/Mediapartners-Google/', $ua ) ) return TRUE;
	if( preg_match( '/Slurp/', $ua ) ) return TRUE;
	if( preg_match( '/Baiduspider/', $ua ) ) return TRUE;
	if( preg_match( '/Yandex/', $ua ) ) return TRUE;
	if( preg_match( '/msnbot/', $ua ) ) return TRUE;
	if( preg_match( '/ia_archiver/', $ua ) ) return TRUE;
	if( preg_match( '/Yeti/', $ua ) ) return TRUE;
	if( preg_match( '/spbot/', $ua ) ) return TRUE;
	if( preg_match( '/crawler/i', $ua ) ) return TRUE;
	if( preg_match( '/spider/i', $ua ) ) return TRUE;
	if( preg_match( '/bot/i', $ua ) ) return TRUE;
	return FALSE;
}

// Dashboard widget
add_action('wp_dashboard_setup', 'yd_searchfunc_register_dashboard_widget');

// Dashboard Widget Function
function yd_searchfunc_register_dashboard_widget() {
	wp_add_dashboard_widget( 
		'yd_searchfunc_dashboard', 
		__('Most searched', 'yd-searchfunc'), 
		'yd_searchfunc_dashboard');
}
 
// Print Dashboard Widget
function yd_searchfunc_dashboard( $sidebar_args = '' ) {
	global $wpdb;
	$table_name = $wpdb->prefix . "yd_searchlog";
	$options = get_option( 'widget_yd_searchfunc' );
	$i = 0;
	$top_count = $options[$i]["dash_top_count"];
	$search_base_url = $options[$i]["search_base_url"];
	$locale = $options[$i]["locale"] . '.' . $options[$i]["charset"];
	$timeformat	= $options[$i]["time_format"];
	setlocale( LC_TIME, $locale );
	
	if( isset( $_GET['ys_sf_del'] ) ) {
		$slug = preg_replace( '/\s/', '+', $_GET['ys_sf_del'] );
		$query = "DELETE FROM `" . $table_name . "` WHERE slug = '"
			. preg_replace( "/'/", "''", $slug )
			. "'";
		$wpdb->query( $query );
		echo '<div class="updated"><i>"' . $_GET['ys_sf_del'] . '" ' . __( 'deleted.', 'yd-searchfunc' ) . '</i>';
		if( $options[$i]["debug"] ) echo '<br/>' . $query;
		echo '</div>';
	}
	if( isset( $_GET['ys_sf_ban'] ) ) {
		$slug = preg_replace( '/\s/', '+', $_GET['ys_sf_ban'] );
		$query = "UPDATE `" . $table_name . "` SET count = -1 "
			. " WHERE slug = '"
			. preg_replace( "/'/", "''", $slug )
			. "'";
		$wpdb->query( $query );
		echo '<div class="updated"><i>"' . $_GET['ys_sf_ban'] . '" ' . __( 'banned.', 'yd-searchfunc' ) . '</i>';
		if( $options[$i]["debug"] ) echo '<br/>' . $query;
		echo '</div>';
	}
	
	echo '<div>';
	
	$query = "SELECT count( term ) as count FROM `" . $table_name . "` WHERE Date(last) = Date(Now())";
	$count_day = $wpdb->get_var( $query );
	$query = "SELECT count( term ) as count FROM `" . $table_name . "` WHERE Week(last) = Week(Now())";
	$count_week = $wpdb->get_var( $query );	
	$query = "SELECT count( term ) as count FROM `" . $table_name . "` WHERE Month(last) = Month(Now())";
	$count_month = $wpdb->get_var( $query );	
	$query = "SELECT count( term ) as count FROM `" . $table_name . "` WHERE Year(last) = Year(Now())";
	$count_year = $wpdb->get_var( $query );	
	$query = "SELECT count( term ) as count FROM `" . $table_name . "` WHERE 1";
	$count_total = $wpdb->get_var( $query );	
	$query = "SELECT first FROM `" . $table_name . "` ORDER BY first LIMIT 1";
	$first_date = $wpdb->get_var( $query );
			
 	$query = "SELECT term, slug, count, last FROM `" . $table_name . "` WHERE count > 0 ORDER BY count DESC, last DESC LIMIT $top_count";
	$res = $nb = $wpdb->get_results( $query, ARRAY_A );
	if ( $res ) {
		echo '<ol style="list-style:none inside;">';
		foreach ( $res as $row ) {
			echo '<li style="list-style:decimal;">';
			echo '<a href="' . $search_base_url
				. $row['slug'] . '" target="_out">' 
				. $row['term'] . ' (' . $row['count'] 
				. ')';
			echo '</a> ';
			echo strftime( $timeformat, strtotime( $row['last'] ) );
			echo ' - <a href="?ys_sf_del=' . $row['slug'] . '">' . __( 'Del', 'yd-searchfunc' ) . '</a>';
			echo ' - <a href="?ys_sf_ban=' . $row['slug'] . '">' . __( 'Ban', 'yd-searchfunc' ) . '</a>';
			echo '</li>';
		}
		echo '</ol>';
		echo '<ul>';
		echo '<li>' . $count_day . ' ' . __( 'different searches today.', 'yd-searchfunc' ) . '</li>';
		echo '<li>' . $count_week . ' ' . __( 'different searches this week.', 'yd-searchfunc' ) . '</li>';
		echo '<li>' . $count_month . ' ' . __( 'different searches this month.', 'yd-searchfunc' ) . '</li>';
		echo '<li>' . $count_year . ' ' . __( 'different searches this year.', 'yd-searchfunc' ) . '</li>';
		echo '<li>' . $count_total . ' ' . __( 'different searches since ', 'yd-searchfunc' ) .
			strftime( $timeformat, strtotime( $first_date ) ) .
			'</li>';
		echo '</ul>';
	} else {
		echo '<i>' . __( 'No search data.', 'yd-searchfunc' ) . '</i>';
	}
	echo '</div>';
}

function yd_most_searched(
		$top_count = FALSE, 
		$echo = TRUE, 
		$display_rownum = 'not_set',
		$display_count = 'not_set',
		$search_base_url = FALSE
	) {
	global $wpdb;
	$options = get_option( 'widget_yd_searchfunc' );
	$i = 0;
	if( $top_count === FALSE ) $top_count = $options[$i]["func_top_count"];
	if( $display_rownum == 'not_set' ) $display_rownum = $options[$i]["display_rownum"];
	if( $display_count == 'not_set' ) $display_count = $options[$i]["display_count"];
	if( $search_base_url === FALSE ) $search_base_url = $options[$i]["search_base_url"];
	$table_name = $wpdb->prefix . "yd_searchlog";
	$query = "SELECT term, slug, count FROM `" . $table_name . "` WHERE count > 0 ORDER BY count DESC LIMIT $top_count";
	$res = $nb = $wpdb->get_results( $query, ARRAY_A );
	$html = '';
	$html .= '<div class="yd_most_searched">';
	if ( $res ) {
		if( $display_rownum ) $html .= '<ol>';
			else $html .= '<ul>';
		foreach ( $res as $row ) {
			$html .= '<li>';
			$html .= '<a href="' . $search_base_url
				. $row['slug'] . '">' 
				. $row['term'];
			if( $display_count ) $html .= ' (' . $row['count'] . ')';
			$html .= '</li>';
		}
		if( $display_rownum ) $html .= '</ol>';
			else $html .= '</ul>';
	} else {
		$html .= '<i>' . __( 'No search data.', 'yd-searchfunc' ) . '</i>';
	}
	$html .= '</div>';
	if( $echo ) echo $html;
		else return $html;
}

function yd_most_searched_widget( $args, $cache_name = NULL ) {
	if( isset( $args ) && $args === FALSE ) {
		$echo = FALSE;
	} else {
		if( is_array( $args ) ) extract( $args );
		$echo = TRUE;
	}
	$options = get_option('widget_yd_searchfunc');
	$i = 1;
	$title = $options[$i]['widget_title'];
	$html = '';
	if( is_admin() ) return;
	if( !check_yd_widget_cache( 'yd_most_searched' ) ) {
		$html .= $before_widget;
		if( $title )
			$html .= $before_title . $title . $after_title;
		$html .= '<div class="yd_most_searched_widget">';
		
		$html .= yd_most_searched(
			$options[$i]['widget_top_count'], 
			FALSE,
			$options[$i]['widget_display_rownum'],
			$options[$i]['widget_display_count']
		);
		
		//$html .= '<a href="' . $bottom_link . '">' . $bottom_text . '</a>';
		//TODO: some more backlinkware ;-)
		$html .= '</div>' . $after_widget;
		update_yd_widget_cache( 'widget_yd_yd_most_searched_' . $cache_key, $html );
	} else {
		//echo "FROM CACHE<br/>";
		$html = get_yd_widget_cache( 'yd_most_searched' );
	}
	if( $echo ) {
		echo $html;
	} else {
		return $html;
	}
}

function yd_most_searched_widget_control( $number ) {
	$options = get_option( 'widget_yd_searchfunc' );
	$to_update = Array(
		'widget_title',
		'widget_top_count',
		'widget_display_rownum',
		'widget_display_count'
	);
	if ( $_POST["yd_searchfunc-submit-$number"] ) {
		if( yd_update_options( 'widget_yd_searchfunc', $number, $to_update, $_POST, 'yd_searchfunc-' ) ) {
			clear_yd_widget_cache( 'searchfunc' );
		}
	}
	foreach( $to_update as $key ) {
		$v[$key] = htmlspecialchars( $options[$number][$key], ENT_QUOTES );
	}
	?>
<div style="float: right"><a
	href="http://www.yann.com/en/wp-plugins/yd-search-functions"
	title="Help!" target="_blank">?</a></div>
<strong><?php echo __('Widget title:', 'yd-searchfunc') ?></strong>
<br />
<input
	style="width: 450px;" id="yd_searchfunc-widget_title-<?php echo "$number"; ?>"
	name="yd_searchfunc-widget_title-<?php echo "$number"; ?>" type="text"
	value="<?php echo $v['widget_title']; ?>" />
<br />
<strong><?php echo __('List:', 'yd-searchfunc') ?></strong>
<br />
<input type="text" size="3"
	name="yd_searchfunc-widget_top_count-<?php echo "$number"; ?>" 
	value="<?php echo $v['widget_top_count']; ?>" />
	<?php echo __('most searched expressions', 'yd-searchfunc') ?>
<br />
<strong><?php echo __('Display row number in listing:', 'yd-searchfunc') ?></strong>
<br />
<input type="checkbox" 
	name="yd_searchfunc-widget_display_rownum-<?php echo "$number"; ?>"
	value="1"
	<?php if( $v['widget_display_rownum'] == 1 ) echo ' checked="checked" '; ?>
	/>
<br />
<strong><?php echo __('Display count in listing:', 'yd-searchfunc') ?></strong>
<br />
<input type="checkbox" 
	name="yd_searchfunc-widget_display_count-<?php echo "$number"; ?>"
	value="1" ';
	<?php if( $v['widget_display_count'] == 1 ) echo ' checked="checked" '; ?>
	/>
<hr />
<input
	type="hidden" id="yd_searchfunc-submit-<?php echo "$number"; ?>"
	name="yd_searchfunc-submit-<?php echo "$number"; ?>" value="1" />
	<?php
}

function widget_searchfunc_init() {
	// Check for the required API functions
	if ( !function_exists('register_sidebar_widget') || !function_exists('register_widget_control') )
	return;
	register_sidebar_widget( __('YD Most Searched', 'yd-searchfunc'), 'yd_most_searched_widget' );
	register_widget_control( __('YD Most Searched', 'yd-searchfunc'), 'yd_most_searched_widget_control', 470, 470, 1 );
}

// Tell Dynamic Sidebar about our new widget and its control
add_action('plugins_loaded', 'widget_searchfunc_init');

// ============================ Generic YD WP functions ==============================

include( 'yd-wp-lib.inc.php' );

if( !function_exists( 'yd_update_options_nostrip_array' ) ) {
	function yd_update_options_nostrip_array( $option_key, $number, $to_update, $fields, $prefix ) {
		$options = $newoptions = get_option( $option_key );
		/*echo '<pre>';
		echo 'fields: ';
		var_dump( $fields );*/
		foreach( $to_update as $key ) {
			// reset the value
			if( is_array( $newoptions[$number][$key] ) ) {
				$newoptions[$number][$key] = array();
			} else {
				$newoptions[$number][$key] = '';
			}
			/*echo $key . ': ';
			var_dump( $fields[$prefix . $key . '-' . $number] );*/
			if( !is_array( $fields[$prefix . $key . '-' . $number] ) ) {
				$value = html_entity_decode( stripslashes( $fields[$prefix . $key . '-' . $number] ) );
				$newoptions[$number][$key] = $value;
			} else {
				//it's a multi-valued field, make an array...
				if( !is_array( $newoptions[$number][$key] ) )
					$newoptions[$number][$key] = array( $newoptions[$number][$key] );
				foreach( $fields[$prefix . $key . '-' . $number] as $v )
					$newoptions[$number][$key][] = html_entity_decode( stripslashes( $v ) );	
			}
			//echo $key . " = " . $prefix . $key . '-' . $number . " = " . $newoptions[$number][$key] . "<br/>";
		}
		//echo '</pre>';
		if ( $options != $newoptions ) {
			$options = $newoptions;
			update_option( $option_key, $options );
			return TRUE;
		} else {
			return FALSE;
		}
	}
}

if( !function_exists( 'check_yd_widget_cache' ) ) {
	function check_yd_widget_cache( $widg_id ) {
		$option_name = 'yd_cache_' . $widg_id;
		$cache = get_option( $option_name );
		//echo "rev: " . $cache["revision"] . " - " . get_yd_cache_revision() . "<br/>";
		if( $cache["revision"] != get_yd_cache_revision() ) {
			return FALSE;
		} else {
			return TRUE;
		}
	}
}

if( !function_exists( 'update_yd_widget_cache' ) ) {
	function update_yd_widget_cache( $widg_id, $html ) {
		//echo "uwc " . $widg_id;
		$option_name = 'yd_cache_' . $widg_id;
		$nvarr["html"] = $html;
		$nvarr["revision"] = get_yd_cache_revision();
		$newvalue = $nvarr;
		if ( get_option( $option_name ) ) {
			update_option( $option_name, $newvalue );
		} else {
			$deprecated=' ';
			$autoload='no';
			add_option($option_name, $newvalue, $deprecated, $autoload);
		}
	}
}

if( !function_exists( 'get_yd_widget_cache' ) ) {
	function get_yd_widget_cache( $widg_id ) {
		$option_name = 'yd_cache_' . $widg_id;
		$nvarr = get_option( $option_name );
		return $nvarr["html"];
	}
}

if( !function_exists( 'clear_yd_widget_cache' ) ) {
	function clear_yd_widget_cache( $widg_id ) {
		$caches = yd_get_all_widget_caches( 'yd_cache_' );
		foreach( $caches as $cache_name ) {
			$option_name = 'yd_cache_' . $widg_id;
			$nvarr["html"] = __('clear', 'yd-searchfunc');
			$nvarr["revision"] = 0;
			$newvalue = $nvarr;
			update_option( $option_name, $newvalue );
		}
	}
}

if( !function_exists( 'yd_get_all_widget_caches') ) {
	function yd_get_all_widget_caches( $widget_prefix ) {
		global $wpdb;
		$query = "SELECT option_name FROM $wpdb->options WHERE option_name LIKE '$widget_prefix%'";
		return $wpdb->get_col( $query );
	}
}

if( !function_exists( 'get_yd_cache_revision' ) ) {
	function get_yd_cache_revision() {
		global $wpdb;
		return $wpdb->get_var( "SELECT max( ID ) FROM " . $wpdb->posts .
			" WHERE post_type = 'post' and post_status = 'publish'" );
	}
}
?>