define([
    'lib/saito/markItUp.media'
], function(MarkitUpMedia) {

    describe("markItUp library", function() {

        describe("multimedia button", function() {

            var input,
                result,
                expected,
                markItUp;

            markItUp = MarkitUpMedia;

            $.each(['m4a', 'ogg', 'mp3', 'wav', 'opus'], function(key, value) {
                it("outputs an [audio] tag for " + value + " files", function() {
                    input = 'http://foo.bar/baz.' + value;
                    result = markItUp.multimedia(input);
                    expected =  '[audio]http://foo.bar/baz.' + value + '[/audio]';
                    expect(result).toEqual(expected);
                    });
            });

            $.each(['mp4', 'webm', 'm4v'], function(key, value) {
                it("outputs an [video] tag for " + value + " files", function() {
                                                                                    input = 'http://foo.bar/baz.' + value;
                                                                                    result = markItUp.multimedia(input);
                                                                                    expected =  '[video]http://foo.bar/baz.' + value + '[/video]'
                                                                                    expect(result).toEqual(expected);
                                                                                    });
            });

            it("outputs an [iframe] tag for <iframe> tags", function() {
                input = '<iframe src="http://www.youtube.com/embed/qa-4E8ZDj9s" width="425" '
                    + 'height="349" frameborder="0" allowfullscreen></iframe>';
                result = markItUp.multimedia(input);
                expected = '[iframe src=http://www.youtube.com/embed/qa-4E8ZDj9s width=425 '
                    + 'height=349 frameborder=0 allowfullscreen][/iframe]';
                expect(result).toEqual(expected);
            });

            it("outputs an [flash_video] tag for <object> tags", function() {
                                                                                input = '<object … src="http://www.youtube.com/v/qa-4E8ZDj9s?version=3&amp;hl=en_US" … width="425" height="349" …'
                                                                                result = markItUp.multimedia(input);
                                                                                expected = '[flash_video]http://www.youtube.com/v/qa-4E8ZDj9s?version=3&amp;hl=en_US|425|349[/flash_video]';
                                                                                });

            it("outputs an [iframe] tag for a raw youtube url", function() {
                input = 'http://www.youtube.com/watch?v=qa-4E8ZDj9s';
                result = markItUp.multimedia(input);
                expected = '[iframe src=//www.youtube.com/embed/qa-4E8ZDj9s' +
                    ' width=425 height=349 frameborder=0 allowfullscreen][/iframe]';
                expect(result).toEqual(expected);
            });

            it("outputs an [iframe] tag for a raw youtube url without protocol", function() {
                input = 'www.youtube.com/watch?v=0u8KUgUqprw';
                result = markItUp.multimedia(input);
                expected = '[iframe src=//www.youtube.com/embed/0u8KUgUqprw' +
                    ' width=425 height=349 frameborder=0 allowfullscreen][/iframe]';
                expect(result).toEqual(expected);
            });

            it("outputs an [iframe] tag for youtu.be url shortener ", function() {
                input = 'http://youtu.be/qa-4E8ZDj9s';
                result = markItUp.multimedia(input);
                expected = '[iframe src=//www.youtube.com/embed/qa-4E8ZDj9s' +
                    ' width=425 height=349 frameborder=0 allowfullscreen][/iframe]';
                expect(result).toEqual(expected);
            });

            it("outputs nothing for embed.ly if embed.ly is disabled", function() {
                                                                                      SaitoApp.app.settings.embedly_enabled = 0;
                                                                                      input = 'https://twitter.com/apfelwiki/status/211385090444505088';
                                                                                      result = markItUp.multimedia(input);
                                                                                      expected = '';
                                                                                      expect(result).toEqual(expected);
                                                                                      });

            it("outputs [embed] tag to use embed.ly as fallback", function() {
                 input = 'https://twitter.com/apfelwiki/status/211385090444505088';
                 result = markItUp.multimedia(input, {embedlyEnabled: true});
                 expected = '[embed]' + input + '[/embed]';
                 expect(result).toEqual(expected);
            });

            $.each(['png', 'gif', 'jpg', 'jpeg', 'webp'], function(key, value) {
                it("outputs an [img] tag for " + value + " files", function() {
                    input = 'http://foo.bar/baz.' + value;
                    result = markItUp.multimedia(input);
                    expected =  '[img]http://foo.bar/baz.' + value + '[/img]';
                    expect(result).toEqual(expected);
                });
            });

            it("replaces dropbox horrible html fubar with download link", function() {
                input = 'https://www.dropbox.com/foo/baz.png';
                result = markItUp.multimedia(input);
                expected =  '[img]https://dl.dropbox.com/foo/baz.png[/img]';
                expect(result).toEqual(expected);
            });

        });

    });

});
