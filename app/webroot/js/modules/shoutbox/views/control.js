define([
    'jquery', 'underscore', 'backbone', 'marionette',
    'text!modules/shoutbox/templates/control.html'
], function($, _, Backbone, Marionette, Tpl) {

    "use strict";

    var ShoutboxView = Marionette.ItemView.extend({

        template: Tpl

    });

    return ShoutboxView;
});
