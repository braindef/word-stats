<?php

/* # Exit if not called by uninstall process
-------------------------------------------------------------- */
if( !WP_UNINSTALL_PLUGIN ) { exit( __( 'Please, don\'t load this file directly', 'word-stats' ) ); }

/* # Purge settings
-------------------------------------------------------------- */
delete_option( 'word_stats_total_counts_cache' );
delete_option( 'word_stats_monthly_counts_cache' );
delete_option( 'word_stats_version' );
delete_option( 'word_stats_ignore_keywords' );
delete_option( 'word_stats_count_unpublished' );
delete_option( 'word_stats_totals' );
delete_option( 'word_stats_show_keywords' );
delete_option( 'word_stats_add_tags' );
delete_option( 'word_stats_averages' );
delete_option( 'word_stats_replace_word_count' );
delete_option( 'word_stats_RI_Column' );
delete_option( 'word_stats_diagnostic_too_short' );
delete_option( 'word_stats_diagnostic_too_long' );
delete_option( 'word_stats_diagnostic_too_difficult' );
delete_option( 'word_stats_diagnostic_too_simple' );
delete_option( 'word_stats_diagnostic_no_keywords' );
delete_option( 'word_stats_diagnostic_spammed_keywords' );
delete_option( 'word_stats_ignore_common' );
delete_option( 'word_stats_done_caching' );
delete_option( 'word_stats_cache_start' );

// Retained.
//delete_option( 'word_stats_premium' );

/* # Purge metadata
-------------------------------------------------------------- */
global $wpdb;

set_time_limit( 0 );

// Delete the custom meta fields
$query = "
	DELETE FROM $wpdb->postmeta
	WHERE $wpdb->postmeta.meta_key = 'readability_ARI'
	OR $wpdb->postmeta.meta_key = 'readability_CLI'
	OR $wpdb->postmeta.meta_key = 'readability_LIX'
	OR $wpdb->postmeta.meta_key = 'word_stats_cached'
	OR $wpdb->postmeta.meta_key = 'word_stats_keywords'
	OR $wpdb->postmeta.meta_key = 'word_stats_word_count'
";

$posts = $wpdb->query( $query, OBJECT );

/* EOF */
