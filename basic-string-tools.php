<?php
/*
Basic String Tools
by: Fran Ontanaya <email@franontanaya.com>
Version: 0.7
License: GPLv2
-------------------------------------------------------------- */

/* Changelog
= 0.7 =
* Strip shortcodes. Should be optional in the future.
* Renamed bst_html_stripper to bst_strip_html

= 0.6 =
* Replaced html_entity_decode with our own entity dictionary.
* Several fixes/improvements to the text simplification regex patterns.
* Added Chinese ideograms block to the list of word characters.
* Replaced the function to trim arrays by bst_array_remove_empty.

= 0.5 =
* Added Is_array check to first argument in bst_match_regarray
* &nbsp; is replaced by normal spaces before running html_decode_entities, instead of after. Seems to get rid of a bug in which word counting for texts with a &nbsp; would fail.

= 0.4 =
* Added \/| to the last preg_replace in simple boundaries to take care of forward slashes.
* keywords are trimmed before counting to catch some cases of trailing spaces/line breaks.
* Added support for in word punctuation marks when splitting words.
* Added bst_htmlentities_decode.
* Split ignored keywords filter to bst_regfilter_keyword_counts
* Removed deprecated arguments from bst_keywords
* bst_keywords and bst_strip_html now can take $charset
* Added trim to bst_strip_html

= 0.3 =
* Added bst_get_common_words() with English and Spanish lists.

= 0.2 =
* Fix: Quote entities being counted as keywords.

= 0.1 =
* First release.
*/

function bst_split_text( $text ) {
	$simplified = bst_simple_boundaries( $text );
	$stats[ 'text' ] = $simplified[ 'text' ];
	$simplified[ 'text' ] = bst_trim_text( $simplified[ 'text' ] );
	$stats[ 'sentences' ] = bst_split_sentences( $simplified[ 'text' ] );
	$stats[ 'words' ] = bst_split_words( $simplified[ 'text' ] );
	$stats[ 'alphanumeric' ] = $simplified[ 'alphanumeric' ];
	return $stats;
}

