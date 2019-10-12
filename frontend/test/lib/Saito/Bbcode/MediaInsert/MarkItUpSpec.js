/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

import $ from 'jquery';
import MarkupMultimedia from 'lib/saito/Editor/Bbcode/MediaInsert/markup.media.ts';

describe("markup library", function () {

  describe("multimedia button", function () {

    let input,
      result,
      expected,
      markup;

    markup = new MarkupMultimedia();

    $.each(['m4a', 'ogg', 'mp3', 'wav', 'opus'], function (key, value) {
      it("outputs an [audio] tag for " + value + " files on end of url", function () {
        input = 'http://foo.bar/baz.' + value;
        result = markup.multimedia(input);
        expect(result.getTag()).toEqual('audio');
        expect(result.getContent()).toEqual(input);
        expect(result.getAttributes()).toEqual(null);
      });

      it("outputs an [audio] tag for " + value + " files in / url", function () {
        input = 'http://foo.bar/baz.' + value + '/foo';
        result = markup.multimedia(input);
        expect(result.getTag()).toEqual('audio');
        expect(result.getContent()).toEqual(input);
        expect(result.getAttributes()).toEqual(null);
      });

      it("outputs an [audio] tag for " + value + " files in ? url", function () {
        input = 'http://foo.bar/baz.' + value + '?foo';
        result = markup.multimedia(input);
        expect(result.getTag()).toEqual('audio');
        expect(result.getContent()).toEqual(input);
        expect(result.getAttributes()).toEqual(null);
      });
    });

    $.each(['mp4', 'webm', 'm4v'], function (key, value) {
      it("outputs an [video] tag for " + value + " files", function () {
        input = 'http://foo.bar/baz.' + value;
        result = markup.multimedia(input);
        expect(result.getTag()).toEqual('video');
        expect(result.getContent()).toEqual(input);
        expect(result.getAttributes()).toEqual(null);
      });
    });

    it("does nothing on empty input", function () {
      input = '';
      result = markup.multimedia(input);
      expected = '';
      expect(result).toEqual(expected);
    });

    it("outputs an [iframe] tag for <iframe> tags", function () {
      const content = 'src="http://www.youtube.com/embed/qa-4E8ZDj9s" width="560" ' +
        'height="315" frameborder="0" allowfullscreen';
      const input = '<iframe ' + content + '></iframe>';
      const result = markup.multimedia(input);
      expect(result.getTag()).toEqual('iframe');
      expect(result.getContent()).toEqual(null);
      const attributes = 'src=http://www.youtube.com/embed/qa-4E8ZDj9s ' +
        'width=560 height=315 frameborder=0 allowfullscreen';
      expect(result.getAttributes()).toEqual(attributes);
    });

    it("outputs an [iframe] tag for a raw youtube url", function () {
      input = 'http://www.youtube.com/watch?v=qa-4E8ZDj9s';
      result = markup.multimedia(input);
      expect(result.getTag()).toEqual('iframe');
      expect(result.getContent()).toEqual(null);
      const attributes = 'src=//www.youtube-nocookie.com/embed/qa-4E8ZDj9s' +
        ' allowfullscreen=allowfullscreen frameborder=0 height=315 width=560';
      expect(result.getAttributes()).toEqual(attributes);
    });

    it("outputs an [iframe] tag for a raw youtube url without protocol", function () {
      input = 'www.youtube.com/watch?v=0u8KUgUqprw';
      result = markup.multimedia(input);
      expect(result.getTag()).toEqual('iframe');
      expect(result.getContent()).toEqual(null);
      const attributes = 'src=//www.youtube-nocookie.com/embed/0u8KUgUqprw' +
        ' allowfullscreen=allowfullscreen frameborder=0 height=315 width=560';
      expect(result.getAttributes()).toEqual(attributes);
    });

    it("outputs an [iframe] tag for youtu.be url shortener ", function () {
      input = 'http://youtu.be/qa-4E8ZDj9s';
      result = markup.multimedia(input);
      expect(result.getTag()).toEqual('iframe');
      expect(result.getContent()).toEqual(null);
      const attributes = 'src=//www.youtube-nocookie.com/embed/qa-4E8ZDj9s' +
        ' allowfullscreen=allowfullscreen frameborder=0 height=315 width=560';
      expect(result.getAttributes()).toEqual(attributes);
    });

    it("outputs [embed] tag to use embed.ly as fallback", function () {
      input = 'https://twitter.com/apfelwiki/status/211385090444505088';

      result = markup.multimedia(input);

      expect(result.getTag()).toEqual('embed');
      expect(result.getContent()).toEqual(input);
      expect(result.getAttributes()).toEqual(null);
    });

    $.each(['png', 'gif', 'jpg', 'jpeg', 'webp'], function (key, value) {
      it("outputs an [img] tag for " + value + " files", function () {
        input = 'http://foo.bar/baz.' + value;

        result = markup.multimedia(input);

        expect(result.getTag()).toEqual('img');
        expect(result.getContent()).toEqual(input);
        expect(result.getAttributes()).toEqual(null);
      });
    });

    it("replaces dropbox horrible html fubar with download link", function () {
      input = 'https://www.dropbox.com/foo/baz.png';

      result = markup.multimedia(input);

      expect(result.getTag()).toEqual('img');
      expect(result.getContent()).toEqual('https://dl.dropbox.com/foo/baz.png');
      expect(result.getAttributes()).toEqual(null);
    });
  });
});
