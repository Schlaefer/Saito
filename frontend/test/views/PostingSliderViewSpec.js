import PostingSliderView from 'views/PostingSliderView';
import { PostingModel } from 'modules/posting/models/PostingModel';
import AnswerModel from 'modules/answering/models/AnswerModel';
import App from 'models/app';

describe('posting slider', () => {
  describe('childview sent a new answer', () => {
    describe('on non-inline form', () =>  {
      it('redirects to new posting', () => {
        const model = new PostingModel();
        const view = new PostingSliderView({model});
        const answerModel = new AnswerModel({id: 20});

        App.request.set({action: 'non-specific-action-triggering-default-route'});
        spyOn(window, 'redirect');

        view.triggerMethod('childview:answering:send:success', answerModel);

        expect(window.redirect).toHaveBeenCalledWith('/test/root/entries/view/20');
      });

      it('redirects to new mix posting', () => {
        const model = new PostingModel();
        const view = new PostingSliderView({model});
        const answerModel = new AnswerModel({id: 20});

        App.request.set({action: 'mix'});
        spyOn(window, 'redirect');

        view.triggerMethod('childview:answering:send:success', answerModel);

        expect(window.redirect).toHaveBeenCalledWith('/test/root/entries/mix/20');
      });
    });
  })
});
