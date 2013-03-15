define([
'underscore',
'backbone',
'models/app'
], function(_, Backbone, App) {

        "use strict";

		var PreviewModel = Backbone.Model.extend({

            defaults: {
                rendered: "",
                data: "",
                fetchingData: 0
            },

            initialize: function() {
                this.webroot = App.settings.get('webroot');

                this.listenTo(this, 'change:data', this._fetchRendered);
            },

            _fetchRendered: function() {
                this.set('fetchingData', 1);
                $.post(
                    this.webroot + 'entries/preview/',
                    this.get('data'),
                    _.bind(function(data) {
                        this.set('fetchingData', 0);
                        this.set('rendered', data.html);
                        App.eventBus.trigger('notificationUnset', 'all');
                        App.eventBus.trigger(
                            'notification',
                            data
                        );
                    }, this),
                    'json'
                );
            }

		});

		return PreviewModel;
	});