<?php
/*
Plugin Name: Word Stats
Plugin URI: http://bestseller.franontanaya.com/?p=101
Description: A suite of word counters, keyword counters and readability analysis for your blog.
Author: Fran Ontanaya
Version: 4.0.1
Author URI: http://www.franontanaya.com

Copyright (C) 2010 Fran Ontanaya
contacto@franontanaya.com
http://bestseller.franontanaya.com/?p=101

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.

Thanks to Allan Ellegaard for testing and input.
*/

/*
	Note: All bst_ functions come from basic_string_tools.php.
*/

/* # Constants.
-------------------------------------------------------------- */
define( 'WS_TOO_SHORT', 150 );
define( 'WS_TOO_LONG', 1500 );
define( 'WS_RI_BASIC', 7 );
define( 'WS_RI_ADVANCED', 15 );
define( 'WS_NO_KEYWORDS', 2 );
define( 'WS_SPAMMED_KEYWORDS', 20 );

define( 'WS_CURRENT_VERSION', '4.0' );

/* # Activate premium.
-------------------------------------------------------------- */
// No special checks. This is open source, you could hack around it easily (if your time is less valuable than €2).
if ( $_GET[ 'word-stats-action' ] == 'basic' ) { update_option( 'word_stats_premium', 0 ); } // Disables premium. Only for testing purposes.
if ( $_GET[ 'word-stats-action' ] == 'alternative') {	update_option( 'word_stats_premium', 1 ); }
if ( $_GET[ 'word-stats-action' ] == 'payment') {	update_option( 'word_stats_premium', 2 ); }
if ( $_GET[ 'word-stats-action' ] == 'donation' ) { update_option( 'word_stats_premium', 3 ); }

/* # Word Countswp-admin/
-------------------------------------------------------------- */
load_plugin_textdomain( 'word-stats', '/wp-content/plugins/word-stats/languages/', 'word-stats/languages/' );

/* # Basic string tools class
-------------------------------------------------------------- */
require_once( 'basic-string-tools.php' );

/* # Check version. Perform upgrades.
-------------------------------------------------------------- */
/* Pre 3.1 */
// Fix inconsistent naming for some options
if ( Word_Stats_Core::is_option( 'ws-premium' ) ) { Word_Stats_Core::move_option( 'ws-premium', 'word_stats_premium' ); }
if ( Word_Stats_Core::is_option( 'ws-total-counts-cache' ) ) { Word_Stats_Core::move_option( 'ws-total-counts-cache', 'word_stats_total_counts_cache' ); }
if ( Word_Stats_Core::is_option( 'ws-monthly-counts-cache' ) ) { Word_Stats_Core::move_option( 'ws-monthly-counts-cache', 'word_stats_monthly_counts_cache' ); }

// There's an ignore list, but not plugin version = Pre 3.1 install.
if ( !Word_Stats_Core::is_option( 'word_stats_version' ) && Word_Stats_Core::is_option( 'word_stats_ignore_keywords' ) ) {
	$keywords_to_upgrade = explode( "\n", str_replace( "\r", '', get_option( 'word_stats_ignore_keywords' ) ) );
	if ( count( $keywords_to_upgrade ) ) {
		for ( $i = 0; $i < count( $keywords_to_upgrade ); $i++ ) {
			// preg_replace to avoid duplicated start and end regex characters.
			$keywords_to_upgrade[ $i ] =  '^' . preg_replace( '/^[\^]+||[\$]+$/', '', $keywords_to_upgrade[ $i ] ) . '$';
		}
		$i = null;
		update_option( 'word_stats_ignore_keywords', implode( "\n", $keywords_to_upgrade ) );
	}
}
/* End pre 3.1.0 */

/* Reset keyword count relevance to defaults since the calculation method changed from 3.3 to 3.4 */
if ( version_compare( get_option( 'word_stats_version' ), '3.4' ) ) {
	update_option( 'word_stats_diagnostic_no_keywords', WS_NO_KEYWORDS );
	update_option( 'word_stats_diagnostic_spammed_keywords', WS_SPAMMED_KEYWORDS );
}

//  Deprecated option
if ( get_option( 'ws-counts-cache' ) ) { delete_option( 'ws-counts-cache' ); }

// Update version
if ( get_option( 'word_stats_version' ) != WS_CURRENT_VERSION ) {
	update_option( 'word_stats_version', WS_CURRENT_VERSION );
}

include( 'word-counts-widget.php' );

/* # Background caching
-------------------------------------------------------------- */
/*
	If there's caching to do, adds an AJAX call to the admin head so the caching starts doing its thing.
	Before adding the action hooks, we check if intial caching isn't complete yet and either:
		a) there's no caching worker active, or
		b) any caching worker was started (and possibly interrupted without finishing) more than 300 seconds ago.
*/
function word_stats_call_cache_worker() {
	?>
	<script type="text/javascript" >
		jQuery( document ).ready( function( $ ) {
			var worker_call = { action: 'cache_pending' };
			jQuery.post( ajaxurl, worker_call, function( response ) { return response; } ); // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
		} );
	</script>
	<?php
}
function word_stats_cache_worker() {
	update_option( 'word_stats_cache_start', time() );
	echo Word_Stats_Core::cache_pending();
	update_option( 'word_stats_cache_start', false );
	die(); // this is required to return a proper result
}
$is_worker_free =  !get_option( 'word_stats_done_caching', false ) && ( !get_option( 'word_stats_cache_start' ) OR ( time() - get_option( 'word_stats_cache_start' ) > 300 ) );
if ( $is_worker_free ) {
	add_action( 'admin_head', 'word_stats_call_cache_worker' );
	add_action('wp_ajax_cache_pending', 'word_stats_cache_worker' );
}