// REQUIRES PHP 5 and UTF-8
// Note that parsing for non-Latin scripts may be incomplete.
function bst_simple_boundaries( $text ) {

	// ToDo: Enable only selected blocks for better performance
	$bst_regex[ "combining_marks" ] = '[\x{00AD}\x{2010}\x{031C}-\x{0361}\x{20D0}-\x{20F0}\x{1DC0}-\x{1DFF}\x{FE20}-\x{FE26}\x{0483}-\x{0489}\x{A66F}-\x{A67D}\x{0951}-\x{0954}\x{037A}\x{0384}-\x{0385}\x{3099}-\x{309C}\x{30FB}-\x{30FE}]';
	$bst_regex[ "word_chars" ] = 'A-Za-z0-9\x{FB00}-\x{FB4F}\x{0621}-\x{064A}\x{0660}-\x{0669}\x{066E}-\x{06D3}\x{06D5}\x{06EE}-\x{06FF}\x{FB50}-\x{FBB1}\x{FE80}-\x{FEFC}\x{0750}-\x{077F}\x{20A0}-\x{20CF}\x{0400}-\x{0482}\x{0498}-\x{04FF}\x{2DE0}-\x{2DFF}\x{A640}-\x{A66E}\x{A680}-\x{A697}\x{0500}-\x{0525}\x{0904}-\x{0939}\x{093E}-\x{0950}-\x{0955}-\x{096F}\x{0972}-\x{097F}\x{A8E0}-\x{A8F0}\x{1F200}-\x{1F2FF}\x{10A0}-\x{10FA}\x{0386}\x{0388}-\x{03FF}\x{1F00}-\x{1FBC}\x{1FC2}-\x{1FCC}\x{1FD0}-\x{1FDB}\x{1FE0}-\x{1FEC}\x{1FF2}-\x{1FFC}\x{FF10}-\x{FF19}\x{FF21}-\x{FF3A}\x{FF41}-\x{FF5A}\x{FF66}-\x{FF9D}\x{05D0}-\x{05EA}\x{3040}-\x{3096}\x{30A1}-\x{30FA}\x{00C0}-\x{00D6}\x{00D8}-\x{00F6}\x{00F9}-\x{00FF}\x{0100}-\x{017F}\x{1E00}-\x{1EFF}\x{0180}-\x{024F}\x{2C60}-\x{2C7F}\x{A726}-\x{A787}\x{0D05}-\x{0D39}\x{0D3E}-\x{0D44}\x{1D400}-\x{1D7FF}\x{0710}-\x{072F}\x{074D}-\x{074F}\x{1700}-\x{1714}\x{4E00}-\x{9fBE}';
	$bst_regex[ 'in_word_marks' ] = '·\'';
	$bst_regex[ "short_pauses" ] = '[\.]{3}|[;:\x{2026}\x{2015}\x{0387}]';

	# Decode entities.
	$text= bst_html_entity_decode( $text );

	# Replace some special characters
	$text = preg_replace( '/\x{00A0}|[\_”“’\"=\[\]\\\\\+\-\/]/u', ' ', $text );

	# Remove combining marks et al, as they are meaningless for this purpose and can split words
	$text = preg_replace( '/' . $bst_regex[ 'combining_marks' ] . '/u', '', $text );

	# Replace ellipsis with commas when not followed by capitalized word
	$text = preg_replace( '/\x{2026}(?=\s[a-z])|[\.]{3}(?=\s[a-z])/u', ',', $text );

	# Typical end of sentence, including unicodes | All remaining ellipsis
	$text = preg_replace( '/[\!\?\.;\x{06D4}\x{203C}\x{2047}-\x{2049}\x{2026}]+|[\.]{3}/u', '.', $text );

	# Replace all remaining short pauses with colons
	$text = preg_replace( '/' . $bst_regex[ 'short_pauses' ] . '/u', ',', $text );

	# Replace non-word characters, save short pauses, with spaces
	$text = preg_replace( '/[^' . $bst_regex[ 'word_chars' ] . $bst_regex[ 'in_word_marks' ] .  ',\.\n ]/u', ' ', $text );

	# Replace in word chars not in word. I.e. that's, paral·lel
	$text = preg_replace( '/(?<=[^A-Za-z])[' . $bst_regex[ 'in_word_marks' ] .  '](?=[^A-Za-z])/u', ' ', $text );

	$result[ 'text' ] = $text;
	$result[ 'alphanumeric' ] = preg_replace( '/[^' . $bst_regex[ 'word_chars' ] . ']/u', '', $text );

	return $result;
}

# Find if there is a match for a word in an array of regular expressions
function bst_match_regarray( $regex_array, $text ) {
	if ( !is_array( $regex_array ) ) { return false; }
	foreach( $regex_array as $r ) {
		# Remove unnecessary regexp wrapper
		$r = trim( $r, '/' );
		if ( preg_match( '/\/[g,i]/', substr( $r, strlen( $r ) - 2, 2) ) ) { $r = substr( $r, 0, strlen( $r - 2 ) ); }
		if ( preg_match( '/\/[g,i][g,i]/', substr( $r, strlen( $r ) - 3, 3) ) ) { $r = substr( $r, 0, strlen( $r - 3 ) ); }

		$regex = '/' . $r  . '/ui';
		if ( preg_match( $regex,  $text ) ) return true;
	}
	return false;
}

# Decode HTML entities using our own dictionary.
# When we think we found an entity, we do a replace for that specific one.
function bst_html_entity_decode( $text ) {
	$entities = bst_get_entities_hash();
	$offset = 0;
	$new_text = $text;
	for ( $i=0; $i < 10000; $i++ ) {
		preg_match( '/(?<=\&)[A-Za-z]+(?=;)/', $text, $result, PREG_OFFSET_CAPTURE, $offset );
		if ( $result[0][0] )  {
			$offset = (int) $result[0][1] + 1;
			if ( $entities[ $result[0][0] ] ) {
				$pattern = '&' . preg_quote( $result[0][0] ) . ';';
				$new_text = str_replace( $pattern, $entities[ $result[0][0] ], $new_text );
			}
		} else {
			break;
		}
	}
	$text = $new_text;
	return $text;
}

