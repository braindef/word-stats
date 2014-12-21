<?php
/*
Plugin Name: Word Stats
Plugin URI: http://wordpress.org/extend/plugins/word-stats/stats/
Description: A suite of word counters, keyword counters and readability analysis for your blog.
Author: Fran Ontanaya
Version: 4.5.1
Author URI: http://www.franontanaya.com

Copyright (C) 2014 Fran Ontanaya
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

Thanks to Allan Ellegaard, Eric Johnson, Feuerwächter and Chedr for testing and feedback.
*/

/* # Setup
-------------------------------------------------------------- */
# Used to perform upgrades
define( 'WS_CURRENT_VERSION', '4.5.1' );

define( 'WS_DIR_PATH', plugin_dir_path( __FILE__ ) );
define( 'WS_FOLDER', basename( WS_DIR_PATH ) );

# Load translation strings
load_plugin_textdomain( 'word-stats', false, WS_FOLDER . '/languages/' );

# Generic functions to do string stuff
require_once 'basic-string-tools.php';

# This variable is meant to be used globally where needed.
# Among other things, we usually won't want options changing during execution.
$word_stats_options = Word_Stats_Core::load_options();

/*
	Check version. Perform upgrades.
*/
# Force recache to update stats.
if ( version_compare( $word_stats_options[ 'version' ], '4.5.1' ) < 0 ) {
	Word_Stats_Core::recount_all();
}

# Update version
if ( $word_stats_options[ 'version' ] != WS_CURRENT_VERSION ) {
	$word_stats_options[ 'version' ] = WS_CURRENT_VERSION;
	update_option( 'word_stats_options', $word_stats_options );
}


/* # Hooks
-------------------------------------------------------------- */
# Hook live stats. Load only when editing a post.
if ( isset( $_GET[ 'action' ] ) ) {
	if ( $_GET[ 'action' ] == 'edit' || !strpos( $_SERVER[ 'SCRIPT_FILENAME' ], 'post-new.php' ) === false ) {
		add_action( 'admin_footer', array( 'Word_Stats_Core' , 'live_stats' ) );
	}
}

#	Hook stats caching when a post is saved
add_action( 'save_post', array( 'Word_Stats_Core', 'cache_stats' ) );

# Hook the functions for the display of total word counts on the dashboard.
# User may want to switch it off for performance.
# ToDo: Add options to disable the widget and the shortcode.
if ( $word_stats_options[ 'totals' ] ) {
    if ( version_compare( get_bloginfo( 'version' ), '3.8.0' ) < 0 ) {
    	add_action( 'right_now_content_table_end', array( 'Word_Stats_Core', 'total_word_counts' ) );
    } else {
    	add_action( 'dashboard_glance_items', array( 'Word_Stats_Core', 'total_word_counts' ) );
    }
}

# Hook readability level column in the posts management list
if ( $word_stats_options[ 'RI_column' ] ) {
	add_filter( 'manage_posts_columns', array( 'Word_Stats_Core', 'add_posts_list_column' ) );
	add_action( 'manage_posts_custom_column', array( 'Word_Stats_Core', 'create_posts_list_column' ) );
	add_action( 'admin_init', array( 'Word_Stats_Core', 'style_column' ) );
}


/* # Widgets
-------------------------------------------------------------- */
add_action( 'widgets_init', create_function( '', 'return register_widget( "widget_ws_word_counts" );' ) );
include 'word-counts-widget.php';

/* # Shortcodes
-------------------------------------------------------------- */
class Word_Stats_Shortcodes {
	# List total word counts
	public function word_counts( $atts = null, $content = null ) { return '<ul class="word-stats-counts">' . Word_Stats_Core::get_word_counts( 'list' ) . '</ul>'; }
}
add_shortcode( 'wordcounts', array( 'Word_Stats_Shortcodes', 'word_counts' ) );


/* # Scheduled tasks
-------------------------------------------------------------- */
/*
	Processing lots of posts can be too slow and resource intensive, and most of them aren't going to change,
	so we save the post stats once and use those cached stats until the post is modified.
	We schedule more caching ASAP when we see posts pending caching (see is_worker_needed).
	We keep checking for uncached content I.e. content created by other means than the post saving action.
*/

# Do caching, and signal we started the task.
function word_stats_cache_worker() {
	Word_Stats_State::set( 'cache_start', time() );
	Word_Stats_Core::cache_pending();
	Word_Stats_State::set( 'cache_start', false );
}
add_action( 'word_stats_worker', 'word_stats_cache_worker');
if ( Word_Stats_State::is_worker_needed() ) {
	wp_schedule_single_event( time(), 'word_stats_worker' );
}


