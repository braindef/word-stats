<?php
/*
Basic String Tools
by: Fran Ontanaya <email@franontanaya.com>
Version: 0.1
License: GPLv2
-------------------------------------------------------------- */

// Find if there is a match for a word in an array of regular expressions
function bst_match_regarray( $regexArray, $text ) {
	foreach( $regexArray as $r ) {
		// Remove unnecessary regexp wrapper
		$r = trim( $r, '/' );
		if ( preg_match( '/\/[g,i]/', substr( $r, strlen( $r ) - 2, 2) ) ) { $r = substr( $r, 0, strlen( $r - 2 ) ); }
		if ( preg_match( '/\/[g,i][g,i]/', substr( $r, strlen( $r ) - 3, 3) ) ) { $r = substr( $r, 0, strlen( $r - 3 ) ); }

		$regex = '/' . $r  . '/i';
		if ( preg_match( $regex,  $text ) ) return true;
	}
	return false;
}

// Strip html tags without gluing words.
function bst_html_stripper ( $text ) {
	$search = array('@<script[^>]*?>.*?</script>@si',  // Strip out javascript
		            '@<[\/\!]*?[^<>]*?>@si',            // Strip out HTML tags
		            '@<style[^>]*?>.*?</style>@siU',    // Strip style tags properly
		            '@<![\s\S]*?--[ \t\n\r]*>@'         // Strip multi-line comments including CDATA
	);
	return preg_replace( $search, ' ', $text );
}

function bst_array_first( $array ) {
	if( !is_array( $array ) || !count( $array ) ) return false;
	return array_shift( array_keys( $array ) );
}

function bst_array_last( $array ) {
	if( !is_array( $array ) || !count( $array ) ) return false;
	return array_pop( array_keys( $array ) );
}

function bst_Ym_to_unix( $month ) {
	if ( !$month ) { return time(); } else { return strtotime( $month . '-01' ); }
}

