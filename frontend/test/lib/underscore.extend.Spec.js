import _ from 'underscore';
import 'lib/saito/underscore.extend';

describe('underscore', function () {
  describe('counts chars in _.chars()', function () {
    it('count ASCII only char', function () {
      var input, expected, result;

      input = 'abc d';
      expected = 5;
      result = _.chars(input);
      expect(result).toEqual(expected);
    });

    it('count multibyte chars', function () {
      var input, expected, result;

      input = '🎬';
      expected = 1;
      result = _.chars(input);
      expect(result).toEqual(expected);

      input = 'abc d 🎬🏁';
      expected = 8;
      result = _.chars(input);
      expect(result).toEqual(expected);
    });
  });
});