/* # State functions
-------------------------------------------------------------- */
/*
	Gets state variables in and out the state array.
	These signals help the caching worker figure out what needs to be done, and when.
*/
class Word_Stats_State {
	#	1. Are we done caching posts?
	#	2. Is there no caching started, OR is it longer than 30 seconds since the last caching iteration?
	public function is_worker_needed() {
		global $word_stats_options;
		if ( !count( Word_Stats_Core::get_uncached_posts_ids() ) ) { return false; }
		$status = Word_Stats_State::get( 'cache_start' );
		return ( !$status OR ( time() - $status > 30 ) );
	}
	public function get( $var ) {
		$status = get_option( 'word_stats_state', false );
		if ( is_array( $var ) ) {
			foreach ( $var as $v ) {
			    if ( isset( $status[ $v ] ) ) {
    			    $result[ $v ] = $status[ $v ];
                }
			}
			return $result;
		} else {
		    if ( isset( $status[ $var ] ) ) {
    			return $status[ $var ];
            } else {
                return null;
            }
		}
	}
	public function set( $var, $value ) {
		$status = get_option( 'word_stats_state', false );
		$status[ $var ] = $value;
		update_option( 'word_stats_state', $status );
	}
}

/* # Core functions
-------------------------------------------------------------- */
class Word_Stats_Core {

	/*
		# General purpose functions
	*/
	# Checks if the option doesn't exist (as opposed to just being empty). We are probably not needing this anymore after we moved the options to an array.
	public function is_option( $option ) { return get_option( $option, null ) !== null; }

	# Self explanatory. Used sometimes for upgrading deprecated options.
	public function move_option( $option1, $option2 ) { update_option( $option2, get_option( $option1 ) ); delete_option( $option1 ); }
	public function move_option_to_var( $option, &$var ) { $var = get_option( $option, false ); delete_option( $option ); }

	# Return YYYY-MM from the given date. Currently expects $date to be YYYY-MM-DD.
	public function get_year_month( $date ) { return substr( $date, 0, 7 ); }

	# Checks if a post is a content type or a functional type.
	public function is_content_type( $name ) { return ( $name != 'attachment' && $name != 'nav_menu_item' && $name != 'revision' ); }

	# Checks if a plugin is active.
	public function is_plugin_plugged( $plugin ) { return in_array( $plugin . '/' . $plugin . '.php', get_option( 'active_plugins' ) ); }

	# Perform ksort only if the variable is an array. Return false instead of throwing an error if it isn't.
	public function safe_ksort( &$array ) { if ( is_array( $array ) && !empty( $array ) ) { ksort( $array ); return true; } else { return false; } }

	# add_to_all: Add a value to several variables.
	public function add_to_all( $vars, $value ) { foreach ( $vars as &$var ) { $var += $value; } }

	/*
		# Data functions
	*/
	# Select posts according to the count unpublished option
	public function get_posts( $post_type_name ) {
		global $wpdb, $word_stats_options;
		$query = $wpdb->prepare(
		    "SELECT * FROM $wpdb->posts WHERE " .
		    ( !$word_stats_options[ 'count_unpublished' ] ? "post_status = 'publish' AND " : '' ) .
		    "post_type = %s ORDER BY ID DESC",
		    $post_type_name );
		return $wpdb->get_results( $query, OBJECT );
	}

	public function load_options() {
		$options = get_option( 'word_stats_options', false );
		if ( !$options ) {
			# Set defaults for a new install.
			$options = Array(
				'version' => WS_CURRENT_VERSION,
				'ignore_keywords' => '',
				'count_unpublished' => false,
				'live_keywords' => true,
				'add_tags' => false,
				'totals' => true,
				'live_averages' => true,
				'replace_word_count' => true,
				'RI_column' => true,
				'diagnostic_too_short' => 150,
				'diagnostic_too_long' => 1500,
				'diagnostic_too_difficult' => 15,
				'diagnostic_too_simple' => 7,
				'diagnostic_no_keywords' => 2,
				'diagnostic_spammed_keywords' => 20,
				'ignore_common' =>  substr( get_bloginfo( 'language' ), 0, 2 )
			);
			update_option( 'word_stats_options', $options );
		}
		return $options;
	}


	/* # Caching
	-------------------------------------------------------------- */
	/*
		Retrieve the ids of the posts still uncached.
		Do it only for published posts if count unpublished option is disabled.
	*/
	public function get_uncached_posts_ids() {
		global $wpdb, $word_stats_options;
		# Use $wpdb->prepare here if you add any variables
		$query = "SELECT $wpdb->posts.ID " .
			"FROM $wpdb->posts " .
			"WHERE $wpdb->posts.ID NOT IN ( " .
				"SELECT DISTINCT post_id " .
				"FROM $wpdb->postmeta " .
				"WHERE meta_key = 'word_stats_cached' " .
				"AND meta_value = TRUE " .
			") " .
			"AND $wpdb->posts.post_type != 'attachment' " .
			"AND $wpdb->posts.post_type != 'nav_menu_item' " .
			"AND $wpdb->posts.post_type != 'revision' " .
			( !$word_stats_options[ 'count_unpublished' ] ? " AND $wpdb->posts.post_status = 'publish'" : '' );
		$result = $wpdb->get_results( $query, OBJECT );
		return $result;
	}