# Strip HTML tags without gluing words. We assume the HTML is not encoded into entities.
function bst_strip_html( $text, $charset = null ) {
	$search = array( '@<script[^>]*?>.*?</script>@si',  # Strip out javaScript
		            '@<[\/\!]*?[^<>]*?>@si',            # Strip out HTML tags
		            '@<style[^>]*?>.*?</style>@siU',    # Strip style tags properly
		            '@<![\s\S]*?--[ \t\n\r]*>@'         # Strip multi-line comments including CDATA
	);
	$text = preg_replace( $search, ' ', $text );
	return trim( $text );
}

# Strip shortcodes, e.g. WordPress, bbCodes, etc.
function bst_strip_shortcodes( $text ) {
    return preg_replace( '@\[[a-zA-Z0-9\s\"\-\_]+\]]@si', ' ', $text );
}

# Helper functions
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

# Return an array with the most common functional words for the specified language.
function bst_get_common_words( $lang ) {
	switch( $lang ) {
		case 'en':
			$patterns =  array(
				'^about$', '^above$', '^after$', '^again$', '^ago$', '^all$', '^almost$', '^along$', '^already$', '^also$', '^although$', '^always$', '^a[nm]$', '^among$', '^an[dy]$', '^another$',  '^anybody$', '^anything$', '^anywhere$', '^are(n\'t)?$', '^around$', '^a[st]$', '^back$', '^else$', '^be(en)?$', '^before$', '^being$', '^below$', '^beneath$', '^beside$', '^between$', '^beyond$', '^billions?$', '^both$', '^each$', '^but$', '^by$', '^can(n\'t)?$', '^could(n\'t)?$', '^did(n\'t)?$', '^do(n\'t)?$', '^does(n\'t)?$', '^doing$', '^done$', '^down$', '^during$', '^eight(een)?$', '^eighth$', '^eighty$', '^either$', '^eleven$', '^enough$', '^even$', '^ever$', '^every$', '^everybody$', '^everyone$', '^everything$', '^everywhere$', '^except$', '^far$', '^few$', '^fewer$', '^fifteen$', '^fifth$', '^fifty$', '^first$', '^five$', '^for$', '^forty$', '^four(teen)?$', '^fourth$', '^hundreds?$', '^from$', '^gets?$', '^getting$', '^got$', '^ha[sd](n\'t)?$', '^have(n\'t)?$', '^having$', '^he$', '^he\'d$', '^he\'ll$', '^hence$', '^her[es]?$', '^herself$', '^he\'s$', '^him(self)?$', '^his$', '^hither$', '^how(ever)?$', '^near$', '^I\'[dm]$', '^i[fnst]$'. '^I\'ll$', '^into$',  '^I\'ve$', '^isn\'t$', '^its(elf)?$', '^it\'s$', '^last$', '^less$', '^many$', '^me$', '^may$', '^might$', '^millions?$', '^[mn]ine$', '^m[uo]st$', '^much$', '^mustn\'t$', '^my(self)?$', '^near([lb]y)?$',  '^neither$', '^never$', '^next$', '^nineteen$', '^ninety$', '^ninth$', '^no[rtw]?$', '^nobody$', '^n?one$', '^noone$', '^nothing$', '^nowhere$', '^off?$', '^often$', '^o[rn]$', '^once$',  '^only$', '^others?$', '^ought(n\'t)?$', '^ours?$', '^ourselves$', '^out$', '^over$', '^quite$', '^rather$', '^round$', '^seconds?$', '^seven(teen)?$', '^seventh$', '^seventy$', '^shall$', '^shan\'t$','^she\'d$', '^she$', '^she\'ll$', '^she\'s$', '^should(n\'t)?$', '^since$', '^six(teen)?$', '^sixteenth$', '^sixth$', '^sixtieth$', '^sixty$', '^so(me)?$', '^somebody$', '^someone$', '^something$', '^sometimes$', '^somewhere$', '^soon$', '^still$', '^such$', '^ten(th)?$', '^tha[nt]$', '^that\'s$', '^the(ir)?$', '^theirs$', '^themselves$', '^the[rs]e$', '^thence$', '^therefore$', '^they\'d$', '^they\'ll$', '^they\'re$', '^the[mny]$', '^third$', '^thirteen$', '^thirty$', '^this$', '^those$', '^though$', '^thousands?$', '^three$', '^thrice$', '^through$', '^thus$', '^till$', '^too?$', '^towards$', '^today$', '^tomorrow$', '^twelve$', '^twentieth$', '^twenty$', '^twice$', '^two$', '^under(neath)?$', '^unless$', '^until$', '^u[ps]$', '^very$', '^when$', '^was(n\'t)?$', '^we$', '^we\'d$', '^we\'ll$', '^were$', '^we\'re$', '^weren\'t$', '^we\'ve$', '^what$', '^whence$', '^where(as)?$', '^which$', '^while$', '^whom?$', '^whose$', '^why$', '^will$', '^with(in)?$', '^without$', '^won\'t$', '^would(n\'t)?$', '^ye[st]$', '^yesterday$', '^your?$', '^you\'d$', '^you\'ll$', '^you\'re$', '^yours(elf)?$', '^yourselves$', '^you\'ve$', '^www$', '^com$', '^org$', '^net$', '^more$', '^http$'
			);
			break;
		case 'es':
			$patterns = array(
				'^algo$', '^alguien$', '^alg(u|ú)n$', '^algun[oa]s?$', '^a(ll|h|qu)í$', '^a(ll|c)á$', '^bastante$', '^cerca$', '^de$', '^demasiad[oa]s?$', '^(é|e)l$', '^ell[oa]s?$', '^entonces$', '^es[oa]s?$', '^este$', '^est[ao]s?$', '^hay$', '^lejos$', '^l[oa]s?$', '^menos$', '^[sm](i|í)$', '^mi[ao]?s$', '^much[oa]s?$', '^muy$', '^nada$', '^nadie$', '^ningun[ao]s?$', '^no$', '^nunca$', '^otr[oa]s?$', '^poc[oa]s?$', '^porque$', '^qu(e|é)$', '^siempre$', '^sus?$', '^tan$', '^tant[oa]s?$', '^teng[oa]$', '^todavía$', '^tod[oa]s?$', '^t(u|ú)$', '^tus$', '^un[oa]s?$', '^usted$', '^ustedes$', '^[vn]uestr[ao]s?$', '^[nv]osotros$', '^yo$', '^al$', '^lado$', '^alrededor$', '^antes$', '^través$', '^bajo$', '^cada$', '^cerca$', '^c(ó|o)mo$', '^con$', '^contra$', '^del?$', '^desde$', '^después$', '^detrás$', '^durante$', '^entre$', '^vez$', '^fuera$', '^hacia$', '^hasta$', '^para$', '^por$', '^según$', '^sin$', '^sobre$', '^s(o|ó)lo$', '^tras$', '^ser$', '^estar$', '^es$', '^son$', '^están?$', '^serían?$', '^estarían?$', '^qui(e|é)n(es)?$', '^cu(a|á)l(es)?$', '^a?d(o|ó)nde$', '^cu(a|á)ndo$', '^www$', '^com$', '^org$', '^net$', '^m(a|á)s$', '^pero$', '^forma$', '^puede[ns]?$', '^podía[ns]?$', '^tiene[ns]?$', '^http$', '^parte$', '^ciert[oa]$', '^cualquiera?$', '^también$', '^hace[rsn]?$', '^había[sn]?$', '^estaba[sn]?$', '^aqu(e|é)l(los|las?)?$', '^tenía[sn]?$', '^así$', '^aunque$', '^a(u|ú)n$', '^dos$', '^tres$', '^cuatro$', '^cinco$', '^seis$', '^siete$', '^ocho$', '^nueve$'
			);
			break;
		default:
			$patterns = false;
			break;
	}
	return $patterns;
}

