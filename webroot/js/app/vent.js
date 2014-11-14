define(['marionette'], function() {
    //noinspection JSHint

    var eventBus = function() {
        this.vent = new Backbone.Wreqr.EventAggregator();
        this.commands = new Backbone.Wreqr.Commands();
        this.reqres = new Backbone.Wreqr.RequestResponse();

        // Request/response, facilitated by Backbone.Wreqr.RequestResponse
        // from marionette
        this.request = function(){
            var args = Array.prototype.slice.apply(arguments);
            return this.reqres.request.apply(this.reqres, args);
        };
    };

    return new eventBus();

});