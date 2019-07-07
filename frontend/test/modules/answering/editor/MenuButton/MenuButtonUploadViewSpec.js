import { InsertVw } from 'modules/answering/editor/MenuButton/MenuButtonUploadView';
import { Model } from 'backbone';
import * as Radio from 'backbone.radio';

describe('answering form', function () {
  describe('uploader button inserts', function () {
    it('known file', function () {
      const channel = jasmine.createSpyObj(Radio.Channel, ['request']);
      const model = new Model({ 'mime': 'plain/text', 'name': 'foo.txt' });
      const view = new InsertVw({ model, channel }).render();

      view.getUI('button').trigger('click');

      expect(channel.request).toHaveBeenCalledWith(
        'insert:text',
        '[file src=upload]foo.txt[/file]',
      );
    });

    it('unknown file', function () {
      const channel = jasmine.createSpyObj(Radio.Channel, ['request']);
      const model = new Model({ 'mime': 'foo/bar', 'name': 'foo.txt' });
      const view = new InsertVw({ model, channel }).render();

      view.getUI('button').trigger('click');

      expect(channel.request).toHaveBeenCalledWith(
        'insert:text',
        '[file src=upload]foo.txt[/file]',
      );
    });

    it('image', function () {
      const channel = jasmine.createSpyObj(Radio.Channel, ['request']);
      const model = new Model({ 'mime': 'image/jpeg', 'name': 'foo.jpg' });
      const view = new InsertVw({ model, channel }).render();

      view.getUI('button').trigger('click');

      expect(channel.request).toHaveBeenCalledWith(
        'insert:text',
        '[img src=upload]foo.jpg[/img]',
      );
    });

    it('audio', function () {
      const channel = jasmine.createSpyObj(Radio.Channel, ['request']);
      const model = new Model({ 'mime': 'audio/mpeg', 'name': 'foo.mp3' });
      const view = new InsertVw({ model, channel }).render();

      view.getUI('button').trigger('click');

      expect(channel.request).toHaveBeenCalledWith(
        'insert:text',
        '[audio src=upload]foo.mp3[/audio]',
      );
    });

    it('video', function () {
      const channel = jasmine.createSpyObj(Radio.Channel, ['request']);
      const model = new Model({ 'mime': 'video/mp4', 'name': 'foo.mp4' });
      const view = new InsertVw({ model, channel }).render();

      view.getUI('button').trigger('click');

      expect(channel.request).toHaveBeenCalledWith(
        'insert:text',
        '[video src=upload]foo.mp4[/video]',
      );
    });
  });
});
