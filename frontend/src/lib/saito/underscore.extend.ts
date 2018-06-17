import * as _ from 'underscore';

declare module 'underscore' {
    // tslint:disable-next-line:interface-name
    interface UnderscoreStatic {
        char(text: string): number;
        property(key: string|string[]): (object: object) => any;
    }
}

_.mixin({
  /**
   * Calculate chars in string
   *
   * @param {string} string
   * @return int
   */
  chars: (text: string) => {
    const twoByteEmojis = text.match(/(\uD83C[\uDF00-\uDFFF]|\uD83D[\uDC00-\uDFFF])/g) || [];
    const count = text.length - twoByteEmojis.length;
    return count;
  },
});