	/*
		Does the word count, keywords and readability calculations, and stores them as meta, for a chunk of posts.
		This function will run in the background as long as possible, otherwise the browser would timeout while waiting for it.
		We exit if the plugin has been removed from the active plugins list. This loop can be very slow in cheap production servers.
		Notice we update the cache_start time, so the script that checks if the worker is active doesn't have
		to make a long guess about if the script timed out. This way if the stored time is older than some
		seconds we know the worker isn't active.
	*/
	public function cache_pending() {
		ignore_user_abort( true ); # Don't stop if the user browses away. Probably not needed.
		ini_set( 'max_execution_time', 600 );  # Try to work for ten minutes. This is better than set_time_limit, which is just a wrapper.
        $actual_MET = ini_get( 'max_execution_time' ); # In case setting the execution time is disabled on the server
        if ( isset( $actual_MET ) ) {
            $timeout = max( time() + $actual_MET - 5, 25 ); # Exit a few seconds before we run out of time.
        } else {
            $timeout = time() + 25;
        }
		$posts = Word_Stats_Core::get_uncached_posts_ids();
		if ( count( $posts ) ) {
			$posts_checked = 0;
			foreach ( $posts as $post ) {
			    if ( time() > $timeout ) { exit(); }
				Word_Stats_State::set( 'cache_start', time() );
				if ( !Word_Stats_Core::is_plugin_plugged( 'word-stats' ) ) { exit(); }
				$posts_checked++;
				if ( !get_post_meta( $post->ID, 'word_stats_cached', true ) ) { Word_Stats_Core::cache_stats( $post->ID ); }
			}
		}
		return $posts_checked;
	}

	# Resets the cache status of all posts.
	public function recount_all()  {
		global $wpdb;
		return $wpdb->query( "UPDATE $wpdb->postmeta SET meta_value = 0 WHERE meta_key = 'word_stats_cached'" );
	}

	#	Calculates the various stats for the current or specified post, saves them in post metas,
	#	then updates the cached blog-wide total word counts.
	#	ARI = Automated Readability Index. CLI = Coleman-Liau Index.
	public function cache_stats( $id = null ) {
		if ( !$id ) {
			global $post;
			if ( !$post->ID ) { return null; }
			$id = $post->ID;
		}
		$the_post = get_post( $id );
		if ( strlen( $the_post->post_content ) !== 0 ) {
            $no_shortcodes = strip_shortcodes( $the_post->post_content );
            $charset = get_bloginfo( 'charset' );
		    $all_text = bst_strip_html( $no_shortcodes, $charset );
		    if ( $all_text ) {
			    $stats = bst_split_text( $all_text );
			    $total_alphanumeric = mb_strlen( $stats[ 'alphanumeric' ] ); # mb_strlen = multibyte strlen
			    $total_sentences = count( $stats[ 'sentences' ] );
			    $total_words = count( $stats[ 'words' ] );
			    $word_array = $stats[ 'words' ];
			    $all_text = $stats[ 'text' ];
			    if ( $total_words > 0 && $total_sentences > 0 ) { # No divisions by zero, thanks.
				    $chars_per_word = intval( $total_alphanumeric / $total_words );
				    $chars_per_sentence = intval( $total_alphanumeric / $total_sentences );
				    $words_per_sentence = intval( $total_words / $total_sentences );

				    $ARI = max( round( 4.71 * ( $total_alphanumeric / $total_words ) + 0.5 * ( $total_words / $total_sentences ) - 21.43, 1 ), 0 );
				    $CLI = max( round( 5.88 * ( $total_alphanumeric / $total_words ) - 29.6 * ( $total_sentences / $total_words ) - 15.8, 1), 0 );

				    $LIXlongwords = 0;
				    for ($i = 0; $i < count( $word_array ); $i = $i + 1 ) {
					    if ( mb_strlen( $word_array[ $i ] ) > 6 ) { $LIXlongwords++; }
				    }
				    $temp = preg_split( '/[,;\.\(\:]/', $all_text );
				    $LIX = max( round( $total_words / count( $temp ) + ( $LIXlongwords * 100 ) / $total_words, 1 ), 0 );
			    } else {
				    $ARI = $CLI = $LIX = '0';
			    }
		    } else {
				    $ARI = $CLI = $LIX = '0';
		    }
		    # Remove ignored keywords
		    $ignore = Word_Stats_Core::get_ignored_keywords();
		    $keywords = bst_regfilter_keyword_counts( bst_keywords( $the_post->post_content, 3, get_bloginfo( 'charset' ) ), $ignore );
	    } else {
	        $ARI = 0; $CLI = 0; $LIX = 0; $total_words = 0; $keywords = array();
	    }
		# Cache the stats
		update_post_meta( $id, 'readability_ARI', $ARI );
		update_post_meta( $id, 'readability_CLI', $CLI );
		update_post_meta( $id, 'readability_LIX', $LIX );
		update_post_meta( $id, 'word_stats_word_count', $total_words );
		update_post_meta( $id, 'word_stats_keywords', serialize( $keywords ) );
		update_post_meta( $id, 'word_stats_cached', true );
	}