/* # Core functions
-------------------------------------------------------------- */
class Word_Stats_Core {

	/* # General purpose functions
	-------------------------------------------------------------- */
	/*
		is_option: Checks if the option doesn't exist (as opposed to just being empty).
		move_option: Indeed.
		get_year_month: Return YYYY-MM from the given date. Currently expects $date to be YYYY-MM-DD.
		is_content_type: Checks if a post is a content type or a functional type.
		total_word_counts: Output dashboard totals.
		safe_ksort: Perform ksort only if the variable is an array. Return false instead of throwing an error if it isn't.
	*/
	public function is_option( $option ) { return get_option( $option, null ) !== null; }
	public function move_option( $option1, $option2 ) { update_option( $option2, get_option( $option1 ) ); delete_option( $option1 ); }
	public function get_year_month( $date ) { return substr( $date, 0, 7 ); }
	public function is_content_type( $name ) { return ( $name != 'attachment' && $name != 'nav_menu_item' && $name != 'revision' ); }
	public function total_word_counts() { echo Word_Stats_Core::get_word_counts( 'table' ); }
	public function is_plugin_plugged( $plugin ) { return in_array( $plugin . '/' . $plugin . '.php', get_option( 'active_plugins' ) ); }
	public function safe_ksort( &$array ) {
		if ( is_array( $array ) && !empty( $array ) ) {
			ksort( $array );
			return true;
		} else {
			return false;
		}
	}

	/* # Model functions (WIP)
	-------------------------------------------------------------- */
	/*
		Select posts according to the count unpublished option
	*/
	public function get_posts( $post_type_name ) {
		global $wpdb;
		if ( get_option( 'word_stats_count_unpublished' ) ) {
			$query = "SELECT * FROM $wpdb->posts WHERE post_type = '" . mysql_real_escape_string( $post_type_name ) . "' ORDER BY ID DESC";
		} else {
			$query = "SELECT * FROM $wpdb->posts WHERE post_status = 'publish' AND post_type = '" . mysql_real_escape_string( $post_type_name ) . "' ORDER BY ID DESC";
		}
		$posts = $wpdb->get_results( $query, OBJECT );
		return $posts;
	}

	/* # Shortcodes
	-------------------------------------------------------------- */
	/*
		word_counts_sc: Output word counts
	*/
	public function word_counts_sc( $atts = null, $content = null ) { return '<ul class="word-stats-counts">' . Word_Stats_Core::get_word_counts( 'list' ) . '</ul>'; }

	/* # Live stats
	-------------------------------------------------------------- */
	/*
		Loads javascript for stat counting, outputs the counting code and the relevant html.
	*/
	public function live_stats() {
		global $post;
		bst_js_string_tools();
		include( 'view-live-stats.php' );
	}

	/* # Caching
	-------------------------------------------------------------- */
	/*
		When the plugin is freshly installed in a large blog, stats collecting
		could time out when the user tries to load stats for the entire timeline.
		To avoid this we load a worker with an AJAX script so it caches in the background.
	*/

	/*
		Retrieve the ids of the posts still uncached.
		Called too when the "please, wait" message is shown with a count of posts remaining.
	*/
	public function get_uncached_posts_ids() {
		global $wpdb;
		$query = "SELECT $wpdb->posts.ID
			FROM $wpdb->posts
			WHERE $wpdb->posts.ID NOT IN (
				SELECT DISTINCT post_id
				FROM  $wpdb->postmeta
				WHERE meta_key = 'word_stats_cached'
				AND meta_value = TRUE
			)
			AND $wpdb->posts.post_type != 'attachment'
			AND $wpdb->posts.post_type != 'nav_menu_item'
			AND $wpdb->posts.post_type != 'revision'
		";
		return $wpdb->get_results( $query, OBJECT );
	}

	/*
		Does the word count, keywords and readability calculations, and stores them as meta, for a chunk of posts.
		This function will run for as long as necessary, so it should be called via AJAX, then wait for the result;
		otherwise, the browser may timeout while waiting for it.
		This loop can be very slow in cheap production servers. We exit if the plugin has been removed from the active plugins list.
	*/
	public function cache_pending() {
		if ( get_option( 'word_stats_done_caching', false ) ) { return false; }
		ignore_user_abort( true );
		set_time_limit( 0 );  // Work for as long as necessary.
		$posts = Word_Stats_Core::get_uncached_posts_ids();
		if ( count( $posts ) ) {
			$posts_checked = 0;
			foreach ( $posts as $post ) {
				if ( !Word_Stats_Core::is_plugin_plugged( 'word-stats' ) ) { exit(); }
				$posts_checked++;
				if ( !get_post_meta( $post->ID, 'word_stats_cached', true ) ) { Word_Stats_Core::cache_stats( $post->ID ); }
			}
		}
		update_option( 'word_stats_done_caching', true );
		return $posts_checked;
	}

