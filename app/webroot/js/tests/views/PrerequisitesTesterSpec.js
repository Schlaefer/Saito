define(['views/prerequisitesTester', 'models/app'], function(PrerequisitesTesterView, App) {

  describe('Prerequisite Tester', function() {

    beforeEach(function() {
      setFixtures(sandbox({class: 'app-prerequisites-warning'}));
      $.i18n = { __: {}};
    });

    it('warns if localStorage is not available', function() {
      var l10n = Date.now();
      spyOn(App.reqres, 'request').andReturn(false);
      spyOn($.i18n, '__').andReturn(l10n);
      var view = new PrerequisitesTesterView({
        el: $('#sandbox')
      });
      expect(view.$el).toContain('.app-prerequisites-warning');
      expect(view.$el).toContainText(l10n);
    });

    it('doesn\'t warn if localStorage is available', function() {
      spyOn(App.reqres, 'request').andReturn(true);
      var view = new PrerequisitesTesterView({
        el: $('#sandbox')
      });
      expect(view.$el).not.toContain('.app-prerequisites-warning');
    });

  });

});