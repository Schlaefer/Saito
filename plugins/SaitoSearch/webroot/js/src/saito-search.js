SaitoApp.callbacks.afterViewInit.push(function () {
  var FormState;
  var FormView;

  FormState = Backbone.Model.extend({
    defaults: {
      order: $('#order-time')[0].checked ? 'time' : 'rank'
    }
  });

  FormView = Marionette.View.extend({
    ui: {
      'form': 'form',
      'orderRankButton': '#order-rank',
      'orderTimeButton': '#order-time',
      'searchField': '#search_fulltext_textfield'
    },
    events: {
      'click @ui.orderRankButton': '_sortRank',
      'click @ui.orderTimeButton': '_sortTime'
    },
    modelEvents: {
      'change:order': '_resendForm'
    },
    initialize: function (options) {
      this._selectSearchText();
    },
    _sortTime: function () {
      this.model.set('order', 'time');
    },
    _sortRank: function () {
      this.model.set('order', 'rank');
    },
    _selectSearchText: function () {
      this.$(this.ui.searchField).select();
    },
    _resendForm: function (event) {
      this.$el.submit()
    }
  });

  new FormView({ el: '#search_form', model: new FormState() });

});
