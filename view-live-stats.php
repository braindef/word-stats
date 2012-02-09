<?php

	if( !WS_CURRENT_VERSION ) { exit( __( 'Please, don\'t load this file directly', 'word-stats' ) ); }

	echo '
	<script type="text/javascript">
		function wsRefreshStats() {
			var statusInfo = document.getElementById( "post-status-info" );
			var allText = document.getElementById("content").value;
			allText = bstHtmlStripper( allText );
			var totalCharacters = 0;
			var totalWords = 0;
			var totalSentences = 0;
			var totalAlphanumeric = 0;
			var charsPerWord = 0;
			var charsPerSentence = 0;
			var wordsPerSentence = 0;
			var ARItext = 0;
			var CLItext = 0;
			var LIXtext = 0;
			var wordArray = new Array();
			var stats = new Array();
			var temp = "";
			if ( allText ) {
				totalCharacters = allText.length;
				stats = bstSplitText( allText );
				allText = stats[ "text" ];
				totalAlphanumeric = stats[ "alphanumeric"].length;
				totalSentences = stats[ "sentences" ].length;
				totalWords = stats[ "words" ].length;
				wordArray = stats[ "words"].slice( 0 ); /* array copy kludge */
				delete stats;
			}
			if ( totalWords > 0 && totalSentences > 0 ) {
				charsPerWord = ( totalAlphanumeric / totalWords );
				charsPerWord = charsPerWord.toFixed( 0 );
				charsPerSentence = ( totalAlphanumeric / totalSentences );
				charsPerSentence = charsPerSentence.toFixed( 0 );
				wordsPerSentence = ( totalWords / totalSentences );
				wordsPerSentence = wordsPerSentence.toFixed( 0 );
				densityDivisor = ( totalWords / 1000 );
				densityDivisor = densityDivisor.toFixed( 0 );
				if ( densityDivisor < 1 ) { densityDivisor = 1; }

				/* Automated Readability Index */
				var ARI = 4.71 * ( totalAlphanumeric / totalWords ) + 0.5 * ( totalWords / totalSentences ) - 21.43;
				ARI = ARI.toFixed( 1 );
				if ( ARI < ', WS_RI_BASIC, ' ) { ARItext = \'<span style="color: #06a;">\' + ARI + "</span>"; }
				if ( ARI >= ', WS_RI_BASIC, ' && ARI < ', WS_RI_ADVANCED, ' ) { ARItext = \'<span style="color: #0a6;">\' + ARI + "</span>"; }
				if ( ARI >= ', WS_RI_ADVANCED, ' ) { ARItext = \'<span style="color: #c36;">\' + ARI + "</span>"; }

				/* Coleman-Liau Index */
				var CLI = 5.88 * ( totalAlphanumeric / totalWords ) - 29.6 * ( totalSentences / totalWords ) - 15.8;
				CLI = CLI.toFixed( 1 );
				if ( CLI < ', WS_RI_BASIC, ' ) { CLItext = \'<span style="color: #06a;">\' + CLI + "</span>"; }
				if ( CLI >= ', WS_RI_BASIC, ' && CLI < ', WS_RI_ADVANCED, ' ) { CLItext = \'<span style="color: #0a6;">\' + CLI + "</span>"; }
				if ( CLI >= ', WS_RI_ADVANCED, ' ) { CLItext = \'<span style="color: #c36;">\' + CLI + "</span>"; }

				/* LIX */
				var LIXlongwords = 0;
				for (var i = 0; i < wordArray.length; i=i+1 ) {
					if ( wordArray[ i ].length > 6 ) { LIXlongwords = LIXlongwords + 1; }
				}
				temp = allText.split( /[,;\.\(\:]/ );
				var LIX = totalWords / temp.length + ( LIXlongwords * 100 ) / totalWords;
				LIX = LIX.toFixed( 1 );
				if ( LIX < 30 ) { LIXtext = \'<span style="color: #06a;">\' + LIX + "</span>"; }
				if ( LIX >= 30 && LIX < 55 ) { LIXtext = \'<span style="color: #0a6;">\' + LIX + "</span>"; }
				if ( LIX >= 55 ) { LIXtext = \'<span style="color: #c36;">\' + LIX + "</span>"; }

				temp = "";';
				if ( get_option( 'word_stats_show_keywords' ) || !Word_Stats_Core::is_option( 'word_stats_show_keywords' ) ) {

					echo '
					/* Find keywords */
					var wordHash = new Array;
					var topCount = 0; ';

					if ( get_option( 'word_stats_ignore_keywords' ) ) {
						echo 'var ignKeywords = "' , strtolower( str_replace( "\r", '', str_replace( "\n", '::', get_option( 'word_stats_ignore_keywords' ) ) ) ), '";
					ignKeywords = ignKeywords.split( "::" );';
					} else {
						echo 'var ignKeywords = new Array;';
					}

					echo '
					for (var i = 0; i < wordArray.length; i = i + 1) {
						wordArray[i] = wordArray[i].toLowerCase();
						if ( !bstMatchRegArray( ignKeywords, wordArray[i] ) ) {
						if ( wordArray[i].length > 2 ) {
							if ( !wordHash[ wordArray[i] ] ) { wordHash[ wordArray[i] ] = 0; }
							wordHash[ wordArray[i] ] = wordHash[ wordArray[i] ] + 1;
							if ( wordHash[ wordArray[i] ] > topCount ) { topCount = wordHash[ wordArray[i] ]; }
						}
					}
					}';

					// Add tags. Note $post has been declared global above.
					if ( get_option( 'word_stats_add_tags' ) && get_the_tags( $post->ID ) ) {
						echo '/* Add last saved tags */', "\n";
						foreach ( get_the_tags( $post->ID ) as $tag ) {
							$tag->name = strtolower( esc_attr( $tag->name ) );
							if ( strlen( $tag->name ) > 3 ) {
								echo '
										if ( !wordHash[ "', $tag->name, '" ] ) { wordHash[ "', $tag->name, '" ] = 0; }
										wordHash[ "', $tag->name, '" ] = wordHash[ "', $tag->name, '" ] + 1;
										if ( wordHash[ "', $tag->name, '" ] > topCount ) { topCount = wordHash[ "', $tag->name, '" ]; }
								';
							}
						}
					}

					$threshold_no_keywords = ( !Word_Stats_Core::is_option( 'word_stats_diagnostic_no_keywords' ) ) ? WS_NO_KEYWORDS : get_option( 'word_stats_diagnostic_no_keywords' );
					$threshold_spammed_keywords = ( !Word_Stats_Core::is_option( 'word_stats_diagnostic_spammed_keywords' ) ) ? WS_SPAMMED_KEYWORDS : get_option( 'word_stats_diagnostic_spammed_keywords' );
					if ( $threshold_no_keywords < 1 ) { $threshold_no_keywords = 1; }
					if ( $threshold_spammed_keywords < 1 ) { $threshold_spammed_keywords = 1; }

					echo '
					for ( var j in wordHash ) {
						if ( wordHash[j] / densityDivisor > ';
						echo $threshold_no_keywords;
					echo' ) {
							if ( wordHash[j]  / densityDivisor >= ';
							echo $threshold_spammed_keywords;
							echo ' ) {
								temp = temp + \'<span style="font-weight:bold; color:#c30;">\' + j + " (" + wordHash[j] + ")</span> ";
							} else if ( wordHash[j]  / densityDivisor > ';
							echo intval( $threshold_spammed_keywords - ( ( $threshold_spammed_keywords - $threshold_no_keywords ) / 1.5 ) );
							echo ' ) {
								temp = temp + \'<span style="font-weight:bold;color:#090;">\' + j + " (" + wordHash[j] + ")</span> ";
							} else {
								temp = temp + j + " (" + wordHash[j] + ") ";
							}
						}
					}

					if ( temp == "" ) {
						temp = "<br><strong>', esc_attr( __( 'Keywords:', 'word-stats' ) ), '</strong><br>', esc_attr( __( 'No relevant keywords.', 'word-stats' ) ), '";
					} else {
						temp = "<br><strong>', esc_attr( __( 'Keywords:', 'word-stats' ) ), '</strong><br>" + temp;
					} }';
				}
				echo '	if ( statusInfo.innerHTML.indexOf( "edit-word-stats" ) < 1 ) {
						statusInfo.innerHTML = statusInfo.innerHTML + "<tbody><tr><td id=\'edit-word-stats\' style=\'padding-left:7px; padding-bottom:4px;\' colspan=\'2\'><strong>', esc_attr( __( 'Readability:', 'word-stats' ) ), '</strong><br><a title=\'Automated Readability Index\'>ARI</a>: " + ARItext + "&nbsp; <a title=\'Coleman-Liau Index\'>CLI</a>: " + CLItext + "&nbsp; <a title=\'Läsbarhetsindex\'>LIX</a>: " + LIXtext ';
				if ( get_option( 'word_stats_averages' ) || !Word_Stats_Core::is_option( 'word_stats_averages' ) ) {
					echo '+ "<br>" + totalCharacters + " ', esc_attr( __( 'characters', 'word-stats' ) ),
						'; " + totalAlphanumeric + " ', esc_attr( __( 'alphanumeric characters', 'word-stats' ) ),
						'; " + totalWords + " ', esc_attr( __( 'words', 'word-stats' ) ),
						'; " + totalSentences + " ', esc_attr( __( 'sentences', 'word-stats' ) ),
						'.<br>" + charsPerWord + " ', esc_attr( __( 'characters per word', 'word-stats' ) ),
						'; " + charsPerSentence + " ', esc_attr( __( 'characters per sentence', 'word-stats' ) ),
						'; " + wordsPerSentence + " ', esc_attr( __( 'words per sentence', 'word-stats' ) ), '."';
				}
				echo ' + temp + "</td></tr></tbody>";
					} else {
					 	document.getElementById( "edit-word-stats").innerHTML = "<strong>', esc_attr( __( 'Readability:', 'word-stats' ) ), '</strong><br><a title=\'Automated Readability Index\'>ARI</a>: " + ARItext + "&nbsp; <a title=\'Coleman-Liau Index\'>CLI</a>: " + CLItext + "&nbsp; <a title=\'Läsbarhetsindex\'>LIX</a>: " + LIXtext ';
				if ( get_option( 'word_stats_averages' ) || !Word_Stats_Core::is_option( 'word_stats_averages' ) ) {
					echo '+ "<br>" + totalCharacters + " ', esc_attr( __( 'characters', 'word-stats' ) ),
						'; " + totalAlphanumeric + " ', esc_attr( __( 'alphanumeric characters', 'word-stats' ) ),
						'; " + totalWords + " ', esc_attr( __( 'words', 'word-stats' ) ),
						'; " + totalSentences + " ', esc_attr( __( 'sentences', 'word-stats' ) ),
						'.<br>" + charsPerWord + " ', esc_attr( __( 'characters per word', 'word-stats' ) ),
						'; " + charsPerSentence + " ', esc_attr( __( 'characters per sentence', 'word-stats' ) ),
						'; " + wordsPerSentence + " ', esc_attr( __( 'words per sentence', 'word-stats' ) ), '."';
				}
				echo ' + temp;
					}';

				// Replace WordPress' word count
				if ( get_option( 'word_stats_replace_word_count' ) || !Word_Stats_Core::is_option( 'word_stats_replace_word_count' ) ) {
					echo '
					if ( document.getElementById( "wp-word-count") != null ) { /* WP 3.2 */
						document.getElementById( "wp-word-count").innerHTML = "' . __( 'Word count:' ) . ' " + totalWords + " <small>' . __( '(Word Stats plugin)', 'word-stats' ) . '</small>";
					}
					if ( document.getElementById( "word-count") != null ) { /* WP 3.0 */
						document.getElementById( "word-count").innerHTML = totalWords;
					}';
				}
			echo '
		}

		var statsTime = setInterval( "wsRefreshStats()", 5000 );
		wsRefreshStats();

	</script>';

/* EOF */
