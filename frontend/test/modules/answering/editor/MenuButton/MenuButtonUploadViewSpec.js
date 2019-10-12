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

      const args = channel.request.calls.mostRecent().args;
      expect(args[0]).toEqual('insert:text');
      expect(args[1].getTag()).toEqual('file');
      expect(args[1].getAttributes()).toEqual('src=upload');
      expect(args[1].getContent()).toEqual('foo.txt');
    });

    it('unknown file', function () {
      const channel = jasmine.createSpyObj(Radio.Channel, ['request']);
      const model = new Model({ 'mime': 'foo/bar', 'name': 'foo.txt' });
      const view = new InsertVw({ model, channel }).render();

      view.getUI('button').trigger('click');

      const args = channel.request.calls.mostRecent().args;
      expect(args[0]).toEqual('insert:text');
      expect(args[1].getTag()).toEqual('file');
      expect(args[1].getAttributes()).toEqual('src=upload');
      expect(args[1].getContent()).toEqual('foo.txt');
    });

    it('image', function () {
      const channel = jasmine.createSpyObj(Radio.Channel, ['request']);
      const model = new Model({ 'mime': 'image/jpeg', 'name': 'foo.jpg' });
      const view = new InsertVw({ model, channel }).render();

      view.getUI('button').trigger('click');

      const args = channel.request.calls.mostRecent().args;
      expect(args[0]).toEqual('insert:text');
      expect(args[1].getTag()).toEqual('img');
      expect(args[1].getAttributes()).toEqual('src=upload');
      expect(args[1].getContent()).toEqual('foo.jpg');
    });

    it('audio', function () {
      const channel = jasmine.createSpyObj(Radio.Channel, ['request']);
      const model = new Model({ 'mime': 'audio/mpeg', 'name': 'foo.mp3' });
      const view = new InsertVw({ model, channel }).render();

      view.getUI('button').trigger('click');

      const args = channel.request.calls.mostRecent().args;
      expect(args[0]).toEqual('insert:text');
      expect(args[1].getTag()).toEqual('audio');
      expect(args[1].getAttributes()).toEqual('src=upload');
      expect(args[1].getContent()).toEqual('foo.mp3');
    });

    it('video', function () {
      const channel = jasmine.createSpyObj(Radio.Channel, ['request']);
      const model = new Model({ 'mime': 'video/mp4', 'name': 'foo.mp4' });
      const view = new InsertVw({ model, channel }).render();

      view.getUI('button').trigger('click');

      const args = channel.request.calls.mostRecent().args;
      expect(args[0]).toEqual('insert:text');
      expect(args[1].getTag()).toEqual('video');
      expect(args[1].getAttributes()).toEqual('src=upload');
      expect(args[1].getContent()).toEqual('foo.mp4');
    });
  });
});
