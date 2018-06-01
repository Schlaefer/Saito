import _ from 'underscore';
import Marionette from 'backbone.marionette';
import AddView from './views/uploaderAddVw';
import CollectionView from './views/uploaderCollectionVw';
import UploadsCollection from './collections/uploads.ts';
import SpinnerVw from 'views/spinnerVw';
import Tpl from './templates/uploaderTpl.html';

export default Marionette.View.extend({
  regions: {
    addRegion: '.js-imageUploader-add',
    collectionRegion: '.js-imageUploader-list',
  },

  template: Tpl,

  /**
   * Backbone initializer
   */
  initialize: function (options) {
    this.collection = new UploadsCollection();
    this.InsertVw = options.InsertVw || null;
  },

  /**
   * Marionette onRender callback
   */
  onRender: function () {
    this.showChildView('addRegion', new AddView({ collection: this.collection }));
    this.showChildView('collectionRegion', new SpinnerVw());

    this.collection.fetch({
      success: collection => {
        const clV = new CollectionView({
          collection: collection,
          InsertVw: this.InsertVw,
        });
        this.showChildView('collectionRegion', clV);
      }
    });
  },
});
