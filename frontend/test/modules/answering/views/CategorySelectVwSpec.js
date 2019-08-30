import AnswerModel from 'modules/answering/models/AnswerModel';
import CategorySelect from 'modules/answering/views/CategorySelectVw';
import { SubjectInputView as View } from 'modules/answering/views/SubjectInputVw';
import _ from 'underscore';

describe('answering form', function () {
  const categories = { 1: 'Ontopic', 2: 'Offtopic'};
  let model;

  beforeEach(() => {
    model = new AnswerModel();
  })

  afterEach(() => {
    model.destroy();
  })


  describe('category select', function () {
    it('shows with autoselect category false', function () {
      const view = new CategorySelect({
          categories,
          autoselectCategory: false,
          model,
      }).render();

      const html = view.getUI('select');

      expect(html).toContainHtml('<option value=""></option>');
    });

    it('shows with autoselect category true', function () {
      const view = new CategorySelect({
          categories,
          autoselectCategory: true,
          model,
      }).render();

      const html = view.getUI('select');

      expect(html).not.toContainHtml('<option value=""></option>');
    });

    it('shows categories', function () {
      const view = new CategorySelect({
          categories,
          model,
      }).render();

      const html = view.getUI('select');

      expect(html).toContainElement('option[value=1]:contains("Ontopic")');
      expect(html).toContainElement('option[value=2]:contains("Offtopic")');
    });

    it('selects the category', () => {
      model.set('category_id', 2);
      const view = new CategorySelect({
          categories,
          model,
      }).render();

      const html = view.getUI('select');
      expect(html).toContainElement('option[value=2][selected=selected]');
    });
  });
});
