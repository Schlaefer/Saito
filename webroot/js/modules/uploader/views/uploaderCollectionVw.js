define([
  'jquery',
  'underscore',
  'backbone',
  'marionette',
  'modules/uploader/views/uploaderItemVw',
  'views/noContentYetVw',
  'blazy',
], function (
  $,
  _,
  Backbone,
  Marionette,
  UploaderItemView,
  EmptyView,
  Blazy,
  ) {
    'use strict';

    return Marionette.CollectionView.extend({
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

      childViewOptions: function() {
        return {
          InsertVw: this.InsertVw,
        };
      },

      initialize: function(options) {
        this.InsertVw = options.InsertVw || null;
      },

      onRender: function () {
        this.initLazyLoading();
      },

      initLazyLoading: function() {
        new Blazy();
      }
    });
  });
