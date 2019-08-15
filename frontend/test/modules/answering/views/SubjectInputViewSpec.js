import AnswerModel from 'modules/answering/models/AnswerModel';
import { SubjectInputView as View } from 'modules/answering/views/SubjectInputVw';
import _ from 'underscore';

describe('answering form', function () {
  describe('subject input field', function () {
    it('initializes from existing value', function () {
      const subject = 'existing content';

      const view = new View({
        model: new AnswerModel({ subject }),
      });
      view.render();

      const result = view.getUI('input').val();
      expect(result).toEqual(subject);
    });

    it('is not required on answers', function () {
      const subject = 'existing content';

      const view = new View({ model: new AnswerModel() }).render();

      const result = view.getUI('input');
      expect(result).toHaveAttr('required');
    });

    it('is required on new threads', function () {
      const subject = 'existing content';

      const view = new View({ model: new AnswerModel({ 'pid': 1 }) }).render();

      const result = view.getUI('input');
      expect(result).not.toHaveAttr('required');
    });

    it('initializes char counter', function () {
      const subject = '12345';
      const max = 100;

      const view = new View({
        max,
        model: new AnswerModel({ subject }),
      });
      view.render();

      const result = view.getUI('counter').html();
      expect(result).toEqual(max - subject.length + '');

      const progressBar = view.getUI('progressBar');
      expect(progressBar).toHaveCss({ width: (100 * subject.length / max) + '%' });
    });

    it('updates char counter number', function () {
      const max = 50;
      const model = new AnswerModel();
      const view = new View({ max, model });
      view.render();

      /// start out with empty subject
      let result = view.getUI('counter').html();
      expect(result).toEqual(max + '');

      const progressBar = view.getUI('progressBar');
      expect(progressBar).toHaveCss({ width: '0%' });

      /// change subject
      const subject = 'funky';
      const input = view.getUI('input');
      input.val(subject);
      input.trigger('input');

      result = view.getUI('counter').html();
      expect(result).toEqual(max - subject.length + '');

      expect(progressBar).toHaveCss({ width: (100 * subject.length / max) + '%' });
    });

    it('has a maxlength attribute', function () {
      const max = 99;
      const model = new AnswerModel();
      const view = new View({ max, model });
      view.render();

      const result = view.getUI('input');

      expect(result).toHaveAttr('maxlength', '99');
    });

  });
});
