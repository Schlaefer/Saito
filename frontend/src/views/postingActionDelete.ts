import { Model } from 'backbone';
import { View } from 'backbone.marionette';
import $ from 'jquery';
import App from 'models/app';
import ModalDialog from 'modules/modalDialog/modalDialog';
import _ from 'underscore';

/**
 * Dialog for deleteing a posting.
 */
export default class extends View<Model> {
    public constructor(options: any = {}) {
        _.defaults(options, {
            events: {
                'click @ui.abort': '_onAbort',
                'click @ui.submit': '_onSubmit',
            },
            template: _.template(`
<div class="panel">
  <div class="panel-content">
      <p>
          <%- $.i18n.__('tree.delete.confirm') %>
      </p>
  </div>
  <div class="panel-footer panel-form">
      <button class="btn btn-primary js-abort"><%- $.i18n.__('posting.delete.abort.btn') %></button>
      &nbsp;
      <button class="btn btn-link js-delete"><%- $.i18n.__('posting.delete.title') %></button>
  </div>
</div>
  `),
            ui: {
                abort: '.js-abort',
                submit: '.js-delete',
            },

        });
        super(options);
    }

    public onRender() {
        ModalDialog.show(this, { title: $.i18n.__('posting.delete.title') });
    }

    private _onAbort(event: Event) {
        event.preventDefault();
        ModalDialog.hide();
    }

    private _onSubmit(event: Event) {
        event.preventDefault();
        const id = this.model.get('id');
        const url = App.settings.get('webroot') + 'entries/delete/' + id;
        window.redirect(url);
    }

    private onBeforeClose() {
        this.destroy();
    }
}
