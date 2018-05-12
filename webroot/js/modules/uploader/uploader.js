define(
  [
    'underscore',
    'marionette',
    'modules/uploader/views/uploaderAddVw',
    'modules/uploader/views/uploaderCollectionVw',
    'modules/uploader/collections/uploads',
    'views/spinnerVw',
    'text!modules/uploader/templates/uploaderTpl.html',
  ],
  function (
    _,
    Marionette,
    AddView,
    CollectionView,
    UploadsCollection,
    SpinnerVw,
    Tpl,
  ) {
    'use strict';

    const UploaderView = Marionette.View.extend({
      regions: {
        addRegion: '.js-imageUploader-add',
        collectionRegion: '.js-imageUploader-list',
      },

      template: _.template(Tpl),

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

    return UploaderView;
  });
