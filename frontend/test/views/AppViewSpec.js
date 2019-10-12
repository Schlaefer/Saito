import App from 'models/app';
import View from 'views/app';
import AnswerModel from 'modules/answering/models/AnswerModel.ts';

describe("App", function () {
  describe("View", function () {
    let view;

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

      App.request.set(SaitoApp.request);
      view = new View();
      done();
    });

    it('manually mark as read should call entries/update', function () {
      spyOn(window, 'redirect');

      view.manuallyMarkAsRead();

      expect(window.redirect).toHaveBeenCalledWith(
        '/test/root/entries/update'
      );
    });

    describe('initialize answer view from DOM', () => {
      it('redirects on successful answer', () => {
        spyOn($, 'ajax'); // suppress ajax calls
        spyOn(window, 'redirect');

        const answeringView = view._initAnsweringNotInlined($('<div><div>'));

        const model = new AnswerModel({'id': 9});
        answeringView.trigger('answering:send:success', model);

        expect(window.redirect).toHaveBeenCalledWith('/test/root/entries/view/9');
      });

      it('inits model-ID from DOM on edit', () => {
        spyOn($, 'ajax'); // suppress ajax calls
        const answeringView = view._initAnsweringNotInlined($('<div data-edit="9"><div>'));
        expect(answeringView.model.get('id')).toBe(9);
      })
    });

  });
});
