import Backbone from 'backbone';

export default Backbone.Model.extend({

  isLoggedIn: function() {
    return this.get('id') > 0;
  }

});
