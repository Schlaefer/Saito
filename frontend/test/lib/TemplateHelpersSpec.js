import $ from 'jquery';
import moment from 'moment';
import TemplateHelpers from 'lib/saito/templateHelpers';
// import 'lib/jquery.i18n/jquery.i18n.extend.js';

$.i18n.setDictionary({});


describe('Template Helpers', function () {

  describe('Time', function () {

    describe('format', function () {

      beforeEach(function (done) {
        this.Time = TemplateHelpers.Time;
        done();
      });

      it('should wrap into time-tag', function () {
        var result = this.Time.format(0);
        // @todo ?!
        // expect($(result)).toEqual('time');
      });

      it('option {wrap: false} should not wrap into time-tag', function () {
        var result = this.Time.format(0, null, { wrap: false });
        expect($(result)).not.toEqual('time');
      });

      it('should output title tag on time-tag', function () {
        var result = this.Time.format(1397487471);
        expect($(result)).toHaveAttr('title', '2014-04-14 16:57:51');
      });

      it('should accept JS-millisecond timestamp', function () {
        var result = this.Time.format(1397487471);
        expect($(result)).toHaveAttr('title', '2014-04-14 16:57:51');
      });

      it('should output attribute datetime on time-tag', function () {
        var result = this.Time.format(1397487471); // 2014-04-14 14:57:51 UTC
        expect($(result)).toHaveAttr('datetime', '2014-04-14T16:57:51+02:00');
      });

      it('format "normal" should show time because it\'s yesterday but withing the last 6 hours', function () {
        var date = moment([2004, 8, 15, 23, 48]),
          result;

        spyOn(this.Time, 'now').and.returnValue(moment([2004, 8, 16, 4, 23]));
        result = this.Time.format(date);
        expect($(result)).toHaveHtml('23:48');
      });

      it('format "normal" should show time because it is over 6 hours but today', function () {
        var date = moment([2004, 8, 16, 4, 48]),
          result;

        spyOn(this.Time, 'now').and.returnValue(moment([2004, 8, 16, 15, 23]));
        result = this.Time.format(date);
        expect($(result)).toHaveHtml('04:48');
      });

      it('format "normal" should show "yesterday"', function () {
        var date = moment([2004, 8, 15, 23, 48]),
          result;

        spyOn(this.Time, 'now').and.returnValue(moment([2004, 8, 16, 15, 23]));
        result = this.Time.format(date);
        expect($(result)).toHaveHtml('time.relative.yesterday 23:48');
      });

      it('format "normal" should show date', function () {
        var date = moment([2004, 8, 15, 23]),
          result;

        spyOn(this.Time, 'now').and.returnValue(moment([2004, 8, 16, 17, 1]));
        result = this.Time.format(date);
        expect($(result)).toHaveHtml('15.09.2004');
      });

    });

  });

});