	/*
		This function is called when a post save doesn't find a monthly totals cache to update,
		which in normal conditions means the plugin is a fresh install.
	*/
	public function cache_totals() {
		set_time_limit( 0 );  // Work for as long as necessary.
		global $wp_post_types;
		$author_count = $totals = array();
		foreach( $wp_post_types as $post_type ) {
			if ( Word_Stats_Core::is_content_type( $post_type->name ) ) {
				$posts = Word_Stats_Core::get_posts( $post_type->name );
				foreach( $posts as $post ) {
					$word_count = get_post_meta( $post->ID, 'word_stats_word_count', true );
					if ( $word_count == '' ) {
						$word_count = bst_count_words( $post->post_content );
						update_post_meta( $post->ID,  'word_stats_word_count', $word_count );
					}
					$author_count[ $post->post_author ][ $post_type->name ][ Word_Stats_Core::get_year_month( $post->post_date ) ] += $word_count;
					$totals[ $post_type->name ] += $word_count;
				}
			}
		}
		update_option( 'word_stats_total_words_cache', $totals );
		update_option( 'word_stats_monthly_counts_cache', $author_count );
	}

	/*
		Calculates the various stats for the current or specified post, saves them in post metas,
		then updates the cached blog-wide total word counts.
		ARI = Automated Readability Index. CLI = Coleman-Liau Index.
	*/
	public function cache_stats( $id = null ) {
		if ( !$id ) {
			global $post;
			if ( !$post->ID ) { return null; }
			$id = $post->ID;
		}
		$the_post = get_post( $id );
		$all_text = bst_html_stripper( $the_post->post_content, get_bloginfo( 'charset' ) );

		if ( $all_text ) {
			$stats = bst_split_text( $all_text );
			$total_alphanumeric = mb_strlen( $stats[ 'alphanumeric' ] ); // mb_strlen = multibyte strlen
			$total_sentences = count( $stats[ 'sentences' ] );
			$total_words = count( $stats[ 'words' ] );
			$word_array = $stats[ 'words' ];
			$all_text = $stats[ 'text' ];

			if ( $total_words > 0 && $total_sentences > 0 ) { // No divisions by zero, thanks.
				$chars_per_word = intval( $total_alphanumeric / $total_words );
				$chars_per_sentence = intval( $total_alphanumeric / $total_sentences );
				$words_per_sentence = intval( $total_words / $total_sentences );

				$ARI = round( 4.71 * ( $total_alphanumeric / $total_words ) + 0.5 * ( $total_words / $total_sentences ) - 21.43, 1);
				$CLI = round( 5.88 * ( $total_alphanumeric / $total_words ) - 29.6 * ( $total_sentences / $total_words ) - 15.8, 1);

				$LIXlongwords = 0;
				for ($i = 0; $i < count( $word_array ); $i = $i + 1 ) {
					if ( mb_strlen( $word_array[ $i ] ) > 6 ) { $LIXlongwords++; }
				}
				$temp = preg_split( '/[,;\.\(\:]/', $all_text );
				$LIX = round( $total_words / count( $temp ) + ( $LIXlongwords * 100 ) / $total_words, 1) ;
			} else {
				$ARI = $CLI = $LIX = '0';
			}
		} else {
				$ARI = $CLI = $LIX = '0';
		}

		// Remove ignored keywords
		$ignore = Word_Stats_Core::get_ignored_keywords();
		$keywords = bst_regfilter_keyword_counts( bst_keywords( $the_post->post_content, 3, get_bloginfo( 'charset' ) ), $ignore );

		// Update the total counts.
		$post_type = get_post_type( $id );
		$old_total_words = get_post_meta( $id, 'word_stats_word_count', true );
		Word_Stats_Core::get_cached_totals( $author_count, $totals );
		if ( $author_count === null ) {
			Word_Stats_Core::cache_totals();
			$author_count  = get_option( 'word_stats_monthly_counts_cache', null );
		}
		if ( $old_total_words ) {
			$author_count[ $the_post->post_author ][ $post_type ][ Word_Stats_Core::get_year_month( $the_post->post_date ) ] -= $old_total_words;
			$totals[ $post_type ] -= $old_total_words;
		}
		$author_count[ $the_post->post_author ][ $post_type ][ Word_Stats_Core::get_year_month( $the_post->post_date ) ] += $total_words;
		$totals[ $post_type ] -= $total_words;

		// Cache the stats
		update_post_meta( $id, 'readability_ARI', $ARI );
		update_post_meta( $id, 'readability_CLI', $CLI );
		update_post_meta( $id, 'readability_LIX', $LIX );
		update_post_meta( $id, 'word_stats_word_count', $total_words );
		update_post_meta( $id, 'word_stats_keywords', serialize( $keywords ) );
		update_post_meta( $id, 'word_stats_cached', true );
		update_option( 'word_stats_monthly_counts_cache', $author_count );
		update_option( 'word_stats_total_words_cache', $total_words );
	}

