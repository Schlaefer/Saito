define([], function () {

    "use strict";

    return function(controller) {

        describe('uses CakePHP urls', function() {

            beforeEach(function (done) {
                var that = this;

                this.webroot = '/foo/bar/';

                this.server = sinon.fakeServer.create();

                require(['models/' + controller, 'models/app'], _.bind(function(Model, App) {
                    App.settings.set('webroot', this.webroot);
                    that.model = new Model();
                    done();
                }, this));

            });

            afterEach(function() {
                this.server.restore();
            });

            it('add/ for create', function() {
                this.model.save();
                expect(this.server.requests.length)
                    .toEqual(1);
                expect(this.server.requests[0].method)
                    .toEqual("POST");
                expect(this.server.requests[0].url)
                    .toEqual(this.webroot + controller + "s/add");
            });

            it('view/#id for read', function() {
                this.model.save({
                    id: 5
                });
                this.model.fetch();
                expect(this.server.requests.length)
                    .toEqual(2);
                expect(this.server.requests[1].method)
                    .toEqual("GET");
                expect(this.server.requests[1].url)
                    .toEqual(this.webroot + controller + "s/view/" + this.model.get('id'));
            });

            it('edit/#id for update', function() {
                this.model.save({
                    id: 5
                });
                expect(this.server.requests.length)
                    .toEqual(1);
                expect(this.server.requests[0].method)
                    .toEqual("PUT");
                expect(this.server.requests[0].url)
                    .toEqual(this.webroot + controller + "s/edit/" + this.model.get('id'));
            });

            it('delete/#id for delete', function() {
                this.model.save({ id: 5 });
                this.model.destroy();
                expect(this.server.requests.length)
                    .toEqual(2);
                expect(this.server.requests[1].method)
                    .toEqual("DELETE");
                expect(this.server.requests[1].url)
                    .toEqual(this.webroot + controller + "s/delete/" + this.model.get('id'));
            });
        });
    };
});
