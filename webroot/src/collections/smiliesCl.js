import Bb from 'backbone';

export default Bb.Collection.extend({
  modelId: function(attributes) {
    // Collection is filled with all codes for all smilies.
    // "icon" is unique for smilies on all codes so only one smiley
    // per code is put into the collection.
    return attributes.icon;
  }
});