	/*
		Load cached totals, recache if empty.
	*/
	public function get_cached_totals( &$author_count, &$totals ) {
		$author_count  = get_option( 'word_stats_monthly_counts_cache', null );
		$totals  = get_option( 'word_stats_total_words_cache', null );
		if ( $author_count === null || empty( $author_count ) || !is_array( $author_count ) || empty( $totals ) || !is_array( $totals ) || $totals === null ) {
			Word_Stats_Core::cache_totals();
			$author_count  = get_option( 'word_stats_monthly_counts_cache', null );
			$totals  = get_option( 'word_stats_total_words_cache', null );
		}
	}

	/*
		Output the cached word counts with the proper HTML tags.
	*/
	public function get_word_counts( $mode ) {
		$html = '';
		Word_Stats_Core::get_cached_totals( $author_count, $totals );
		$total_all = 0;
		foreach ( $totals as $type => $words ) {
			$text = __( 'Words', 'word-stats' ) . ' (' . $type . ')';
			$total_all += (int)$words;
			if ( $mode == 'table' ) {
				$html .= '<tr><td class="first b"><a>' . number_format_i18n( $words ) . '</a></td><td class="t"><a>' . $text . '</a></td></tr>';
			} else {
				$html .= '<li class="word-stats-count">' . number_format_i18n( $words ) . ' ' . $text . '</li>';
			}
		}

		// Absolute total words
		$text =  __( 'Total words', 'word-stats' );
		$total_all =  number_format_i18n( $total_all );
		if ( $mode == 'table' ) {
			$html .= '<tr><td class="first b word-stats-dash-total"><a>' . $total_all . '</a></td><td class="t"><a>' . $text . '</a></td></tr>';
		} else {
			$html .= '<li class="word-stats-count word-stats-list-total">' . $total_all . ' ' . $text . '</li>';
		}

		return $html;
	}

	/*Add a column to the post management list
	-------------------------------------------------------------- */
	public function add_posts_list_column( $defaults ) {
		 $defaults[ 'readability' ] = __( 'R.I.', 'word-stats' );
		 return $defaults;
	}

	public function calc_ws_index( $ARI, $CLI, $LIX ) {
		// Translate as Basic / Intermediate / Advanced
		return ( floatval( $ARI ) + floatval( $CLI ) + ( ( floatval( $LIX ) - 10 ) / 2 ) ) / 3;
	}

	public function create_posts_list_column( $name ) {
		global $post;
		if ( $name == 'readability' ) {
			$ARI = get_post_meta( $post->ID, 'readability_ARI', true );
			$CLI = get_post_meta( $post->ID, 'readability_CLI', true );
			$LIX = get_post_meta( $post->ID, 'readability_LIX', true );

			if ( !$ARI ) {
				// If there is no data or the post is blank
				echo '<span style="color:#999;">--</span>';
			} else {
				// Trying to aggregate the indexes in a meaningful way.
				$r_avg = Word_Stats_Core::calc_ws_index( $ARI, $CLI, $LIX );
				if ( $r_avg < WS_RI_BASIC ) { echo '<span style="color: #06a;">', round( $r_avg, 1 ), '</span>'; }
				if ( $r_avg >= WS_RI_BASIC && $r_avg < WS_RI_ADVANCED ) { echo '<span style="color: #0a6;">', round( $r_avg, 1 ), '</span>'; }
				if ( $r_avg >= WS_RI_ADVANCED ) { echo '<span style="color: #c36;">', round( $r_avg, 1 ), '</span>'; }
			}
		}
	}

	// Load style for the column
	public function style_column() {
		wp_register_style( 'word-stats-css', plugins_url() . '/word-stats/css/word-stats.css' );
		wp_enqueue_style( 'word-stats-css' );
	}

	// Assign default or custom threshold values
	public function assign_thresholds( &$too_short = null, &$too_long = null, &$too_difficult = null, &$too_simple = null, &$no_keywords = null, &$spammed_keywords = null) {
		$too_short = ( !Word_Stats_Core::is_option( 'word_stats_diagnostic_too_short' ) ) ? WS_TOO_SHORT : get_option( 'word_stats_diagnostic_too_short' );
		$too_long = ( !Word_Stats_Core::is_option( 'word_stats_diagnostic_too_long' ) ) ? WS_TOO_LONG : get_option( 'word_stats_diagnostic_too_long' );
		$too_difficult = ( !Word_Stats_Core::is_option( 'word_stats_diagnostic_too_difficult' ) ) ? WS_RI_ADVANCED : get_option( 'word_stats_diagnostic_too_difficult' );
		$too_simple = ( !Word_Stats_Core::is_option( 'word_stats_diagnostic_too_simple' ) ) ? WS_RI_BASIC : get_option( 'word_stats_diagnostic_too_simple' );
		$no_keywords = ( !Word_Stats_Core::is_option( 'word_stats_diagnostic_no_kws' ) ) ? WS_NO_KEYWORDS : get_option( 'word_stats_diagnostic_no_kws' );
		$spammed_keywords = ( !Word_Stats_Core::is_option( 'word_stats_diagnostic_spammed_kws' ) ) ? WS_SPAMMED_KEYWORDS : get_option( 'word_stats_diagnostic_spammed_kws' );
	}

