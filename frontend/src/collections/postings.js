import _ from 'underscore';
import Backbone from 'backbone';
import { PostingModel } from 'modules/posting/models/PostingModel';

export default Backbone.Collection.extend({
  model: PostingModel
});
