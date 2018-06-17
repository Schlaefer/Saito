import Marionette from 'backbone.marionette';
import App from 'models/app';
import Template from '../templates/uploadItemTpl.html';

export default Marionette.View.extend({
  className: 'card',

  regions: {
    rgForm: '.js-rgForm',
  },

  template: Template,

  ui: {
    btnDelete: '.js-btnDelete',
  },

  events: {
    'click @ui.btnDelete': '_delete',
  },

  initialize: function (options) {
    this.InsertVw = options.InsertVw || null;
  },

  onRender: function () {
    if (this.InsertVw) {
      this.showChildView('rgForm', new this.InsertVw({ model: this.model }));
    }

    //// delay display of loading spinner
    this.$('.image-uploader-spinner').css('visibility', 'hidden');
    _.delay(() => { this.$('.image-uploader-spinner').css('visibility', 'visible'); }, 2000);
  },

  /**
   * deletes upload
   */
  _delete: function (event) {
    event.preventDefault();

    this.model.destroy({
      error: (model, response) => {
        msg = response.responseJSON.errors[0];
        App.eventBus.trigger('notification', { message: msg, type: 'error' });
      },
    });
  },
});
