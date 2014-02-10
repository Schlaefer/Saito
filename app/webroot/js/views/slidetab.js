define([
    'jquery',
    'underscore',
    'backbone'
], function($, _, Backbone, ShoutModel, ShoutsView) {

    "use strict";

    var SlidetabView = Backbone.View.extend({

        events: {
            "click .slidetab-tab": "clickSlidetab"
        },

        initialize: function(options) {
            this.collection = options.collection;
            this.model.set({isOpen: this.isOpen()}, {silent: true});

            this.listenTo(this.model, 'change', this.toggleSlidetab);
        },

        isOpen: function() {
            return this.$el.find(".slidetab-outer").is(":visible");
        },

        clickSlidetab: function(model) {
            this.model.save('isOpen', !this.model.get('isOpen'));
        },

        toggleSlidetab: function() {
            if (this.model.get('isOpen')) {
                this.show();
            } else {
                this.hide();
            }
            this.toggleSlidetabTabInfo();
        },

        show: function() {
            this.$el.animate({
                'width': 280
            });
            this.$el.addClass('is-open');
        },

        hide: function() {
            this.$el.animate(
                {
                    'width': 28
                },
                _.bind(function() {
                    this.$el.removeClass('is-open');
                }, this)
            );
        },

        toggleSlidetabTabInfo: function() {
            this.$el.find('.slidetab-tab-info').toggle();
        }

    });

    return SlidetabView;

});
