/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

import $ from 'jquery';
import { MarkupMultimedia } from 'lib/saito/markup.media.ts';

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
        expected = '[audio]http://foo.bar/baz.' + value + '[/audio]';
        expect(result).toEqual(expected);
      });

      it("outputs an [audio] tag for " + value + " files in / url", function () {
        input = 'http://foo.bar/baz.' + value + '/foo';
        result = markup.multimedia(input);
        expected = '[audio]http://foo.bar/baz.' + value + '/foo[/audio]';
        expect(result).toEqual(expected);
      });

      it("outputs an [audio] tag for " + value + " files in ? url", function () {
        input = 'http://foo.bar/baz.' + value + '?foo';
        result = markup.multimedia(input);
        expected = '[audio]http://foo.bar/baz.' + value + '?foo[/audio]';
        expect(result).toEqual(expected);
      });
    });

    $.each(['mp4', 'webm', 'm4v'], function (key, value) {
      it("outputs an [video] tag for " + value + " files", function () {
        input = 'http://foo.bar/baz.' + value;
        result = markup.multimedia(input);
        expected = '[video]http://foo.bar/baz.' + value + '[/video]';
        expect(result).toEqual(expected);
      });
    });

    it("does nothing on empty input", function () {
      input = '';
      result = markup.multimedia(input);
      expected = '';
      expect(result).toEqual(expected);
    });

    it("outputs an [iframe] tag for <iframe> tags", function () {
      input = '<iframe src="http://www.youtube.com/embed/qa-4E8ZDj9s" width="560" ' +
        'height="315" frameborder="0" allowfullscreen></iframe>';
      result = markup.multimedia(input);
      expected = '[iframe src=http://www.youtube.com/embed/qa-4E8ZDj9s ' +
        'width=560 height=315 frameborder=0 allowfullscreen][/iframe]';
      expect(result).toEqual(expected);
    });

    it("outputs an [iframe] tag for a raw youtube url", function () {
      input = 'http://www.youtube.com/watch?v=qa-4E8ZDj9s';
      result = markup.multimedia(input);
      expected = '[iframe src=//www.youtube-nocookie.com/embed/qa-4E8ZDj9s' +
        ' allowfullscreen=allowfullscreen frameborder=0 height=315 width=560][/iframe]';
      expect(result).toEqual(expected);
    });

    it("outputs an [iframe] tag for a raw youtube url without protocol", function () {
      input = 'www.youtube.com/watch?v=0u8KUgUqprw';
      result = markup.multimedia(input);
      expected = '[iframe src=//www.youtube-nocookie.com/embed/0u8KUgUqprw' +
        ' allowfullscreen=allowfullscreen frameborder=0 height=315 width=560][/iframe]';
      expect(result).toEqual(expected);
    });

    it("outputs an [iframe] tag for youtu.be url shortener ", function () {
      input = 'http://youtu.be/qa-4E8ZDj9s';
      result = markup.multimedia(input);
      expected = '[iframe src=//www.youtube-nocookie.com/embed/qa-4E8ZDj9s' +
        ' allowfullscreen=allowfullscreen frameborder=0 height=315 width=560][/iframe]';
      expect(result).toEqual(expected);
    });

    it("outputs [embed] tag to use embed.ly as fallback", function () {
      input = 'https://twitter.com/apfelwiki/status/211385090444505088';
      result = markup.multimedia(input);
      expected = '[embed]' + input + '[/embed]';
      expect(result).toEqual(expected);
    });

    $.each(['png', 'gif', 'jpg', 'jpeg', 'webp'], function (key, value) {
      it("outputs an [img] tag for " + value + " files", function () {
        input = 'http://foo.bar/baz.' + value;
        result = markup.multimedia(input);
        expected = '[img]http://foo.bar/baz.' + value + '[/img]';
        expect(result).toEqual(expected);
      });
    });

    it("replaces dropbox horrible html fubar with download link", function () {
      input = 'https://www.dropbox.com/foo/baz.png';
      result = markup.multimedia(input);
      expected = '[img]https://dl.dropbox.com/foo/baz.png[/img]';
      expect(result).toEqual(expected);
    });

  });

});
