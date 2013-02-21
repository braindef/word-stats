Unicode reference for bst_single_boundaries:

	Combining marks:

			\x{00AD}\x{2010} # Breaking Hyphens
			\x{031C}-\x{0361} # Combining Diacritical Marks
			\x{20D0}-\x{20F0} # Combining Diacritical Marks for Symbols
			\x{1DC0}-\x{1DFF} # Combining Diacritical Marks Supplement
			\x{FE20}-\x{FE26} # Combining Half Marks
			\x{0483}-\x{0489} # Cyrillic
			\x{A66F}-\x{A67D} # Cyrillic Extended-B
			\x{0951}-\x{0954} # Devaganari
			\x{037A}\x{0384}-\x{0385} # Greek and Coptic
			\x{3099}-\x{309C} # Hiragana
			\x{30FB}-\x{30FE}  # Katakana
			# This list is incomplete.

	Word characters:

			(\b[A-Za-z]+'[A-Za-z]+\b) # Apostrophes within a word. I.e. can't, you're.
			A-Za-z0-9 # Basic Latin
			\x{FB00}-\x{FB4F} # Alphabetic Presentation Forms (ToDo: Split ligated forms)
			\x{0621}-\x{064A}\x{0660}-\x{0669}\x{066E}-\x{06D3}\x{06D5}\x{06EE}-\x{06FF}'. # Arabic
			\x{FB50}-\x{FBB1} # Arabic Presentation Forms A
			\x{FE80}-\x{FEFC} # Arabic Presentation Forms B
			\x{0750}-\x{077F} # Arabic Supplement
			\x{20A0}-\x{20CF} # Currency symbols.
			\x{0400}-\x{0482}\x{0498}-\x{04FF} # Cyrillic
			\x{2DE0}-\x{2DFF} # Cyrillic Extended-A
			\x{A640}-\x{A66E}\x{A680}-\x{A697} # Cyrillic Extended-B
			\x{0500}-\x{0525} # Cyrillic Supplement
			\x{0904}-\x{0939}\x{093E}-\x{0950}-\x{0955}-\x{096F}\x{0972}-\x{097F} # Devanagari
			\x{A8E0}-\x{A8F0} # Devanagari Extended
			\x{1F200}-\x{1F2FF} # Enclosed Ideographic Supplement
			\x{10A0}-\x{10FA} # Georgian
			\x{0386}\x{0388}-\x{03FF} # Greek and Coptic
			\x{1F00}-\x{1FBC}\x{1FC2}-\x{1FCC}\x{1FD0}-\x{1FDB}\x{1FE0}-\x{1FEC}\x{1FF2}-\x{1FFC} # Greek Extended
			\x{FF10}-\x{FF19}\x{FF21}-\x{FF3A}\x{FF41}-\x{FF5A}\x{FF66}-\x{FF9D} # Halfwidth and Fullwidth Forms
			\x{05D0}-\x{05EA} # Hebrew
			\x{3040}-\x{3096} # Hiragana
			\x{30A1}-\x{30FA} # Katakana
			\x{00C0}-\x{00D6}\x{00D8}-\x{00F6}\x{00F9}-\x{00FF} # Latin-1 Supplement
			\x{0100}-\x{017F} # Latin Extended-Aignore
			\x{1E00}-\x{1EFF} # Latin Extended Additional
			\x{0180}-\x{024F} # Latin Extended-B
			\x{2C60}-\x{2C7F} # Latin Extended-C
			\x{A726}-\x{A787} # Latin Extended-D
			\x{0D05}-\x{0D39}\x{0D3E}-\x{0D44} # Malayam
			\x{1D400}-\x{1D7FF} # Mathematical Alphanumeric Symbols
			\x{0710}-\x{072F}\x{074D}-\x{074F} # Syriac
			\x{1700}-\x{1714} # Tagalog
			\x{4E00}-\x{9fBE} # Chinese
			 # This list is incomplete.
