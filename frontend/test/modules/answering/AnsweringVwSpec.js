import _ from 'underscore';
import $ from 'jquery';
import AnsweringView from 'modules/answering/answering.ts';
import AnswerModel from 'modules/answering/models/AnswerModel';
import {MetaModel} from 'modules/answering/Meta';

describe('answering form', () => {
  let metaFixture = {
    draft: {
        id: 5,
        subject: 'Draft Subject',
        text: 'Draft Text',
    },
    editor: {
        buttons: [],
        categories: [{ id: 1, title: 'Ontopic'}],
        smilies: [],
    },
    meta: {
        autoselectCategory: true,
        info: '',
        isEdit: false,
        last: '',
        quoteSymbol: '>',
        subject: 'Subject Placeholder',
        text: '> Cited Parent Text',
        subjectMaxLength: 75,
    },
    posting: {},
  };

  describe('loads meta data', () => {
    it('sends request with id', () => {
        const meta = new MetaModel();
        const model = new AnswerModel({ id: 99 });
        const view = new AnsweringView({meta, model});

        spyOn(meta, 'set');
        spyOn(meta, 'fetch');

        view.render();

        expect(meta.set).toHaveBeenCalledWith('id', 99);
        expect(meta.fetch).toHaveBeenCalled();
    })

    it('sends request with pid', () => {
        const meta = new MetaModel();
        const model = new AnswerModel({ pid: 815 });
        const view = new AnsweringView({meta, model});

        spyOn($, 'ajax');
        view.render();

        expect($.ajax.calls.mostRecent().args[0]['data']).toEqual({ pid: 815 });
    })

    describe('fails', () => {
      it('and calls error handler', () => {
        const meta = new MetaModel();
        spyOn(meta, 'fetch').and.callFake((params) => { params.error() });
        const view = new AnsweringView({meta});
        spyOn(view, 'onAnsweringLoadError');

        view.render();

        expect(view.onAnsweringLoadError).toHaveBeenCalled();
      });
    });

    describe('and succeeds', () => {
      it('calling the main build routine', () => {
        const meta = new MetaModel();
        spyOn(meta, 'fetch').and.callFake((params) => { params.success() });

        const view = new AnsweringView({meta});
        spyOn(view, 'onAnsweringLoadSuccess');
        view.render();

        expect(view.onAnsweringLoadSuccess).toHaveBeenCalled();
      });

      describe('if posting is an answer', () => {
        /**
         * Not fully implemented yet. Only drafts so far.
         * @todo
         */
        it ('initializes all the subregions from the meta data', () => {
          const meta = new MetaModel(metaFixture);
          const model = new AnswerModel({ pid: 99 });
          const view = new AnsweringView({model, meta});
          view.render();

          /// Check drafts view
          expect(view.model.get('subject')).toEqual('Draft Subject');
          expect(view.model.get('text')).toEqual('Draft Text');

          const draftView = view.getChildView('drafts');
          expect(draftView.model.get('id')).toEqual(5);
          expect(draftView.model.get('pid')).toEqual(99);
          expect(draftView.model.get('subject')).toEqual('Draft Subject');
          expect(draftView.model.get('text')).toEqual('Draft Text');

          /// Check cite view
          const citeView = view.getChildView('cite');
          expect(citeView.model.get('quoteSymbol')).toEqual('>');
          expect(citeView.model.get('text')).toEqual('> Cited Parent Text');
        });
      });

      describe('if posting is an edit', () => {
        it('populates the subject', () => {
          const fixture = _.clone(metaFixture)
          delete(fixture.draft),
          fixture.posting = {
            id: 99,
            subject: 'The edit subject',
            text: 'The edit text',
          }

          const meta = new MetaModel(fixture);
          const model = new AnswerModel({ pid: 99 });
          const view = new AnsweringView({model, meta});
          view.render();

          expect(view.model.get('subject')).toEqual('The edit subject');
          expect(view.model.get('text')).toEqual('The edit text');
        });
      });
    });
  });

  describe('submit', () => {
    let view;

    beforeEach(() => {
        const meta = new MetaModel(metaFixture);
        const model = new AnswerModel({subject: 'foo', text: 'bar'});
        view = new AnsweringView({meta, model});
    });

    describe('does not validate', () => {
      it('fails', () => {
          view.render();
          spyOn(view, 'checkFormValidity').and.returnValue(false);
          spyOn(view, 'enableAnswering');
          spyOn(view, 'disableAnswering');

          view.triggerMethod('submit');

          expect(view.checkFormValidity).toHaveBeenCalled();
          expect(view.enableAnswering).toHaveBeenCalled();
          expect(view.disableAnswering).toHaveBeenCalled();
      });
    });

    describe('does validate', () => {
      let ajax;

      beforeEach(() => {
        ajax = spyOn($, 'ajax');
      });

      afterEach(() => {
        expect($.ajax.calls.mostRecent().args[0]['url']).toEqual('/test/root/api/v2/postings/');
      });

      it('disables and enables answering', () => {
          ajax.and.callFake((params) => { params.error() })
          view.render();

          const submitBtn = view.getChildView('submitBtn');
          spyOn(submitBtn, 'enable');
          spyOn(submitBtn, 'disable');
          const drafts = view.getChildView('drafts');
          spyOn(drafts, 'enable');
          spyOn(drafts, 'disable');

          submitBtn.trigger('answer:send:submit');

          /// on form start
          expect(drafts.disable).toHaveBeenCalled();
          expect(submitBtn.disable).toHaveBeenCalled();

          /// after ajax error
          expect(drafts.enable).toHaveBeenCalled();
          expect(submitBtn.enable).toHaveBeenCalled();
      });

      describe('connecting to server succeeds', () => {
        describe('server responses with validation errors', () => {
          beforeEach(() => {
            ajax.and.callFake((params) => { params.success() })
          });
          it('shows the validation errors', () => {
            view.render();

            const errors = [
                {"source":{"field":"#category-id"}, "title":"error-category"},
                {"source":{"field":"#subject"}, "title":"error-subject"},
            ];
            ajax.and.callFake((params) => { params.success({ errors }); });
            spyOn(view.errorVw.collection, 'reset').and.callThrough();
            spyOn(view.errorVw, 'render').and.callThrough();

            view.getChildView('submitBtn').trigger('answer:send:submit');

            expect(view.errorVw.collection.reset).toHaveBeenCalledWith(errors);
            expect(view.errorVw.render).toHaveBeenCalled();

            // just in case rudimentary test
            expect(view.$('.vld-msg').get(0)).toContainText('error-category');
            expect(view.$('.vld-msg').get(1)).toContainText('error-subject');
          });
        });
      });

      describe('connecting to server fails', () => {
        beforeEach(() => {
          ajax.and.callFake((params) => { params.error() })
        });

        it('handles the error', () => {
          spyOn(view, 'triggerMethod').and.callThrough();
          view.render();

          view.getChildView('submitBtn').trigger('answer:send:submit');

          expect(view.triggerMethod).toHaveBeenCalledWith('answering:send:error');
        });
      });
    });
  });
});