	# This function is a simpler faster version of load_report_stats.
	public function recount_totals() {
		global $wp_post_types, $wpdb, $word_stats_options;
		$report[ 'type_count' ][ 'custom' ] = 0;
		# Dates
		$period_start = '1900-01-01';
		$period_end = date( 'Y-m-d' );

		foreach( $wp_post_types as $post_type ) {
			$report[ 'type_count' ][ $post_type->name ] = 0;
			# Load only content and custom post types
			if ( Word_Stats_Core::is_content_type( $post_type->name ) ) {

				$query = $wpdb->prepare(
	                "SELECT * FROM $wpdb->posts " .
					"WHERE post_type = %s AND " .
					( !$word_stats_options[ 'count_unpublished' ] ? "post_status = 'publish' AND " : '' ) .
					"post_date BETWEEN %s AND %s " .
					"ORDER BY post_date DESC",
                    $post_type->name, $period_start, $period_end );

				$posts = $wpdb->get_results( $query, OBJECT );

				foreach( $posts as $post ) {
					$post_word_count = intval( get_post_meta( $post->ID, 'word_stats_word_count', true ) );

					# Add up words per author and aggregated. Group per month.
					# Counts per type. Custom post types are aggregated.
					$post_month = mysql2date( 'Y-m', $post->post_date );
					$report[ 'author_count_total' ][ -1 ] += $post_word_count;
					if ( $post_type->name != 'post' && $post_type->name != 'page' ) {
						$report[ 'type_count' ][ 'custom' ]++;
						$report[ 'author_count' ][ -1 ][ 'custom' ][ $post_month ] += $post_word_count;
					} else {
						$report[ 'type_count' ][ $post_type->name ]++;
						$report[ 'author_count' ][ -1 ][ $post_type->name ][ $post_month ] += $post_word_count;
					}
				}
			}
		}
		return $report;
	}


	/*
		# Live stats
	*/
	# Loads javascript for stat counting, outputs the counting code and the relevant html.
	public function live_stats() {
		global $post, $word_stats_options;
		bst_js_string_tools();
		include 'view-live-stats.php';
	}

	#	Output the cached word counts per type with the proper HTML tags.
	public function get_word_counts( $mode ) {
		if ( Word_Stats_State::is_worker_needed() ) { return ''; }
		$report = Word_Stats_Core::recount_totals();
		$total_all = $report[ 'author_count_total' ][ -1 ];

		$html = '';
		foreach ( $report[ 'author_count' ][ -1 ] as $type => $months ) {
			$words = array_sum( $months );
			$text = __( 'Words', 'word-stats' ) . ' (' . $type . ')';
			$html .= ( $mode == 'table'
							 ? '<tr><td class="first b"><a>' . number_format_i18n( $words ) . ' </a></td><td class="t"><a>' . $text . "\n" . '</a></td></tr>'
							 : '<li class="word-stats-count">' . number_format_i18n( $words ) . ' ' . $text . "\n" .  '</li>' );
		}
		# Absolute total words
		$text =  __( 'Total words', 'word-stats' );
		$total_all =  number_format_i18n( $total_all );
		$html .= ( $mode == 'table'
			 			 ? '<tr><td class="first b word-stats-dash-total"><a>' . $total_all . ' </a></td><td class="t"><a>' . $text . "\n" .  '</a></td></tr>'
						 : '<li class="word-stats-count word-stats-list-total">' . $total_all . ' ' . $text . "\n" .  '</li>' );
		return $html;
	}

	/*
		Output dashboard totals.
	*/
	public function total_word_counts() { echo Word_Stats_Core::get_word_counts( 'table' ); }


	/*
		Add a column to the post management list.
	*/
	public function add_posts_list_column( $defaults ) {
		 $defaults[ 'readability' ] = __( 'Level', 'word-stats' );
		 return $defaults;
	}

	# Aggregate all indexes into one
	public function calc_ws_index( $ARI, $CLI, $LIX ) { return max( ( floatval( $ARI ) + floatval( $CLI ) + ( max( floatval( $LIX ) - 10, 0 ) / 2 ) ) / 3, 0 ); }

