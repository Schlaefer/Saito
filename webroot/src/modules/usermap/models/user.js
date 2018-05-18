define(['backbone'], function(Backbone) {

  "use strict";

  var UserModel = Backbone.Model.extend({

    latlng: function() {
      return [this.get('lat'), this.get('lng')];
    }

  });

  return UserModel;

});