import _ from 'underscore';
import Mn from 'backbone.marionette';
import UploaderVw from 'modules/uploader/uploader';

export default Mn.View.extend({
  template: _.template(`
    <ul class="nav nav-tabs" id="myTab" role="tablist">
      <li class="nav-item">
        <a class="nav-link active" id="home-tab" data-toggle="tab" href="#home" role="tab" aria-controls="home" aria-selected="true">@todo Bookmarks</a>
      </li>
      <li class="nav-item">
        <a class="js-btnUploads nav-link" id="profile-tab" data-toggle="tab" href="#profile" role="tab" aria-controls="profile" aria-selected="false">@todo Uploads</a>
      </li>
    </ul>
    <div class="tab-content" id="myTabContent">
      <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab"></div>
      <div class="js-rgUploads tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab"></div>
    </div>
  `),
  regions: {
    rgUploads: '.js-rgUploads',
  },
  ui: {
    btnUploads: '.js-btnUploads',
  },
  events: {
    'click @ui.btnUploads': 'handleBtnUploads',
  },
  initialize: function () {
    this.listenTo(this.$el, 'shown.bs.tab', 'handleTabSwitch');
  },
  handleBtnUploads(event) {
    if (this.getRegion('rgUploads').hasView()) {
      return;
    }
    this.showChildView('rgUploads', new UploaderVw());
  },
});
