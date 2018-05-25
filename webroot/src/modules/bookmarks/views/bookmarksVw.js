import Mn from 'backbone.marionette';
import EmptyView from 'views/noContentYetVw';
import ItemView from './bookmarkItemVw';

export default Mn.CollectionView.extend({
  childView: ItemView,
  emptyView: EmptyView,
  emptyViewOptions: () => {
    return {
      model: new Backbone.Model({ content: $.i18n.__('bkm.ncy') })
    }
  },
});
