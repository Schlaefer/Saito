import AnswerModel from 'modules/answering/models/AnswerModel';
import { SubjectInputView as View } from 'modules/answering/SubjectInputView';
import $ from 'jquery';
import _ from 'underscore';
import App from 'models/app';

describe('answering form', function () {
  describe('subject input field', function () {
    const fixture = `
<div class="postingform-subject-wrapper">
    <input type="text" name="subject" maxlength="75" class="js-subject postingform-subject form-control" value="">
    <div class="postingform-subject-progress">
      <div class="js-progress" style="width: 0;"></div>
    </div>
    <div class="postingform-subject-count"></div>
</div>
    `;

    const maxlength = 75;

    let view;
    beforeEach(function () {
      App.settings.set('subject_maxlength', maxlength);
      setFixtures(fixture);
    });

    afterEach(function () {
      if (view) view.destroy();
    });

    it('initializes from non-empty field value', function () {
      const content = 'existing content';
      $('input').attr('value', content);
      view = new View({ el: '.postingform-subject-wrapper', model: new AnswerModel()});

      const expected = (maxlength - content.length) + '';
      const result = view.getUI('counter').html();
      expect(result).toEqual(expected);
    });

    it('throws error if max length is not set', function () {
      App.settings.set('subject_maxlength', null);
      expect(() => { new View({ el: '.postingform-subject-wrapper' }) })
        .toThrowError(Error, 'No subject_maxlength in App settings.');
    });
  });
});
