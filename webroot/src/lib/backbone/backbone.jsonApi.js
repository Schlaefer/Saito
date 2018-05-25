import Backbone from 'backbone';

Backbone.JsonApiModel = Backbone.Model.extend({
  /** Bb respone parser */
  parse: function (response, options) {
    let data = response;

    // empty response from server (204)
    if (!data) {
      return data;
    }

    // single item is requested from server
    if ('data' in data) {
      data = response.data;
    }

    // item data from attributes
    if ('attributes' in data) {
      data = data.attributes;
    }

    return data;
  },
});

Backbone.JsonApiCollection = Backbone.Collection.extend({
  /** Bb response parser */
  parse: function (response, options) {
    return response.data;
  },
});
