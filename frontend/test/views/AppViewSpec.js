import App from 'models/app';
import View from 'views/app';

describe("App", function () {

  describe("View", function () {

    beforeEach(function (done) {
      var SaitoApp = {
        app: {
          settings: {
            webroot: '/web/root/'
          }
        },
        request: {
          controller: 'dummydata'
        }
      };

      App.request = SaitoApp.request;
      this.view = new View();
      done();

    });

    it('manually mark as read should call entries/update', function () {
      spyOn(window, 'redirect');

      this.view.manuallyMarkAsRead();

      expect(window.redirect).toHaveBeenCalledWith(
        '/test/root/entries/update'
      );
    });
  });
});
