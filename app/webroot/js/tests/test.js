// doc: <http://docs.jquery.com/Qunit>

module('markItUp');
test('markItUp.multimedia()', function() {
	var input,
			result,
			expected,
			message;

	// test html5 video
	$.each(['mp4', 'webm', 'm4v'], function(key, value) {
		input = 'http://foo.bar/baz.' + value;
		result = markItUp.multimedia(input);
		expected =  '[video]http://foo.bar/baz.' + value + '[/video]'
		equal(result, expected);
	});

	// test html5 audio
	$.each(['m4a', 'ogg', 'mp3', 'wav', 'opus'], function(key, value) {
		input = 'http://foo.bar/baz.' + value;
		result = markItUp.multimedia(input);
		expected =  '[audio]http://foo.bar/baz.' + value + '[/audio]'
		equal(result, expected);
	});

	// test iframe
	input = '<iframe src="http://www.youtube.com/embed/qa-4E8ZDj9s" width="425" '
		+ 'height="349" frameborder="0" allowfullscreen></iframe>';
	result = markItUp.multimedia(input);
	expected = '[iframe src=http://www.youtube.com/embed/qa-4E8ZDj9s width=425 '
		+ 'height=349 frameborder=0 allowfullscreen][/iframe]';
	equal(result, expected);

	// test flash
	input = '<object … src="http://www.youtube.com/v/qa-4E8ZDj9s?version=3&amp;hl=en_US" … width="425" height="349" …'
	result = markItUp.multimedia(input);
	expected = '[flash_video]http://www.youtube.com/v/qa-4E8ZDj9s?version=3&amp;hl=en_US|425|349[/flash_video]';
	equal(result, expected);

	// test raw url youtube
	input = 'http://www.youtube.com/watch?v=qa-4E8ZDj9s'
	result = markItUp.multimedia(input);
	expected = '[iframe src=http://www.youtube.com/embed/qa-4E8ZDj9s width=425 '
		+ 'height=349 frameborder=0 allowfullscreen][/iframe]';
	equal(result, expected);

	// test raw url without protocol youtube;
	message = 'test raw url without protocol youtube';
	input = 'www.youtube.com/watch?v=0u8KUgUqprw';
	result = markItUp.multimedia(input);
	expected = '[iframe src=http://www.youtube.com/embed/0u8KUgUqprw width=425 '
		+ 'height=349 frameborder=0 allowfullscreen][/iframe]';
	equal(result, expected, message);


	// test raw url youtube shortener
	message = 'test raw url youtube shortener'
	input = 'http://youtu.be/qa-4E8ZDj9s';
	result = markItUp.multimedia(input);
	expected = '[iframe src=http://www.youtube.com/embed/qa-4E8ZDj9s width=425 '
		+ 'height=349 frameborder=0 allowfullscreen][/iframe]';
	equal(result, expected, message);

  // test embedly with support disabled
  SaitoApp.settings.embedly_enabled = 0;
	message = 'test embed.ly disabled'
	input = 'https://twitter.com/apfelwiki/status/211385090444505088';
	result = markItUp.multimedia(input);
	expected = '';
	equal(result, expected, message);

  // test embedly with support enabled
  SaitoApp.settings.embedly_enabled = 1;
	message = 'test embed.ly'
	input = 'https://twitter.com/apfelwiki/status/211385090444505088';
	result = markItUp.multimedia(input);
	expected = '[embed]' + input + '[/embed]';
	equal(result, expected, message);

})