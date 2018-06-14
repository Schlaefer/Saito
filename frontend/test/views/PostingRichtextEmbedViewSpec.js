import { PostingRichtextEmbedModel, PostingRichtextEmbedView } from 'views/postingRichtextEmbed';

describe('Posting', function () {
  describe('Richtext', function () {
    describe('Embed', function () {
      let view = null;

      beforeEach(() => {
        const sandbox = document.createElement('div');
        const mdl = new PostingRichtextEmbedModel();
        view = new PostingRichtextEmbedView({ el: sandbox, model: mdl });
      });

      afterEach(() => {
        view.destroy();
      });

      it('shows link if only url is provided', () => {
        const url = 'https://foo/bar/';
        view.model.set('url', url);
        view.render();

        const link = view.$('a');
        expect(link).toExist();
        expect(link).toHaveText(url);
        expect(link).toHaveAttr('target', '_blank');
        expect(link).toHaveAttr('href', url);
      });

      it('shows preprendered HTML', () => {
        const html = '<p><a href="foo">' + Math.random() + '</a></p>';
        view.model.set('html', html);
        view.render();

        expect(view.$el).toContainHtml(html);
      });


      it('shows widget with title', () => {
        view.model.set({
          title: 'title',
          url: 'https://u.rl/',
        });
        view.render();

        const title = view.$('div.card a.card-title');
        expect(title).toContainHtml('<h5>title</h5>');
        expect(title).toHaveAttr('target', '_blank');
        expect(title).toHaveAttr('href', 'https://u.rl/');
      });

      it('shows widget with image', () => {
        view.model.set({
          image: '/assets/image.jpg',
          url: 'https://u.rl/',
        });
        view.render();

        const image = view.$('img.card-img-top');
        expect(image).toHaveAttr('src', '/assets/image.jpg');
      });

      it('shows widget with provider', () => {
        view.model.set({
          title: 'foo',
          providerIcon: '/assets/image.png',
          providerName: 'provider',
          providerUrl: 'https://provid.er/',
          url: 'https://u.rl/',
        });
        view.render();


        const providerIcon = view.$('div.richtext-embed-provider img.richtext-embed-provider-icon');
        expect(providerIcon).toHaveAttr('src', '/assets/image.png');

        const providerLink = view.$('div.richtext-embed-provider a');
        expect(providerLink).toHaveText('provider');
        expect(providerLink).toHaveAttr('target', '_blank');
        expect(providerLink).toHaveAttr('href', 'https://provid.er/');
      })
    });
  });
});