	public function create_posts_list_column( $name ) {
		global $post, $word_stats_options;
		if ( $name == 'readability' ) {
			$ARI = get_post_meta( $post->ID, 'readability_ARI', true );
			$CLI = get_post_meta( $post->ID, 'readability_CLI', true );
			$LIX = get_post_meta( $post->ID, 'readability_LIX', true );

			if ( !$ARI ) {
				# If there is no data or the post is blank
				echo '<span style="font-weight:bold;color:#999;">--</span>';
			} else {
				# Trying to aggregate the indexes in a meaningful way.
				$r_avg = Word_Stats_Core::calc_ws_index( $ARI, $CLI, $LIX );
				if ( $r_avg < $word_stats_options[ 'diagnostic_too_simple' ] ) { $color = "#06a"; }
				if ( $r_avg >= $word_stats_options[ 'diagnostic_too_simple' ] && $r_avg < $word_stats_options[ 'diagnostic_too_difficult' ] ) { $color = "#0a6"; }
				if ( $r_avg >= $word_stats_options[ 'diagnostic_too_difficult' ] ) { $color = "#c36"; }
				echo '<span style="font-weight:bold;color: ' . $color . '">', round( $r_avg, 0 ), '</span>';
			}
		}
	}

	# Load style for the column
	public function style_column() {
		wp_register_style( 'word-stats-css', plugins_url( WS_FOLDER . '/css/word-stats.css' ) );
		wp_enqueue_style( 'word-stats-css' );
	}

	/*
		Return an array with the user's list of ignored keywords merged with the selected (if any) or default list of common words.
	*/
	public function get_ignored_keywords() {
		global $word_stats_options;
		$common = ( $word_stats_options[ 'ignore_common' ] ? bst_get_common_words( $word_stats_options[ 'ignore_common' ] ) : array() );
		return ( $word_stats_ignore[ 'ignore_keywords' ] ? array_unique( array_merge( explode( "\n", preg_replace('/\r\n|\r/', "\n", $word_stats_options[ 'ignore_keywords' ] ) ), $common ) ) : $common );
	}

} # end class Word_Stats_Core

/* # Admin functions
-------------------------------------------------------------- */
class Word_Stats_Admin {

	/*
		Options
	*/
	public function init_settings() {
		$settings = array( 'word_stats_options', 'word_stats_state' );
		foreach( $settings as $setting ) { register_setting( 'word-stats-settings-group', $setting ); }
	}

	# Generate sections and fields for the options page
	public function options_section( $label ) { echo '<h3>' . $label . '</h3>'; }

	public function options_field( $name, $type, $value, $default, $checked, $min, $max, $width, $align, $label ) {
		if ( $default ) { echo '<input type="hidden" name="' . $name . '" value="' . $default . '" />'; }
		echo '<p>
			<input type="', $type, '" ',
			'name="', $name, '" ',
			'style="', ( $width ? 'width: ' . $width . ';' : '' ), ( $align ? 'text-align: ' . $align . ';' : '' ), '" ';
			if ( $type == 'number' ) {
				echo ( $min ? 'min="' . $min . '" ' : '' ), ( $max ? 'max="' . $max . '" ' : '' );
			}
			if ( $type == 'checkbox' ) {
				echo ( $checked ? 'checked="checked" ' : '' );
			}
			echo 'value="', $value, '" />';
			echo ' <label for="', $name, '">', $label, '</label>';
	}

	public function settings_page() {
		global $word_stats_options;
		include 'view-settings.php';
	}

	public function diagnostics_row( $common, $class, $stat ) {
		return '<td class=\'ws-table-title\'> ' . $common[0] . '</td><td class=\'ws-table-type\'>' . $common[1] . '<td class=\'ws-table-date\'>' . $common[2] . '</td><td class=\'ws-table-word-count\'>' . $common[3] . '</td><td class=\'ws-table-' . $class . '\'>' . $stat . '</td>';
	}

