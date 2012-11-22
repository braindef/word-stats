/*
Javascript version of basic_string_tools
by: Fran Ontanaya <email@franontanaya.com>
Version: 0.1
License: GPLv2
*/

var bstAllCombiningMarks =
	"[\u00AD\u2010" + // Breaking Hyphens
	"\u031C-\u0361" + // Combining Diacritical Marks
	"\u20D0-\u20F0" + // Combining Diacritical Marks for Symbols
	"\u1DC0-\u1DFF" + // Combining Diacritical Marks Supplement
	"\uFE20-\uFE26" + // Combining Half Marks
	"\u0483-\u0489" + // Cyrillic
	"\uA66F-\uA67D" + // Cyrillic Extended-B
	"\u0951-\u0954" + // Devaganari
	"\u037A\u0384-\u0385" + // Greek and Coptic
	"\u3099-\u309C" + // Hiragana
	"\u30FB-\u30FE]"; // Katakana
	// * This list is incomplete.

// ToDo: Add option to enable only selected blocks for better performance?
var bstAllWordChars =
	"A-Za-z0-9" + // Basic Latin
	"\uFB00-\uFB4F" + // Alphabetic Presentation Forms (ToDo: Split ligated forms)
	"\u0621-\u064A\u0660-\u0669\u066E-\u06D3\u06D5\u06EE-\u06FF"+ // Arabic
	"\uFB50-\uFBB1" + // Arabic Presentation Forms A
	"\uFE80-\uFEFC" + // Arabic Presentation Forms B
	"\u0750-\u077F" + // Arabic Supplement
	"\u20A0-\u20CF" + // Currency symbols.
	"\u0400-\u0482\u0498-\u04FF" + // Cyrillic
	"\u2DE0-\u2DFF" + // Cyrillic Extended-A
	"\uA640-\uA66E\uA680-\uA697" + // Cyrillic Extended-B
	"\u0500-\u0525" + // Cyrillic Supplement
	"\u0904-\u0939\u093E-\u0950-\u0955-\u096F\u0972-\u097F" + // Devanagari
	"\uA8E0-\uA8F0" + // Devanagari Extended
	"\u1F200-\u1F2FF" + // Enclosed Ideographic Supplement
	"\u10A0-\u10FA" + // Georgian
	"\u0386\u0388-\u03FF" + // Greek and Coptic
	"\u1F00-\u1FBC\u1FC2-\u1FCC\u1FD0-\u1FDB\u1FE0-\u1FEC\u1FF2-\u1FFC" + // Greek Extended
	"\uFF10-\uFF19\uFF21-\uFF3A\uFF41-\uFF5A\uFF66-\uFF9D" + // Halfwidth and Fullwidth Forms
	"\u05D0-\u05EA" + // Hebrew
	"\u3040-\u3096" + // Hiragana
	"\u30A1-\u30FA" + // Katakana
	"\u00C0-\u00D6\u00D8-\u00F6\u00F9-\u00FF" + // Latin-1 Supplement
	"\u0100-\u017F" + // Latin Extended-A
	"\u1E00-\u1EFF" + // Latin Extended Additional
	"\u0180-\u024F" + // Latin Extended-B
	"\u2C60-\u2C7F" + // Latin Extended-C
	"\uA726-\uA787" + // Latin Extended-D
	"\u0D05-\u0D39\u0D3E-\u0D44" + // Malayam
	"\u1D400-\u1D7FF}" + // Mathematical Alphanumeric Symbols
	"\u0710-\u072F\u074D-\u074F" + // Syriac
	"\u1700-\u1714"; // Tagalog
// * This list is incomplete.

var 	bstInWordMarks = "\'(?![A-Za-z])";

var bstAllShortPauses =
	"[\.]{3}|[;:\u2026\u2015" +
	"\u00B7\u0387]"; // Greek
// * This list is incomplete

function bstMatchRegArray( regexArray, text ) {
	var regex;
	var r;
	for( r in regexArray ) {
		regex = new RegExp( regexArray[ r ], "i" );
		if ( text.match( regex ) !== null ) { return true; }
	}
	return false;
}

