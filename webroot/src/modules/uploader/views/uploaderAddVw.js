import Marionette from 'backbone.marionette';
import App from 'models/app';
import humanize from 'humanize';
import SpinnerVw from 'views/spinnerVw';
import Template from '../templates/uploaderAddTpl.html';

export default Marionette.View.extend({
  className: 'imageUploader-add',

  template: Template,

  templateContext: () => {
    // fct: App.settings isn't initialized at template creation
    return {
      uploadSize: humanize.filesize(App.settings.get('upload_max_img_size')),
    }
  },

  ui: {
    dropLayer: '.js-drop',
    indicator: '.js-indicator',
    inputFile: '#Upload0File',
    heading: 'h2',
  },

  regions: {
    spinner: '.js-imageUploadSpinner',
  },

  events: {
    'dragleave @ui.dropLayer': '_handleDragLeave',
    'dragover @ui.dropLayer': '_handleDragOver',
    'drop @ui.dropLayer': '_handleDrop',
    'change @ui.inputFile': '_uploadManual',
  },

  onRender: function () {
    this._initDropUploader();
  },

  _uploadManual: function (event) {
    event.preventDefault();

    const formData = new FormData();
    const input = this.getUI('inputFile')[0];
    formData.append(
      input.name,
      input.files[0]
    );

    this._send(formData);
  },

  /**
   * Sends form-data via ajax
   */
  _send: function (formData) {
    this.showChildView('spinner', new SpinnerVw());

    const xhr = new XMLHttpRequest();
    xhr.open('POST', App.settings.get('apiroot') + 'uploads');
    xhr.setRequestHeader('Accept', 'application/json, text/javascript');
    xhr.setRequestHeader('Authorization', 'bearer ' + App.settings.get('jwt'));

    const onError = (msg) => {
      App.eventBus.trigger('notification', {
        type: 'error',
        message: msg || $.i18n.__('upload_genericError'),
      });
    };

    xhr.onloadend = (request) => {
      this.detachChildView('spinner');
      // clears out form field
      this.render();

      if (('' + xhr.status)[0] !== '2') {
        var msg = null;
        try {
          msg = JSON.parse(xhr.responseText).errors[0].title;
        } catch (e) {
          onError();
        }
        onError(msg);
        return;
      }

      this.collection.add(JSON.parse(xhr.responseText).data.attributes);
    }
    xhr.onerror = onError;

    xhr.send(formData);
  },

  _initDropUploader: function () {
    const layer = this.getUI('dropLayer')[0];
    const supported = ('draggable' in layer) || ('ondragstart' in layer && 'ondrop' in layer);

    if (!supported || !window.FileReader) {
      this.getUI('dropLayer').remove();
      return;
    }

    this.getUI('heading').html($.i18n.__('upload_new_title'));
  },

  _handleDrop: function (event) {
    this._handleDragLeave(event);
    const files = event.originalEvent.dataTransfer.files;
    const formData = new FormData();
    formData.append(
      'upload[0][file]',
      files[0]
    );

    this._send(formData);
  },

  _handleDragOver: function (event) {
    event.preventDefault();
    this.getUI('indicator').removeClass('fadeOut').addClass('fadeIn');
  },

  _handleDragLeave: function (event) {
    event.preventDefault();
    this.getUI('indicator').removeClass('fadeIn').addClass('fadeOut');
  },
});
