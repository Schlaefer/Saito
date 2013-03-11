define([], function(SlideTabModel) {

    "use strict";

    describe("Slidetab model", function () {

        beforeEach(function () {
            var flag = false,
                that = this;

            this.webroot = '/foo/bar/';

            this.server = sinon.fakeServer.create();

            require(['models/slidetab', 'models/app'], _.bind(function(Model, App) {
                App.settings.set('webroot', this.webroot);
                that.model = new Model({
                    id: "testSlider"
                });
                flag = true;
            }, this));

            waitsFor(function() {
                return flag;
            });
        });

        afterEach(function() {
            this.server.restore();
        });

        it('to toggle show at users/ajax_toggle/…', function() {
            this.model.save();
            expect(this.server.requests.length)
                .toEqual(1);
            expect(this.server.requests[0].method)
                .toEqual("GET");
            expect(this.server.requests[0].url)
                .toEqual(this.webroot + "users/ajax_toggle/show_" + this.model.get('id'));
        });

    });
});
