# Einfache Suche #

## Suchoperatoren

Kein Operator impliziert `ODER`. `apple banana` findet Datensätze, die mindestens eines der beiden Wörter enthalten.

`+` bedeutet `UND`. `+apple +juice` findet Datensätze, die beide Wörter enthalten.

`+apple macintosh` findet Datensätze, die das Wort „apple“ enthalten, stuft aber solche Datensätze höher ein, die auch „macintosh“ enthalten.

`-` bedeutet `NICHT`. `+apple -macintosh` findet Datensätze, die das Wort „apple“, aber nicht das Wort „macintosh“ enthalten.

`apple*` findet Datensätze, die Wörter wie „apple“, „apples“, „applesauce“ oder „applet“ enthalten. Dieser Operator funktioniert *nicht* am Anfang eines Wortes!

`"some words"` findet Datensätze, die die exakte Phrase „some words“ enthalten. Dies wäre etwa „some words of wisdom“, nicht aber „some noise words“.
