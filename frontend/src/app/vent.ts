import Radio, { Channel } from 'backbone.radio';

class EventBus {
  public vent: Channel;

  public constructor() {
    this.vent = Radio.channel('app');
  }

}

export default new EventBus();
