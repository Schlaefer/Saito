import Bb from 'backbone';
import Mn from 'backbone.marionette';
import ChildView from 'views/answeringSmileyVw';

export default Mn.CollectionView.extend({
  className: 'collapsablet panel-input flex-row flex-wrap',
  childView: ChildView,
  childViewTriggers: {
    // pass insert on to answering form
    'answering:insert': 'answering:insert',
  },
});
