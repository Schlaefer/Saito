import $ from 'jquery';
import Backbone from 'backbone';
import Marionette from 'backbone.marionette';
import UploaderItemView from '../views/uploaderItemVw';
import { NoContentView as EmptyView } from 'views/NoContentView.ts';
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
      model: new Backbone.Model({ content: $.i18n.__('upl.ncy') })
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
    new Blazy({
      // lazy load elements inside a scrolling container: selector of the container
      container: '#saito-modal-dialog',
      success: (el) => {
        // ugly hack to get to the parent here
        $(el).parent().parent().find('.image-uploader-spinner').remove();
      }
    });
  }
});
