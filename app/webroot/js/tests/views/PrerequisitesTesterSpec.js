define([
  'jquery',
  'views/prerequisitesTester', 'models/app',
  'lib/jquery.i18n/jquery.i18n.extend'
],
    function($, PrerequisitesTesterView, App) {

  describe('Prerequisite Tester', function() {

    beforeEach(function() {
      setFixtures(sandbox({class: 'app-prerequisites-warning'}));
      $.i18n.setDict({});
    });

    it('warns if localStorage is not available', function() {
      var l10n = Date.now();
      spyOn(App.reqres, 'request').and.returnValue(false);
      spyOn($.i18n, '__').and.returnValue(l10n);
      var view = new PrerequisitesTesterView({
        el: $('#sandbox')
      });
      expect(view.$el).toContainElement('.app-prerequisites-warning');
      expect(view.$el).toContainText(l10n);
    });

    it('doesn\'t warn if localStorage is available', function() {
      spyOn(App.reqres, 'request').and.returnValue(true);
      var view = new PrerequisitesTesterView({
        el: $('#sandbox')
      });
      expect(view.$el).not.toContainElement('.app-prerequisites-warning');
    });

  });

});