/*
	Functions to remove empty values from arrays
*/
function bst_array_remove_empty( $array ) {
	return array_filter( $array, '_bst_remove_empty' );
}

function _bst_remove_empty( $value ) {
	return $value != "";
}

function bst_trim_text( $text ) {
	# Trim spaces
	$text = preg_replace( '/[ ]+(?=[\.\n])/u', '', $text );
	return trim( $text );
}

function bst_split_sentences( $text ) {
#	return bst_trim_array( preg_split( '/[\.\n]+/', $text ) );
	return bst_array_remove_empty( preg_split( '/[\.\n]+/', $text ) );
}

function bst_split_words( $text ) {
#	return bst_trim_array( preg_split( '/[ ,\.\n]+/', $text ) );
	return bst_array_remove_empty( preg_split( '/[ ,\.\n]+/', $text ) );
}

function bst_count_words( $text ) {
	$simplified = bst_simple_boundaries( $text );
	return count( bst_split_words( $simplified[ 'text' ] ) );
}

# Output a JavaScript version of the class (for WordPress)
function bst_js_string_tools() {
	if ( function_exists( 'plugins_url' ) ) {
		$js_path = plugins_url( 'word-stats/basic-string-tools.js' );
		echo '<script type="text/javascript" src="' . $js_path . '"></script>';
		return true;
	}
	return false;
}