	// Return an array with the user's list of ignored keywords merged with the selected (if any) or default list of common words.
	// Using get_bloginfo( 'language' ) for the default list. Makes things smoother for new installs.
	public function get_ignored_keywords() {
		$ignore_lang = get_option( 'word_stats_ignore_common', null );
		if ( $ignore_lang === null ) {
				$common = bst_get_common_words( substr( get_bloginfo( 'language' ), 0, 2 ) );
		} elseif ( $ignore_lang ) {
			if ( in_array( $ignore_lang, array( 'en', 'es' ) ) ) {
				$common = bst_get_common_words( $ignore_lang );
			} else {
				$common = array();
			}
		} else {
			return array();
		}

		$ignore = get_option( 'word_stats_ignore_keywords' );
		if ( !empty( $ignore ) ) {
			$ignore = explode( "\n", preg_replace('/\r\n|\r/', "\n", get_option( 'word_stats_ignore_keywords' ) ) );
			if ( !empty( $common ) ) {
				$ignore = array_unique( array_merge( $ignore, $common ) );
			}
		} else {
			$ignore = $common;
		}
		return $ignore;
	}

} // end class Word_Stats_Core

/* # Hooks
-------------------------------------------------------------- */
/*
	Hook live stats. Load only when editing a post.
*/
if ( $_GET[ 'action' ] == 'edit' || !strpos( $_SERVER[ 'SCRIPT_FILENAME' ], 'post-new.php' ) === false ) {
	add_action( 'admin_footer', array( 'Word_Stats_Core' , 'live_stats' ) );
}

/*
	Hook stats caching
*/
add_action( 'save_post', array( 'Word_Stats_Core', 'cache_stats' ) );

/*
	Hook the functions for the total word counts output.
	Note that the shorcodes can't be disabled, they'll continue using the stored data.
*/
if ( get_option( 'word_stats_totals' ) || get_option( 'word_stats_totals' ) == '' ) {
	add_action( 'right_now_content_table_end', array( 'Word_Stats_Core', 'total_word_counts' ) );
	add_action( 'widgets_init', create_function( '', 'return register_widget( "widget_ws_word_counts" );' ) );
}
add_shortcode( 'wordcounts', array( 'Word_Stats_Core', 'word_counts_sc' ) );

/*
	Hook readability custom column
*/
if ( get_option( 'word_stats_RI_Column' ) || get_option( 'word_stats_RI_Column' ) == '' ) {
	add_filter( 'manage_posts_columns', array( 'Word_Stats_Core', 'add_posts_list_column' ) );
	add_action( 'manage_posts_custom_column', array( 'Word_Stats_Core', 'create_posts_list_column' ) );
	add_action( 'admin_init', array( 'Word_Stats_Core', 'style_column' ) );
}


/* # Admin functions
-------------------------------------------------------------- */
class Word_Stats_Admin {

	/*
		Register settings
	*/
	function register_settings( $settings ) {
		foreach( $settings as $setting ) { 	register_setting( 'word-stats-settings-group', $setting ); }
	}
	function init_settings() {
		Word_Stats_Admin::register_settings( array( 'word_stats_RI_column', 'word_stats_totals', 'word_stats_replace_word_count', 'word_stats_averages', 'word_stats_show_keywords', 'word_stats_ignore_keywords', 'word_stats_add_tags', 'word_stats_count_unpublished', 'word_stats_diagnostic_thresholds', 'word_stats_diagnostic_too_short', 'word_stats_diagnostic_too_long', 'word_stats_diagnostic_too_difficult', 'word_stats_diagnostic_too_simple', 'word_stats_diagnostic_no_kws', 'word_stats_diagnostic_spammed_kws', 'word_stats_ignore_common' ) );
	}

	/*
		Settings page
	*/
	public function settings_page() {
		// Default values
		$opt_RI_column = ( !Word_Stats_Core::is_option( 'word_stats_RI_column' ) ) ? 1 : get_option( 'word_stats_RI_column' );
		$opt_totals = ( !Word_Stats_Core::is_option( 'word_stats_totals' ) ) ?  1 : get_option( 'word_stats_totals' );
		$opt_replace_wc = ( !Word_Stats_Core::is_option( 'word_stats_replace_word_count' ) ) ? 1 : get_option( 'word_stats_replace_word_count') ;
		$opt_averages = ( !Word_Stats_Core::is_option( 'word_stats_averages' ) ) ? 1 : get_option( 'word_stats_averages' );
		$opt_show_keywords = ( !Word_Stats_Core::is_option( 'word_stats_show_keywords' ) ) ? 1 : get_option( 'word_stats_show_keywords' );
		$opt_add_tags = ( !Word_Stats_Core::is_option( 'word_stats_add_tags' ) ) ? 1 : get_option( 'word_stats_add_tags' );
		$opt_count_unpublished = ( !Word_Stats_Core::is_option( 'word_stats_count_unpublished' ) ) ? 0 : get_option( 'word_stats_count_unpublished' );
		$opt_ignore_keywords = get_option( 'word_stats_ignore_keywords' );
		Word_Stats_Core::assign_thresholds( 	$opt_diagnostic_thresholds[ 'too_short' ], $opt_diagnostic_thresholds[ 'too_long' ], $opt_diagnostic_thresholds[ 'too_difficult' ], $opt_diagnostic_thresholds[ 'too_simple' ], $opt_diagnostic_thresholds[ 'no_keywords' ], $opt_diagnostic_thresholds[ 'spammed_keywords' ] );
		$opt_ignore_common = ( !Word_Stats_Core::is_option( 'word_stats_ignore_common' ) ) ? substr( get_bloginfo( 'language' ), 0, 2 ) : get_option( 'word_stats_ignore_common' );

		// Output the page.
		include( 'view-settings.php' );
	}

