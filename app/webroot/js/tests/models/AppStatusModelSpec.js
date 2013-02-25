define([], function() {

    "use strict";

    describe("App status model", function () {

        beforeEach(function () {
            var flag = false,
                that = this;

            this.webroot = '/foo/bar/';

            this.server = sinon.fakeServer.create();

            require(['models/appStatus', 'models/app'], _.bind(function(Model, App) {
                that.model = new Model();
                that.model.setWebroot(this.webroot);
                flag = true;
            }, this));

            waitsFor(function() {
                return flag;
            });
        });

        afterEach(function() {
            this.server.restore();
        });

        it('fetches data from saitos/status/', function() {
            this.model.fetch();
            expect(this.server.requests.length)
                .toEqual(1);
            expect(this.server.requests[0].method)
                .toEqual("GET");
            expect(this.server.requests[0].url)
                .toEqual(this.webroot + "saitos/status/");
        });

    });
});