/*
	Return keywords with thresholds
	$ignore is an array of regular expressions
	$minimum is the minimum appareances to return a keyword
	$ratio, if specified, applies the minimum to that word count; i.e. minimum 2, ratio = 1000 requires 2 appareances per 1000 words.
*/
function bst_keywords( $text, $minimum = 0, $charset = 'UTF-8' ) {
	# No funky divisions
	if ( $ratio < 0 ) { $ratio = 0; }

	if ( !$text ) { return false; }
	if ( !$ignore ) { $ignore = array(); }
	$text = bst_strip_html( $text, $charset );
	$text = bst_strip_shortcodes( $text );

	$word_hash = array();
	$top_word_count = 0;
	$stats = bst_split_text( $text );
	$word_array = $stats[ 'words' ];
	$total_words = count( $word_array );

	# Count keywords
	foreach ( $word_array as $word ) {
		$word = trim( strtolower( $word ) );
		if ( strlen( $word ) > 3 ) {
			if ( !$word_hash[ $word ] ) { $word_hash[ $word ] = 0; }
			$word_hash[ $word ]++;
			if ( $word_hash[ $word ] > $top_word_count ) { $top_word_count = $word_hash[ $word ]; }
		}
	}

	unset( $word_array ); # Not needed anymore.

	$filtered_result = array();
	$purged_result = array();

	# We want a ratio
	if( $ratio && $minimum ) {
		# Filter
		foreach ( $word_hash as $keyword => $appareances ) {
			if ( intval( $appareances / ( $total_words / $ratio ) ) >= $minimum ) {
				$filtered_result[ $keyword ] = $appareances;
			}
		}
	# Only minimum
	} elseif( !$ratio && $minimum ) {
		# Filter
		foreach ( $word_hash as $keyword => $appareances ) {
			if ( $appareances >= $minimum ) {
				$filtered_result[ $keyword ] = $appareances;
			}
		}
	} else {
		$filtered_result = $word_hash;
	}

	return $filtered_result;
}

# Remove from array pairs which key matches a regex from the filter array
function bst_regfilter_keyword_counts( $keywords, $filter ) {
	if ( !$keywords || !is_array( $keywords ) ) { return false; }
	foreach ( $keywords as $word => $appareances ) {
		if ( !bst_match_regarray( $filter, $word ) ) {
			$filtered_result[ $word ] = $appareances;
		}
	}
	return $filtered_result;
}

