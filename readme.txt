=== Plugin Name ===
Contributors: Fran_Ontanaya
Donate link: http://bestseller.franontanaya.com/
Tags: word count, dashboard, readability, keywords
Requires at least: 3.0.0
Tested up to: 3.2
Stable tag: 1.5.5

Adds total and monthly per author word counts, provides a more accurate live word count, displays keywords and readability levels of each post.

== Description ==

Word Stats adds total and monthly per author word counts, stats, keyword counts and readability level indexes for your blog posts, and replaces the live word count from WordPress with a more accurate algorithm.

It counts the number of words in each public post type for each author and month and displays the totals in your dashboard. You can also show the total word counts in your blog with a widget or the &#91;wordcounts&#93; shortcode.

Also, it provides live statistics for the edit post panel:

* Color coded legibility indexes: Automated Readability Index, Coleman-Liau Index and LIX. 
* Total characters, alphanumeric characters, words and sentences.
* Characters per word, characters per sentence, words per sentence.
* Relevant keywords, except those specified to be ignored.

You can display optionally an extra column with the aggregated readability level of each post in the manage posts list.  

It includes i18n translations to Spanish and Catalonian.

= About the legibility indexes =

* ARI is based on word length and words per sentence.
* CLI is based on alphanumeric characters per 100 words and sentences per 100 words
* LIX is based on average words between pauses (periods, colons, semicolons, etc.) and average words longer than 6 characters.

== Installation ==

1. Install it from the plugins admin page or upload the zip with WordPress' built-in tool or unzip it to 'wp-content/plugins'.
2. Activate it.
3. Go to Settings | Word Stats and set up the optional features.

== Frequently Asked Questions ==

= What do the numbers and colors of the readability indexes mean? =

For ARI and CLI, they are the U.S. grade level of the text. Roughly, grade level 1 corresponds to ages 6-8, grade 8 to 14 years old, grade 12 to 17 years old. The color code is 0-8: green; 8-12: yellow; 12-16: orange; 16-20: red; +20: purple.

For LIX:

* below 25: Children's Books (green)
* 25 - 30: Simple texts (green)
* 30 - 40: Normal Text / Fiction (yellow)
* 40 - 50: Factual information, such as Wikipedia (orange)
* 50 - 60: Technical texts (red)
* over 60: Specialist texts / research / dissertations (purple)

= Why didn't you include other common readability indexes? =

These three indexes don't rely on syllable counting, which is a bit more complicated and language dependent. I may try to add them in future versions.

= How accurate are the calculations of the readability indexes? =

Word Stats uses simple algorithms. For fairly ordinary English texts they will closely match human counting; for example, the algorithm for Coleman-Liau produces the same result for the example piece in the Wikipedia article. The margin of error will be greater for short pieces with dashes and apostrophes or in other languages, but they should be still good indicators.

= Do the readability indexes really reflect how easy is the text? = 

They try to reflect how easy the text is to read, not to understand. You can write an article about relativity in simple English and it will be rated as low level. Also, poor writing skills can cheat the indexes; for example by abusing periods or commas.

= Why the readability index column doesn't show any value? =

The values are cached once the post is saved.

= Why the live counters seem to lag? =

The calculations are refreshed every 15 seconds.

= How does Word Stats pick the relevant keywords? =

It shows any keyword that appears at least three times and at least 1/5 times the top keyword.

= When tags are included in the live word count? =

Currently they are included when you load the post. If you add new tags, save the post and reload it. 

= How does Word Stats count sentences? =

Word Stats treats «?», «!», «;» and «.» as sentence limitators. It ignores periods following a capital letter, as in «U.S.A.».

= How does Word Stats count words? =

For the live count, it uses the sentence limitators plus blank spaces. For the total word counts, it uses the PHP function str_word_count. 

= The word counts don't include all public post types =

'Attachment', 'nav_menu_item' and 'revision' are excluded.

= The anchors for the total word counts in the dashboard are blank =

They are just for style consistence.

= Can I style the word counts lists? =

The CSS classes are:

* Container widget li: word-stats-counts-widget
* h2 widget title: word-stats-counts-title
* Widget and shortcode ul of counts: word-stats-counts
* li of all count items (widget and shortcode): word-stats-count
* li of the total word count (widget and shortcode): word-stats-list-total
* li of the total word count (dashboard): word-stats-dash-total

== Screenshots ==

1. Total word counts in the dashboard.
2. Live stats for the post being edited.
3. Extra column showing an aggregate of the readability indexes.
4. Stats page displaying monthly words per author.

== Changelog ==
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
* Added an options page.
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
= 1.2 =
The plugin has been renamed to Word Stats. If you were using Bestseller Total Words, don't forget to delete it.

= 1.0 =
First release. You don't need to use this plugin if you are using Bestseller Theme for WordPress 0.5.0.
