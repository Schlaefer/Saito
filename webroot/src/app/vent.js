import Marionette from 'backbone.marionette';
import Radio from 'backbone.radio'

const eventBus = function () {
  this.vent = Radio.channel('app');
  this.request = function () {
    var args = Array.prototype.slice.apply(arguments);
    return this.vent.request.apply(this.reqres, args);
  };
};

export default new eventBus();