	/*
		Read the cached stats and output the data set for the stats page.
	*/
	public function load_report_stats( $author_graph, $period_start, $period_end ) {
		global $user_ID, $current_user, $wp_post_types, $wpdb, $word_stats_options;
		ini_set( 'max_execution_time', 300 ); # Loading the stats can timeout on some servers with 30s max execution and "all time" is selected

		$report[ 'total_keywords' ] = $report[ 'recent_posts_rows' ] = array();
		$report[ 'totals_readability' ][ 0 ] = $report[ 'totals_readability' ][ 1 ] = $report[ 'totals_readability' ][ 2 ] = $report[ 'type_count' ][ 'custom' ] =
		$dg_difficult_row = $dg_simple_row = $dg_short_row = $dg_long_row = $dg_no_keywords_row =
		$cached = $not_cached = 0;

		# Validate dates
		$period_start = date( 'Y-m-d', strtotime( $period_start ) );
		$period_end = date( 'Y-m-d', strtotime( $period_end ) + 86400 ); # Last day included

		$ignore = Word_Stats_Core::get_ignored_keywords();

		foreach( $wp_post_types as $post_type ) {
			$report[ 'type_count' ][ $post_type->name ] = 0;
			# Load only content and custom post types

			if ( Word_Stats_Core::is_content_type( $post_type->name ) ) {
                $query = $wpdb->prepare(
                    "SELECT * FROM $wpdb->posts
					WHERE post_type = %s AND " .
					( !$word_stats_options[ 'count_unpublished' ] ? "post_status = 'publish' AND " : '' ) .
					"post_date BETWEEN %s AND %s " .
					"ORDER BY post_date DESC",
					$post_type->name, $period_start, $period_end );
				$posts = $wpdb->get_results( $query, OBJECT );

				foreach( $posts as $post ) {
					# load_report_stats is not meant to be called before all posts are cached.
					# But in case a post happens to be created while the script was running, we cache it here.
					if ( !get_post_meta( $post->ID, 'word_stats_cached', true ) ) { Word_Stats_Core::cache_stats( $post->ID ); }

					$post_word_count = intval( get_post_meta( $post->ID, 'word_stats_word_count', true ) );
					$keywords = unserialize( get_post_meta( $post->ID,  'word_stats_keywords', true ) );

					# Add up words per author and aggregated. Group per month.
					$post_month = mysql2date( 'Y-m', $post->post_date );
					Word_Stats_Core::add_to_all( array(
						&$report[ 'author_count' ][ -1 ][ $post_type->name ][ $post_month ],
						&$report[ 'author_count' ][ $post->post_author ][ $post_type->name ][ $post_month ],
						&$report[ 'author_count_total' ][ $post->post_author ],
						&$report[ 'author_count_total' ][ -1 ]
					), $post_word_count );

					# Divisor to calculate keyword density per 1000 words
					$density_divisor = ( intval( $post_word_count / 1000 ) ? intval( $post_word_count / 1000 ) : 1 );

					# Unless the selected author is -1 (all), stats for posts by the selected author only
					if ( $author_graph == -1 || $post->post_author == $author_graph ) {
						$dg_relevant_keywords = $dg_spammed_keywords = array();
						$too_short = (int) $word_stats_options[ 'diagnostic_too_short' ]; # For code readability

						# Remove ignored words.
						$keywords = bst_regfilter_keyword_counts( $keywords, $ignore );

						# Aggregate the keywords, then create two lists for keywords flagged as relevant and spammed, according to their density.
						# Posts that are already flagged as too short aren't diagnosed as having no relevant keywords.
						if ( $keywords && is_array( $keywords ) ) { #empty arrays test false
							foreach ( $keywords as $key => $value ) {
								if ( $report[ 'total_keywords' ][ $key ] === null ) { $report[ 'total_keywords' ][ $key ] = 0; 	}
								$report[ 'total_keywords' ][ $key ] += (int) $value;
								if ( $post_word_count >= $too_short ) {
									$density = $value / $density_divisor;
									if ( $density > (int) $word_stats_options[ 'diagnostic_no_keywords' ] ) { $dg_relevant_keywords[ $key ] = true; }
									if ( $density > (int) $word_stats_options[ 'diagnostic_spammed_keywords' ] ) { $dg_spammed_keywords[ $key ] = $value; }
								}
							}
						}

						# Counts per type. Custom post types are aggregated.
						if ( $post_type->name != 'post' && $post_type->name != 'page' ) {
							$report[ 'type_count' ][ 'custom' ]++;
						} else {
							$report[ 'type_count' ][ $post_type->name ]++;
						}

						# Get the readability index.
						$ARI = get_post_meta( $post->ID, 'readability_ARI', true );
						$CLI = get_post_meta( $post->ID, 'readability_CLI', true );
						$LIX = get_post_meta( $post->ID, 'readability_LIX', true );

						if ( $ARI && $CLI && $LIX ) {
							$ws_index_n = Word_Stats_Core::calc_ws_index( $ARI, $CLI, $LIX );
							# Aggregate levels in 3 tiers (Basic, Intermediate, Advanced)
							if ( $ws_index_n < $word_stats_options[ 'diagnostic_too_simple' ] ) { $ws_index = 0; }
							elseif ( $ws_index_n < $word_stats_options[ 'diagnostic_too_difficult' ] ) { $ws_index = 1; }
							else { $ws_index = 2; }
							$report[ 'totals_readability' ][ $ws_index ]++;
						}

						# Empty title fix.
						$post_title = ( $post->post_title == '' ? '#' . $post->ID . ' ' . __( '(no title)', 'word-stats' ) : htmlentities( $post->post_title, null, 'utf-8' ) );

						$post_link = ( current_user_can( 'edit_post', $post->ID ) ? '<a href=\'' . get_edit_post_link( $post->ID ) . '\'>' .  $post_title . '</a>' : $post_title );
						$common = array( $post_link, $post_type->name, mysql2date('Y-m-d', $post->post_date ), number_format( $post_word_count ) );

						if ( $ws_index_n > (int) $word_stats_options[ 'diagnostic_too_difficult' ] && $post_word_count >= $too_short ) {
							$report[ 'diagnostic' ][ 'too_difficult' ][ $dg_difficult_row ] = Word_Stats_Admin::diagnostics_row( $common, 'readability', round( $ws_index_n ) );
							$dg_difficult_row++;
						}
						if ( $ws_index_n < (int) $word_stats_options[ 'diagnostic_too_simple' ] && $post_word_count >= $too_short ) {
							$report[ 'diagnostic' ][ 'too_simple' ][ $dg_simple_row ] = Word_Stats_Admin::diagnostics_row( $common, 'readability', round( $ws_index_n ) );
							$dg_simple_row++;
						}
						if ( $post_word_count < $too_short ) {
							$report[ 'diagnostic' ][ 'too_short' ][ $dg_short_row ] = Word_Stats_Admin::diagnostics_row( $common, 'readability', round( $ws_index_n ) );
							$dg_short_row++;
						}
						if ( $post_word_count > intval( $word_stats_options[ 'diagnostic_too_long' ] ) ) {
							$report[ 'diagnostic' ][ 'too_long' ][ $dg_long_row ] = Word_Stats_Admin::diagnostics_row( $common, 'readability', round( $ws_index_n ) );
							$dg_long_row++;
						}
						if ( empty( $dg_relevant_keywords )  && $post_word_count >= $too_short ) {
							$report[ 'diagnostic' ][ 'no_keywords' ][ $dg_no_keywords_row ] = Word_Stats_Admin::diagnostics_row( $common, 'readability', round( $ws_index_n ) );
							$dg_no_keywords_row++;
						}
						if ( count( $dg_spammed_keywords ) > 0 ) {
							$report[ 'diagnostic' ][ 'spammed_keywords' ][ $dg_spammed_row ] = Word_Stats_Admin::diagnostics_row( $common, 'keywords', implode( ', ', array_keys( $dg_spammed_keywords ) ) );
							$dg_spammed_row++;
						}
					}
				}
			}
		}

		# Sort keywords by frequency, descending
		asort( $report[ 'total_keywords' ] );
		$report[ 'total_keywords' ] = array_reverse( $report[ 'total_keywords' ], true );

		# Sort timeline
		Word_Stats_Core::safe_ksort( $report[ 'author_count' ][ $author_graph ][ 'post' ] );
		Word_Stats_Core::safe_ksort( $report[ 'author_count' ][ $author_graph ][ 'page' ] );
		Word_Stats_Core::safe_ksort( $report[ 'author_count' ][ $author_graph ][ 'custom' ] );

		return $report;
	}

