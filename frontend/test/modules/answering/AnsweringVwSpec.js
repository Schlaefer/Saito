import $ from 'jquery';
import AnsweringView from 'modules/answering/answering.ts';

describe('answering form', () => {
  describe ('loads meta data' , () => {
    afterEach(() => {
      expect($.ajax.calls.mostRecent().args[0]['url']).toEqual('/test/root/api/v2/postings/meta/');
    });

    describe('fails', () => {
      it('and calls error handler', () => {
        spyOn($, 'ajax').and.callFake((params) => { params.error() });

        const view = new AnsweringView();
        spyOn(view, 'onAnsweringLoadError');

        view.render();

        expect(view.onAnsweringLoadError).toHaveBeenCalled();
      });
    });

    describe('and succeeds', () => {
      it('calling the main build routine', () => {
        spyOn($, 'ajax').and.callFake((params) => { params.success() });

        const view = new AnsweringView();
        spyOn(view, 'onAnsweringLoadSuccess');
        view.render();

        expect(view.onAnsweringLoadSuccess).toHaveBeenCalled();
      });
    });
  });
});
