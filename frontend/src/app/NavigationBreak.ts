/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

 import Marionette from 'backbone.marionette';
 import App from 'models/app';

 class NavigationBreak extends Marionette.Object {
     /**
      * Backbone initialize
      */
     public initialize() {
         App.eventBus.reply('app:navigation:allow', this.allow);
         App.eventBus.reply('app:navigation:disallow', this.disallow);
     }

     /**
      * Allows navigation
      */
     public allow() {
         window.onbeforeunload = null;
     }

     /**
      * Breaks navigation request
      */
     public disallow() {
         window.onbeforeunload = () => true;
     }
 }

 export default NavigationBreak;
