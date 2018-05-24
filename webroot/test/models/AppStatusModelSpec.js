import Model from 'models/appStatus';
import App from 'models/app';

describe("App status model", function () {
  beforeEach(function (done) {
    jasmine.Ajax.install();
    this.webroot = '/foo/bar/';
    this.model = new Model({}, {
      settings: {
        get: () => { return this.webroot; }
      }
    });
    done();
  });

  afterEach(function () {
    delete (this.model);
    jasmine.Ajax.uninstall();
  });

  it('fetches data from saitos/status/', function () {
    this.model.start();
    expect(jasmine.Ajax.requests.mostRecent().method).toBe('GET');
    expect(jasmine.Ajax.requests.mostRecent().url).toBe(this.webroot + 'status/status/');
  });
});
