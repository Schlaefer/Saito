import AnswerModel from 'modules/answering/models/AnswerModel';
import CategorySelect from 'modules/answering/views/CategorySelectVw';
import _ from 'underscore';

describe('answering form', function () {
  const categories = [
    { id: 1, title: 'Ontopic'},
    { id: 2, title: 'Offtopic'}
  ];
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

      debugger;
      expect(html).toContainHtml(`<option value="" disabled="disabled" selected="selected">answer.cat.l</option>`);
    });

    it('shows with autoselect category true', function () {
      const view = new CategorySelect({
          categories,
          autoselectCategory: true,
          model,
      }).render();

      const html = view.getUI('select');

      expect(html).not.toContainHtml(`<option value="" disabled="disabled" selected="selected">answer.cat.l</option>`);
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
