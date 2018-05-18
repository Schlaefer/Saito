import _ from 'underscore';
import Backbone from 'backbone';
import GeshiModel from 'models/geshi';

export default Backbone.Collection.extend({
  model: GeshiModel
});
