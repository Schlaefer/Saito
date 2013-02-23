define([
	'underscore',
	'backbone',
    'models/app'
	], function(_, Backbone, App) {

		var PreviewModel = Backbone.Model.extend({

            defaults: {
                rendered: "",
                data: ""
            },

            initialize: function() {
                this.webroot = App.settings.get('webroot');

                this.listenTo(this, 'change:data', this._fetchRendered)
            },

            _fetchRendered: function() {
                $.post(
                    this.webroot + 'entries/preview/',
                    this.get('data'),
                    _.bind(function(data) {
                        this.set('rendered', data);
                    }, this)
                )
            }

		});

		return PreviewModel;
	});