	/*
		Generate diagnostic tables for the reports page
	*/
	public function diagnostics_table( $title, $fields, $id, $rows ) {
		$html = '<h4 class="ws-diagnostic-title">' . $title . '</h4>' .
					'<table class="ws-diagnostics" id="ws-diagnostic-' . $id . '">' .
					'<thead><tr>';
					foreach( $fields as $field ) {
						$html .= '<td class="ws-table-' . strtolower( str_replace( ' ', '-', $field ) ) . '">' . $field . '</td>';
					}
		$html .= '</tr></thead>';
		$even = false;
		foreach( $rows as $row ) {
			$html .= '<tr' . ( $even ? '' : ' class="ws-row-even" ' ) . '>' . $row . '</tr>';
			$even = !$even;
		}
		$html .= '</table>';
		return $html;
	}

	public function diagnostics_tables( $report ) {
		$table_fields = array(
			__( 'Title', 'word-stats' ),
			__( 'Post Type', 'word-stats'),
			__( 'Date', 'word-stats' ),
			__( 'Word Count', 'word-stats' ),
			__( 'Spammed Keywords', 'word-stats' )
		);
		$diagnostic_tables = ( count( $report[ 'diagnostic' ][ 'spammed_keywords' ] ) ?
			Word_Stats_Admin::diagnostics_table( __( 'Spammed keywords', 'word-stats' ), $table_fields, 'spammed-keywords', $report[ 'diagnostic' ][ 'spammed_keywords' ] ) : '' );
		$table_fields = array(
			__( 'Title', 'word-stats' ),
			__( 'Post Type', 'word-stats'),
			__( 'Date', 'word-stats' ),
			__( 'Word Count', 'word-stats' ),
			__( 'Readability' , 'word-stats' )
		);
		$diagnostic_tables .=
			( count( $report[ 'diagnostic' ][ 'no_keywords' ] ) ?
				Word_Stats_Admin::diagnostics_table( __( 'No relevant keywords', 'word-stats' ), $table_fields, 'no_keywords', $report[ 'diagnostic' ][ 'no_keywords' ] ) : '' ) .
			( count( $report[ 'diagnostic' ][ 'too_difficult' ] ) ?
				Word_Stats_Admin::diagnostics_table( __( 'Difficult text', 'word-stats' ), $table_fields, 'too-difficult', $report[ 'diagnostic' ][ 'too_difficult' ] ) : '' ) .
			( count( $report[ 'diagnostic' ][ 'too_simple' ] ) ?
				Word_Stats_Admin::diagnostics_table( __( 'Simple text', 'word-stats' ), $table_fields, 'too-simple', $report[ 'diagnostic' ][ 'too_simple' ] ) : '' ) .
			( count( $report[ 'diagnostic' ][ 'too_long' ] ) ?
				Word_Stats_Admin::diagnostics_table( __( 'Text may be too long', 'word-stats' ), $table_fields, 'too-long', $report[ 'diagnostic' ][ 'too_long' ] ) : '' ) .
			( count( $report[ 'diagnostic' ][ 'too_short' ] ) ?
				Word_Stats_Admin::diagnostics_table( __( 'Text may be too short', 'word-stats' ), $table_fields, 'too-short', $report[ 'diagnostic' ][ 'too_short' ] ) : '' );
		return $diagnostic_tables;
	}

