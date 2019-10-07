/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

import BbcodeTag from 'lib/saito/Editor/Bbcode/BbcodeTag.ts';

describe('BbcodeTag', () => {
  describe('getters', () => {
    it ('give tag', () => {
      const tag = new BbcodeTag({tag: 'foo'});
      const result = tag.getTag();
      expect(result).toEqual('foo');
    });

    it ('give attributes', () => {
      const tag = new BbcodeTag({tag: 'foo', attributes: 'bar'});
      const result = tag.getAttributes();
      expect(result).toEqual('bar');
    });

    it ('give content', () => {
      const tag = new BbcodeTag({tag: 'foo', content: 'baz'});
      const result = tag.getContent();
      expect(result).toEqual('baz');
    });
  });

  describe('to string outputs', () => {
    it ('outputs full tag', () => {
      const tag = new BbcodeTag(
        { tag: 'foo', attributes: 'bar', content: 'baz', },
        { prefix: 'zip', suffix: 'zap'}
      );

      const result = tag.toString();

      expect(result).toEqual('zip[foo bar]baz[/foo]zap');
    });

    it ('outputs tag with default prefix and suffix', () => {
      const tag = new BbcodeTag({ tag: 'foo'});
      const result = tag.toString();
      expect(result).toEqual('[foo][/foo] ');
    });
  });
});
