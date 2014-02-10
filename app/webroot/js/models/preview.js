define([
'underscore',
'backbone',
'models/app'
], function(_, Backbone, App) {

        "use strict";

		var PreviewModel = Backbone.Model.extend({

            defaults: {
                isFetchingData: false,
                rendered: null,
                data: null
            },

            initialize: function() {
                this.webroot = App.settings.get('webroot');

                this.listenTo(this, 'change:data', this._fetchRendered);
            },

            _fetchRendered: function() {
                this.set('fetchingData', true);
                $.post(
                    this.webroot + 'entries/preview/',
                    this.get('data'),
                    _.bind(function(data) {
                        this.set('fetchingData', false);
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