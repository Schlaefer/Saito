define(['backbone', 'modules/usermap/models/user'],
    function(Backbone, UserModel) {

      'use strict';

      var UsersCollection = Backbone.Collection.extend({

        model: UserModel

      });

      return UsersCollection;

    });
