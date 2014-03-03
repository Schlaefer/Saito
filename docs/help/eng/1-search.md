# Simple Search #

## Operators ##

No operator implies `OR`. `apple banana` finds entries that contain at least one of the two words.

`+` stands for `AND`. `+apple +juice` finds entries that contain both words.

`+apple macintosh` finds entries that contain the word “apple”, but rank entries higher if they also contain “macintosh”.

`-` stands for `NOT`.  `+apple -macintosh` finds entries that contain the word “apple” but not “macintosh”.

`apple*` finds entries that contain words such as “apple”, “apples”, “applesauce”, or “applet”. This operator *doesn't* work at the beginning of a search term.

`"some words"` Find entries that contain the exact phrase “some words” (for example, entries that contain “some words of wisdom” but not “some noise words”).