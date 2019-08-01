import AnswerModel from 'modules/answering/models/AnswerModel';

describe('answer model', function () {
  describe('default value', function () {
    let model;

    beforeEach(function () {
      model = new AnswerModel();
    });

    afterEach(function () {
      model.destroy()
    });

    it('subject', () => {
      expect(model.get('subject')).toEqual('');
    });

    it('text', () => {
      expect(model.get('text')).toEqual('');
    });

    it('pid', () => {
      expect(model.get('pid')).toBeUndefined();
    });
  })
});