	/*
		Display the stats page if the caching is complete.
	*/
	public function reports_page() {
		global $user_ID, $current_user, $wp_post_types, $wpdb;

		if ( Word_Stats_State::is_worker_needed() ) {
		    _e( 'Stats collection hasn\'t completed yet. Try again later.', 'word-stats' );
		    return false;
		} 	# Relevant when going straight to the graphs page right after installing the plugin.

		if( isset( $_GET[ 'view-all' ] ) ) {
			$period_start = '1900-01-01';
			$period_end = date( 'Y-m-d' );
		} else {
			$period_start = isset( $_GET[ 'period-start' ] ) ? $_GET[ 'period-start' ] : date( 'Y-m-d', time() - 15552000 );
			$period_end = isset( $_GET[ 'period-end' ] ) ? $_GET[ 'period-end' ] : date( 'Y-m-d' );
		}

		$author_graph = isset( $_GET[ 'author-tab' ] ) ? intval( $_GET[ 'author-tab' ] ) : $user_ID;

		$report = Word_Stats_Admin::load_report_stats( $author_graph, $period_start, $period_end );

		if ( $report ) {
			# Get oldest date (for the graph)
			if( isset( $_GET[ 'view-all' ] ) ) {
				$period_start = date( 'Y-m-d', min(
					bst_Ym_to_unix( bst_array_first( $report[ 'author_count' ][ $author_graph ][ 'post' ] ) ),
					bst_Ym_to_unix( bst_array_first( $report[ 'author_count' ][ $author_graph ][ 'page' ] ) ),
					bst_Ym_to_unix( bst_array_first( $report[ 'author_count' ][ $author_graph ][ 'custom' ] ) ) )
				);
			}
			include 'graph-options.php';
			$diagnostic_tables = Word_Stats_Admin::diagnostics_tables( $report );
			include 'view-report-graphs.php';
		} else {
			_e( 'Sorry, word counting failed for an unknown reason.', 'word-stats' );
		}
	}
}

function word_stats_report_init() {
		wp_register_style( 'ws-reports-page', plugins_url( WS_FOLDER . '/css/reports-page.css' ) );
		wp_register_style( 'ws-jquery-ui', plugins_url( WS_FOLDER . '/js/ui/css/custom/jquery-ui-1.9.2.custom.min.css' ) );
}
function word_stats_report_styles() {
		wp_enqueue_style( 'ws-reports-page' );
		wp_enqueue_style( 'ws-jquery-ui' );
}
function word_stats_create_menu() {
	global $word_stats_options;
	add_action( 'admin_init', array( 'Word_Stats_Admin', 'init_settings' ) );
	add_options_page( 'Word Stats Plugin Settings', 'Word Stats', 'manage_options', 'word-stats-options', array( 'Word_Stats_Admin', 'settings_page' ) );
	//if ( !Word_Stats_State::is_worker_needed() ) {
		$page = add_submenu_page( 'index.php', 'Word Stats Plugin Stats', 'Word Stats', 'edit_posts', 'word-stats-graphs', array( 'Word_Stats_Admin', 'reports_page' ) );
		add_action( 'admin_print_styles-' . $page, 'word_stats_report_styles' );  # Load styles for the reports page
	//}
}
add_action( 'admin_init', 'word_stats_report_init' );
add_action( 'admin_menu', 'word_stats_create_menu' );


/* # Notices
-------------------------------------------------------------- */
function word_stats_notice( $mode = 'updated', $message ) { echo '<div class="', $mode, '"><p>', $message, '</p></div>'; }
function word_stats_notice_cacheing() {
	$posts_uncached = count( Word_Stats_Core::get_uncached_posts_ids() );
	if ( $posts_uncached > 0 ) {
    	word_stats_notice( 'updated', sprintf( __( 'Word stats collection is scheduled (%s posts left). Stats will be available in a little while.', 'word-stats' ), $posts_uncached ) );
    }
}
function word_stats_notice_donation() {
	word_stats_notice( 'updated fade', __( 'Thanks for your contribution!' , 'word-stats' ) . ' ' . __( 'With your support we can bring you even more premium features!', 'word-stats' ) );
}
if( isset( $_GET[ 'word-stats-action' ] ) ) {
	if( $_GET[ 'word-stats-action' ] == 'donation' ) { add_action( 'admin_notices', 'word_stats_notice_donation' ); }
}
if ( Word_Stats_State::is_worker_needed() ) { add_action( 'admin_notices', 'word_stats_notice_cacheing' ); }

# EOF
