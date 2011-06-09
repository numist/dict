dict: Simple clean online dictionary
====================================

dict is a silly project mostly driven by my intense dislike for every online dictionary's cluttered and cumbersome design paired with my appreciation of the design and cleanliness of the Mac OS X built in dictionary.

Use and Installation
--------------------

Currently the code is deployed at [http://numist.net/define/](http://numist.net/define/), but it is not currently finished. For now, word lookup can be done by appending GET arguments to the link above to look up the word(s). For example: [http://numist.net/define/?file&pig&run](http://numist.net/define/?file&pig&run).

Installation requires the dictionary files from Mac OS X 10.4 or earlier, and scripts included in this package will build a SQLite database of words and references. The process will be documented as the scripts themselves stop being so prototypey.

License
-------

dict's source code is licensed under the [Creative Commons Attribution 3.0 Unported License](http://creativecommons.org/licenses/by/3.0/), except for the  external libraries listed below:

External Libraries
------------------

- [PHP Simple HTML DOM Parser](http://simplehtmldom.sourceforge.net/) (Included)
