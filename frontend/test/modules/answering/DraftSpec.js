import $ from 'jquery';
import { DraftModel, DraftView } from 'modules/answering/Draft.ts';

describe('answering form', () => {
  describe ('with draft' , () => {
    it('sends new draft', (done) => {
      const model = new DraftModel();
      const view = new DraftView({
        model,
        timers: { early: 1, debounce: 1, long: 1 }
      });
      spyOn(view, 'send').and.callThrough();
      spyOn($, 'ajax');

      view.render();
      const data = { subject: 'foo', text: 'bar' };
      model.set(data);

      setTimeout(
        () => {
          expect(view.send).toHaveBeenCalled();

          const request = $.ajax.calls.mostRecent().args[0];
          expect(JSON.parse(request.data)).toEqual(data);
          expect(request.type).toEqual('POST');
          expect(request.url).toEqual('/test/root/api/v2/drafts/');

          done();
        },
        4 // wait for long and short timer to fire
      );
    });

    it('does not send a new draft with empty fields', (done) => {
      const model = new DraftModel();
      const view = new DraftView({
        model,
        timers: { early: 1, debounce: 1, long: 1 }
      });
      spyOn(view, 'send').and.callThrough();
      spyOn($, 'ajax');

      view.render();
      const data = { subject: '', text: '' };
      model.set(data);

      setTimeout(
        () => {
          expect(view.send).not.toHaveBeenCalled();
          done();
        },
        4 // wait for long and short timer to fire
      );
    });

    it('updates an existing draft', (done) => {
      const model = new DraftModel();
      const view = new DraftView({
        model,
        timers: { early: 1, debounce: 1, long: 1 }
      });
      spyOn(view, 'send').and.callThrough();
      spyOn($, 'ajax');

      view.render();
      const data = { 'id': 5, subject: 'foo', text: 'bar' };
      model.set(data);

      setTimeout(
        () => {
          expect(view.send).toHaveBeenCalled();

          const request = $.ajax.calls.mostRecent().args[0];
          expect(JSON.parse(request.data)).toEqual(data);
          expect(request.type).toEqual('PUT');
          expect(request.url).toEqual('/test/root/api/v2/drafts/5');

          done();
        },
        4 // wait for long and short timer to fire
      );
    });

    it('updates an existing draft with empty fields', (done) => {
      const model = new DraftModel();
      const view = new DraftView({
        model,
        timers: { early: 1, debounce: 1, long: 1 }
      });
      spyOn(view, 'send').and.callThrough();
      spyOn($, 'ajax');

      view.render();
      const data = { 'id': 5, subject: '', text: '' };
      model.set(data);

      setTimeout(
        () => {
          expect(view.send).toHaveBeenCalled();

          const request = $.ajax.calls.mostRecent().args[0];
          expect(JSON.parse(request.data)).toEqual(data);
          expect(request.type).toEqual('PUT');
          expect(request.url).toEqual('/test/root/api/v2/drafts/5');

          done();
        },
        4 // wait for long and short timer to fire
      );
    });

  });
});
