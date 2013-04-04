define([
    'jquery',
    'underscore',
    'backbone'
], function($, _, Backbone) {

    "use strict";

    return Backbone.View.extend({

        initialize: function() {
            this.$el.dialog({
                autoOpen: false,
                show: {effect: "scale", duration: 200},
                hide: {effect: "fade", duration: 200},
                width: 400,
                position: [$('#btn-category-chooser').offset().left + $('#btn-category-chooser').width() - $(window).scrollLeft() - 410, $('#btn-category-chooser').offset().top - $(window).scrollTop() + $('#btn-category-chooser').height()],
                title: $.i18n.__('Categories'),
                resizable: false
            });
        },

        toggle: function() {
            if (this.$el.dialog("isOpen")) {
                this.$el.dialog('close');
            } else {
                this.$el.dialog('open');
            }
        }


    });

});