// REQUIRES PHP 5 and UTF-8
// Note that parsing for non-Latin scripts may be incomplete.
function bst_simple_boundaries( $text ) {

	$bst_regex[ "combining_marks" ] = '[\x{00AD}\x{2010}\x{031C}-\x{0361}\x{20D0}-\x{20F0}\x{1DC0}-\x{1DFF}\x{FE20}-\x{FE26}\x{0483}-\x{0489}\x{A66F}-\x{A67D}\x{0951}-\x{0954}\x{037A}\x{0384}-\x{0385}\x{3099}-\x{309C}\x{30FB}-\x{30FE}]';
		/*
				\x{00AD}\x{2010} // Breaking Hyphens
				\x{031C}-\x{0361} // Combining Diacritical Marks
				\x{20D0}-\x{20F0} // Combining Diacritical Marks for Symbols
				\x{1DC0}-\x{1DFF} // Combining Diacritical Marks Supplement
				\x{FE20}-\x{FE26} // Combining Half Marks
				\x{0483}-\x{0489} // Cyrillic
				\x{A66F}-\x{A67D} // Cyrillic Extended-B
				\x{0951}-\x{0954} // Devaganari
				\x{037A}\x{0384}-\x{0385} // Greek and Coptic
				\x{3099}-\x{309C} // Hiragana
				\x{30FB}-\x{30FE}  // Katakana
				// This list is incomplete.
		*/

		// ToDo: Enable only selected blocks for better performance? It would need a constructor to assemble the regex.
	$bst_regex[ "word_chars" ] = 'A-Za-z0-9\x{FB00}-\x{FB4F}\x{0621}-\x{064A}\x{0660}-\x{0669}\x{066E}-\x{06D3}\x{06D5}\x{06EE}-\x{06FF}\x{FB50}-\x{FBB1}\x{FE80}-\x{FEFC}\x{0750}-\x{077F}\x{20A0}-\x{20CF}\x{0400}-\x{0482}\x{0498}-\x{04FF}\x{2DE0}-\x{2DFF}\x{A640}-\x{A66E}\x{A680}-\x{A697}\x{0500}-\x{0525}\x{0904}-\x{0939}\x{093E}-\x{0950}-\x{0955}-\x{096F}\x{0972}-\x{097F}\x{A8E0}-\x{A8F0}\x{1F200}-\x{1F2FF}\x{10A0}-\x{10FA}\x{0386}\x{0388}-\x{03FF}\x{1F00}-\x{1FBC}\x{1FC2}-\x{1FCC}\x{1FD0}-\x{1FDB}\x{1FE0}-\x{1FEC}\x{1FF2}-\x{1FFC}\x{FF10}-\x{FF19}\x{FF21}-\x{FF3A}\x{FF41}-\x{FF5A}\x{FF66}-\x{FF9D}\x{05D0}-\x{05EA}\x{3040}-\x{3096}\x{30A1}-\x{30FA}\x{00C0}-\x{00D6}\x{00D8}-\x{00F6}\x{00F9}-\x{00FF}\x{0100}-\x{017F}\x{1E00}-\x{1EFF}\x{0180}-\x{024F}\x{2C60}-\x{2C7F}\x{A726}-\x{A787}\x{0D05}-\x{0D39}\x{0D3E}-\x{0D44}\x{1D400}-\x{1D7FF}\x{0710}-\x{072F}\x{074D}-\x{074F}\x{1700}-\x{1714}';

		/*
				'A-Za-z0-9' . // Basic Latin
				'\x{FB00}-\x{FB4F}' . // Alphabetic Presentation Forms (ToDo: Split ligated forms)
				'\x{0621}-\x{064A}\x{0660}-\x{0669}\x{066E}-\x{06D3}\x{06D5}\x{06EE}-\x{06FF}'. // Arabic
				'\x{FB50}-\x{FBB1}' . // Arabic Presentation Forms A
				'\x{FE80}-\x{FEFC}' . // Arabic Presentation Forms B
				'\x{0750}-\x{077F}' . // Arabic Supplement
				'\x{20A0}-\x{20CF}' . // Currency symbols.
				'\x{0400}-\x{0482}\x{0498}-\x{04FF}' . // Cyrillic
				'\x{2DE0}-\x{2DFF}' . // Cyrillic Extended-A
				'\x{A640}-\x{A66E}\x{A680}-\x{A697}' . // Cyrillic Extended-B
				'\x{0500}-\x{0525}' . // Cyrillic Supplement
				'\x{0904}-\x{0939}\x{093E}-\x{0950}-\x{0955}-\x{096F}\x{0972}-\x{097F}' . // Devanagari
				'\x{A8E0}-\x{A8F0}' . // Devanagari Extended
				'\x{1F200}-\x{1F2FF}' . // Enclosed Ideographic Supplement
				'\x{10A0}-\x{10FA}' . // Georgian
				'\x{0386}\x{0388}-\x{03FF}' . // Greek and Coptic
				'\x{1F00}-\x{1FBC}\x{1FC2}-\x{1FCC}\x{1FD0}-\x{1FDB}\x{1FE0}-\x{1FEC}\x{1FF2}-\x{1FFC}' . // Greek Extended
				'\x{FF10}-\x{FF19}\x{FF21}-\x{FF3A}\x{FF41}-\x{FF5A}\x{FF66}-\x{FF9D}' . // Halfwidth and Fullwidth Forms
				'\x{05D0}-\x{05EA}' . // Hebrew
				'\x{3040}-\x{3096}' . // Hiragana
				'\x{30A1}-\x{30FA}' . // Katakana
				'\x{00C0}-\x{00D6}\x{00D8}-\x{00F6}\x{00F9}-\x{00FF}' . // Latin-1 Supplement
				'\x{0100}-\x{017F}' . // Latin Extended-Aignore
				'\x{1E00}-\x{1EFF}' . // Latin Extended Additional
				'\x{0180}-\x{024F}' . // Latin Extended-B
				'\x{2C60}-\x{2C7F}' . // Latin Extended-C
				'\x{A726}-\x{A787}' . // Latin Extended-D
				'\x{0D05}-\x{0D39}\x{0D3E}-\x{0D44}' . // Malayam
				'\x{1D400}-\x{1D7FF}' . // Mathematical Alphanumeric Symbols
				'\x{0710}-\x{072F}\x{074D}-\x{074F}' . // Syriac
				'\x{1700}-\x{1714}'; // Tagalog
			// * This list is incomplete.
		*/

	$bst_regex[ "short_pauses" ] = '[\.]{3}|[;:\x{2026}\x{2015}\x{00B7}\x{0387}]';

	// Make sure HTML entities are decoded
	//$text = html_entity_decode( $text );

	// Replace no break spaces
	$text = preg_replace( '/\x{00A0}|\&nbsp;/u', ' ', $text );

	// Remove combining marks et al, as they are meaningless for this purpose and can split words
	$text = preg_replace( '/' . $bst_regex[ 'combining_marks' ] . '/u', '', $text );

	// Replace ellipsis with commas when not followed by capitalized word
	$text = preg_replace( '/\x{2026}(?=\s[a-z])|[\.]{3}(?=\s[a-z])/u', ',', $text );

	// Typical end of sentence, including unicodes | All remaining ellipsis
	$text = preg_replace( '/[\!\?\.;\x{06D4}\x{203C}\x{2047}-\x{2049}\x{2026}]+|[\.]{3}/u', '.', $text );

	// Replace all remaining short pauses with colons
	$text = preg_replace( '/' . $bst_regex[ 'short_pauses' ] . '/u', ',', $text );

	// Replace non-word characters, save short pauses and end of sentence, with spaces
	$text = preg_replace( '/[^' . $bst_regex[ 'word_chars' ] . ',\.\n]/u', ' ', $text );

	$result[ 'text' ] = $text;
	$result[ 'alphanumeric' ] = preg_replace( '/[^' . $bst_regex[ 'word_chars' ] . ']/u', '', $text );

	return $result;
}

