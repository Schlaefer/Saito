define([], function() {

  'use strict';

  describe('Slidetab model', function() {

    beforeEach(function(done) {
      var that = this;

      this.webroot = '/foo/bar/';

      this.server = sinon.fakeServer.create();

      require(['models/slidetab', 'models/app'], _.bind(function(Model, App) {
        App.settings.set('webroot', this.webroot);
        that.model = new Model({
          id: "testSlider"
        });
        done();
      }, this));

    });

    afterEach(function() {
      this.server.restore();
    });

    it('to toggle show at users/ajax_toggle/…', function() {
      this.model.save();
      expect(this.server.requests.length)
        .toEqual(1);
      expect(this.server.requests[0].method)
        .toEqual('POST');
      expect(this.server.requests[0].url)
        .toEqual(this.webroot + 'users/slidetab_toggle');
    });

  });
});
