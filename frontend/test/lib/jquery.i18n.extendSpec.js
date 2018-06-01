import $ from 'jquery';
import 'lib/jquery.i18n/jquery.i18n.extend';

describe('jquery.i18n.extend', function () {
  describe('replaces :token tags', function () {
    beforeEach(function (done) {
      $.i18n.setDict({});
      done();
    });

    it('in a s string not in the dictionary', function () {
      var result,
        expected = 'This; is better than fu :tokenNo nothing';
      result = $.i18n.__(
        ':tokenA; is :token_b than fu :tokenNo :token-c',
        {
          tokenA: 'This',
          token_b: 'better',
          'token-c': 'nothing'
        }
      );
      expect(result).toEqual(expected);
    });

    it('in a string in the dictionary', function () {
      var expected,
        result;

      $.i18n.setDict({
        'token test': ':tokenA; is :token_b than fu :tokenNo :token-c'
      });

      expected = 'This; is better than fu :tokenNo nothing';
      result = $.i18n.__(
        'token test',
        {
          tokenA: 'This',
          token_b: 'better',
          'token-c': 'nothing'
        }
      );
      expect(result).toEqual(expected);
    });

  });

});