	/*
		Analyze the posts database and output the data set for the stats page
	*/
	public function load_report_stats( $author_graph, $period_start, $period_end ) {
		global $user_ID, $current_user, $wp_post_types, $wpdb;

		// $report contains all the data needed to render the stats page
		$report[ 'total_keywords' ] = $report[ 'recent_posts_rows' ] = array();
		$report[ 'totals_readability' ][ 0 ] = $report[ 'totals_readability' ][ 1 ] = $report[ 'totals_readability' ][ 2 ] = 0;
		$cur_author = get_userdata( $author_graph );

		// Validate dates
		$period_start = date( 'Y-m-d', strtotime( $period_start ) );
		$period_end = date( 'Y-m-d', strtotime( $period_end ) + 86400 ); // Last day included

		// Load the list of ignored keywords
		$ignore = Word_Stats_Core::get_ignored_keywords();

		// Load diagnostics thresholds
		Word_Stats_Core::assign_thresholds( 	$threshold_too_short, $threshold_too_long, $threshold_too_difficult, $threshold_too_simple, $threshold_no_keywords, $threshold_spammed_keywords );

		$report[ 'type_count' ][ 'custom' ] = 0;

		// Initialize row counters for the diagnostics tables arrays
		$dg_difficult_row = $dg_simple_row = $dg_short_row = $dg_long_row = $dg_no_keywords_row = 0;

		$cached = 0; $not_cached = 0;
		foreach( $wp_post_types as $post_type ) {

			$report[ 'type_count' ][ $post_type->name ] = 0;

			// Load only content and custom post types
			if ( $post_type->name != 'attachment' && $post_type->name != 'nav_menu_item' && $post_type->name != 'revision' ) {

				// Load the posts
				$query = "SELECT * FROM $wpdb->posts WHERE ";
				if ( !get_option( 'word_stats_count_unpublished' ) ) {
					$query .= "post_status = 'publish' AND ";
				}
				$query .= " post_type = '" . mysql_real_escape_string( $post_type->name ) . "' AND post_date BETWEEN '" . mysql_real_escape_string( $period_start ) . "' AND '" . mysql_real_escape_string( $period_end ) . "' ORDER BY post_date DESC";
				$posts = $wpdb->get_results( $query, OBJECT );

				foreach( $posts as $post ) {

					// Are stats cached? Note that get_post_meta( , , true ) is counterintuitive: the value is an array, but we ask for a single value;
					// otherwise, get_post_meta wraps the array into an array.
					if ( get_post_meta( $post->ID, 'word_stats_cached', true ) ) {
						$post_word_count = (int) get_post_meta( $post->ID, 'word_stats_word_count', true );
						$keywords = unserialize( get_post_meta( $post->ID,  'word_stats_keywords', true ) );
						$cached++;
					} else {
						Word_Stats_Core::cache_stats( $post->ID );
						$post_word_count = (int) get_post_meta( $post->ID, 'word_stats_word_count', true );
						$keywords = unserialize( get_post_meta( $post->ID,  'word_stats_keywords', true ) );
						$not_cached++;
					}

					// Count words per author. Group per month.
					$post_month = mysql2date( 'Y-m', $post->post_date );
					$report[ 'author_count' ][ $post->post_author ][ $post_type->name ][ $post_month ] += $post_word_count;
					$report[ 'author_count_total' ][ $post->post_author ] += $post_word_count;
					$report[ 'all_total_words' ] += $post_word_count;

					// Divisor to calculate keyword density per 1000 words
					$densityDivisor = ( intval( $post_word_count / 1000 ) ) ? intval( $post_word_count / 1000 ) : 1;

					// Reset keyword diagnostics count
					$dg_relevant_keywords = $dg_spammed_keywords = array();

					// Remove ignored words.
					$keywords = bst_regfilter_keyword_counts( $keywords, $ignore );

					// Aggregate the keywords, then create two lists for keywords flagged as relevant and spammed, according to their density.
					// Posts that are already flagged as too short aren't diagnosed as having no relevant keywords.
					if ( $keywords && is_array( $keywords ) ) {
						foreach( $keywords as $key=>$value ) {
							if( $report[ 'total_keywords' ][ $key ] === null ) { $report[ 'total_keywords' ][ $key ] = 0; }
							$report[ 'total_keywords' ][ $key ] += (int) $value;
							if ( $post_word_count >= (int) $threshold_too_short && !array_key_exists( $key, $dg_relevant_keywords ) && $value  / $densityDivisor > intval( $threshold_no_keywords ) ) { $dg_relevant_keywords[ $key ] = true; }
							if ( !array_key_exists( $key, $dg_spammed_keywords ) && $value  / $densityDivisor > intval( $threshold_spammed_keywords ) ) { $dg_spammed_keywords[ $key ] = $value; }
						}
					}

					// Stats for posts by the selected author only
					if ( $post->post_author == $author_graph ) {

						// Counts per type. Custom post types are aggregated.
						if ( $post_type->name != 'post' && $post_type->name != 'page' ) {
							$report[ 'type_count' ][ 'custom' ]++;
						} else {
							$report[ 'type_count' ][ $post_type->name ]++;
						}

						// Get the readability index.
						$ARI = get_post_meta( $post->ID, 'readability_ARI', true ); $CLI = get_post_meta( $post->ID, 'readability_CLI', true ); $LIX = get_post_meta( $post->ID, 'readability_LIX', true );
						// In previous versions we called Word_Stats_Core::cache_stats( $post->ID ) here if the metas weren't set.

						if ( $ARI  && $CLI && $LIX ) {
							$ws_index_n = Word_Stats_Core::calc_ws_index( $ARI, $CLI, $LIX );
							// Aggregate levels in 3 tiers like Google does (Basic, Intermediate, Advanced)
							if ( $ws_index_n < WS_RI_BASIC ) { $ws_index = 0; } elseif ( $ws_index_n < WS_RI_ADVANCED ) { $ws_index = 1; } else { $ws_index = 2; }
							$report[ 'totals_readability' ][ $ws_index ]++;
						}

						// Diagnostics.
						// Empty title fix.
						$post_title = ( $post->post_title == '' ) ? '#' . $post->ID . ' ' . __( '(no title)', 'word-stats' ) : htmlentities( $post->post_title, null, 'utf-8' );
						$post_link = ( current_user_can( 'edit_post', $post->ID ) ) ? '<a href=\'' . get_edit_post_link( $post->ID ) . '\'>' .  $post_title . '</a>' : $post_title;

						// Difficult text table
						if ( $ws_index_n > intval( $threshold_too_difficult ) && $post_word_count >= intval( $threshold_too_short ) ) {
							$report[ 'diagnostic' ][ 'too_difficult' ][ $dg_difficult_row ] = '<td class=\'ws-table-title\'> ' . $post_link . '</td><td class=\'ws-table-type\'>' . $post_type->name . '<td class=\'ws-table-date\'>' . mysql2date('Y-m-d', $post->post_date ) . '</td><td class=\'ws-table-word-count\'>' . number_format( $post_word_count ) . '</td><td class=\'ws-table-readability\'>' . round( $ws_index_n ) . '</td>';
							$dg_difficult_row++;
						}

						// Simple text table
						if ( $ws_index_n < intval( $threshold_too_simple ) && $post_word_count >= intval( $threshold_too_short ) ) {
							$report[ 'diagnostic' ][ 'too_simple' ][ $dg_simple_row ] = '<td class=\'ws-table-title\'> ' . $post_link . '</td><td class=\'ws-table-type\'>' . $post_type->name . '<td class=\'ws-table-date\'>' . mysql2date('Y-m-d', $post->post_date ) . '</td><td class=\'ws-table-word-count\'>' . number_format( $post_word_count ) . '</td><td class=\'ws-table-readability\'>' . round( $ws_index_n ) . '</td>';
							$dg_simple_row++;
						}

						// Short text table
						if ( $post_word_count < intval( $threshold_too_short ) ) {
							$report[ 'diagnostic' ][ 'too_short' ][ $dg_short_row ] = '<td class=\'ws-table-title\'> ' . $post_link . '</td><td class=\'ws-table-type\'>' . $post_type->name . '<td class=\'ws-table-date\'>' . mysql2date('Y-m-d', $post->post_date ) . '</td><td class=\'ws-table-word-count\'>' . number_format( $post_word_count ) . '</td><td class=\'ws-table-readability\'>' . round( $ws_index_n ) . '</td>';
							$dg_short_row++;
						}

						// Long text table
						if ( $post_word_count > intval( $threshold_too_long ) ) {
							$report[ 'diagnostic' ][ 'too_long' ][ $dg_long_row ] = '<td class=\'ws-table-title\'> ' . $post_link . '</td><td class=\'ws-table-type\'>' . $post_type->name . '<td class=\'ws-table-date\'>' . mysql2date('Y-m-d', $post->post_date ) . '</td><td class=\'ws-table-word-count\'>' . number_format( $post_word_count ) . '</td><td class=\'ws-table-readability\'>' . round( $ws_index_n ) . '</td>';
							$dg_long_row++;
						}

						// No keywords table
						if ( empty( $dg_relevant_keywords )  && $post_word_count >= intval( $threshold_too_short ) ) {
							$report[ 'diagnostic' ][ 'no_keywords' ][ $dg_no_keywords_row ] = '<td class=\'ws-table-title\'> ' . $post_link . '</td><td class=\'ws-table-type\'>' . $post_type->name . '<td class=\'ws-table-date\'>' . mysql2date('Y-m-d', $post->post_date ) . '</td><td class=\'ws-table-word-count\'>' . number_format( $post_word_count ) . '</td><td class=\'ws-table-readability\'>' . round( $ws_index_n ) . '</td>';
							$dg_no_keywords_row++;
						}

						// Keyword abuse table
						if ( count( $dg_spammed_keywords ) > 0 ) {
							$report[ 'diagnostic' ][ 'spammed_keywords' ][ $dg_spammed_row ] = '<td class=\'ws-table-title\'> ' . $post_link . '</td><td class=\'ws-table-type\'>' . $post_type->name . '<td class=\'ws-table-date\'>' . mysql2date('Y-m-d', $post->post_date ) . '</td><td class=\'ws-keywords\'>' . implode( ', ', array_keys( $dg_spammed_keywords ) ) . '</td>';
							$dg_spammed_row++;
						}
					}
				}
			}
		}

		// Sort keywords by frequency, descending
		asort( $report[ 'total_keywords' ] );
		$report[ 'total_keywords' ] = array_reverse( $report[ 'total_keywords' ], true );

		// Sort timeline
		Word_Stats_Core::safe_ksort( $report[ 'author_count' ][ $author_graph ][ 'post' ] );
		Word_Stats_Core::safe_ksort( $report[ 'author_count' ][ $author_graph ][ 'page' ] );
		Word_Stats_Core::safe_ksort( $report[ 'author_count' ][ $author_graph ][ 'custom' ] );

		return $report;
	}