function bst_trim_array( $array ) {
	// Remove the last item if it's empty
	if ( $array[ 0 ] == "" || $array[ 0 ] == "\n" ) { $array = array_slice( $array, 1, count( $array )  ); }
	if ( $array[ count( $array ) - 1 ] == "" || $array[ count( $array ) - 1 ] == "\n" ) { $array = array_slice( $array, 0, count( $array )  - 1 ); }
	return $array;
}

function bst_trim_text( $text ) {
	// Trim spaces
	$text = preg_replace( '/[ ]+[\.\n]/u', '', $text );
	return trim( $text );
}

function bst_split_sentences( $text ) {
	return bst_trim_array( preg_split( '/[\.\n]+/', $text ) );
}

function bst_split_words( $text ) {
	return bst_trim_array( preg_split( '/[ ,\.\n]+/', $text ) );
}

function bst_count_words( $text ) {
	$simplified = bst_simple_boundaries( $text );
	return count( bst_split_words( $simplified[ 'text' ] ) );
}

function bst_split_text( $text ) {
	$simplified = bst_simple_boundaries( $text );
	$stats[ 'text' ] = $simplified[ 'text' ];
	$simplified[ 'text' ] = bst_trim_text( $simplified[ 'text' ] );
	$stats[ 'sentences' ] = bst_split_sentences( $simplified[ 'text' ] );
	$stats[ 'words' ] = bst_split_words( $simplified[ 'text' ] );
	$stats[ 'alphanumeric' ] = $simplified[ 'alphanumeric' ];
	return $stats;
}

// Output a JavaScript version of the class (for WordPress)
function bst_js_string_tools() {
	if ( function_exists( 'plugins_url' ) ) {
		$js_path = plugins_url( 'word-stats/basic-string-tools.js' );
		echo '<script type="text/javascript" src="' . $js_path . '"></script>';
		return true;
	}
	return false;
}

// Return keywords with thresholds
// $ignore is an array of regular expressions
function bst_keywords( $text, $ignore, $top_ratio = 0, $minimum = 0 ) {
	if ( !$text ) { return false; }
	if ( !$ignore ) { $ignore = array(); }

	$text = bst_html_stripper( $text );

	$word_hash = array();
	$top_word_count = 0;
	$stats = bst_split_text( $text );
	$word_array = $stats[ 'words' ];
	// Count keywords
	foreach ( $word_array as $word ) {
		$word = strtolower( $word );

		//if ( !in_array( $word, $ignore ) ) {
		if( !bst_match_regarray( $ignore, $word ) ) {
			if ( strlen( $word ) > 3 ) {
				if ( !$word_hash[ $word ] ) { $word_hash[ $word ] = 0; }
				$word_hash[ $word ]++;
				if ( $word_hash[ $word ] > $top_word_count ) { $top_word_count = $word_hash[ $word ]; }
			}
		}
	}

	if( $top_ratio && $minimum ) {
		// Filter
		$filtered_result = array();
		foreach ( $word_hash as $keyword => $appareances ) {
			if ( $appareances >= $top_word_count / $top_ratio && $appareances > $minimum ) {
				$filtered_result[ $word ] = $appareances;
			}
		}
		return $filtered_result;
	} else {
		return $word_hash;
	}
}

/* EOF */
