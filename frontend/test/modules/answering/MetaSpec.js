import $ from 'jquery';
import {MetaModel} from 'modules/answering/Meta';

describe('answering', () => {
  describe('meta', () => {
    it('fetches from the correct URL', () => {
      spyOn($, 'ajax');
      const model = new MetaModel();

      model.fetch();

      expect($.ajax.calls.mostRecent().args[0]['url']).toEqual('/test/root/api/v2/postingmeta/');
    });
  });
});
