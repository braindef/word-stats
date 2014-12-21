=== Plugin Name ===
Contributors: Fran Ontanaya
Tags: seo, keywords, words, statistics, analytics, diagnostics, counters, readability, linguistics
Requires at least: 4.1.0
Tested up to: 4.1.0
Stable tag: 4.5.1

A suite of word counters, keyword counters and readability analysis for your blog.

== Description ==

Word Stats adds a suite of linguistic diagnostics to help you keep track of your content and improve its quality.

The reports page lets you select an author and period to analyze, and displays:

* The total word count.
* The number and percentage of posts of each post type.
* The top 20 keywords.
* The percentage of posts of basic, intermediate and advanced readability level.
* A graph with monthly word counts for each post type.
* Diagnostics tables, with links to edit the posts that may be too short, too long, too difficult, too simple, lack relevant keywords or abuse certain keywords.

You can display the total word counts for each post type in your dashboard, widget areas and inside your posts with the &#91;wordcounts&#93; shortcode.

Word Stats also extends the info area of the post edit form with these live stats:

* Relevant keywords. Common words can be blacklisted with regular expressions in the settings page.
* A more accurate word count.
* Color coded readability tests: Automated Readability Index, Coleman-Liau Index and LIX.
* Total characters, alphanumeric characters, words and sentences.
* Characters per word, characters per sentence, words per sentence.

Additionally, an extra column with the readability level of each post can be displayed in the manage posts list.

Word Stats includes basic support for Unicode scripts, including cyrillic, greek, arabic, hindi and japanese (mileage may vary).

Spanish and Catalan translations are bundled with the plugin.

**Contact**

Feel free to send feedback, requests or suggestions at email@franontanaya.com.

