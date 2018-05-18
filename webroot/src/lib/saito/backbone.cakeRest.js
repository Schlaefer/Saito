import Backbone from 'backbone';

export default {

  methodToCakePhpUrl: {
    'read': 'view',
    'create': 'add',
    'update': 'edit',
    'delete': 'delete'
  },

  sync: function (method, model, options) {
    this.urlRoot = this.webroot;
    options = options || {};
    options.url = this.urlRoot + model.methodToCakePhpUrl[method.toLowerCase()];
    if (!this.isNew()) {
      options.url =
        options.url +
        (options.url.charAt(options.url.length - 1) === '/' ? '' : '/') +
        this.id;
    }
    Backbone.sync(method, model, options);
  }
};
