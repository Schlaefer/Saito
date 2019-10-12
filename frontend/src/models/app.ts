import EventBus from 'app/vent';
import { Model } from 'backbone';
import { Channel } from 'backbone.radio';
import AppStatusModel from 'models/appStatus';
import CurrentUserModel from 'models/currentUser';
import CakeRequest from '../app/CakeRequest';

class AppModel extends Model {
    /**
     * global event handler for the app
     */
    public eventBus: Channel;

    /**
     * CakePHP app settings
     */
    public settings: Model;

    /**
     * Current app status from server
     */
    public status: AppStatusModel;

    /**
     * CurrentUser
     */
    public currentUser: CurrentUserModel;

    /**
     * Request info from CakePHP
     */
    public request: CakeRequest;

    public constructor(options: any = {}) {
        super(options);
        this.eventBus = EventBus.vent;
        this.request = new CakeRequest();
        this.settings = new Model();
        this.status = new AppStatusModel({}, { settings: this.settings });
        this.currentUser = new CurrentUserModel();
    }

}

const instance = new AppModel();

export default instance;
