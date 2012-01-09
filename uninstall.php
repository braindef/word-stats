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

// Retained.
//delete_option( 'word_stats_premium' );

/* # Purge metadata
-------------------------------------------------------------- */
global $wpdb;

// Load the posts
$query = "SELECT * FROM $wpdb->posts ORDER BY post_date DESC";
$posts = $wpdb->get_results( $query, OBJECT );

foreach ( $posts as $post ) {
		delete_post_meta( $post->ID, 'readability_ARI' );
		delete_post_meta( $post->ID, 'readability_CLI' );
		delete_post_meta( $post->ID, 'readability_LIX' );
}

// Done
?>
