import $ from 'jquery';
import Backbone from 'backbone';
import Marionette from 'backbone.marionette';
import UploaderItemView from '../views/uploaderItemVw';
import EmptyView from 'views/noContentYetVw';
import Blazy from 'blazy';

export default Marionette.CollectionView.extend({
  className: 'imageUploader-cards',

  childView: UploaderItemView,

  emptyView: EmptyView,

  collectionEvents: {
    'add': 'initLazyLoading',
  },

  emptyViewOptions: () => {
    return {
      model: new Backbone.Model({ content: $.i18n.__('imageUploader.ncy') })
    }
  },

  childViewOptions: function () {
    return {
      InsertVw: this.InsertVw,
    };
  },

  initialize: function (options) {
    this.InsertVw = options.InsertVw || null;
  },

  onRender: function () {
    this.initLazyLoading();
  },

  initLazyLoading: function () {
    new Blazy();
  }
});
