import $ from 'jquery';
import _ from 'underscore'
import Vent from 'app/vent';
import moment from 'moment';
import 'lib/jquery.i18n/jquery.i18n.extend';

var TemplateHelper = function () {
  this.webroot = Vent.vent.request('webroot');
};

export default {

  Time: _.extend(new TemplateHelper(), {
    moment: moment,

    _timeFormats: {
      long: 'DD.MM.YYYY',
      longWithTime: 'DD.MM.YYYY HH:mm',
      RFC3339: 'YYYY-MM-DDTHH:mm:ssZ',
      theRightWay: 'YYYY-MM-DD HH:mm:ss',
      time: 'HH:mm'
    },

    lastMidnight: function () {
      return moment(this.now().format('YYYY-MM-DD'));
    },

    now: function () {
      return moment();
    },

    _normal: function (timestamp) {
      var now = this.now(),
        midnight = this.lastMidnight(),
        diff = now.diff(timestamp) / 1000;

      if (timestamp.isAfter(midnight) || diff < 21600) {
        return timestamp.format(this._timeFormats.time);
      }

      if (diff < 64800) {
        return $.i18n.__('time.relative.yesterday') + ' ' +
          timestamp.format(this._timeFormats.time);
      }

      return timestamp.format(this._timeFormats.long);
    },

    format: function (timestamp, format, options) {
      var out, ts;
      // defaults
      format = format || 'normal';
      options = options || {};
      _.defaults(options, {
        wrap: true
      });

      // convert input to ts
      if (moment.isMoment(timestamp)) {
        ts = timestamp;
      } else if (_.isNumber(timestamp)) {
        // is unix timestamp
        if (('' + timestamp).length < 13) {
          timestamp = Math.round(timestamp * 1000);
        }
        ts = new moment(timestamp);
      } else if (_.isDate(timestamp)) {
        ts = new moment(timestamp);
      } else {
        throw 'No valid timestamp: ' + timestamp.toJSON();
      }

      // generate timestamp
      if (format === 'normal') {
        out = this._normal(ts);
      } else if (this._timeFormats[format]) {
        out = ts.format(this._timeFormats[format]);
      } else {
        out = ts.format(format);
      }

      // wrapping
      if (options.wrap) {
        var string = ' ',
          attributes = {
            title: ts.format(this._timeFormats.theRightWay),
            datetime: ts.format(this._timeFormats.RFC3339)
          };
        _.each(attributes, function (value, name) {
          string += name + '="' + value + '" ';
        });
        out = '<time' + string + '>' + out + '</time>';
      }

      return out;
    }
  }),

  User: _.extend(new TemplateHelper(), {

    templates: {
      linkToUserProfile: _.template('<a href="<%- url %>"><%- name %></a>')
    },

    /**
     * generates link to user profile
     *
     * @param id user id
     * @param name user name
     * @returns string
     */
    linkToUserProfile: function (id, name) {
      var url = this.urlToUserProfile(id);
      return this.templates.linkToUserProfile({ url: url, name: name });
    },

    urlToUserProfile: function (id) {
      return this.webroot + 'users/view/' + id;
    }

  })

};
