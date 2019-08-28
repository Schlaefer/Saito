import CakeFormErrorView from 'lib/saito/CakeFormErrorView.ts';
import { Model } from 'backbone';

describe('CakeFormErrorView', () => {
  const selectorIds = ['a', 'b', 'c', 'd'];
  const fixture = `
    <form>
      <button id="a">Show</button>
      <div>
        <div>
          <div>
            <div>
              <button id="b">Show</button>
            </div>
          </div>
        </div>
      </div>
      <div>
        <div>
          <div>
            <input id="c" type="text">
          </div>
        </div>
        <div class="vld-msg" id="test-vld-msg-c"></div>
      </div>
      <div>
        <div>
            <select id="d"></select>
        </div>
        <div class="vld-msg" id="test-vld-msg-d"></div>
      </div>
    </form>
  `;

  let view;

  beforeEach(() => {
    setFixtures(fixture);
    view = new CakeFormErrorView({ el: 'form'});
  });

  afterEach(() => {
    view.destroy();
  });

  describe('input field', () => {
    it('error indicator class is set and removed', () => {
      view.render();
      expect(view.$('#a')).not.toHaveClass('is-invalid');

      const model = new Model({source: {field: '#a'}, title: 'error-a'});
      view.collection.add(model);
      view.render();
      expect(view.$('#a')).toHaveClass('is-invalid');

      view.collection.remove(model);
      view.render();
      expect(view.$('#a')).not.toHaveClass('is-invalid');
    })
  });

  describe('error message', () => {
    it('is is set directly next to input', () => {
      const a = new Model({source: {field: '#a'}, title: 'error-a'});
      const b = new Model({source: {field: '#b'}, title: 'error-b'});
      view.collection.add([a, b]);
      view.render();

      expect(view.$('#a').siblings().get(0)).toHaveClass('invalid-feedback');
      expect(view.$('#b').siblings().get(0)).toHaveClass('invalid-feedback');
    })

    it('is set directly next to input', () => {
      const a = new Model({source: {field: '#a'}, title: 'error-a'});
      const b = new Model({source: {field: '#b'}, title: 'error-b'});
      view.collection.add([a, b]);
      view.render();

      const aMsg = view.$('#a').siblings().get(0);
      expect(aMsg).toHaveClass('invalid-feedback');
      expect(aMsg).toHaveHtml('error-a');

      const bMsg = view.$('#b').siblings().get(0);
      expect(bMsg).toHaveClass('invalid-feedback');
      expect(bMsg).toHaveHtml('error-b');
    })

    it('is set in dedicated tag', () => {
      const c = new Model({source: {field: '#c'}, title: 'error-c'});
      const d = new Model({source: {field: '#d'}, title: 'error-d'});
      view.collection.add([c, d]);
      view.render();

      const cMsg = view.$('#test-vld-msg-c').children().get(0);
      expect(cMsg).toHaveClass('invalid-feedback');
      expect(cMsg).toHaveHtml('error-c');

      const dMsg = view.$('#test-vld-msg-d').children().get(0);
      expect(dMsg).toHaveClass('invalid-feedback');
      expect(dMsg).toHaveHtml('error-d');
    })
  });
});
