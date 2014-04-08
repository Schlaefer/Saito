define(['underscore', 'app/vent'], function(_, Vent) {

  'use strict';

  var webroot = Vent.reqres.request('webroot');
  var templateHelper = {
    webroot: webroot
  };

  return {
    User: _.extend(templateHelper, {
      templates: {
        linkToUserProfile: _.template('<a href="<%- url %>"><%- name %></a>')
      },

      /**
       * generates link to user profile
       *
       * @param id user id
       * @param name user name
       * @returns string
       */
      linkToUserProfile: function(id, name) {
        var url = this.urlToUserProfile(id);
        return this.templates.linkToUserProfile({url: url, name: name});
      },

      urlToUserProfile: function(id) {
        return this.webroot + 'users/view/' + id;
      }
    })
  };

});