	public function ws_diagnostics_table( $title, $fields, $id, $rows ) {
		include( 'view-diagnostics-table.php' );
	}

	/*
		Display the stats page if the caching is complete.
	*/
	public function ws_reports_page() {

		// Relevant when going straight to the graphs page right after installing the plugin.
		if ( !get_option( 'word_stats_done_caching', false ) ) { return false; }

		global $user_ID, $current_user, $wp_post_types, $wpdb;

		if( $_GET[ 'view-all' ] ) { $period_start = '1900-01-01'; } else { $period_start = $_GET[ 'period-start' ] ? $_GET[ 'period-start' ] : date( 'Y-m-d', time() - 15552000 ); }
		if( $_GET[ 'view-all' ] ) { $period_end = date( 'Y-m-d' ); } else { $period_end = $_GET[ 'period-end' ] ? $_GET[ 'period-end' ] : date( 'Y-m-d' ); }

		$author_graph = ( $_GET[ 'author-tab' ] ) ? intval( $_GET[ 'author-tab' ] ) : $user_ID;
		$report = Word_Stats_Admin::load_report_stats( $author_graph, $period_start, $period_end );

		if ( $report ) {
			include( 'view-report-graphs.php' );  // To Do: Finish separating View from Model/Controller
		} else {
			_e( 'Sorry, word counting failed for an unknown reason.', 'word-stats' );
		}
	}
}