# Entities dictionary
function bst_get_entities_hash( ) {
	return array(
		'Aacute' => 'Á',
		'aacute' => 'á',
		'Acirc' => 'Â',
		'acirc' => 'â',
		'acute' => '´',
		'AElig' => 'Æ',
		'aelig' => 'æ',
		'Agrave' => 'À',
		'agrave' => 'à',
		'alefsym' => 'ℵ',
		'Alpha' => 'Α',
		'alpha' => 'α',
		'amp' => '&',
		'and' => '∧',
		'ang' => '∠',
		'apos' => '\'',
		'Aring' => 'Å',
		'aring' => 'å',
		'asymp' => '≈',
		'Atilde' => 'Ã',
		'atilde' => 'ã',
		'Auml' => 'Ä',
		'auml' => 'ä',
		'bdquo' => '„',
		'Beta' => 'Β',
		'beta' => 'β',
		'brvbar' => '¦',
		'bull' => '•',
		'cap' => '∩',
		'Ccedil' => 'Ç',
		'ccedil' => 'ç',
		'cedil' => '¸',
		'cent' => '¢',
		'Chi' => 'Χ',
		'chi' => 'χ',
		'circ' => 'ˆ',
		'clubs' => '♣',
		'cong' => '≅',
		'copy' => '©',
		'crarr' => '↵',
		'cup' => '∪',
		'curren' => '¤',
		'dagger' => '†',
		'Dagger' => '‡',
		'darr' => '↓',
		'dArr' => '⇓',
		'deg' => '° ',
		'Delta' => 'Δ',
		'delta' => 'δ',
		'diams' => '♦',
		'divide' => '÷',
		'Eacute' => 'É',
		'eacute' => 'é',
		'Ecirc' => 'Ê',
		'ecirc' => 'ê',
		'Egrave' => 'È',
		'egrave' => 'è',
		'empty' => '∅',
		'emsp' => ' ',
		'ensp' => ' ',
		'Epsilon' => 'Ε',
		'epsilon' => 'ε',
		'equiv' => '≡',
		'Eta' => 'Η',
		'eta' => 'η',
		'ETH' => 'Ð',
		'eth' => 'ð',
		'Euml' => 'Ë',
		'euml' => 'ë',
		'euro' => '€',
		'exist' => '∃',
		'fnof' => 'ƒ',
		'forall' => '∀',
		'frac12' => '½',
		'frac14' => '¼',
		'frac34' => '¾',
		'frasl' => '⁄',
		'Gamma' => 'Γ',
		'gamma' => 'γ',
		'ge' => '≥',
		'gt' => '>',
		'harr' => '↔',
		'hArr' => '⇔',
		'hearts' => '♥',
		'hellip' => '…',
		'Iacute' => 'Í',
		'iacute' => 'í',
		'Icirc' => 'Î',
		'icirc' => 'î',
		'iexcl' => '¡',
		'Igrave' => 'Ì',
		'igrave' => 'ì',
		'image' => 'ℑ',
		'infin' => '∞',
		'int' => '∫',
		'Iota' => 'Ι',
		'iota' => 'ι',
		'iquest' => '¿',
		'isin' => '∈',
		'Iuml' => 'Ï',
		'iuml' => 'ï',
		'Kappa' => 'Κ',
		'kappa' => 'κ',
		'Lambda' => 'Λ',
		'lambda' => 'λ',
		'lang' => '〈',
		'laquo' => '«',
		'larr' => '←',
		'lArr' => '⇐',
		'lceil' => '⌈',
		'ldquo' => '“',
		'le' => '≤',
		'lfloor' => '⌊',
		'lowast' => '∗',
		'loz' => '◊',
		'lrm' => ' ',
		'lsaquo' => '‹',
		'lsquo' => '‘',
		'lt' => '<',
		'macr' => '¯',
		'mdash' => '—',
		'micro' => 'µ',
		'middot' => '·',
		'minus' => '−',
		'Mu' => 'Μ',
		'mu' => 'μ',
		'nabla' => '∇',
		'nbsp' => ' ',
		'ndash' => '–',
		'ne' => '≠',
		'ni' => '∋',
		'not' => '¬',
		'notin' => '∉',
		'nsub' => '⊄',
		'Ntilde' => 'Ñ',
		'ntilde' => 'ñ',
		'Nu' => 'Ν',
		'nu' => 'ν',
		'Oacute' => 'Ó',
		'oacute' => 'ó',
		'Ocirc' => 'Ô',
		'ocirc' => 'ô',
		'OElig' => 'Œ',
		'oelig' => 'œ',
		'Ograve' => 'Ò',
		'ograve' => 'ò',
		'oline' => '‾',
		'Omega' => 'Ω',
		'omega' => 'ω',
		'Omicron' => 'Ο',
		'omicron' => 'ο',
		'oplus' => '⊕',
		'or' => '∨',
		'ordf' => 'ª',
		'ordm' => 'º',
		'Oslash' => 'Ø',
		'oslash' => 'ø',
		'Otilde' => 'Õ',
		'otilde' => 'õ',
		'otimes' => '⊗',
		'Ouml' => 'Ö',
		'ouml' => 'ö',
		'para' => '¶ ',
		'part' => '∂',
		'permil' => '‰',
		'perp' => '⊥',
		'Phi' => 'Φ',
		'phi' => 'φ',
		'Pi' => 'Π',
		'pi' => 'π',
		'piv' => 'ϖ',
		'plusmn' => '±',
		'pound' => '£',
		'Prime' => '″',
		'prime' => '′',
		'prod' => '∏',
		'prop' => '∝',
		'Psi' => 'Ψ',
		'psi' => 'ψ',
		'quot' => '"',
		'radic' => '√',
		'rang' => '〉',
		'raquo' => '»',
		'rarr' => '→',
		'rArr' => '⇒',
		'rceil' => '⌉',
		'rdquo' => '”',
		'real' => 'ℜ',
		'reg' => '®',
		'rfloor' => '⌋',
		'Rho' => 'Ρ',
		'rho' => 'ρ',
		'rlm' => ' ',
		'rsaquo' => '›',
		'rsquo' => '’',
		'sbquo' => '‚',
		'Scaron' => 'Š',
		'scaron' => 'š',
		'sdot' => '⋅',
		'sect' => '§',
		'shy' => '',
		'Sigma' => 'Σ',
		'sigma' => 'σ',
		'sigmaf' => 'ς',
		'sim' => '∼',
		'spades' => '♠',
		'sub' => '⊂',
		'sube' => '⊆',
		'sum' => '∑',
		'sup' => '⊃',
		'sup1' => '¹',
		'sup2' => '²',
		'sup3' => '³',
		'supe' => '⊇',
		'szlig' => 'ß',
		'Tau' => 'Τ',
		'tau' => 'τ',
		'there4' => '∴',
		'Theta' => 'Θ',
		'theta' => 'θ',
		'thetasym' => 'ϑ',
		'thinsp' => ' ',
		'THORN' => 'Þ',
		'thorn' => 'þ ',
		'tilde' => '˜',
		'times' => '×',
		'trade' => '™',
		'Uacute' => 'Ú',
		'uacute' => 'ú',
		'uarr' => '↑',
		'uArr' => '⇑',
		'Ucirc' => 'Û',
		'ucirc' => 'û',
		'Ugrave' => 'Ù',
		'ugrave' => 'ù',
		'uml' => '¨',
		'upsih' => 'ϒ',
		'Upsilon' => 'Υ',
		'upsilon' => 'υ',
		'Uuml' => 'Ü',
		'uuml' => 'ü',
		'weierp' => '℘',
		'Xi' => 'Ξ',
		'xi' => 'ξ',
		'Yacute' => 'Ý',
		'yacute' => 'ý',
		'yen' => '¥',
		'Yuml' => 'Ÿ',
		'yuml' => 'ÿ',
		'Zeta' => 'Ζ',
		'zeta' => 'ζ'
	);
}

/* EOF */
