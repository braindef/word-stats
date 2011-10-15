=== Plugin Name ===
Contributors: Fran_Ontanaya
Donate link: http://bestseller.franontanaya.com/
Tags: word count, dashboard, readability, keywords, words, statistics, reports
Requires at least: 3.0.0
Tested up to: 3.2.1
Stable tag: 3.0

A suit of word counters, keyword counters and readability analysis displays for your blog.

== Description ==

Word Stats adds a suit of linguistic statistics to help you keep track of your content and improve its quality.

It counts the number of words in each public post type for each author and month. The results are displayed in an analytics page, and can be added to your dashboard, widget areas and inside your posts with the &#91;wordcounts&#93; shortcode.

It extends the info area of the post edit form with these live stats:

* Relevant keywords; common words can be blacklisted in the settings page.
* A more accurate word count.
* Color coded readability tests: Automated Readability Index, Coleman-Liau Index and LIX.
* Total characters, alphanumeric characters, words and sentences.
* Characters per word, characters per sentence, words per sentence.

The aggregated readability level of each post can be displayed in an extra column in the manage posts list.

Since version 2.0, Word Stats includes basic support for Unicode scripts, including cyrillic, greek, arabic, hindi and japanese (mileage may vary).

Spanish and Catalan translations are bundled with the plugin.

= About the readability tests =

* ARI is based on word length and words per sentence.
* CLI is based on characters per 100 words, excluding non-word characters, and sentences per 100 words
* LIX is based on average words between pauses (periods, colons, semicolons, etc.) and average words longer than 6 characters.

Check [http://en.wikipedia.org/wiki/Readability_test](http://en.wikipedia.org/wiki/Readability_test) for more information.

== Installation ==

1. Install it from the plugins admin page or upload the zip with WordPress' built-in tool or unzip it to 'wp-content/plugins'.
2. Activate it.
3. Go to Settings | Word Stats and set up the optional features.

== Frequently Asked Questions ==

= About the readability tests =

**What do the numbers and colors mean?**

For ARI and CLI, they are the U.S. grade level of the text. Roughly, grade level 1 corresponds to ages 6-8, grade 8 to 14 years old, grade 12 to 17 years old. The color code is 0-8: green; 8-12: yellow; 12-16: orange; 16-20: red; +20: purple.

For LIX:

* below 25: Children's Books (green)
* 25 - 30: Simple texts (green)
* 30 - 40: Normal Text / Fiction (yellow)
* 40 - 50: Factual information, such as Wikipedia (orange)
* 50 - 60: Technical texts (red)
* over 60: Specialist texts / research / dissertations (purple)

**Why other common tests aren't included?**

These three tests don't rely on syllable counting, which is a bit more complicated and language dependent. I may try to add them in future versions.

**How accurate are the tests?**

Word Stats uses simple algorithms. For fairly ordinary English texts they will closely match human counting; for example, the algorithm for Coleman-Liau produces the same result for the example piece in the Wikipedia article. The margin of error will be greater for short pieces with dashes and apostrophes or in other languages, but they should be still good indicators.

**Do the tests really reflect how easy is the text?**

They try to reflect how easy the text is to read. You can write an article about relativity in simple English and it will be rated as low level. Also, poor writing skills can cheat the indexes; for example by abusing periods or commas.

**The readability index column doesn't show any value after installing the plugin**

The values are cached once each post is saved. A feature to recalculate all manually is already in the To Do list.

= About the live stats =

**Why the live counters seem to lag?**

The calculations are refreshed every 15 seconds.

**How does Word Stats pick the relevant keywords?**

It shows any keyword that appears at least three times and at least 1/5 times the top keyword. Words blacklisted in the settings page are excluded.

Post tags can be counted optionally as keywords. They are added when the post is loaded. If you add new tags, save the post and reload it.

= About the counting algorithm =

**Why the live word count doesn't match the saved stats?**

The live count uses the JavaScript regex engine (ECMA-262), while the saved stats uses PHP's Perl Compatible Regular Expressions. Also the JavaScript code uses the browser to strip HTML tags, while the PHP code uses an internal function. There are small differences on how they process the text.

This may be fixed in future versions by replacing the JavaScript functions with AJAX calls.

**The word counts don't include all public post types**

'Attachment', 'nav_menu_item' and 'revision' are excluded.

= About the statistics displays =

**The anchors for the total word counts in the dashboard are blank**

They are just for style consistence.

== Screenshots ==

1. Analytics page.
2. Total word counts in the dashboard.
3. Live stats for the post being edited.
4. Extra column showing an aggregate of the readability indexes.

== Changelog ==
= 3.0 =
* New premium stats page.
* Allowed all users with access to the stats page to see stats for all authors.
* Readability Index column is calculated when viewing the posts list.
* Fix: Duplicated readability index value when number was a round integer.

= 2.2 =
* Fix: Wrong values in Readability Index column (missing multibyte support).

= 2.1 =
* Fix: Broken upgrade.

= 2.0 =
* Fix: Readability Index column values were being displayed in all custom columns.
* Fix: Word count replacement for WP +3.2.
* Added code to deal with non-Latin Unicode scripts.
* Upgraded text splitting functions.
* Updated readme.txt.

= 1.5.6 =
* Fix: Missing before_widget and after_widget support.

= 1.5.5 =
* Added option to disable counting words from drafts and posts pending review.
* Fixed bug when counting words for the first time.
* Tested on WordPress 3.2-beta2

= 1.5.4 =
* Now contributor and Author users can view their own monthly word stats. Admin and Editor can view monthly stats from all users.
* Word count stats are calculated for drafts and pending posts too.
* Tested with WP 3.1.1

= 1.5.3 =
* Optionally adds the last saved tags to the live keyword count.
* Removed separate counts for words with or without capitals.

= 1.5.2 =
* Lists the word counts per author and post type. Fixes unsorted dates issue.

= 1.5.1 =
* Forces a word count if the stats page is viewed before any post has been saved.

= 1.5 =
* Added stats page with monthly word counts per author.
* Functions wrapped in classes to prevent name collisions.

= 1.4.4 =
* All HTML output converted to PHP strings.

= 1.4.3 =
* Replaces by default the live word count from WordPress.
* Added missing translation strings.

= 1.4.2 =
* Option to disable live character/word/sentences averages.
* Compatibility bump for WordPress 3.0.4.
* Removed one line break outside the script.

= 1.4.1 =
* Increased ignored keywords textarea size.
* Increased minimum count for relevant keywords to 3 and lowered relevancy threshold to 1/5 of the top keyword.
* Fixed live stats not loading in new post page.

= 1.4 =
* Added keyword live count and ignore keywords option.
* Live stats script now loads only when editing a post.
* Now HTML no-break spaces are processed too.
* Fixed typo in Spanish translation file.

= 1.3 =
* Stats are cached as post metadata when the post is saved.
* Added a Readability Index column to the edit posts list that shows an aggregate of all indexes.
* Fixed a short PHP open tag.
* Added a settings page.
* Added option to disable R.I. column.
* Added option to disable total word counts.

= 1.2 =
* Counts words from any registered public post type, including any custom post types.
* Displays word counts per post type, plus the total word count.
* Added widget to display the word counts.
* Added shortcode to display the word counts.
* Renamed to Word Stats.
* Added statistics and readability indexes to the edit post panel.
* Added new screenshots.

= 1.1 =
* Counts words from stories custom post type.

= 1.0 =
* First release.

== Upgrade Notice ==
= 2.0 =
Please, note that the new text splitting code makes a more intensive use of regex and the performance hasn't been tested in stressful conditions
