import Model from 'modules/slidetabs/models/slidetab';
import App from 'models/app';

describe('Slidetab model', function () {
  beforeEach(function (done) {
    jasmine.Ajax.install();

    this.webroot = '/foo/bar/';

    App.settings.set('webroot', this.webroot);
    this.model = new Model({
      id: "testSlider"
    });

    done();
  });

  afterEach(function () {
    delete (this.model);
    jasmine.Ajax.uninstall();
  });

  it('to toggle show at users/ajaxToggle/…', function () {
    this.model.save();
    expect(jasmine.Ajax.requests.mostRecent().url).toBe(this.webroot + 'users/slidetabToggle');
    expect(jasmine.Ajax.requests.mostRecent().method).toBe('POST');
  });
});
