define(['sinon'], function(sinon) {

    "use strict";

    describe("App status model", function () {

        beforeEach(function (done) {
            var flag = false,
                that = this;

            this.webroot = '/foo/bar/';

            this.server = sinon.fakeServer.create();

            require(['models/appStatus', 'models/app'], _.bind(function(Model, App) {
              var webroot = this.webroot;
              that.model = new Model({}, {settings: {
                get: function() { return webroot; }
              }});
              done();
            }, this));

        });

        afterEach(function() {
            delete(this.model);
            this.server.restore();
        });

        it('fetches data from saitos/status/', function() {
            this.model.start();
            expect(this.server.requests.length)
                .toEqual(1);
            expect(this.server.requests[0].method)
                .toEqual("GET");
            expect(this.server.requests[0].url)
                .toEqual(this.webroot + "status/status/");
        });

    });
});