function word_stats_report_init() {
		wp_register_style( 'ws-reports-page', plugins_url() . '/word-stats/css/reports-page.css' );
		wp_register_style( 'ws-jquery-ui', plugins_url() . '/word-stats/js/ui/jquery-ui-1.7.3.custom.css' );
}

function word_stats_report_styles() {
		wp_enqueue_style( 'ws-reports-page' );
		wp_enqueue_style( 'ws-jquery-ui' );
}

function word_stats_create_menu() {
	add_action( 'admin_init', array( 'Word_Stats_Admin', 'init_settings' ) );
	add_options_page( 'Word Stats Plugin Settings', 'Word Stats', 'manage_options', 'word-stats-options', array( 'Word_Stats_Admin', 'settings_page' ) );
	if ( get_option( 'word_stats_totals' ) || !Word_Stats_Core::is_option( 'word_stats_totals' ) ) {
		$page = add_submenu_page( 'index.php', 'Word Stats Plugin Stats', 'Word Stats', 'edit_posts', 'word-stats-graphs', array( 'Word_Stats_Admin', 'ws_reports_page' ) );
		// Load styles for the reports page
		add_action( 'admin_print_styles-' . $page, 'word_stats_report_styles' );
	}
}
add_action( 'admin_init', 'word_stats_report_init' );
add_action( 'admin_menu', 'word_stats_create_menu' );


/* # Notices
-------------------------------------------------------------- */
function word_stats_notice( $mode = 'updated', $message ) { echo '<div class="', $mode, '"><p>', $message, '</p></div>'; }
function word_stats_notice_cacheing() {
	$posts_uncached = count( Word_Stats_Core::get_uncached_posts_ids() );
	word_stats_notice( 'updated', sprintf( __( 'Word stats collection is underway (%s posts left). Graphs will be available at the end of the process.', 'word-stats' ), $posts_uncached ) );
}
function word_stats_notice_payment() {
	word_stats_notice( 'updated fade', __( 'Thanks for your contribution!' , 'word-stats' ) . ' ' . __( 'Word Stats Plugin is now upgraded to Premium!', 'word-stats' ) );
}
function word_stats_notice_alternative() {
	word_stats_notice( 'updated fade', __( 'Word Stats Plugin is now upgraded to Premium!', 'word-stats' ) . ' ' . __( 'Please, use the donation button to express your support.' , 'word-stats' ) );
}
function word_stats_notice_donation() {
	word_stats_notice( 'updated fade', __( 'Thanks for your contribution!' , 'word-stats' ) . ' ' . __( 'With your support we can bring you even more premium features!', 'word-stats' ) );
}
if( $_GET[ 'word-stats-action' ] == 'payment' ) { add_action( 'admin_notices', 'word_stats_notice_payment' );	}
if( $_GET[ 'word-stats-action' ] == 'alternative' ) { add_action( 'admin_notices', 'word_stats_notice_alternative' );	}
if( $_GET[ 'word-stats-action' ] == 'donation' ) { add_action( 'admin_notices', 'word_stats_notice_donation' ); }
if ( !get_option( 'word_stats_done_caching', false ) ) { add_action( 'admin_notices', 'word_stats_notice_cacheing' ); }

/* EOF */
