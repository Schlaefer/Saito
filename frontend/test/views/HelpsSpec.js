import { SaitoHelpView } from 'views/helps';

describe('Saito help popup', function () {
  const fixture = `
        <button id="shp-show">Show</button>
        <div class="shp" data-shpid="19"></div>
    `
  let view = null;

  beforeEach(() => {
    setFixtures(fixture);
    view = new SaitoHelpView({
      el: '#shp-show',
      elementName: '.shp',
      webroot: '/root/',
    });
  });

  afterEach(() => {
    view.destroy();
  });

  describe('if help is not required', function () {
    it('shows no help button', function () {
      $('.shp').hide();
      view.render();
      expect(view.$el).not.toHaveClass('is-active');
    });
  });

  describe('if help is required', function () {
    it('shows help button on page', function () {
      view.render();
      expect(view.$el).toHaveClass('is-active');
    });

    it('shows popup on click', function () {
      view.render();
      expect($('i.fa-question-circle')).not.toExist();
      view.$el.click();
      expect($('i.fa-question-circle')).toExist();
    });
  })
});