Or follow me on Twitter: [https://twitter.com/FranOntanaya](https://twitter.com/FranOntanaya)

== Changelog ==
= 4.5.1 =
* Fix: Prepared query wasn't working on the dashboard.
* Fix: Provisional fix for dashboard stats in WordPress +3.8

= 4.5.0 =
* Fix: PHP 5.5 compatibility issue.
* Fix: Strip shortcodes.
* Fix: Provisionally raised max_execution_time to 5 minutes on load_report_stats to avoid timeouts when viewing all time stats.
* Fix: Added isset checks on worker state function to avoid Illegal string offset warnings on upgrade.
* Fix: Option boxes width.
* Fix: Both LIX and the average readability index now floor at 0
* Fix: Dash submenu is not disabled during stats collection -- will just alert that counting is underway
* Fix: Stats collection alert was displayed with 0 posts left.
* Fix: Changed some paths so they still work when Word Stats is installed in a folder different from default
* Removed pre 4.4.1 version upgrade code.
* Replaced the Donate button with a LinkedIn link.
* Tentatively added a timeout check to the stats collection worker

= 4.4.2 =
* Fix: 'Undefined index' notices in debug mode due to using $_GET variables before checking if they were set.
* Fix: 'Undefined variable' notice due to word_stats_report_styles action being loaded when the worker was still busy collecting stats and add_submenu_page was disabled, therefore $page not being defined for the action.

= 4.4.1 =
* Fix: Forcing a recount of all posts to clear bad stats.

= 4.4 =
* Fix: Some posts were showing 0 words counted upon saving due to bad html entity decoding. A custom entity decoding dictionary has been added to address this.
* Fix: Several fixes/minor improvements to the server side counting patterns.
* Fix: Added Chinese ideograms to the list of recognized word characters.
* UTF-8 character documentation moved out of basic-string-tools.php to basic-string-tools.md.

= 4.3.1 =
* Fix: datepicker plugin had stopped working.
* Fix: Reports page was displaying an incorrect period start date.
* Design: Datepicker theme matches better the admin user interface.
* Minimum WP version required raised to 3.1.

= 4.3 =
* Fix/Code: Word Stats uses now WP Cron instead of admin side AJAX calls to work on caching the word stats. Removes javascript bug that prevented caching from starting.
* Fix: Dashboard/shortcode/widget total word counts are now recounted every time. Fixes several inconsistencies due to caching/bugs.
* Fix: The caching worker function was echoing debug output.
* Fix/Code: set_time_limit replaced with ini_set to prevent warnings.
* Fix: Bad HTML formatting in the regular expressions description.
* Design: Removed decimal from readability index column values. Changed column title, made values bold.
* Code: Tested with WP 3.5.
* Code: Plugin options grouped in arrays.
* Code: Replaced get_option calls and constants by global variables.
* Code: Cleaned and simplified code and updated documentation.
* Code: Removed functions assign_thresholds and get_cached_totals.
* Code: Added Word_Stats_Admin::options_section and Word_Stats_Admin::options_field to generate html for the options page.
* Code: ws_diagnostics_table renamed to diagnostics_table.
* Code: graph-options.php's include moved from view-report-graphs.php to word-stats.php. Options are stored in an array instead of a function.
* Code: Simplified get_ignored_keywords.
* Code: The caching worker updates on its state more often.

= 4.2.3 =
* Fix: Some non word characters (square brackets, equals) weren't being removed from keywords.

= 4.2.2 =
* Fix: PHP array_keys() warnings being thrown when keywords list was empty.

= 4.2.1 =
* Fix: Keywords with double quotes weren't being split nor escaped, causing the stats page to stop loading due to javascript error.

= 4.2 =
* Feature: Common ignored keywords apply to the live stats too.
* Fix: 'All authors' option in the reports page wasn't functional.
* Fix: Keywords weren't counted separatedly for each author.
* Fix: "they're" and "third" keywords not being ignored.

= 4.1.0 =
* Feature: The reports page can now display aggregate stats for all authors.
* Fix: Invalid argument error in basic-string-tools.php

= 4.0.5 =
* Fix: bst_match_regarray() in basic-string-tools.php now returns false instead of error when the first argument isn't an array.
* Basic test on 3.4.2

= 4.0.4 =
* Fix: Enabled all access by default to conform with WordPress guidelines.

= 4.0.3 =
* Fix: Wrong second argument passed to legacy versions of get_html_translation_table.

= 4.0.2 =
* Fix: Version check boolean expression evaluated false only when both versions were equal.

= 4.0.1 =
* Fix: Parse error: get_html_translation_table takes only two arguments in PHP < 5.3.4.

= 4.0 =
* Feature: English and Spanish common words can be ignored for keyword counts (when supported, defaults to the blog's language setting).
* Feature: Keyword settings and diagnostics are now relative to density per 1000 words. Default settings have been adjusted.
* Feature: Keyword counts are cached in the 'word_stats_keywords' post meta.
* Feature: Post word count is cached in the 'word_stats_word_count' post meta.
* Feature/Fix: Posts that are already diagnosed as too short aren't listed as having no relevant keywords or being too simple/too difficult.
* Fix: Added AJAX call to make the script work in the initial caching for all posts in the background. Should prevent timeouts when displaying all time stats shortly after installing the plugin.
* Fix: Uninstall loop to delete custom fields was too slow in production servers, replaced with SQL query.
* Fix: Admin notices were being displayed in the wrong place in the stats and diagnostics page.
* Fix: English contractions and Catalonian interpuncts aren't split into different words.
* Fix: Count unpublished default set to disabled.
* Fix: Missing Unicode support for ignored keywords made patterns like [eé] not match the accented character.
* Fix: Proper html entity decoding when counting keywords.
* Fix: Posts with empty titles showed no link in the diagnostics tables.
* Fix: Added check to prevent loading plugin script files directly.
* Fix: Some upgrades from pre 3.1 versions ran during fresh installs, preloaded the ignored keywords list with one empty regexp.
* Fix: Difficulty threshold settings weren't stored, apparently due to a limit in the length for setting keys.
* Fix: Wrong counter was being used in the diagnostics table for difficult posts.
* Fix: Greedy regexp was gluing together keywords at end and beggining of line.
* Fix: Forward slashes weren't excluded from cached keywords.
* Fix: Missing translation string for "Ignore these keywords:" in settings page.
* Fix: Graphs area is hidden and a notice is displayed when JavaScript is off.
* Code: Notices use now the WordPress built in admin_notices action.
* Code: Capitalized class names to follow WordPress standards.
* Code: Renamed core class from word_stats_readability to Word_Stats_Core. Moved functions from word_stats_counts to Word_Stats_Core.
* Code: Diagnostics table template moved to view-diagnostics-table.php.
* Code: Stats page graphs template moved to view-report-graphs.php.
* Code: Live stats template moved to view-live-stats.php.
* Code: Simplified some redundant code.

== Upgrade Notice ==
= 4.3 =
Although no new features are included, this is a substantial code rewrite. Test it before using it in a production enviroment!

If you have been having problems with stats caching getting stuck, this release may help. Caching is now done via WP-Cron instead of an AJAX call. Also caching of unpublished posts is deferred until the option to count them is selected. The changes also cover the possible source of some reported problems with stats not updating correctly.

== Installation ==

1. Install it from the plugins admin page, or upload the zip with WordPress' built-in tool, or unzip it to 'wp-content/plugins'.
2. Activate it.
3. Go to Settings | Word Stats and set up the optional features.

**Uninstall note**

* All settings and post metadata, save the premium status, are deleted when you uninstall the plugin.
* If you want to retain the settings and/or metadata, disable the plugin instead of uninstalling it, or delete it manually from the plugins folder.

== Frequently Asked Questions ==

**The stats caching notice seems stuck**

* If your version is older than 4.3, try upgrading.
* Sometimes WP Cron doesn't start a scheduled work right away. Wait a minute and load any page.
* Check that WP Cron isn't disabled.

**The word count/readability calculation isn't accurate**

Word Stats uses simple algorithms (but more elaborate than the PHP word counter). For fairly ordinary English texts they will closely match human counting. The margin of error will be greater for short pieces, text with complex punctuation or in other languages, but they should be still good indicators.

**Some HTML/shortcode words are being counted as keywords**

Some cases are still not filtered out. Report them in the plugin support forum or via email.

**This plugin makes my site slow**

Some of the plugin's tasks can be a bit intensive, specially on large blogs. Try disabling counting for unpublished posts, and live stats when you don't need them. Our resources are limited, for now we are working on features rather than optimization.

**Some common words show as keywords, even though the setting to exclude common words is activated**

The built in lists only exclude some of the most common words. Performing many matches with regular expressions can be very slow.

**Why the live counters seem to lag?**

The calculations are refreshed every few seconds.

**Are the ignored keywords counted in the total words?**

Yes. They are ignored only for keyword counts.

**Do the indexes really reflect how easy is the text?**

They try to reflect how easy the text is to read. You can write an article about relativity in simple English and it will be rated as low level.

**What do the readability number mean?**

For ARI and CLI, they are the U.S. grade level of the text. Roughly, grade level 1 corresponds to ages 6-8, grade 8 to 14 years old, grade 12 to 17 years old.

For LIX:

* below 25: Children's Books
* 25 - 30: Simple texts
* 30 - 40: Normal Text / Fiction
* 40 - 50: Factual information, such as Wikipedia
* 50 - 60: Technical texts
* over 60: Specialist texts / research / dissertations

Each index uses a different algorithm:

* ARI is based on word length and words per sentence.
* CLI is based on characters per 100 words, excluding non-word characters, and sentences per 100 words
* LIX is based on average words between pauses (periods, colons, semicolons, etc.) and average words longer than 6 characters.

Check [http://en.wikipedia.org/wiki/Readability_test](http://en.wikipedia.org/wiki/Readability_test) for more information.

**Why other common tests aren't included?**

These three indexes don't rely on syllable counting, which is a bit more complicated and language dependent.

**The stats page timed out.**

Try selecting a shorter period, disabling the setting to count drafts and pending posts, disabling the setting to ignore common words or shortening your list of ignored keywords.

== Screenshots ==

1. Analytics page.
2. Total word counts in the dashboard.
3. Live stats for the post being edited.
4. Extra column showing an aggregate of the readability indexes.
