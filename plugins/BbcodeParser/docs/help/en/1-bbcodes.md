# Saito flavored BBCode #

BBCodes available in front-end. The availability of some BBCode-tags depends on forum-configuration (e.g. multimedia-settings) or place.

## Bold ##

	[b]text[/b]

Outputs bold (important) text.

## Italic ##

	[i]text[/i]

Outputs italic (emphazised) text.

## Strike ##

	[s]text[/s]

or

	[strike]text[/strike]

Outputs struck through text.

## List ##

	[list]
	[*] item 1
	[*] item 2
	[/list]

## Horizontal Ruler ##

`[hr]` or `[---]` creates an horizontal ruler.

## Edit Marker ##

`[e]` creates an edit marker.

## Links ##

### Autolink ###

Simple URLs (`http://example.com/foo`) that occur as text are automatically converted into a clickable link.

### Explicit Links ###

	[url]http://example.com/[/url]

### Links with Link-Text ###

	[url=http://example.com/  <label=none>]Link[/url]

Usually the top-level domain is appended to a `[url]` link. This can be controlled with the `label` parameter.


### Email Link ###

	[email]mail@tosomeone.com[/email]

or

	[email=mail@tosomeone.com]Mail[/email]


### Internal Shortlinks ###

	#123

Is a link to the posting with the ID `123`.

	@Alex

Is a link to Alex's profile page.


## Spoiler ##

	[spoiler]content[/spoiler]

Content is only shown if a masking spoiler-text is clicked.

## Code ##


	[code=<language>]<source>[/code]

Verbatim source code with.

If `<language>` is not `text` (default) the `<source>` is highlighted in the particular language, e.g. `[code PHP]…`. For available languages refer the [GeSHI documentation](http://qbnz.com/highlighter/).

## Citation ##

	> You guys got any milk?

A special citation character (depending on forum-settings) at the beginning of the line followed by a space marks the following text in that line as a citation from the parent posting.

## Multimedia ##

Some multimedia-tags are complicated to setup manually. It is highly recommended to add multimedia-tags via the provided GUI-option(s).

### Image ###

	[img]http://example.com/image.png[/img]

50 pixel width:

	[img=50]http://localhost/img/macnemo.png[/img]

Constrained to 50 pixel width or 100 pixel height:

	[img=50x100]http://localhost/img/macnemo.png[/img]

Float image left or right:

	[img=<left|right>]http://localhost/img/macnemo.png[/img]


### HTML5-Audio ###

	[audio]http://example.com/audio.ogg[/audio]

Choose an [appropriate file-format][Audio] for your audience.

[Audio]: http://en.wikipedia.org/wiki/HTML5_Audio#Supported_browsers


### HTML5-Video ###

	[video]http://example.com/audio.webm[/video]

Choose an [appropriate file-format][Video] for your audience.

[Video]: http://en.wikipedia.org/wiki/HTML5_video#Browser_support


### Iframe &amp; Flash ###

Please use the provided GUI-features.

### Upload ###

	[upload]filename.ext[/upload]

### Embed.ly ###

If activated `[embed]<URL>[/embed]` tries to embed the URL via [embed.ly](http://embed.ly/).

## Layout ##

### Float ###

	[float]content[/float]

Floats the content to the side.