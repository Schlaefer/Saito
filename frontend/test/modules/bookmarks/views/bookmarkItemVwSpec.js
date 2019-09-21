import Bb from 'backbone';
import Model from 'modules/bookmarks/models/bookmark';
import View from 'modules/bookmarks/views/bookmarkItemVw';
import App from 'models/app';

describe('bookmarks', function () {
  describe('single bookmark', function () {
    var view;

    beforeEach(function () {
      jasmine.Ajax.install();

      const model = new Model({
        comment: null,
        threadline_html: 'foo',
      });
      view = new View({ model: model }).render();
    });

    afterEach(function () {
      jasmine.Ajax.uninstall();
    });

    it('disables all buttons on save start', function () {
      const $btnSave = view.getUI('btnSave');
      const $btnEdit = view.getUI('btnEdit');
      const $btnDelete = view.getUI('btnDelete');

      expect($btnDelete).not.toHaveAttr('disabled', 'disabled');
      expect($btnEdit).not.toHaveAttr('disabled', 'disabled');
      expect($btnSave).not.toHaveAttr('disabled', 'disabled');

      $btnSave.click();

      expect($btnEdit).toHaveAttr('disabled', 'disabled');
      expect($btnDelete).toHaveAttr('disabled', 'disabled');
      expect($btnSave).toHaveAttr('disabled', 'disabled');
    });

    it('disables all buttons on delete start', function () {
      const $btnSave = view.getUI('btnSave');
      const $btnEdit = view.getUI('btnEdit');
      const $btnDelete = view.getUI('btnDelete');

      expect($btnDelete).not.toHaveAttr('disabled', 'disabled');
      expect($btnEdit).not.toHaveAttr('disabled', 'disabled');
      expect($btnSave).not.toHaveAttr('disabled', 'disabled');

      $btnDelete.click();

      expect($btnEdit).toHaveAttr('disabled', 'disabled');
      expect($btnDelete).toHaveAttr('disabled', 'disabled');
      expect($btnSave).toHaveAttr('disabled', 'disabled');
    });
  });
});