function bstHtmlStripper( text ) {
	/* Use the browser's parser to strip tags */
	var div = document.createElement( "div" );
	div.innerHTML = text;
	text = div.textContent || div.innerText || "";
	div = null;
	/* text.replace( new RegExp( "<[^\s][^>]*[^\s]>", "g" ), " " ); */
	return text;
}

/* Note that parsing for non-Latin scripts may be incomplete. */
function bstSimpleBoundaries( text ) {
	/* Add reverse() function, so we can fake regexp lookbehind. */
	String.prototype.reverse = function () {
		return this.split('').reverse().join('');
	};

	/* Replace some special characters */
	text = text.replace( new RegExp( "=|-", "g" ), " " );

	text = text.replace( new RegExp( "[\[]|\]", "g" ), " " );
	text = text.replace( new RegExp( "\\\\", "g" ), " " );

	text = text.replace( new RegExp( "\u00A0|\&nbsp;|\"", "g" ), " " );

	/* Remove combining marks et al, as they are meaningless for this purpose and can split words */
	text = text.replace( new RegExp( bstAllCombiningMarks, "g" ), "" );

	/* Replace ellipsis with commas when not followed by capitalized word */
	text = text.replace( new RegExp( "\u2026(?=\s[a-z])|[\.]{3}(?=\s[a-z])", "g" ), "," );

	/* Typical end of sentence, including unicodes | All remaining ellipsis */
	text = text.replace( new RegExp( "[\!\?\.;\u06D4\u203C\u2047-\u2049\u2026]+|[\.]{3}", "g" ), "." );

	/* Replace all remaining short pauses with colons */
	text = text.replace( new RegExp( bstAllShortPauses, "g" ), "," );

	/* Remove single quotes around words, but not inside words. Because JavaScript doesn't support lookbehind, we reverse the string instead. */
	text = text.replace( new RegExp( bstInWordMarks, "g" ), " ");
	text = text.reverse();
	text = text.replace( new RegExp( bstInWordMarks, "g" ), " ");
	text = text.reverse();

	/* Replace non-word characters, save short pauses and end of sentence, with spaces */
	text = text.replace( new RegExp( "[^" + bstAllWordChars + ",\'\.\n]", "g" ), " ");

	var result = new Array();
	result[ "text" ] = text;
	text = text.replace( new RegExp( "[^" + bstAllWordChars + "]" , "g" ), "" );
	result[ "alphanumeric" ] = text;
	return result;
}

 function bstTrimArray( array ) {
	/* Remove the first and last items if they are empty */
	if ( array[ 0 ] == "" || array[ 0 ] == "\n" ) { array = array.slice( 1, array.length ); }
	if ( array[ array.length - 1 ] == "" || array[ array.length -1 ] == "\n" ) { array = array.slice( 0, array.length - 1 ); }
	return array;
}

function trim( s ) {
	var l = 0; var r = s.length - 1;
	while( l < s.length && s[ l ] == ' ' ) {
		l++;
	}
	while( r > l && s[ r ] == ' ' ) {
		r-=1;
	}
	return s.substring( l, r + 1 );
}

function bstTrimText( text ) {
	// Trim spaces
	text = text.replace( new RegExp( "[ ]+(?=[\.\n])", "g"), '' );
	return trim( text );
}

function bstSplitSentences( text ) {
	text = bstTrimArray( text.split( /[\.\n]+/ ) );
	return text;
}

function bstSplitWords( text ) {
	text = bstTrimArray( text.split( /[ ,\.\n]+/ ) );
	return text;
}

function bstSplitText( text ) {
	var stats = new Array();
	if ( text == "" ) {
		stats[ "text" ] = "";
		stats[ "sentences" ] = "";
		stats[ "words" ] = "";
		stats[ "alphanumeric" ] = "";
	} else {
		var simplified = bstSimpleBoundaries( text );
		stats[ "text" ] = simplified[ "text" ];
		simplified[ "text" ] = bstTrimText( simplified[ "text" ] );
		stats[ "sentences" ] = bstSplitSentences( simplified[ "text" ] );
		stats[ "words" ] = bstSplitWords( simplified[ "text" ] );
		stats[ "alphanumeric" ] = simplified[ "alphanumeric" ];
	}
	return stats;
}
