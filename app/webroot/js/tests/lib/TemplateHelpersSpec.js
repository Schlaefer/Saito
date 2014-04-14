define(['jquery', 'moment'],
    function($, moment) {
      describe('Template Helpers', function() {

        describe('Time', function() {

          describe('format', function() {

            beforeEach(function(done) {
              var that = this;

              require(['templateHelpers'], function(TemplateHelpers) {
                that.Time = TemplateHelpers.Time;
                done();
              });
            });

            it('should wrap into time-tag', function() {
              var result = this.Time.format(0);
              expect($(result)).toEqual('time');
            });

            it('option {wrap: false} should not wrap into time-tag', function() {
              var result = this.Time.format(0, null, {wrap: false});
              expect($(result)).not.toEqual('time');
            });

            it('should output title tag on time-tag', function() {
              var result = this.Time.format(1397487471);
              expect($(result)).toHaveAttr('title', '2014-04-14 16:57:51');
            });

            it('should accept JS-millisecond timestamp', function() {
              var result = this.Time.format(1397487471000);
              expect($(result)).toHaveAttr('title', '2014-04-14 16:57:51');
            });

            it('should output attribute datetime on time-tag', function() {
              var result = this.Time.format(1397487471);
              expect($(result)).toHaveAttr('datetime', '2014-04-14T16:57:51+02:00');
            });

            it('format "normal" should show time because it\'s yesterday but withing the last 6 hours', function() {
              var date = moment([2004, 08, 15, 23, 48]),
                  result;

              spyOn(this.Time, 'now').and.returnValue(moment([2004, 08, 16, 04, 23]));
              result = this.Time.format(date);
              expect($(result)).toHaveHtml('23:48');
            });

            it('format "normal" should show time because it is over 6 hours but today', function() {
              var date = moment([2004, 08, 16, 04, 48]),
                  result;

              spyOn(this.Time, 'now').and.returnValue(moment([2004, 08, 16, 15, 23]));
              result = this.Time.format(date);
              expect($(result)).toHaveHtml('04:48');
            });

            it('format "normal" should show "yesterday"', function() {
              var date = moment([2004, 08, 15, 23, 48]),
                  result;

              spyOn(this.Time, 'now').and.returnValue(moment([2004, 08, 16, 15, 23]));
              result = this.Time.format(date);
              expect($(result)).toHaveHtml('yesterday 23:48');
            });

            it('format "normal" should show date', function() {
              var date = moment([2004, 08, 15, 23]),
                  result;

              spyOn(this.Time, 'now').and.returnValue(moment([2004, 08, 16, 17, 01]));
              result = this.Time.format(date);
              expect($(result)).toHaveHtml('15.09.2004');
            });

          });

        });

      });
    });
