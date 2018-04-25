define(['marionette', 'backbone.radio'], function(Marionette, Radio) {
    //noinspection JSHint

    const eventBus = function() {
        this.vent = Radio.channel('app');
        this.request = function(){
            var args = Array.prototype.slice.apply(arguments);
            return this.vent.request.apply(this.reqres, args);
        };
    };

    return new eventBus();
});