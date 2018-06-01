import Backbone from 'backbone';
import ThreadLineModel from 'models/threadline';

export default Backbone.Collection.extend({
  model: ThreadLineModel,
});
