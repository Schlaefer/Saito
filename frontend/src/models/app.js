import Backbone from 'backbone';
import Vent from 'app/vent';
import AppSettingModel from 'models/appSetting';
import AppStatusModel from 'models/appStatus';
import CurrentUserModel from 'models/currentUser';

const AppModel = Backbone.Model.extend({

  /**
   * global event handler for the app
   */
  eventBus: null,

  /**
   * CakePHP app settings
   */
  settings: null,

  /**
   * Current app status from server
   */
  status: null,

  /**
   * CurrentUser
   */
  currentUser: null,

  /**
   * Request info from CakePHP
   */
  request: {
    action: null,
    controller: null,
  },

  initialize: function () {
    this.eventBus = Vent.vent;
    this.settings = new AppSettingModel();
    this.status = new AppStatusModel({}, { settings: this.settings });
    this.currentUser = new CurrentUserModel();
  }
});

const instance = new AppModel();

export default instance;
