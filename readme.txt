=== Plugin Name ===
Contributors: Fran Ontanaya
Tags: seo, keywords, words, statistics, analytics, diagnostics, counters, readability, linguistics, premium
Requires at least: 3.0.0
Tested up to: 3.3.1
Stable tag: 3.3

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

Requires WordPress 3.0 and PHP 5.

**Features planned for next versions**

* Send analytics reports by email.
* Optionally include excerpts in stats.
* Exportable analytics data.

**Contact**

Feel free to send feedback, requests or suggestions at email@franontanaya.com.

Or follow me on Twitter: [https://twitter.com/FranOntanaya](https://twitter.com/FranOntanaya)

== Installation ==

1. Install it from the plugins admin page, or upload the zip with WordPress' built-in tool, or unzip it to 'wp-content/plugins'.
2. Activate it.
3. Go to Settings | Word Stats and set up the optional features.

**Uninstall note**

* All settings and post metadata, save the premium status, are deleted when you uninstall the plugin.
* If you want to retain the settings and/or metadata, disable the plugin instead of uninstalling it, or delete it manually from the plugins folder.

== Frequently Asked Questions ==

**What do the readability numbers and colors mean?**

For ARI and CLI, they are the U.S. grade level of the text. Roughly, grade level 1 corresponds to ages 6-8, grade 8 to 14 years old, grade 12 to 17 years old. The color code is 0-8: green; 8-12: yellow; 12-16: orange; 16-20: red; +20: purple.

For LIX:

* below 25: Children's Books (green)
* 25 - 30: Simple texts (green)
* 30 - 40: Normal Text / Fiction (yellow)
* 40 - 50: Factual information, such as Wikipedia (orange)
* 50 - 60: Technical texts (red)
* over 60: Specialist texts / research / dissertations (purple)

Each index uses a different algorithm:

* ARI is based on word length and words per sentence.
* CLI is based on characters per 100 words, excluding non-word characters, and sentences per 100 words
* LIX is based on average words between pauses (periods, colons, semicolons, etc.) and average words longer than 6 characters.

Check [http://en.wikipedia.org/wiki/Readability_test](http://en.wikipedia.org/wiki/Readability_test) for more information.

**Why other common tests aren't included?**

These three indexes don't rely on syllable counting, which is a bit more complicated and language dependent.

**How accurate are the indexes?**

Word Stats uses simple algorithms. For fairly ordinary English texts they will closely match human counting; for example, the algorithm for Coleman-Liau produces the same result for the example piece in the Wikipedia article. The margin of error will be greater for short pieces with dashes and apostrophes or in other languages, but they should be still good indicators.

**Do the indexes really reflect how easy is the text?**

They try to reflect how easy the text is to read. You can write an article about relativity in simple English and it will be rated as low level.

**Why the live counters seem to lag?**

The calculations are refreshed every 5 seconds.

**How does Word Stats pick the relevant keywords for the live stats?**

It shows any keyword that appears at least three times and at least 1/5 times the top keyword. Words blacklisted in the settings page are excluded.

Post tags can be counted optionally as keywords. They are added when the post is loaded. If you add new tags, save the post and reload it.

**The anchors for the total word counts in the dashboard are blank**

They are just for style consistence.

== Screenshots ==

1. Analytics page.
2. Total word counts in the dashboard.
3. Live stats for the post being edited.
4. Extra column showing an aggregate of the readability indexes.

== Changelog ==
= 3.3 =
* Settings and metadata are deleted upon uninstall.
* Live keyword count uses now the thresholds from the settings page. Spammed keywords are marked red. Top, not spammed keywords are marked green.
* Default thresholds for keywords changed to 4 (relevant) and 10 (spammed).
* Fix: Quote entities being counted as keywords.
* Fix: Options weren't properly tested for existence, affected first install defaults and word count replacement option.
* Fix: Lowered character length threshold for live counting keywords from 3 to 2.
* Tested with WordPress 3.3.1.

= 3.2.2 =
* Fix: Messed ignored words list linked to version check bug. Duplicated characters at the beggining and end of each expression should be removed upon upgrade.
* Uniformized three colors scheme for readability values on the posts list and live stats.
* Some default thresholds in the code moved into constants.

= 3.2.1 =
* Fix: Default diagnostics thresholds weren't read.

= 3.2 =
* Feature: New length, readability and keyword density diagnostics for the reports page.
* New settings for diagnostics thresholds.
* Live count now refreshes every 5 seconds.
* Fix: Live stats not updating when the post is empty.
* Fix: Live stats not trimming empty elements was adding 1 to the total count.
* Fix: Period end day wasn't included in the reports page. I. e. freshly published posts weren't counted.
* Fix: Typo in options page.
* Fix: Inconsistent file name (graph_options.php → graph-options.php).
* Fix: Open anchor tag in live stats.
* Compatibility update to WordPress 3.3.
* Deleted extant css file from trunk.
* Settings page template moved to a separate file (view-settings.php).

= 3.1.1 =
* PHP close tags removed to prevent accidental submission of headers.
* Support links added at the bottom of the analytics and options pages.
* Updated Readme.txt.

= 3.1 =
* Feature: Ignored words list now uses regular expressions. Old plain keywords are updated on upgrade.
* Design: Taller ignored words list textbox.
* Design: Options organized by category.
* Fix: Some underscore versus hyphen inconsistencies for internal option names.

= 3.0.5 =
* Fix: Inaccurate total counts on stats page.

= 3.0.4 =
* Fix: Broken live count.

= 3.0.3 =
* Fix: Bug in stats page query when 'Count words from drafts and posts pending review' option was off.
* Fix: Bug displaying saved options.

= 3.0.2 =
* Fix: Covered some analytics page error cases when there is no data within the period.
* Fix: Missing translation string.

= 3.0.1 =
* Fix: Removed operator ?: unsupported in older PHP 5 versions.
* Fix: Description typo.

= 3.0 =
* New premium stats page.
* Allowed all users with access to the stats page to see stats for all authors.
* Readability Index column is calculated when viewing the posts list.
* Fix: Duplicated readability index value when number was a round integer.

== Upgrade Notice ==
Please, upgrade if you are running version 3.2.1 or earlier. Several bugs are fixed in the latest version.
