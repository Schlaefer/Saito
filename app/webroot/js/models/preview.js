define([
	'underscore',
	'backbone'
	], function(_, Backbone) {

		var PreviewModel = Backbone.Model.extend({

            defaults: {
                rendered: "",
                data: ""
            },

            initialize: function(options) {
                this.webroot = options.webroot;

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