import _ from 'underscore';
import Backbone from 'backbone';
import PostingModel from 'models/posting';

export default Backbone.Collection.extend({
  model: PostingModel
});
