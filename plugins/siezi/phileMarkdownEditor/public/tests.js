//noinspection JSUnresolvedFunction
describe('app', function() {

  describe('editor', function() {
    describe('view', function() {
      var view;
      beforeEach(function() {
        var fixture = setFixtures('<div id="#editor"></div>');
        var model = new EditorModel;
        view = new EditorView({el: fixture, config: [], model: model})
      });
      it('has an initialized editor after startup', function() {
        view.triggerMethod('dom:refresh');
        expect(view.editor).not.toBeNull();
      });
      it('is not showing an empty editor document after startup', function() {
        view.triggerMethod('dom:refresh');
        expect(view.$el).not.toContainElement('iframe');
      });
    });
  });

  describe('navbar', function() {
    describe('collection', function() {
      it('has sort order', function() {
        var collection = new NavbarPages([
          {url: 'sub/page2', folder: 'sub', file: 'page2'},
          {url: 'sub/aaa/page1', folder: 'sub/aaa', file: 'page1'},
          {url: 'sub/page1', folder: 'sub', file: 'page1'},
          {url: 'toot2',  folder: '/', file: 'toot2'},
          {url: 'root1', folder: '/', file: 'root1'}
        ]);
        var result = collection.pluck('url');
        var expected = [
          'root1',
          'sub/page1',
          'sub/page2',
          'sub/aaa/page1',
          'toot2'
        ];
        expect(result).toEqual(expected);
      });
    });
  });
});