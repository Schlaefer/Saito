import Bb from 'backbone';
import Mn from 'backbone.marionette';
import App from 'models/app';
import Tpl from '../templates/answeringSmileyTpl.html';

export default Mn.View.extend({
  tagName: 'span',
  template: Tpl,
  model: Bb.Model,
  ui: {
    button: 'button',
  },
  events: {
    'click @ui.button': 'handleClick',
  },
  templateContext: () => {
    return {
      theme: App.settings.get('theme').toLowerCase(),
      webroot: App.settings.get('webroot'),
    }
  },
  handleClick: function (event) {
    event.preventDefault();
    // additional space to prevent smiley concatenation:
    // `:cry:` and `(-.-)zzZ` becomes `:cry:(-.-)zzZ` which outputs
    // smiley image for `:(`
    const text = ' ' + this.model.get('code');
    this.trigger('answering:insert', text, { focus: false });
  }
});
