import Bb from 'backbone';
import App from 'models/app';

export default Bb.JsonApiModel.extend({
  /** Bb URL property */
  urlRoot: () => { return App.settings.get('apiroot') + 'bookmarks/' },
});
