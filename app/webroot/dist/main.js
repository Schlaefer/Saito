
define('app/vent',['marionette'], function() {
    //noinspection JSHint

    var eventBus = function() {
        this.vent = new Backbone.Wreqr.EventAggregator();
        this.commands = new Backbone.Wreqr.Commands();
        this.reqres = new Backbone.Wreqr.RequestResponse();

        // Request/response, facilitated by Backbone.Wreqr.RequestResponse
        // from marionette
        this.request = function(){
            var args = Array.prototype.slice.apply(arguments);
            return this.reqres.request.apply(this.reqres, args);
        };
    };

    return new eventBus();

});
define('models/appSetting',[
    'underscore',
    'backbone'
], function (_, Backbone) {

    

    var AppSettingModel = Backbone.Model.extend({

    });

    return AppSettingModel;
});

define('models/appStatus',[
  'underscore',
  'backbone',
  'cakeRest',
  'app/vent'
], function(_, Backbone, cakeRest, EventBus) {

  

  var AppStatusModel = Backbone.Model.extend({

    initialize: function() {
      this.methodToCakePhpUrl = _.clone(this.methodToCakePhpUrl);
      this.methodToCakePhpUrl.read = 'status/';

      this.listenTo(this, 'change:lastShoutId', this.onNewShout);
    },

    onNewShout: function(model) {
      var id = model.get('lastShoutId');
      EventBus.commands.execute('shoutbox:update', id);
    },

    setWebroot: function(webroot) {
      this.webroot = webroot + 'saitos/';
    }

  });

  _.extend(AppStatusModel.prototype, cakeRest);

  return AppStatusModel;

});

define('models/currentUser',[
    'underscore',
    'backbone'
], function (_, Backbone) {

    

    var CurrentUserModel = Backbone.Model.extend({

    });

    return CurrentUserModel;
});

define('models/app',[
    'underscore',
    'backbone',
    'app/vent',
    'models/appSetting',
    'models/appStatus',
    'models/currentUser'
], function (_, Backbone, Vent,
    AppSettingModel, AppStatusModel, CurrentUserModel
    ) {

    

    var AppModel = Backbone.Model.extend({


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
        request: null,


        initialize: function () {
          this.eventBus = Vent.vent;
          this.commands = Vent.commands;
          this.reqres = Vent.reqres;
          this.settings = new AppSettingModel();
          this.status = new AppStatusModel();
          this.currentUser = new CurrentUserModel();
        },

        initAppStatusUpdate: function () {
            var resetRefreshTime,
                updateAppStatus,
                setTimer,
                timerId,
                stopTimer,
                refreshTimeAct,
                refreshTimeBase = 10000,
                refreshTimeMax = 90000;

            stopTimer = function () {
                if (timerId !== undefined) {
                    clearTimeout(timerId);
                }
            },

            resetRefreshTime = function () {
                stopTimer();
                refreshTimeAct = refreshTimeBase;
            };

            setTimer = function () {
                timerId = setTimeout(
                    updateAppStatus,
                    refreshTimeAct
                );
            };

            updateAppStatus = _.bind(function () {
                setTimer();
                this.status.fetch();
                refreshTimeAct = Math.floor(
                    refreshTimeAct * (1 + refreshTimeAct / 40000)
                );
                if (refreshTimeAct > refreshTimeMax) {
                    refreshTimeAct = refreshTimeMax;
                }
            }, this);

            this.status.setWebroot(this.settings.get('webroot'));

            this.listenTo(
                this.status,
                'change',
                function () {
                    resetRefreshTime();
                    setTimer();
                }
            );

            updateAppStatus();
            resetRefreshTime();
            setTimer();
        }

    });

    return new AppModel();
});

define('modules/shoutbox/models/shout',['underscore', 'backbone'], function(_, Backbone) {

  

  var ShoutModel = Backbone.Model.extend({

    initialize: function(options) {
      // this.apiroot = options.apiroot + 'shouts/';
      this.webroot = options.webroot + 'shouts/';
      this.collection = options.collection;
    },

    save: function() {
      $.ajax({
        url: this.webroot + 'add',
        type: "post",
        dataType: 'json',
        data: {
          text: this.get('text')
        },
        context: this
      }).done(function(data) {
            // reload shouts after new entry
            this.collection.reset(data);
          });
    }

  });

  return ShoutModel;

});

define('modules/shoutbox/collections/shouts',['underscore', 'backbone', 'modules/shoutbox/models/shout'],
    function(_, Backbone, ShoutModel) {

      

      var ShoutsCollection = Backbone.Collection.extend({

        model: ShoutModel,

        initialize: function(shouts, options) {
          this.apiroot = options.apiroot + 'shouts/';
        },

        fetch: function() {
          $.ajax({
            url: this.apiroot,
            dataType: 'json',
            context: this
          }).done(function(data) {
                if (data.length > 0) {
                  this.reset(data);
                }
              });
        }

      });

      return ShoutsCollection;
    });

//! moment.js
//! version : 2.3.1
//! authors : Tim Wood, Iskren Chernev, Moment.js contributors
//! license : MIT
//! momentjs.com

(function (undefined) {

    /************************************
        Constants
    ************************************/

    var moment,
        VERSION = "2.3.1",
        round = Math.round,
        i,

        YEAR = 0,
        MONTH = 1,
        DATE = 2,
        HOUR = 3,
        MINUTE = 4,
        SECOND = 5,
        MILLISECOND = 6,

        // internal storage for language config files
        languages = {},

        // check for nodeJS
        hasModule = (typeof module !== 'undefined' && module.exports),

        // ASP.NET json date format regex
        aspNetJsonRegex = /^\/?Date\((\-?\d+)/i,
        aspNetTimeSpanJsonRegex = /(\-)?(?:(\d*)\.)?(\d+)\:(\d+)(?:\:(\d+)\.?(\d{3})?)?/,

        // from http://docs.closure-library.googlecode.com/git/closure_goog_date_date.js.source.html
        // somewhat more in line with 4.4.3.2 2004 spec, but allows decimal anywhere
        isoDurationRegex = /^(-)?P(?:(?:([0-9,.]*)Y)?(?:([0-9,.]*)M)?(?:([0-9,.]*)D)?(?:T(?:([0-9,.]*)H)?(?:([0-9,.]*)M)?(?:([0-9,.]*)S)?)?|([0-9,.]*)W)$/,

        // format tokens
        formattingTokens = /(\[[^\[]*\])|(\\)?(Mo|MM?M?M?|Do|DDDo|DD?D?D?|ddd?d?|do?|w[o|w]?|W[o|W]?|YYYYY|YYYY|YY|gg(ggg?)?|GG(GGG?)?|e|E|a|A|hh?|HH?|mm?|ss?|SS?S?|X|zz?|ZZ?|.)/g,
        localFormattingTokens = /(\[[^\[]*\])|(\\)?(LT|LL?L?L?|l{1,4})/g,

        // parsing token regexes
        parseTokenOneOrTwoDigits = /\d\d?/, // 0 - 99
        parseTokenOneToThreeDigits = /\d{1,3}/, // 0 - 999
        parseTokenThreeDigits = /\d{3}/, // 000 - 999
        parseTokenFourDigits = /\d{1,4}/, // 0 - 9999
        parseTokenSixDigits = /[+\-]?\d{1,6}/, // -999,999 - 999,999
        parseTokenWord = /[0-9]*['a-z\u00A0-\u05FF\u0700-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+|[\u0600-\u06FF\/]+(\s*?[\u0600-\u06FF]+){1,2}/i, // any word (or two) characters or numbers including two/three word month in arabic.
        parseTokenTimezone = /Z|[\+\-]\d\d:?\d\d/i, // +00:00 -00:00 +0000 -0000 or Z
        parseTokenT = /T/i, // T (ISO seperator)
        parseTokenTimestampMs = /[\+\-]?\d+(\.\d{1,3})?/, // 123456789 123456789.123

        // preliminary iso regex
        // 0000-00-00 0000-W00 or 0000-W00-0 + T + 00 or 00:00 or 00:00:00 or 00:00:00.000 + +00:00 or +0000)
        isoRegex = /^\s*\d{4}-(?:(\d\d-\d\d)|(W\d\d$)|(W\d\d-\d)|(\d\d\d))((T| )(\d\d(:\d\d(:\d\d(\.\d\d?\d?)?)?)?)?([\+\-]\d\d:?\d\d)?)?$/,

        isoFormat = 'YYYY-MM-DDTHH:mm:ssZ',

        isoDates = [
            'YYYY-MM-DD',
            'GGGG-[W]WW',
            'GGGG-[W]WW-E',
            'YYYY-DDD'
        ],

        // iso time formats and regexes
        isoTimes = [
            ['HH:mm:ss.S', /(T| )\d\d:\d\d:\d\d\.\d{1,3}/],
            ['HH:mm:ss', /(T| )\d\d:\d\d:\d\d/],
            ['HH:mm', /(T| )\d\d:\d\d/],
            ['HH', /(T| )\d\d/]
        ],

        // timezone chunker "+10:00" > ["10", "00"] or "-1530" > ["-15", "30"]
        parseTimezoneChunker = /([\+\-]|\d\d)/gi,

        // getter and setter names
        proxyGettersAndSetters = 'Date|Hours|Minutes|Seconds|Milliseconds'.split('|'),
        unitMillisecondFactors = {
            'Milliseconds' : 1,
            'Seconds' : 1e3,
            'Minutes' : 6e4,
            'Hours' : 36e5,
            'Days' : 864e5,
            'Months' : 2592e6,
            'Years' : 31536e6
        },

        unitAliases = {
            ms : 'millisecond',
            s : 'second',
            m : 'minute',
            h : 'hour',
            d : 'day',
            D : 'date',
            w : 'week',
            W : 'isoWeek',
            M : 'month',
            y : 'year',
            DDD : 'dayOfYear',
            e : 'weekday',
            E : 'isoWeekday',
            gg: 'weekYear',
            GG: 'isoWeekYear'
        },

        camelFunctions = {
            dayofyear : 'dayOfYear',
            isoweekday : 'isoWeekday',
            isoweek : 'isoWeek',
            weekyear : 'weekYear',
            isoweekyear : 'isoWeekYear'
        },

        // format function strings
        formatFunctions = {},

        // tokens to ordinalize and pad
        ordinalizeTokens = 'DDD w W M D d'.split(' '),
        paddedTokens = 'M D H h m s w W'.split(' '),

        formatTokenFunctions = {
            M    : function () {
                return this.month() + 1;
            },
            MMM  : function (format) {
                return this.lang().monthsShort(this, format);
            },
            MMMM : function (format) {
                return this.lang().months(this, format);
            },
            D    : function () {
                return this.date();
            },
            DDD  : function () {
                return this.dayOfYear();
            },
            d    : function () {
                return this.day();
            },
            dd   : function (format) {
                return this.lang().weekdaysMin(this, format);
            },
            ddd  : function (format) {
                return this.lang().weekdaysShort(this, format);
            },
            dddd : function (format) {
                return this.lang().weekdays(this, format);
            },
            w    : function () {
                return this.week();
            },
            W    : function () {
                return this.isoWeek();
            },
            YY   : function () {
                return leftZeroFill(this.year() % 100, 2);
            },
            YYYY : function () {
                return leftZeroFill(this.year(), 4);
            },
            YYYYY : function () {
                return leftZeroFill(this.year(), 5);
            },
            gg   : function () {
                return leftZeroFill(this.weekYear() % 100, 2);
            },
            gggg : function () {
                return this.weekYear();
            },
            ggggg : function () {
                return leftZeroFill(this.weekYear(), 5);
            },
            GG   : function () {
                return leftZeroFill(this.isoWeekYear() % 100, 2);
            },
            GGGG : function () {
                return this.isoWeekYear();
            },
            GGGGG : function () {
                return leftZeroFill(this.isoWeekYear(), 5);
            },
            e : function () {
                return this.weekday();
            },
            E : function () {
                return this.isoWeekday();
            },
            a    : function () {
                return this.lang().meridiem(this.hours(), this.minutes(), true);
            },
            A    : function () {
                return this.lang().meridiem(this.hours(), this.minutes(), false);
            },
            H    : function () {
                return this.hours();
            },
            h    : function () {
                return this.hours() % 12 || 12;
            },
            m    : function () {
                return this.minutes();
            },
            s    : function () {
                return this.seconds();
            },
            S    : function () {
                return toInt(this.milliseconds() / 100);
            },
            SS   : function () {
                return leftZeroFill(toInt(this.milliseconds() / 10), 2);
            },
            SSS  : function () {
                return leftZeroFill(this.milliseconds(), 3);
            },
            Z    : function () {
                var a = -this.zone(),
                    b = "+";
                if (a < 0) {
                    a = -a;
                    b = "-";
                }
                return b + leftZeroFill(toInt(a / 60), 2) + ":" + leftZeroFill(toInt(a) % 60, 2);
            },
            ZZ   : function () {
                var a = -this.zone(),
                    b = "+";
                if (a < 0) {
                    a = -a;
                    b = "-";
                }
                return b + leftZeroFill(toInt(10 * a / 6), 4);
            },
            z : function () {
                return this.zoneAbbr();
            },
            zz : function () {
                return this.zoneName();
            },
            X    : function () {
                return this.unix();
            }
        },

        lists = ['months', 'monthsShort', 'weekdays', 'weekdaysShort', 'weekdaysMin'];

    function padToken(func, count) {
        return function (a) {
            return leftZeroFill(func.call(this, a), count);
        };
    }
    function ordinalizeToken(func, period) {
        return function (a) {
            return this.lang().ordinal(func.call(this, a), period);
        };
    }

    while (ordinalizeTokens.length) {
        i = ordinalizeTokens.pop();
        formatTokenFunctions[i + 'o'] = ordinalizeToken(formatTokenFunctions[i], i);
    }
    while (paddedTokens.length) {
        i = paddedTokens.pop();
        formatTokenFunctions[i + i] = padToken(formatTokenFunctions[i], 2);
    }
    formatTokenFunctions.DDDD = padToken(formatTokenFunctions.DDD, 3);


    /************************************
        Constructors
    ************************************/

    function Language() {

    }

    // Moment prototype object
    function Moment(config) {
        checkOverflow(config);
        extend(this, config);
    }

    // Duration Constructor
    function Duration(duration) {
        var normalizedInput = normalizeObjectUnits(duration),
            years = normalizedInput.year || 0,
            months = normalizedInput.month || 0,
            weeks = normalizedInput.week || 0,
            days = normalizedInput.day || 0,
            hours = normalizedInput.hour || 0,
            minutes = normalizedInput.minute || 0,
            seconds = normalizedInput.second || 0,
            milliseconds = normalizedInput.millisecond || 0;

        // store reference to input for deterministic cloning
        this._input = duration;

        // representation for dateAddRemove
        this._milliseconds = +milliseconds +
            seconds * 1e3 + // 1000
            minutes * 6e4 + // 1000 * 60
            hours * 36e5; // 1000 * 60 * 60
        // Because of dateAddRemove treats 24 hours as different from a
        // day when working around DST, we need to store them separately
        this._days = +days +
            weeks * 7;
        // It is impossible translate months into days without knowing
        // which months you are are talking about, so we have to store
        // it separately.
        this._months = +months +
            years * 12;

        this._data = {};

        this._bubble();
    }

    /************************************
        Helpers
    ************************************/


    function extend(a, b) {
        for (var i in b) {
            if (b.hasOwnProperty(i)) {
                a[i] = b[i];
            }
        }

        if (b.hasOwnProperty("toString")) {
            a.toString = b.toString;
        }

        if (b.hasOwnProperty("valueOf")) {
            a.valueOf = b.valueOf;
        }

        return a;
    }

    function absRound(number) {
        if (number < 0) {
            return Math.ceil(number);
        } else {
            return Math.floor(number);
        }
    }

    // left zero fill a number
    // see http://jsperf.com/left-zero-filling for performance comparison
    function leftZeroFill(number, targetLength) {
        var output = number + '';
        while (output.length < targetLength) {
            output = '0' + output;
        }
        return output;
    }

    // helper function for _.addTime and _.subtractTime
    function addOrSubtractDurationFromMoment(mom, duration, isAdding, ignoreUpdateOffset) {
        var milliseconds = duration._milliseconds,
            days = duration._days,
            months = duration._months,
            minutes,
            hours;

        if (milliseconds) {
            mom._d.setTime(+mom._d + milliseconds * isAdding);
        }
        // store the minutes and hours so we can restore them
        if (days || months) {
            minutes = mom.minute();
            hours = mom.hour();
        }
        if (days) {
            mom.date(mom.date() + days * isAdding);
        }
        if (months) {
            mom.month(mom.month() + months * isAdding);
        }
        if (milliseconds && !ignoreUpdateOffset) {
            moment.updateOffset(mom);
        }
        // restore the minutes and hours after possibly changing dst
        if (days || months) {
            mom.minute(minutes);
            mom.hour(hours);
        }
    }

    // check if is an array
    function isArray(input) {
        return Object.prototype.toString.call(input) === '[object Array]';
    }

    function isDate(input) {
        return Object.prototype.toString.call(input) === '[object Date]';
    }

    // compare two arrays, return the number of differences
    function compareArrays(array1, array2, dontConvert) {
        var len = Math.min(array1.length, array2.length),
            lengthDiff = Math.abs(array1.length - array2.length),
            diffs = 0,
            i;
        for (i = 0; i < len; i++) {
            if ((dontConvert && array1[i] !== array2[i]) ||
                (!dontConvert && toInt(array1[i]) !== toInt(array2[i]))) {
                diffs++;
            }
        }
        return diffs + lengthDiff;
    }

    function normalizeUnits(units) {
        if (units) {
            var lowered = units.toLowerCase().replace(/(.)s$/, '$1');
            units = unitAliases[units] || camelFunctions[lowered] || lowered;
        }
        return units;
    }

    function normalizeObjectUnits(inputObject) {
        var normalizedInput = {},
            normalizedProp,
            prop,
            index;

        for (prop in inputObject) {
            if (inputObject.hasOwnProperty(prop)) {
                normalizedProp = normalizeUnits(prop);
                if (normalizedProp) {
                    normalizedInput[normalizedProp] = inputObject[prop];
                }
            }
        }

        return normalizedInput;
    }

    function makeList(field) {
        var count, setter;

        if (field.indexOf('week') === 0) {
            count = 7;
            setter = 'day';
        }
        else if (field.indexOf('month') === 0) {
            count = 12;
            setter = 'month';
        }
        else {
            return;
        }

        moment[field] = function (format, index) {
            var i, getter,
                method = moment.fn._lang[field],
                results = [];

            if (typeof format === 'number') {
                index = format;
                format = undefined;
            }

            getter = function (i) {
                var m = moment().utc().set(setter, i);
                return method.call(moment.fn._lang, m, format || '');
            };

            if (index != null) {
                return getter(index);
            }
            else {
                for (i = 0; i < count; i++) {
                    results.push(getter(i));
                }
                return results;
            }
        };
    }

    function toInt(argumentForCoercion) {
        var coercedNumber = +argumentForCoercion,
            value = 0;

        if (coercedNumber !== 0 && isFinite(coercedNumber)) {
            if (coercedNumber >= 0) {
                value = Math.floor(coercedNumber);
            } else {
                value = Math.ceil(coercedNumber);
            }
        }

        return value;
    }

    function daysInMonth(year, month) {
        return new Date(Date.UTC(year, month + 1, 0)).getUTCDate();
    }

    function daysInYear(year) {
        return isLeapYear(year) ? 366 : 365;
    }

    function isLeapYear(year) {
        return (year % 4 === 0 && year % 100 !== 0) || year % 400 === 0;
    }

    function checkOverflow(m) {
        var overflow;
        if (m._a && m._pf.overflow === -2) {
            overflow =
                m._a[MONTH] < 0 || m._a[MONTH] > 11 ? MONTH :
                m._a[DATE] < 1 || m._a[DATE] > daysInMonth(m._a[YEAR], m._a[MONTH]) ? DATE :
                m._a[HOUR] < 0 || m._a[HOUR] > 23 ? HOUR :
                m._a[MINUTE] < 0 || m._a[MINUTE] > 59 ? MINUTE :
                m._a[SECOND] < 0 || m._a[SECOND] > 59 ? SECOND :
                m._a[MILLISECOND] < 0 || m._a[MILLISECOND] > 999 ? MILLISECOND :
                -1;

            if (m._pf._overflowDayOfYear && (overflow < YEAR || overflow > DATE)) {
                overflow = DATE;
            }

            m._pf.overflow = overflow;
        }
    }

    function initializeParsingFlags(config) {
        config._pf = {
            empty : false,
            unusedTokens : [],
            unusedInput : [],
            overflow : -2,
            charsLeftOver : 0,
            nullInput : false,
            invalidMonth : null,
            invalidFormat : false,
            userInvalidated : false
        };
    }

    function isValid(m) {
        if (m._isValid == null) {
            m._isValid = !isNaN(m._d.getTime()) &&
                m._pf.overflow < 0 &&
                !m._pf.empty &&
                !m._pf.invalidMonth &&
                !m._pf.nullInput &&
                !m._pf.invalidFormat &&
                !m._pf.userInvalidated;

            if (m._strict) {
                m._isValid = m._isValid &&
                    m._pf.charsLeftOver === 0 &&
                    m._pf.unusedTokens.length === 0;
            }
        }
        return m._isValid;
    }

    function normalizeLanguage(key) {
        return key ? key.toLowerCase().replace('_', '-') : key;
    }

    /************************************
        Languages
    ************************************/


    extend(Language.prototype, {

        set : function (config) {
            var prop, i;
            for (i in config) {
                prop = config[i];
                if (typeof prop === 'function') {
                    this[i] = prop;
                } else {
                    this['_' + i] = prop;
                }
            }
        },

        _months : "January_February_March_April_May_June_July_August_September_October_November_December".split("_"),
        months : function (m) {
            return this._months[m.month()];
        },

        _monthsShort : "Jan_Feb_Mar_Apr_May_Jun_Jul_Aug_Sep_Oct_Nov_Dec".split("_"),
        monthsShort : function (m) {
            return this._monthsShort[m.month()];
        },

        monthsParse : function (monthName) {
            var i, mom, regex;

            if (!this._monthsParse) {
                this._monthsParse = [];
            }

            for (i = 0; i < 12; i++) {
                // make the regex if we don't have it already
                if (!this._monthsParse[i]) {
                    mom = moment.utc([2000, i]);
                    regex = '^' + this.months(mom, '') + '|^' + this.monthsShort(mom, '');
                    this._monthsParse[i] = new RegExp(regex.replace('.', ''), 'i');
                }
                // test the regex
                if (this._monthsParse[i].test(monthName)) {
                    return i;
                }
            }
        },

        _weekdays : "Sunday_Monday_Tuesday_Wednesday_Thursday_Friday_Saturday".split("_"),
        weekdays : function (m) {
            return this._weekdays[m.day()];
        },

        _weekdaysShort : "Sun_Mon_Tue_Wed_Thu_Fri_Sat".split("_"),
        weekdaysShort : function (m) {
            return this._weekdaysShort[m.day()];
        },

        _weekdaysMin : "Su_Mo_Tu_We_Th_Fr_Sa".split("_"),
        weekdaysMin : function (m) {
            return this._weekdaysMin[m.day()];
        },

        weekdaysParse : function (weekdayName) {
            var i, mom, regex;

            if (!this._weekdaysParse) {
                this._weekdaysParse = [];
            }

            for (i = 0; i < 7; i++) {
                // make the regex if we don't have it already
                if (!this._weekdaysParse[i]) {
                    mom = moment([2000, 1]).day(i);
                    regex = '^' + this.weekdays(mom, '') + '|^' + this.weekdaysShort(mom, '') + '|^' + this.weekdaysMin(mom, '');
                    this._weekdaysParse[i] = new RegExp(regex.replace('.', ''), 'i');
                }
                // test the regex
                if (this._weekdaysParse[i].test(weekdayName)) {
                    return i;
                }
            }
        },

        _longDateFormat : {
            LT : "h:mm A",
            L : "MM/DD/YYYY",
            LL : "MMMM D YYYY",
            LLL : "MMMM D YYYY LT",
            LLLL : "dddd, MMMM D YYYY LT"
        },
        longDateFormat : function (key) {
            var output = this._longDateFormat[key];
            if (!output && this._longDateFormat[key.toUpperCase()]) {
                output = this._longDateFormat[key.toUpperCase()].replace(/MMMM|MM|DD|dddd/g, function (val) {
                    return val.slice(1);
                });
                this._longDateFormat[key] = output;
            }
            return output;
        },

        isPM : function (input) {
            // IE8 Quirks Mode & IE7 Standards Mode do not allow accessing strings like arrays
            // Using charAt should be more compatible.
            return ((input + '').toLowerCase().charAt(0) === 'p');
        },

        _meridiemParse : /[ap]\.?m?\.?/i,
        meridiem : function (hours, minutes, isLower) {
            if (hours > 11) {
                return isLower ? 'pm' : 'PM';
            } else {
                return isLower ? 'am' : 'AM';
            }
        },

        _calendar : {
            sameDay : '[Today at] LT',
            nextDay : '[Tomorrow at] LT',
            nextWeek : 'dddd [at] LT',
            lastDay : '[Yesterday at] LT',
            lastWeek : '[Last] dddd [at] LT',
            sameElse : 'L'
        },
        calendar : function (key, mom) {
            var output = this._calendar[key];
            return typeof output === 'function' ? output.apply(mom) : output;
        },

        _relativeTime : {
            future : "in %s",
            past : "%s ago",
            s : "a few seconds",
            m : "a minute",
            mm : "%d minutes",
            h : "an hour",
            hh : "%d hours",
            d : "a day",
            dd : "%d days",
            M : "a month",
            MM : "%d months",
            y : "a year",
            yy : "%d years"
        },
        relativeTime : function (number, withoutSuffix, string, isFuture) {
            var output = this._relativeTime[string];
            return (typeof output === 'function') ?
                output(number, withoutSuffix, string, isFuture) :
                output.replace(/%d/i, number);
        },
        pastFuture : function (diff, output) {
            var format = this._relativeTime[diff > 0 ? 'future' : 'past'];
            return typeof format === 'function' ? format(output) : format.replace(/%s/i, output);
        },

        ordinal : function (number) {
            return this._ordinal.replace("%d", number);
        },
        _ordinal : "%d",

        preparse : function (string) {
            return string;
        },

        postformat : function (string) {
            return string;
        },

        week : function (mom) {
            return weekOfYear(mom, this._week.dow, this._week.doy).week;
        },

        _week : {
            dow : 0, // Sunday is the first day of the week.
            doy : 6  // The week that contains Jan 1st is the first week of the year.
        },

        _invalidDate: 'Invalid date',
        invalidDate: function () {
            return this._invalidDate;
        }
    });

    // Loads a language definition into the `languages` cache.  The function
    // takes a key and optionally values.  If not in the browser and no values
    // are provided, it will load the language file module.  As a convenience,
    // this function also returns the language values.
    function loadLang(key, values) {
        values.abbr = key;
        if (!languages[key]) {
            languages[key] = new Language();
        }
        languages[key].set(values);
        return languages[key];
    }

    // Remove a language from the `languages` cache. Mostly useful in tests.
    function unloadLang(key) {
        delete languages[key];
    }

    // Determines which language definition to use and returns it.
    //
    // With no parameters, it will return the global language.  If you
    // pass in a language key, such as 'en', it will return the
    // definition for 'en', so long as 'en' has already been loaded using
    // moment.lang.
    function getLangDefinition(key) {
        var i = 0, j, lang, next, split,
            get = function (k) {
                if (!languages[k] && hasModule) {
                    try {
                        require('./lang/' + k);
                    } catch (e) { }
                }
                return languages[k];
            };

        if (!key) {
            return moment.fn._lang;
        }

        if (!isArray(key)) {
            //short-circuit everything else
            lang = get(key);
            if (lang) {
                return lang;
            }
            key = [key];
        }

        //pick the language from the array
        //try ['en-au', 'en-gb'] as 'en-au', 'en-gb', 'en', as in move through the list trying each
        //substring from most specific to least, but move to the next array item if it's a more specific variant than the current root
        while (i < key.length) {
            split = normalizeLanguage(key[i]).split('-');
            j = split.length;
            next = normalizeLanguage(key[i + 1]);
            next = next ? next.split('-') : null;
            while (j > 0) {
                lang = get(split.slice(0, j).join('-'));
                if (lang) {
                    return lang;
                }
                if (next && next.length >= j && compareArrays(split, next, true) >= j - 1) {
                    //the next array item is better than a shallower substring of this one
                    break;
                }
                j--;
            }
            i++;
        }
        return moment.fn._lang;
    }

    /************************************
        Formatting
    ************************************/


    function removeFormattingTokens(input) {
        if (input.match(/\[[\s\S]/)) {
            return input.replace(/^\[|\]$/g, "");
        }
        return input.replace(/\\/g, "");
    }

    function makeFormatFunction(format) {
        var array = format.match(formattingTokens), i, length;

        for (i = 0, length = array.length; i < length; i++) {
            if (formatTokenFunctions[array[i]]) {
                array[i] = formatTokenFunctions[array[i]];
            } else {
                array[i] = removeFormattingTokens(array[i]);
            }
        }

        return function (mom) {
            var output = "";
            for (i = 0; i < length; i++) {
                output += array[i] instanceof Function ? array[i].call(mom, format) : array[i];
            }
            return output;
        };
    }

    // format date using native date object
    function formatMoment(m, format) {

        if (!m.isValid()) {
            return m.lang().invalidDate();
        }

        format = expandFormat(format, m.lang());

        if (!formatFunctions[format]) {
            formatFunctions[format] = makeFormatFunction(format);
        }

        return formatFunctions[format](m);
    }

    function expandFormat(format, lang) {
        var i = 5;

        function replaceLongDateFormatTokens(input) {
            return lang.longDateFormat(input) || input;
        }

        localFormattingTokens.lastIndex = 0;
        while (i >= 0 && localFormattingTokens.test(format)) {
            format = format.replace(localFormattingTokens, replaceLongDateFormatTokens);
            localFormattingTokens.lastIndex = 0;
            i -= 1;
        }

        return format;
    }


    /************************************
        Parsing
    ************************************/


    // get the regex to find the next token
    function getParseRegexForToken(token, config) {
        var a;
        switch (token) {
        case 'DDDD':
            return parseTokenThreeDigits;
        case 'YYYY':
        case 'GGGG':
        case 'gggg':
            return parseTokenFourDigits;
        case 'YYYYY':
        case 'GGGGG':
        case 'ggggg':
            return parseTokenSixDigits;
        case 'S':
        case 'SS':
        case 'SSS':
        case 'DDD':
            return parseTokenOneToThreeDigits;
        case 'MMM':
        case 'MMMM':
        case 'dd':
        case 'ddd':
        case 'dddd':
            return parseTokenWord;
        case 'a':
        case 'A':
            return getLangDefinition(config._l)._meridiemParse;
        case 'X':
            return parseTokenTimestampMs;
        case 'Z':
        case 'ZZ':
            return parseTokenTimezone;
        case 'T':
            return parseTokenT;
        case 'MM':
        case 'DD':
        case 'YY':
        case 'GG':
        case 'gg':
        case 'HH':
        case 'hh':
        case 'mm':
        case 'ss':
        case 'M':
        case 'D':
        case 'd':
        case 'H':
        case 'h':
        case 'm':
        case 's':
        case 'w':
        case 'ww':
        case 'W':
        case 'WW':
        case 'e':
        case 'E':
            return parseTokenOneOrTwoDigits;
        default :
            a = new RegExp(regexpEscape(unescapeFormat(token.replace('\\', '')), "i"));
            return a;
        }
    }

    function timezoneMinutesFromString(string) {
        var tzchunk = (parseTokenTimezone.exec(string) || [])[0],
            parts = (tzchunk + '').match(parseTimezoneChunker) || ['-', 0, 0],
            minutes = +(parts[1] * 60) + toInt(parts[2]);

        return parts[0] === '+' ? -minutes : minutes;
    }

    // function to convert string input to date
    function addTimeToArrayFromToken(token, input, config) {
        var a, datePartArray = config._a;

        switch (token) {
        // MONTH
        case 'M' : // fall through to MM
        case 'MM' :
            if (input != null) {
                datePartArray[MONTH] = toInt(input) - 1;
            }
            break;
        case 'MMM' : // fall through to MMMM
        case 'MMMM' :
            a = getLangDefinition(config._l).monthsParse(input);
            // if we didn't find a month name, mark the date as invalid.
            if (a != null) {
                datePartArray[MONTH] = a;
            } else {
                config._pf.invalidMonth = input;
            }
            break;
        // DAY OF MONTH
        case 'D' : // fall through to DD
        case 'DD' :
            if (input != null) {
                datePartArray[DATE] = toInt(input);
            }
            break;
        // DAY OF YEAR
        case 'DDD' : // fall through to DDDD
        case 'DDDD' :
            if (input != null) {
                config._dayOfYear = toInt(input);
            }

            break;
        // YEAR
        case 'YY' :
            datePartArray[YEAR] = toInt(input) + (toInt(input) > 68 ? 1900 : 2000);
            break;
        case 'YYYY' :
        case 'YYYYY' :
            datePartArray[YEAR] = toInt(input);
            break;
        // AM / PM
        case 'a' : // fall through to A
        case 'A' :
            config._isPm = getLangDefinition(config._l).isPM(input);
            break;
        // 24 HOUR
        case 'H' : // fall through to hh
        case 'HH' : // fall through to hh
        case 'h' : // fall through to hh
        case 'hh' :
            datePartArray[HOUR] = toInt(input);
            break;
        // MINUTE
        case 'm' : // fall through to mm
        case 'mm' :
            datePartArray[MINUTE] = toInt(input);
            break;
        // SECOND
        case 's' : // fall through to ss
        case 'ss' :
            datePartArray[SECOND] = toInt(input);
            break;
        // MILLISECOND
        case 'S' :
        case 'SS' :
        case 'SSS' :
            datePartArray[MILLISECOND] = toInt(('0.' + input) * 1000);
            break;
        // UNIX TIMESTAMP WITH MS
        case 'X':
            config._d = new Date(parseFloat(input) * 1000);
            break;
        // TIMEZONE
        case 'Z' : // fall through to ZZ
        case 'ZZ' :
            config._useUTC = true;
            config._tzm = timezoneMinutesFromString(input);
            break;
        case 'w':
        case 'ww':
        case 'W':
        case 'WW':
        case 'd':
        case 'dd':
        case 'ddd':
        case 'dddd':
        case 'e':
        case 'E':
            token = token.substr(0, 1);
            /* falls through */
        case 'gg':
        case 'gggg':
        case 'GG':
        case 'GGGG':
        case 'GGGGG':
            token = token.substr(0, 2);
            if (input) {
                config._w = config._w || {};
                config._w[token] = input;
            }
            break;
        }
    }

    // convert an array to a date.
    // the array should mirror the parameters below
    // note: all values past the year are optional and will default to the lowest possible value.
    // [year, month, day , hour, minute, second, millisecond]
    function dateFromConfig(config) {
        var i, date, input = [], currentDate,
            yearToUse, fixYear, w, temp, lang, weekday, week;

        if (config._d) {
            return;
        }

        currentDate = currentDateArray(config);

        //compute day of the year from weeks and weekdays
        if (config._w && config._a[DATE] == null && config._a[MONTH] == null) {
            fixYear = function (val) {
                return val ?
                  (val.length < 3 ? (parseInt(val, 10) > 68 ? '19' + val : '20' + val) : val) :
                  (config._a[YEAR] == null ? moment().weekYear() : config._a[YEAR]);
            };

            w = config._w;
            if (w.GG != null || w.W != null || w.E != null) {
                temp = dayOfYearFromWeeks(fixYear(w.GG), w.W || 1, w.E, 4, 1);
            }
            else {
                lang = getLangDefinition(config._l);
                weekday = w.d != null ?  parseWeekday(w.d, lang) :
                  (w.e != null ?  parseInt(w.e, 10) + lang._week.dow : 0);

                week = parseInt(w.w, 10) || 1;

                //if we're parsing 'd', then the low day numbers may be next week
                if (w.d != null && weekday < lang._week.dow) {
                    week++;
                }

                temp = dayOfYearFromWeeks(fixYear(w.gg), week, weekday, lang._week.doy, lang._week.dow);
            }

            config._a[YEAR] = temp.year;
            config._dayOfYear = temp.dayOfYear;
        }

        //if the day of the year is set, figure out what it is
        if (config._dayOfYear) {
            yearToUse = config._a[YEAR] == null ? currentDate[YEAR] : config._a[YEAR];

            if (config._dayOfYear > daysInYear(yearToUse)) {
                config._pf._overflowDayOfYear = true;
            }

            date = makeUTCDate(yearToUse, 0, config._dayOfYear);
            config._a[MONTH] = date.getUTCMonth();
            config._a[DATE] = date.getUTCDate();
        }

        // Default to current date.
        // * if no year, month, day of month are given, default to today
        // * if day of month is given, default month and year
        // * if month is given, default only year
        // * if year is given, don't default anything
        for (i = 0; i < 3 && config._a[i] == null; ++i) {
            config._a[i] = input[i] = currentDate[i];
        }

        // Zero out whatever was not defaulted, including time
        for (; i < 7; i++) {
            config._a[i] = input[i] = (config._a[i] == null) ? (i === 2 ? 1 : 0) : config._a[i];
        }

        // add the offsets to the time to be parsed so that we can have a clean array for checking isValid
        input[HOUR] += toInt((config._tzm || 0) / 60);
        input[MINUTE] += toInt((config._tzm || 0) % 60);

        config._d = (config._useUTC ? makeUTCDate : makeDate).apply(null, input);
    }

    function dateFromObject(config) {
        var normalizedInput;

        if (config._d) {
            return;
        }

        normalizedInput = normalizeObjectUnits(config._i);
        config._a = [
            normalizedInput.year,
            normalizedInput.month,
            normalizedInput.day,
            normalizedInput.hour,
            normalizedInput.minute,
            normalizedInput.second,
            normalizedInput.millisecond
        ];

        dateFromConfig(config);
    }

    function currentDateArray(config) {
        var now = new Date();
        if (config._useUTC) {
            return [
                now.getUTCFullYear(),
                now.getUTCMonth(),
                now.getUTCDate()
            ];
        } else {
            return [now.getFullYear(), now.getMonth(), now.getDate()];
        }
    }

    // date from string and format string
    function makeDateFromStringAndFormat(config) {

        config._a = [];
        config._pf.empty = true;

        // This array is used to make a Date, either with `new Date` or `Date.UTC`
        var lang = getLangDefinition(config._l),
            string = '' + config._i,
            i, parsedInput, tokens, token, skipped,
            stringLength = string.length,
            totalParsedInputLength = 0;

        tokens = expandFormat(config._f, lang).match(formattingTokens) || [];

        for (i = 0; i < tokens.length; i++) {
            token = tokens[i];
            parsedInput = (getParseRegexForToken(token, config).exec(string) || [])[0];
            if (parsedInput) {
                skipped = string.substr(0, string.indexOf(parsedInput));
                if (skipped.length > 0) {
                    config._pf.unusedInput.push(skipped);
                }
                string = string.slice(string.indexOf(parsedInput) + parsedInput.length);
                totalParsedInputLength += parsedInput.length;
            }
            // don't parse if it's not a known token
            if (formatTokenFunctions[token]) {
                if (parsedInput) {
                    config._pf.empty = false;
                }
                else {
                    config._pf.unusedTokens.push(token);
                }
                addTimeToArrayFromToken(token, parsedInput, config);
            }
            else if (config._strict && !parsedInput) {
                config._pf.unusedTokens.push(token);
            }
        }

        // add remaining unparsed input length to the string
        config._pf.charsLeftOver = stringLength - totalParsedInputLength;
        if (string.length > 0) {
            config._pf.unusedInput.push(string);
        }

        // handle am pm
        if (config._isPm && config._a[HOUR] < 12) {
            config._a[HOUR] += 12;
        }
        // if is 12 am, change hours to 0
        if (config._isPm === false && config._a[HOUR] === 12) {
            config._a[HOUR] = 0;
        }

        dateFromConfig(config);
        checkOverflow(config);
    }

    function unescapeFormat(s) {
        return s.replace(/\\(\[)|\\(\])|\[([^\]\[]*)\]|\\(.)/g, function (matched, p1, p2, p3, p4) {
            return p1 || p2 || p3 || p4;
        });
    }

    // Code from http://stackoverflow.com/questions/3561493/is-there-a-regexp-escape-function-in-javascript
    function regexpEscape(s) {
        return s.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
    }

    // date from string and array of format strings
    function makeDateFromStringAndArray(config) {
        var tempConfig,
            bestMoment,

            scoreToBeat,
            i,
            currentScore;

        if (config._f.length === 0) {
            config._pf.invalidFormat = true;
            config._d = new Date(NaN);
            return;
        }

        for (i = 0; i < config._f.length; i++) {
            currentScore = 0;
            tempConfig = extend({}, config);
            initializeParsingFlags(tempConfig);
            tempConfig._f = config._f[i];
            makeDateFromStringAndFormat(tempConfig);

            if (!isValid(tempConfig)) {
                continue;
            }

            // if there is any input that was not parsed add a penalty for that format
            currentScore += tempConfig._pf.charsLeftOver;

            //or tokens
            currentScore += tempConfig._pf.unusedTokens.length * 10;

            tempConfig._pf.score = currentScore;

            if (scoreToBeat == null || currentScore < scoreToBeat) {
                scoreToBeat = currentScore;
                bestMoment = tempConfig;
            }
        }

        extend(config, bestMoment || tempConfig);
    }

    // date from iso format
    function makeDateFromString(config) {
        var i,
            string = config._i,
            match = isoRegex.exec(string);

        if (match) {
            for (i = 4; i > 0; i--) {
                if (match[i]) {
                    // match[5] should be "T" or undefined
                    config._f = isoDates[i - 1] + (match[6] || " ");
                    break;
                }
            }
            for (i = 0; i < 4; i++) {
                if (isoTimes[i][1].exec(string)) {
                    config._f += isoTimes[i][0];
                    break;
                }
            }
            if (parseTokenTimezone.exec(string)) {
                config._f += " Z";
            }
            makeDateFromStringAndFormat(config);
        }
        else {
            config._d = new Date(string);
        }
    }

    function makeDateFromInput(config) {
        var input = config._i,
            matched = aspNetJsonRegex.exec(input);

        if (input === undefined) {
            config._d = new Date();
        } else if (matched) {
            config._d = new Date(+matched[1]);
        } else if (typeof input === 'string') {
            makeDateFromString(config);
        } else if (isArray(input)) {
            config._a = input.slice(0);
            dateFromConfig(config);
        } else if (isDate(input)) {
            config._d = new Date(+input);
        } else if (typeof(input) === 'object') {
            dateFromObject(config);
        } else {
            config._d = new Date(input);
        }
    }

    function makeDate(y, m, d, h, M, s, ms) {
        //can't just apply() to create a date:
        //http://stackoverflow.com/questions/181348/instantiating-a-javascript-object-by-calling-prototype-constructor-apply
        var date = new Date(y, m, d, h, M, s, ms);

        //the date constructor doesn't accept years < 1970
        if (y < 1970) {
            date.setFullYear(y);
        }
        return date;
    }

    function makeUTCDate(y) {
        var date = new Date(Date.UTC.apply(null, arguments));
        if (y < 1970) {
            date.setUTCFullYear(y);
        }
        return date;
    }

    function parseWeekday(input, language) {
        if (typeof input === 'string') {
            if (!isNaN(input)) {
                input = parseInt(input, 10);
            }
            else {
                input = language.weekdaysParse(input);
                if (typeof input !== 'number') {
                    return null;
                }
            }
        }
        return input;
    }

    /************************************
        Relative Time
    ************************************/


    // helper function for moment.fn.from, moment.fn.fromNow, and moment.duration.fn.humanize
    function substituteTimeAgo(string, number, withoutSuffix, isFuture, lang) {
        return lang.relativeTime(number || 1, !!withoutSuffix, string, isFuture);
    }

    function relativeTime(milliseconds, withoutSuffix, lang) {
        var seconds = round(Math.abs(milliseconds) / 1000),
            minutes = round(seconds / 60),
            hours = round(minutes / 60),
            days = round(hours / 24),
            years = round(days / 365),
            args = seconds < 45 && ['s', seconds] ||
                minutes === 1 && ['m'] ||
                minutes < 45 && ['mm', minutes] ||
                hours === 1 && ['h'] ||
                hours < 22 && ['hh', hours] ||
                days === 1 && ['d'] ||
                days <= 25 && ['dd', days] ||
                days <= 45 && ['M'] ||
                days < 345 && ['MM', round(days / 30)] ||
                years === 1 && ['y'] || ['yy', years];
        args[2] = withoutSuffix;
        args[3] = milliseconds > 0;
        args[4] = lang;
        return substituteTimeAgo.apply({}, args);
    }


    /************************************
        Week of Year
    ************************************/


    // firstDayOfWeek       0 = sun, 6 = sat
    //                      the day of the week that starts the week
    //                      (usually sunday or monday)
    // firstDayOfWeekOfYear 0 = sun, 6 = sat
    //                      the first week is the week that contains the first
    //                      of this day of the week
    //                      (eg. ISO weeks use thursday (4))
    function weekOfYear(mom, firstDayOfWeek, firstDayOfWeekOfYear) {
        var end = firstDayOfWeekOfYear - firstDayOfWeek,
            daysToDayOfWeek = firstDayOfWeekOfYear - mom.day(),
            adjustedMoment;


        if (daysToDayOfWeek > end) {
            daysToDayOfWeek -= 7;
        }

        if (daysToDayOfWeek < end - 7) {
            daysToDayOfWeek += 7;
        }

        adjustedMoment = moment(mom).add('d', daysToDayOfWeek);
        return {
            week: Math.ceil(adjustedMoment.dayOfYear() / 7),
            year: adjustedMoment.year()
        };
    }

    //http://en.wikipedia.org/wiki/ISO_week_date#Calculating_a_date_given_the_year.2C_week_number_and_weekday
    function dayOfYearFromWeeks(year, week, weekday, firstDayOfWeekOfYear, firstDayOfWeek) {
        var d = new Date(Date.UTC(year, 0)).getUTCDay(),
            daysToAdd, dayOfYear;

        weekday = weekday != null ? weekday : firstDayOfWeek;
        daysToAdd = firstDayOfWeek - d + (d > firstDayOfWeekOfYear ? 7 : 0);
        dayOfYear = 7 * (week - 1) + (weekday - firstDayOfWeek) + daysToAdd + 1;

        return {
            year: dayOfYear > 0 ? year : year - 1,
            dayOfYear: dayOfYear > 0 ?  dayOfYear : daysInYear(year - 1) + dayOfYear
        };
    }

    /************************************
        Top Level Functions
    ************************************/

    function makeMoment(config) {
        var input = config._i,
            format = config._f;

        if (typeof config._pf === 'undefined') {
            initializeParsingFlags(config);
        }

        if (input === null) {
            return moment.invalid({nullInput: true});
        }

        if (typeof input === 'string') {
            config._i = input = getLangDefinition().preparse(input);
        }


        if (moment.isMoment(input)) {
            config = extend({}, input);

            config._d = new Date(+input._d);
        } else if (format) {
            if (isArray(format)) {
                makeDateFromStringAndArray(config);
            } else {
                makeDateFromStringAndFormat(config);
            }
        } else {
            makeDateFromInput(config);
        }

        return new Moment(config);
    }

    moment = function (input, format, lang, strict) {
        if (typeof(lang) === "boolean") {
            strict = lang;
            lang = undefined;
        }
        return makeMoment({
            _i : input,
            _f : format,
            _l : lang,
            _strict : strict,
            _isUTC : false
        });
    };

    // creating with utc
    moment.utc = function (input, format, lang, strict) {
        var m;

        if (typeof(lang) === "boolean") {
            strict = lang;
            lang = undefined;
        }
        m = makeMoment({
            _useUTC : true,
            _isUTC : true,
            _l : lang,
            _i : input,
            _f : format,
            _strict : strict
        }).utc();

        return m;
    };

    // creating with unix timestamp (in seconds)
    moment.unix = function (input) {
        return moment(input * 1000);
    };

    // duration
    moment.duration = function (input, key) {
        var isDuration = moment.isDuration(input),
            isNumber = (typeof input === 'number'),
            duration = (isDuration ? input._input : (isNumber ? {} : input)),
            // matching against regexp is expensive, do it on demand
            match = null,
            sign,
            ret,
            parseIso,
            timeEmpty,
            dateTimeEmpty;

        if (isNumber) {
            if (key) {
                duration[key] = input;
            } else {
                duration.milliseconds = input;
            }
        } else if (!!(match = aspNetTimeSpanJsonRegex.exec(input))) {
            sign = (match[1] === "-") ? -1 : 1;
            duration = {
                y: 0,
                d: toInt(match[DATE]) * sign,
                h: toInt(match[HOUR]) * sign,
                m: toInt(match[MINUTE]) * sign,
                s: toInt(match[SECOND]) * sign,
                ms: toInt(match[MILLISECOND]) * sign
            };
        } else if (!!(match = isoDurationRegex.exec(input))) {
            sign = (match[1] === "-") ? -1 : 1;
            parseIso = function (inp) {
                // We'd normally use ~~inp for this, but unfortunately it also
                // converts floats to ints.
                // inp may be undefined, so careful calling replace on it.
                var res = inp && parseFloat(inp.replace(',', '.'));
                // apply sign while we're at it
                return (isNaN(res) ? 0 : res) * sign;
            };
            duration = {
                y: parseIso(match[2]),
                M: parseIso(match[3]),
                d: parseIso(match[4]),
                h: parseIso(match[5]),
                m: parseIso(match[6]),
                s: parseIso(match[7]),
                w: parseIso(match[8])
            };
        }

        ret = new Duration(duration);

        if (isDuration && input.hasOwnProperty('_lang')) {
            ret._lang = input._lang;
        }

        return ret;
    };

    // version number
    moment.version = VERSION;

    // default format
    moment.defaultFormat = isoFormat;

    // This function will be called whenever a moment is mutated.
    // It is intended to keep the offset in sync with the timezone.
    moment.updateOffset = function () {};

    // This function will load languages and then set the global language.  If
    // no arguments are passed in, it will simply return the current global
    // language key.
    moment.lang = function (key, values) {
        var r;
        if (!key) {
            return moment.fn._lang._abbr;
        }
        if (values) {
            loadLang(normalizeLanguage(key), values);
        } else if (values === null) {
            unloadLang(key);
            key = 'en';
        } else if (!languages[key]) {
            getLangDefinition(key);
        }
        r = moment.duration.fn._lang = moment.fn._lang = getLangDefinition(key);
        return r._abbr;
    };

    // returns language data
    moment.langData = function (key) {
        if (key && key._lang && key._lang._abbr) {
            key = key._lang._abbr;
        }
        return getLangDefinition(key);
    };

    // compare moment object
    moment.isMoment = function (obj) {
        return obj instanceof Moment;
    };

    // for typechecking Duration objects
    moment.isDuration = function (obj) {
        return obj instanceof Duration;
    };

    for (i = lists.length - 1; i >= 0; --i) {
        makeList(lists[i]);
    }

    moment.normalizeUnits = function (units) {
        return normalizeUnits(units);
    };

    moment.invalid = function (flags) {
        var m = moment.utc(NaN);
        if (flags != null) {
            extend(m._pf, flags);
        }
        else {
            m._pf.userInvalidated = true;
        }

        return m;
    };

    moment.parseZone = function (input) {
        return moment(input).parseZone();
    };

    /************************************
        Moment Prototype
    ************************************/


    extend(moment.fn = Moment.prototype, {

        clone : function () {
            return moment(this);
        },

        valueOf : function () {
            return +this._d + ((this._offset || 0) * 60000);
        },

        unix : function () {
            return Math.floor(+this / 1000);
        },

        toString : function () {
            return this.clone().lang('en').format("ddd MMM DD YYYY HH:mm:ss [GMT]ZZ");
        },

        toDate : function () {
            return this._offset ? new Date(+this) : this._d;
        },

        toISOString : function () {
            return formatMoment(moment(this).utc(), 'YYYY-MM-DD[T]HH:mm:ss.SSS[Z]');
        },

        toArray : function () {
            var m = this;
            return [
                m.year(),
                m.month(),
                m.date(),
                m.hours(),
                m.minutes(),
                m.seconds(),
                m.milliseconds()
            ];
        },

        isValid : function () {
            return isValid(this);
        },

        isDSTShifted : function () {

            if (this._a) {
                return this.isValid() && compareArrays(this._a, (this._isUTC ? moment.utc(this._a) : moment(this._a)).toArray()) > 0;
            }

            return false;
        },

        parsingFlags : function () {
            return extend({}, this._pf);
        },

        invalidAt: function () {
            return this._pf.overflow;
        },

        utc : function () {
            return this.zone(0);
        },

        local : function () {
            this.zone(0);
            this._isUTC = false;
            return this;
        },

        format : function (inputString) {
            var output = formatMoment(this, inputString || moment.defaultFormat);
            return this.lang().postformat(output);
        },

        add : function (input, val) {
            var dur;
            // switch args to support add('s', 1) and add(1, 's')
            if (typeof input === 'string') {
                dur = moment.duration(+val, input);
            } else {
                dur = moment.duration(input, val);
            }
            addOrSubtractDurationFromMoment(this, dur, 1);
            return this;
        },

        subtract : function (input, val) {
            var dur;
            // switch args to support subtract('s', 1) and subtract(1, 's')
            if (typeof input === 'string') {
                dur = moment.duration(+val, input);
            } else {
                dur = moment.duration(input, val);
            }
            addOrSubtractDurationFromMoment(this, dur, -1);
            return this;
        },

        diff : function (input, units, asFloat) {
            var that = this._isUTC ? moment(input).zone(this._offset || 0) : moment(input).local(),
                zoneDiff = (this.zone() - that.zone()) * 6e4,
                diff, output;

            units = normalizeUnits(units);

            if (units === 'year' || units === 'month') {
                // average number of days in the months in the given dates
                diff = (this.daysInMonth() + that.daysInMonth()) * 432e5; // 24 * 60 * 60 * 1000 / 2
                // difference in months
                output = ((this.year() - that.year()) * 12) + (this.month() - that.month());
                // adjust by taking difference in days, average number of days
                // and dst in the given months.
                output += ((this - moment(this).startOf('month')) -
                        (that - moment(that).startOf('month'))) / diff;
                // same as above but with zones, to negate all dst
                output -= ((this.zone() - moment(this).startOf('month').zone()) -
                        (that.zone() - moment(that).startOf('month').zone())) * 6e4 / diff;
                if (units === 'year') {
                    output = output / 12;
                }
            } else {
                diff = (this - that);
                output = units === 'second' ? diff / 1e3 : // 1000
                    units === 'minute' ? diff / 6e4 : // 1000 * 60
                    units === 'hour' ? diff / 36e5 : // 1000 * 60 * 60
                    units === 'day' ? (diff - zoneDiff) / 864e5 : // 1000 * 60 * 60 * 24, negate dst
                    units === 'week' ? (diff - zoneDiff) / 6048e5 : // 1000 * 60 * 60 * 24 * 7, negate dst
                    diff;
            }
            return asFloat ? output : absRound(output);
        },

        from : function (time, withoutSuffix) {
            return moment.duration(this.diff(time)).lang(this.lang()._abbr).humanize(!withoutSuffix);
        },

        fromNow : function (withoutSuffix) {
            return this.from(moment(), withoutSuffix);
        },

        calendar : function () {
            var diff = this.diff(moment().zone(this.zone()).startOf('day'), 'days', true),
                format = diff < -6 ? 'sameElse' :
                diff < -1 ? 'lastWeek' :
                diff < 0 ? 'lastDay' :
                diff < 1 ? 'sameDay' :
                diff < 2 ? 'nextDay' :
                diff < 7 ? 'nextWeek' : 'sameElse';
            return this.format(this.lang().calendar(format, this));
        },

        isLeapYear : function () {
            return isLeapYear(this.year());
        },

        isDST : function () {
            return (this.zone() < this.clone().month(0).zone() ||
                this.zone() < this.clone().month(5).zone());
        },

        day : function (input) {
            var day = this._isUTC ? this._d.getUTCDay() : this._d.getDay();
            if (input != null) {
                input = parseWeekday(input, this.lang());
                return this.add({ d : input - day });
            } else {
                return day;
            }
        },

        month : function (input) {
            var utc = this._isUTC ? 'UTC' : '',
                dayOfMonth;

            if (input != null) {
                if (typeof input === 'string') {
                    input = this.lang().monthsParse(input);
                    if (typeof input !== 'number') {
                        return this;
                    }
                }

                dayOfMonth = this.date();
                this.date(1);
                this._d['set' + utc + 'Month'](input);
                this.date(Math.min(dayOfMonth, this.daysInMonth()));

                moment.updateOffset(this);
                return this;
            } else {
                return this._d['get' + utc + 'Month']();
            }
        },

        startOf: function (units) {
            units = normalizeUnits(units);
            // the following switch intentionally omits break keywords
            // to utilize falling through the cases.
            switch (units) {
            case 'year':
                this.month(0);
                /* falls through */
            case 'month':
                this.date(1);
                /* falls through */
            case 'week':
            case 'isoWeek':
            case 'day':
                this.hours(0);
                /* falls through */
            case 'hour':
                this.minutes(0);
                /* falls through */
            case 'minute':
                this.seconds(0);
                /* falls through */
            case 'second':
                this.milliseconds(0);
                /* falls through */
            }

            // weeks are a special case
            if (units === 'week') {
                this.weekday(0);
            } else if (units === 'isoWeek') {
                this.isoWeekday(1);
            }

            return this;
        },

        endOf: function (units) {
            units = normalizeUnits(units);
            return this.startOf(units).add((units === 'isoWeek' ? 'week' : units), 1).subtract('ms', 1);
        },

        isAfter: function (input, units) {
            units = typeof units !== 'undefined' ? units : 'millisecond';
            return +this.clone().startOf(units) > +moment(input).startOf(units);
        },

        isBefore: function (input, units) {
            units = typeof units !== 'undefined' ? units : 'millisecond';
            return +this.clone().startOf(units) < +moment(input).startOf(units);
        },

        isSame: function (input, units) {
            units = typeof units !== 'undefined' ? units : 'millisecond';
            return +this.clone().startOf(units) === +moment(input).startOf(units);
        },

        min: function (other) {
            other = moment.apply(null, arguments);
            return other < this ? this : other;
        },

        max: function (other) {
            other = moment.apply(null, arguments);
            return other > this ? this : other;
        },

        zone : function (input) {
            var offset = this._offset || 0;
            if (input != null) {
                if (typeof input === "string") {
                    input = timezoneMinutesFromString(input);
                }
                if (Math.abs(input) < 16) {
                    input = input * 60;
                }
                this._offset = input;
                this._isUTC = true;
                if (offset !== input) {
                    addOrSubtractDurationFromMoment(this, moment.duration(offset - input, 'm'), 1, true);
                }
            } else {
                return this._isUTC ? offset : this._d.getTimezoneOffset();
            }
            return this;
        },

        zoneAbbr : function () {
            return this._isUTC ? "UTC" : "";
        },

        zoneName : function () {
            return this._isUTC ? "Coordinated Universal Time" : "";
        },

        parseZone : function () {
            if (typeof this._i === 'string') {
                this.zone(this._i);
            }
            return this;
        },

        hasAlignedHourOffset : function (input) {
            if (!input) {
                input = 0;
            }
            else {
                input = moment(input).zone();
            }

            return (this.zone() - input) % 60 === 0;
        },

        daysInMonth : function () {
            return daysInMonth(this.year(), this.month());
        },

        dayOfYear : function (input) {
            var dayOfYear = round((moment(this).startOf('day') - moment(this).startOf('year')) / 864e5) + 1;
            return input == null ? dayOfYear : this.add("d", (input - dayOfYear));
        },

        weekYear : function (input) {
            var year = weekOfYear(this, this.lang()._week.dow, this.lang()._week.doy).year;
            return input == null ? year : this.add("y", (input - year));
        },

        isoWeekYear : function (input) {
            var year = weekOfYear(this, 1, 4).year;
            return input == null ? year : this.add("y", (input - year));
        },

        week : function (input) {
            var week = this.lang().week(this);
            return input == null ? week : this.add("d", (input - week) * 7);
        },

        isoWeek : function (input) {
            var week = weekOfYear(this, 1, 4).week;
            return input == null ? week : this.add("d", (input - week) * 7);
        },

        weekday : function (input) {
            var weekday = (this.day() + 7 - this.lang()._week.dow) % 7;
            return input == null ? weekday : this.add("d", input - weekday);
        },

        isoWeekday : function (input) {
            // behaves the same as moment#day except
            // as a getter, returns 7 instead of 0 (1-7 range instead of 0-6)
            // as a setter, sunday should belong to the previous week.
            return input == null ? this.day() || 7 : this.day(this.day() % 7 ? input : input - 7);
        },

        get : function (units) {
            units = normalizeUnits(units);
            return this[units]();
        },

        set : function (units, value) {
            units = normalizeUnits(units);
            if (typeof this[units] === 'function') {
                this[units](value);
            }
            return this;
        },

        // If passed a language key, it will set the language for this
        // instance.  Otherwise, it will return the language configuration
        // variables for this instance.
        lang : function (key) {
            if (key === undefined) {
                return this._lang;
            } else {
                this._lang = getLangDefinition(key);
                return this;
            }
        }
    });

    // helper for adding shortcuts
    function makeGetterAndSetter(name, key) {
        moment.fn[name] = moment.fn[name + 's'] = function (input) {
            var utc = this._isUTC ? 'UTC' : '';
            if (input != null) {
                this._d['set' + utc + key](input);
                moment.updateOffset(this);
                return this;
            } else {
                return this._d['get' + utc + key]();
            }
        };
    }

    // loop through and add shortcuts (Month, Date, Hours, Minutes, Seconds, Milliseconds)
    for (i = 0; i < proxyGettersAndSetters.length; i ++) {
        makeGetterAndSetter(proxyGettersAndSetters[i].toLowerCase().replace(/s$/, ''), proxyGettersAndSetters[i]);
    }

    // add shortcut for year (uses different syntax than the getter/setter 'year' == 'FullYear')
    makeGetterAndSetter('year', 'FullYear');

    // add plural methods
    moment.fn.days = moment.fn.day;
    moment.fn.months = moment.fn.month;
    moment.fn.weeks = moment.fn.week;
    moment.fn.isoWeeks = moment.fn.isoWeek;

    // add aliased format methods
    moment.fn.toJSON = moment.fn.toISOString;

    /************************************
        Duration Prototype
    ************************************/


    extend(moment.duration.fn = Duration.prototype, {

        _bubble : function () {
            var milliseconds = this._milliseconds,
                days = this._days,
                months = this._months,
                data = this._data,
                seconds, minutes, hours, years;

            // The following code bubbles up values, see the tests for
            // examples of what that means.
            data.milliseconds = milliseconds % 1000;

            seconds = absRound(milliseconds / 1000);
            data.seconds = seconds % 60;

            minutes = absRound(seconds / 60);
            data.minutes = minutes % 60;

            hours = absRound(minutes / 60);
            data.hours = hours % 24;

            days += absRound(hours / 24);
            data.days = days % 30;

            months += absRound(days / 30);
            data.months = months % 12;

            years = absRound(months / 12);
            data.years = years;
        },

        weeks : function () {
            return absRound(this.days() / 7);
        },

        valueOf : function () {
            return this._milliseconds +
              this._days * 864e5 +
              (this._months % 12) * 2592e6 +
              toInt(this._months / 12) * 31536e6;
        },

        humanize : function (withSuffix) {
            var difference = +this,
                output = relativeTime(difference, !withSuffix, this.lang());

            if (withSuffix) {
                output = this.lang().pastFuture(difference, output);
            }

            return this.lang().postformat(output);
        },

        add : function (input, val) {
            // supports only 2.0-style add(1, 's') or add(moment)
            var dur = moment.duration(input, val);

            this._milliseconds += dur._milliseconds;
            this._days += dur._days;
            this._months += dur._months;

            this._bubble();

            return this;
        },

        subtract : function (input, val) {
            var dur = moment.duration(input, val);

            this._milliseconds -= dur._milliseconds;
            this._days -= dur._days;
            this._months -= dur._months;

            this._bubble();

            return this;
        },

        get : function (units) {
            units = normalizeUnits(units);
            return this[units.toLowerCase() + 's']();
        },

        as : function (units) {
            units = normalizeUnits(units);
            return this['as' + units.charAt(0).toUpperCase() + units.slice(1) + 's']();
        },

        lang : moment.fn.lang,

        toIsoString : function () {
            // inspired by https://github.com/dordille/moment-isoduration/blob/master/moment.isoduration.js
            var years = Math.abs(this.years()),
                months = Math.abs(this.months()),
                days = Math.abs(this.days()),
                hours = Math.abs(this.hours()),
                minutes = Math.abs(this.minutes()),
                seconds = Math.abs(this.seconds() + this.milliseconds() / 1000);

            if (!this.asSeconds()) {
                // this is the same as C#'s (Noda) and python (isodate)...
                // but not other JS (goog.date)
                return 'P0D';
            }

            return (this.asSeconds() < 0 ? '-' : '') +
                'P' +
                (years ? years + 'Y' : '') +
                (months ? months + 'M' : '') +
                (days ? days + 'D' : '') +
                ((hours || minutes || seconds) ? 'T' : '') +
                (hours ? hours + 'H' : '') +
                (minutes ? minutes + 'M' : '') +
                (seconds ? seconds + 'S' : '');
        }
    });

    function makeDurationGetter(name) {
        moment.duration.fn[name] = function () {
            return this._data[name];
        };
    }

    function makeDurationAsGetter(name, factor) {
        moment.duration.fn['as' + name] = function () {
            return +this / factor;
        };
    }

    for (i in unitMillisecondFactors) {
        if (unitMillisecondFactors.hasOwnProperty(i)) {
            makeDurationAsGetter(i, unitMillisecondFactors[i]);
            makeDurationGetter(i.toLowerCase());
        }
    }

    makeDurationAsGetter('Weeks', 6048e5);
    moment.duration.fn.asMonths = function () {
        return (+this - this.years() * 31536e6) / 2592e6 + this.years() * 12;
    };


    /************************************
        Default Lang
    ************************************/


    // Set default language, other languages will inherit from English.
    moment.lang('en', {
        ordinal : function (number) {
            var b = number % 10,
                output = (toInt(number % 100 / 10) === 1) ? 'th' :
                (b === 1) ? 'st' :
                (b === 2) ? 'nd' :
                (b === 3) ? 'rd' : 'th';
            return number + output;
        }
    });

    /* EMBED_LANGUAGES */

    /************************************
        Exposing Moment
    ************************************/

    function makeGlobal() {
        /*global ender:false */
        if (typeof ender === 'undefined') {
            // here, `this` means `window` in the browser, or `global` on the server
            // add `moment` as a global object via a string identifier,
            // for Closure Compiler "advanced" mode
            this['moment'] = moment;
        }
    }

    // CommonJS module is defined
    if (hasModule) {
        module.exports = moment;
        makeGlobal();
    } else if (typeof define === "function" && define.amd) {
        define("moment", ['require','exports','module'],function (require, exports, module) {
            if (module.config().noGlobal !== true) {
                makeGlobal();
            }

            return moment;
        });
    } else {
        makeGlobal();
    }
}).call(this);

define('text!modules/shoutbox/templates/shout.html',[],function () { return '<span class="username">\n    <a href="<%= user_url %>"><%= user_name %></a>:\n</span>\n<%= html %>\n';});

define('modules/shoutbox/views/shout',[
  'jquery', 'underscore', 'backbone', 'marionette',
  'text!modules/shoutbox/templates/shout.html'
], function($, _, Backbone, Marionette, Tpl) {

  

  var ShoutboxView = Marionette.ItemView.extend({

    className: 'shout',

    initialize: function(options) {
      this.webroot = options.webroot;
    },

    serializeData: function() {
      var data = this.model.toJSON();
      data.user_url = this.webroot + 'users/view/' +
          this.model.get('user_id');
      return data;
    },

    template: function(data) {
      return _.template(Tpl, data);
    }

  });

  return ShoutboxView;
});

define('modules/shoutbox/models/control',['underscore', 'backbone'], function(_, Backbone) {
  

  var ShoutboxControlModel = Backbone.Model.extend({
    defaults: {
      notify: false
    },

    initialize: function() {
      this.restoreNotify();

      this.listenTo(this, 'change:notify', this.saveNotify);
    },

    restoreNotify: function() {
      if ('localStorage' in window) {
        this.set('notify', localStorage.getItem('shoutbox-notify') === "true");
      }
    },

    saveNotify: function() {
      if ('localStorage' in window) {
        localStorage.setItem('shoutbox-notify', this.get('notify'));
      }
    }
  });

  return new ShoutboxControlModel();
});

define('modules/shoutbox/views/shouts',[
  'jquery', 'underscore', 'backbone', 'marionette', 'moment', 'models/app',
  'modules/shoutbox/views/shout',
  'modules/shoutbox/models/control'
], function($, _, Backbone, Marionette, moment, App, ShoutView, SbCM) {

  

  var ShoutboxCollectionView = Marionette.CollectionView.extend({
    itemView: ShoutView,
    itemViewOptions: {},

    /**
     * Sends notification and stores it in ShoutboxControlModel
     */
    _Notifications: {
      _last: 0,
      _models: [],
      _isEnabled: false,

      init: function(options) {
        this._currentUserId = options.currentUserId;
        this._isEnabled = options.isEnabled;
        // mark all existing as read
        this._last = options.last;
      },

      add: function(model) {
        if (this._isEnabled !== true) { return; }
        // user's own shout
        if (this._currentUserId === model.get('user_id')) { return; }
        if (model.get('id') <= this._last) { return; }
        this._models.push(model);
      },

      send: function() {
        if (this._models.length === 0) { return; }

        _.each(this._models, function(model) {
          App.eventBus.trigger('html5-notification', {
            title: model.get('user_name'),
            message: model.get('text')
          });
        });
        this._last = _.first(this._models).get('id');
        this._models = [];
      }
    },

    /**
     * Appends timestamp and/or <hr> to shout
     */
    _Delimiter: {
      _conversationCoolOff: 300,
      _previousItemTime: null,
      tpl: _.template('<div class="info_text"><span title="<%= time_long %>"><%= time %></span></div>'),

      init: function(options) {
        this.$el = options.$el;
      },

      append: function(itemView) {
        var itemTime = moment(itemView.model.get('time'));
        this._itemTime = itemTime;
        // first entry
        if (this._previousItemTime === null) {
          this._previousItemTime = itemTime;
          return;
        }
        if ((this._previousItemTime.unix() - itemTime.unix()) > this._conversationCoolOff) {
          this._appendTimestamp(this._previousItemTime);
        } else {
          this.$el.append('<hr>');
        }
        this._previousItemTime = itemTime;
      },

      finish: function() {
        this._previousItemTime = null;
        if (this._itemTime) {
          this._appendTimestamp(this._itemTime);
        }
      },

      _appendTimestamp: function(time) {
        this.$el.append(this.tpl({
          time: time.format('LT'),
          time_long: time.format('llll')
        }));
      }
    },

    initialize: function(options) {
      this.itemViewOptions.webroot = options.webroot;
      this._Delimiter.init({$el: this.$el});
      // setup Notifications
      this.setupNotifications();
      this.listenTo(SbCM, 'change:notify', this.setupNotifications);
    },

    setupNotifications: function() {
      var last = 0;
      if (this.collection.size() > 0) {
        last = this.collection.first().get('id');
      }
      this._Notifications.init({
        currentUserId: App.currentUser.get('id'),
        isEnabled: SbCM.get('notify'),
        last: last
      });
    },

    onBeforeRender: function() {
      this.$el.html('');
    },

    onBeforeItemAdded: function(itemView) {
      this._Delimiter.append(itemView);
    },

    onAfterItemAdded: function(itemView) {
      this._Notifications.add(itemView.model);
    },

    onRender: function() {
      this._Delimiter.finish();
      this._Notifications.send();
    }

  });

  return ShoutboxCollectionView;
});

define('text!modules/shoutbox/templates/add.html',[],function () { return '<form>\n    <textarea id="shoutbox-input" maxlength="255" rows="1"></textarea>\n</form>\n';});

define('modules/shoutbox/views/add',[
  'jquery', 'underscore', 'backbone', 'marionette', 'models/app',
  'modules/shoutbox/models/shout',
  'text!modules/shoutbox/templates/add.html',
  'jqueryAutosize'
], function($, _, Backbone, Marionette, App, ShoutModel, Tpl) {

  

  var ShoutboxAdd = Marionette.ItemView.extend({

    template: _.template(Tpl),

    events: {
      "keyup form": "formUp",
      "keydown form": "formDown"
    },

    submit: function() {
      this.model.set('text', this.textarea.val());
      this.model.save();
    },

    clearForm: function() {
      this.textarea.val('').trigger('autosize');
    },

    formDown: function(event) {
      if (event.keyCode === 13 && event.shiftKey === false) {
        event.preventDefault();
        this.submit();
        this.clearForm();
      }
    },

    formUp: function() {
      if (this.textarea.val().length > 0) {
        App.eventBus.trigger('breakAutoreload');
      } else if (this.textarea.val().length === 0) {
        App.eventBus.trigger('initAutoreload');
      }
    },

    onShow: function() {
      this.textarea = this.$('#shoutbox-input');
      this.textarea.autosize();
    }

  });

  return ShoutboxAdd;

});

define('text!modules/shoutbox/templates/control.html',[],function () { return '<!-- @todo css -->\n<input id=\'shoutbox-notify\' type=\'checkbox\' style="display: none; font-size: 100%">\n<!-- @todo i10n -->\n<label for=\'shoutbox-notify\' style="display: inline; font-size: 100%">Notifications</label>\n';});

define('modules/shoutbox/views/control',[
  'jquery', 'underscore', 'backbone', 'marionette', 'models/app',
  'modules/shoutbox/models/control',
  'text!modules/shoutbox/templates/control.html'
], function($, _, Backbone, Marionette, App, SCM, Tpl) {

  

  var ShoutboxView = Marionette.ItemView.extend({
    template: _.template(Tpl),
    events: {
      'click #shoutbox-notify': 'onChangeNotify'
    },

    initialize: function() {
      this.model = SCM;
    },

    onRender: function() {
      this._putNotifyCheckbox();
    },

    _putNotifyCheckbox: function() {
      var active = App.reqres.request('app:html5-notification:available');
      if (active !== true) { return; }
      this.notify = this.$('#shoutbox-notify');
      if (this.model.get('notify')) {
        this.notify.attr('checked', 'checked');
      } else {
        this.notify.removeAttr('checked');
      }
      this.notify.show();
    },

    onChangeNotify: function() {
      var isChecked = this.notify.is(':checked');
      this.model.set('notify', isChecked);
      if (isChecked) {
        App.commands.execute('app:html5-notification:activate');
      }
    }

  });

  return ShoutboxView;
});

define('text!modules/shoutbox/templates/layout.html',[],function () { return '<div>\n    <div id=\'shoutbox-add\'></div>\n    <div id=\'shoutbox-shouts\'></div>\n    <div id=\'shoutbox-control\'></div>\n</div>\n';});

define('modules/shoutbox/shoutbox',['jquery', 'app/app', 'models/app', 'marionette',
        'modules/shoutbox/collections/shouts', 'modules/shoutbox/models/shout',
        'modules/shoutbox/views/shouts', 'modules/shoutbox/views/add',
        'modules/shoutbox/views/control',
        'text!modules/shoutbox/templates/layout.html'],
    function($, Application, App, Marionette, ShoutsCollection, ShoutModel, ShoutsCollectionView, ShoutboxAddView, ShoutboxControlView, LayoutTpl) {

      

      var ShoutboxModule = Application.module("Shoutbox");

      ShoutboxModule.addInitializer(function(options) {
        var shouts = options.SaitoApp.shouts;
        // @todo
        var webroot = App.reqres.request('webroot');
        var apiroot = App.reqres.request('apiroot');

        if ($("#shoutbox").length) {
          var Shoutbox = {

            // main layout
            layout: null,

            // all viewed shouts
            shoutsCollection: null,

            initialize: function() {
              this.initLayout();
              this.initShoutsCollection();
              this.initAdd();
              this.initShouts();
              this.initControl();
            },

            initShoutsCollection: function() {
              this.shoutsCollection = new ShoutsCollection(shouts, {
                apiroot: apiroot,
              });

              var update = _.bind(function() {
                var prevent = !App.reqres.request('slidetab:open', 'shoutbox');
                if (prevent) {
                  return;
                }
                this.shoutsCollection.fetch();
              }, this);

              // always update when slidetab is opened
              App.eventBus.on('slidetab:open', _.bind(function(data) {
                if (data.slidetab === 'shoutbox') {
                  update();
                }
              }, this));

              // connect external app trigger to issue a reload
              App.commands.setHandler("shoutbox:update", _.bind(function(id) {
                var currentShoutId = 0;
                if (this.shoutsCollection.size() > 0) {
                  currentShoutId = this.shoutsCollection.at(0).get('id');
                }
                if (id === currentShoutId) {
                  return;
                }
                update();
              }, this));
            },

            initLayout: function() {
              var ShoutboxLayout = Marionette.Layout.extend({
                el: '#shoutbox',
                template: LayoutTpl,

                regions: {
                  add: '#shoutbox-add',
                  shouts: '#shoutbox-shouts',
                  control: '#shoutbox-control'
                }
              });

              this.layout = new ShoutboxLayout();
              this.layout.render();
            },

            initShouts: function() {
              var shoutsCollectionView = new ShoutsCollectionView({
                collection: this.shoutsCollection,
                webroot: webroot
              });
              this.layout.shouts.show(shoutsCollectionView);
            },

            initControl: function() {
              var shoutsControlView = new ShoutboxControlView();
              this.layout.control.show(shoutsControlView);
            },

            initAdd: function() {
              var addModel = new ShoutModel({
                webroot: webroot,
                apiroot: apiroot,
                collection: this.shoutsCollection
              });
              this.layout.add.show(new ShoutboxAddView({
                model: addModel
              }));
            }

          };

          Shoutbox.initialize();
        }

      });

      return ShoutboxModule;
    });
define('models/threadline',[
    'underscore',
    'backbone',
    'models/app',
    'cakeRest'
], function(_, Backbone, App, cakeRest) {

    

    var ThreadLineModel = Backbone.Model.extend({

        defaults: {
            isInlineOpened: false,
            shouldScrollOnInlineOpen: true,
            isAlwaysShownInline: false,
            isNewToUser: false,
            posting: '',
            html: ''
        },

        initialize: function() {
            this.webroot = App.settings.get('webroot') + 'entries/';
            this.methodToCakePhpUrl = _.clone(this.methodToCakePhpUrl);
            this.methodToCakePhpUrl.read = 'threadLine/';

            this.set('isAlwaysShownInline', App.currentUser.get('user_show_inline') || false);

            this.listenTo(this, "change:html", this._setIsNewToUser);
        },

        _setIsNewToUser: function() {
            // @bogus performance
            this.set('isNewToUser', $(this.get('html')).data('data-new') === '1');
        }

    });

    _.extend(ThreadLineModel.prototype, cakeRest);

    return ThreadLineModel;
});
define('collections/threadlines',[
	'underscore',
	'backbone',
	'models/threadline'
	], function(_, Backbone, ThreadLineModel) {
		var ThreadLineCollection = Backbone.Collection.extend({
			model: ThreadLineModel
		});
		return ThreadLineCollection;
	});
define('views/threadline-spinner',[
	'jquery',
	'underscore',
	'backbone'
	], function($, _, Backbone) {

        

		var ThreadlineSpinnerView = Backbone.View.extend({

			running: false,

			show: function() {
				var effect = _.bind(function() {
					if (this.running === false) {
						this.$el.css({opacity: 1});
						return;
					}
					this.$el.animate({opacity:0.1}, 900, _.bind(function() {
						this.$el.animate({opacity:1}, 500, effect());
					}, this));
				}, this);
				this.running = true;
				effect();
			},

			hide: function() {
				this.running = false;
			}

		});
		return ThreadlineSpinnerView;
	});

define('text!templates/threadline-spinner.html',[],function () { return '<div class="js-thread_inline thread_inline" style="display:none">\n\t<div class="js-btn-strip btn-strip btn-strip-top pointer">\n\t\t<i class="icon-close-widget"></i>\n\t</div>\n\t<div class="t_s">\n\t</div>\n</div>';});

define('models/geshi',[
    'underscore',
    'backbone'
], function (_, Backbone) {

    

    var GeshiModel = Backbone.Model.extend({

        defaults: {
           isPlaintext: false
        }

    });

    return GeshiModel;
});
define('collections/geshis',[
    'underscore',
    'backbone',
    'models/geshi'
], function(_, Backbone, GeshiModel) {

    var GeshisCollection = Backbone.Collection.extend({
        model: GeshiModel
    });

    return GeshisCollection;

});

define('views/geshi',[
    'jquery',
    'underscore',
    'backbone',
    'models/geshi'
], function($, _, Backbone, GeshiModel) {

    

    var GeshiView = Backbone.View.extend({

        plainText: false,
        htmlText: false,

        events: {
            "click .geshi-plain-text": "_togglePlaintext"
        },

        initialize: function() {
            this.model = new GeshiModel();
            this.collection.push(this.model);
            this.block = this.$('.geshi-plain-text').next();

            this._setPlaintextButton();

            this.listenTo(this.model, 'change', this.render);
        },

        _setPlaintextButton: function() {
            if (this.model.get('isPlaintext')) {
                this.$('.geshi-plain-text').html("<i class='icon-list-ol'></i>");
            } else {
                this.$('.geshi-plain-text').html("<i class='icon-reorder'></i>");
            }
        },

        _togglePlaintext: function(event) {
            event.preventDefault();
            this.model.set('isPlaintext', !this.model.get('isPlaintext'));
        },

        _extractPlaintext: function() {
            if (this.plainText !== false) {
                return;
            }
            this.htmlText = this.block.html();
            if (navigator.appName === 'Microsoft Internet Explorer') {
                this.htmlText = this.htmlText.replace(/\n\r/g, "+");
                this.plainText = $(this.htmlText).text().replace(/\+\+/g, "\r");
            } else {
                this.plainText = this.block.text().replace(/code /g, "code \n");
            }
        },

        _renderText: function() {
            if (this.model.get('isPlaintext')) {
                this.block.text(this.plainText).wrapInner("<pre class=\"code\"></pre>");
            } else {
                this.block.html(this.htmlText);
            }
        },

        render: function() {
            this._setPlaintextButton();
            this._extractPlaintext();
            this._renderText();
            return this;
        }


    });

    return GeshiView;

});
define('models/upload',[
    'underscore',
    'backbone',
    'models/app',
    'cakeRest'
], function(_, Backbone, App, cakeRest) {

    

    var UploadModel = Backbone.Model.extend({

        initialize: function() {
            this.webroot = App.settings.get('webroot') + 'uploads/';
        }

    });

    _.extend(UploadModel.prototype, cakeRest);

    return UploadModel;
});

define('collections/uploads',[
    'underscore',
    'backbone',
    'models/upload'
], function(_, Backbone, UploadModel) {
    var UploadsCollection = Backbone.Collection.extend({

        model: UploadModel,

        initialize: function(options) {
           this.url = options.url + 'uploads/index/';
        }
    });

    return UploadsCollection;
});

define('text!templates/upload.html',[],function () { return '<div class="upload_box_delete">\n    <%= linkDelete %>\n</div>\n<div>\n    <div class="upload_box_header">\n        <%= linkImage %>\n    </div>\n</div>\n<div>\n    <div class="l-box-footer box-footer-form upload_box_footer">\n        <%= linkInsert %>\n    </div>\n</div>\n';});

define('views/upload',[
    'jquery',
    'underscore',
    'backbone',
    'models/app',
    'text!templates/upload.html'
], function($, _, Backbone,
            App,
            uploadTpl
    ) {

    

    var UploadView = Backbone.View.extend({

        className: "box-content upload_box current",

        events: {
            "click .upload_box_delete": "_removeUpload",
            "click .btn-submit" : "_insert"
        },

        initialize: function(options) {
            this.textarea = options.textarea;

            this.listenTo(this.model, "destroy", this._uploadRemoved);
        },

        _removeUpload: function(event) {
            event.preventDefault();
            this.model.destroy({
                    success:_.bind(function(model, response) {
                        App.eventBus.trigger(
                            'notification',
                             response
                        );
                    }, this)
                }
            );
        },

        _uploadRemoved: function() {
            this.remove();
        },

        _insert: function(event) {
            event.preventDefault();
            this._insertAtCaret(
                "[upload]" + this.model.get('name') + "[/upload]",
                this.textarea
            );
        },

        _insertAtCaret: function(text, txtarea) {
            //    var scrollPos = txtarea.scrollTop;
            var strPos = 0;
            var br = ((txtarea.selectionStart || txtarea.selectionStart == '0') ?
                "ff" : (document.selection ? "ie" : false ) );
            if (br == "ie") {
                txtarea.focus();
                var range = document.selection.createRange();
                range.moveStart ('character', -txtarea.value.length);
                strPos = range.text.length;
            }
            else if (br == "ff") strPos = txtarea.selectionStart;

            var front = (txtarea.value).substring(0,strPos);
            var back = (txtarea.value).substring(strPos,txtarea.value.length);
            txtarea.value=front+text+back;
            strPos = strPos + text.length;
            if (br == "ie") {
                txtarea.focus();
                var range = document.selection.createRange();
                range.moveStart ('character', -txtarea.value.length);
                range.moveStart ('character', strPos);
                range.moveEnd ('character', 0);
                range.select();
            }
            else if (br == "ff") {
                txtarea.selectionStart = strPos;
                txtarea.selectionEnd = strPos;
                txtarea.focus();
            }
        //    txtarea.scrollTop = scrollPos;

        },

        render: function() {
            this.$el.html(_.template(uploadTpl, this.model.toJSON()));
            return this;
        }

    });

    return UploadView;

});

/*global jQuery:false, alert:false */

/*
 * Default text - jQuery plugin for html5 dragging files from desktop to browser
 *
 * Author: Weixi Yen
 *
 * Email: [Firstname][Lastname]@gmail.com
 *
 * Copyright (c) 2010 Resopollution
 *
 * Licensed under the MIT license:
 *   http://www.opensource.org/licenses/mit-license.php
 *
 * Project home:
 *   http://www.github.com/weixiyen/jquery-filedrop
 *
 * Version:  0.1.0
 *
 * Features:
 *      Allows sending of extra parameters with file.
 *      Works with Firefox 3.6+
 *      Future-compliant with HTML5 spec (will work with Webkit browsers and IE9)
 * Usage:
 *  See README at project homepage
 *
 */
;(function($) {

  jQuery.event.props.push("dataTransfer");

  var default_opts = {
      fallback_id: '',
      url: '',
      refresh: 1000,
      paramname: 'userfile',
      allowedfiletypes:[],
      maxfiles: 25,           // Ignored if queuefiles is set > 0
      maxfilesize: 1,         // MB file size limit
      queuefiles: 0,          // Max files before queueing (for large volume uploads)
      queuewait: 200,         // Queue wait time if full
      data: {},
      headers: {},
      drop: empty,
      dragStart: empty,
      dragEnter: empty,
      dragOver: empty,
      dragLeave: empty,
      docEnter: empty,
      docOver: empty,
      docLeave: empty,
      beforeEach: empty,
      afterAll: empty,
      rename: empty,
      error: function(err, file, i, status) {
        alert(err);
      },
      uploadStarted: empty,
      uploadFinished: empty,
      progressUpdated: empty,
      globalProgressUpdated: empty,
      speedUpdated: empty
      },
      errors = ["BrowserNotSupported", "TooManyFiles", "FileTooLarge", "FileTypeNotAllowed", "NotFound", "NotReadable", "AbortError", "ReadError"],
      doc_leave_timer, stop_loop = false,
      files_count = 0,
      files;

  $.fn.filedrop = function(options) {
    var opts = $.extend({}, default_opts, options),
        global_progress = [];

    this.on('drop', drop).on('dragstart', opts.dragStart).on('dragenter', dragEnter).on('dragover', dragOver).on('dragleave', dragLeave);
    $(document).on('drop', docDrop).on('dragenter', docEnter).on('dragover', docOver).on('dragleave', docLeave);

    $('#' + opts.fallback_id).change(function(e) {
      opts.drop(e);
      files = e.target.files;
      files_count = files.length;
      upload();
    });

    function drop(e) {
      if( opts.drop.call(this, e) === false ) return false;
      files = e.dataTransfer.files;
      if (files === null || files === undefined || files.length === 0) {
        opts.error(errors[0]);
        return false;
      }
      files_count = files.length;
      upload();
      e.preventDefault();
      return false;
    }

    function getBuilder(filename, filedata, mime, boundary) {
      var dashdash = '--',
          crlf = '\r\n',
          builder = '';

      if (opts.data) {
        var params = $.param(opts.data).replace(/\+/g, '%20').split(/&/);

        $.each(params, function() {
          var pair = this.split("=", 2),
              name = decodeURIComponent(pair[0]),
              val  = decodeURIComponent(pair[1]);

          builder += dashdash;
          builder += boundary;
          builder += crlf;
          builder += 'Content-Disposition: form-data; name="' + name + '"';
          builder += crlf;
          builder += crlf;
          builder += val;
          builder += crlf;
        });
      }

      builder += dashdash;
      builder += boundary;
      builder += crlf;
      builder += 'Content-Disposition: form-data; name="' + opts.paramname + '"';
      builder += '; filename="' + filename + '"';
      builder += crlf;

      builder += 'Content-Type: ' + mime;
      builder += crlf;
      builder += crlf;

      builder += filedata;
      builder += crlf;

      builder += dashdash;
      builder += boundary;
      builder += dashdash;
      builder += crlf;
      return builder;
    }

    function progress(e) {
      if (e.lengthComputable) {
        var percentage = Math.round((e.loaded * 100) / e.total);
        if (this.currentProgress !== percentage) {

          this.currentProgress = percentage;
          opts.progressUpdated(this.index, this.file, this.currentProgress);

          global_progress[this.global_progress_index] = this.currentProgress;
          globalProgress();

          var elapsed = new Date().getTime();
          var diffTime = elapsed - this.currentStart;
          if (diffTime >= opts.refresh) {
            var diffData = e.loaded - this.startData;
            var speed = diffData / diffTime; // KB per second
            opts.speedUpdated(this.index, this.file, speed);
            this.startData = e.loaded;
            this.currentStart = elapsed;
          }
        }
      }
    }

    function globalProgress() {
      if (global_progress.length === 0) {
        return;
      }

      var total = 0, index;
      for (index in global_progress) {
        if(global_progress.hasOwnProperty(index)) {
          total = total + global_progress[index];
        }
      }

      opts.globalProgressUpdated(Math.round(total / global_progress.length));
    }

    // Respond to an upload
    function upload() {
      stop_loop = false;

      if (!files) {
        opts.error(errors[0]);
        return false;
      }

      if (opts.allowedfiletypes.push && opts.allowedfiletypes.length) {
        for(var fileIndex = files.length;fileIndex--;) {
          if(!files[fileIndex].type || $.inArray(files[fileIndex].type, opts.allowedfiletypes) < 0) {
            opts.error(errors[3], files[fileIndex]);
            return false;
          }
        }
      }

      var filesDone = 0,
          filesRejected = 0;

      if (files_count > opts.maxfiles && opts.queuefiles === 0) {
        opts.error(errors[1]);
        return false;
      }

      // Define queues to manage upload process
      var workQueue = [];
      var processingQueue = [];
      var doneQueue = [];

      // Add everything to the workQueue
      for (var i = 0; i < files_count; i++) {
        workQueue.push(i);
      }

      // Helper function to enable pause of processing to wait
      // for in process queue to complete
      var pause = function(timeout) {
        setTimeout(process, timeout);
        return;
      };

      // Process an upload, recursive
      var process = function() {

        var fileIndex;

        if (stop_loop) {
          return false;
        }

        // Check to see if are in queue mode
        if (opts.queuefiles > 0 && processingQueue.length >= opts.queuefiles) {
          return pause(opts.queuewait);
        } else {
          // Take first thing off work queue
          fileIndex = workQueue[0];
          workQueue.splice(0, 1);

          // Add to processing queue
          processingQueue.push(fileIndex);
        }

        try {
          if (beforeEach(files[fileIndex]) !== false) {
            if (fileIndex === files_count) {
              return;
            }
            var reader = new FileReader(),
                max_file_size = 1048576 * opts.maxfilesize;

            reader.index = fileIndex;
            if (files[fileIndex].size > max_file_size) {
              opts.error(errors[2], files[fileIndex], fileIndex);
              // Remove from queue
              processingQueue.forEach(function(value, key) {
                if (value === fileIndex) {
                  processingQueue.splice(key, 1);
                }
              });
              filesRejected++;
              return true;
            }

            reader.onerror = function(e) {
                switch(e.target.error.code) {
                    case e.target.error.NOT_FOUND_ERR:
                        opts.error(errors[4]);
                        return false;
                    case e.target.error.NOT_READABLE_ERR:
                        opts.error(errors[5]);
                        return false;
                    case e.target.error.ABORT_ERR:
                        opts.error(errors[6]);
                        return false;
                    default:
                        opts.error(errors[7]);
                        return false;
                };
            };

            reader.onloadend = !opts.beforeSend ? send : function (e) {
              opts.beforeSend(files[fileIndex], fileIndex, function () { send(e); });
            };
            
            reader.readAsBinaryString(files[fileIndex]);

          } else {
            filesRejected++;
          }
        } catch (err) {
          // Remove from queue
          processingQueue.forEach(function(value, key) {
            if (value === fileIndex) {
              processingQueue.splice(key, 1);
            }
          });
          opts.error(errors[0]);
          return false;
        }

        // If we still have work to do,
        if (workQueue.length > 0) {
          process();
        }
      };

      var send = function(e) {

        var fileIndex = ((typeof(e.srcElement) === "undefined") ? e.target : e.srcElement).index;

        // Sometimes the index is not attached to the
        // event object. Find it by size. Hack for sure.
        if (e.target.index === undefined) {
          e.target.index = getIndexBySize(e.total);
        }

        var xhr = new XMLHttpRequest(),
            upload = xhr.upload,
            file = files[e.target.index],
            index = e.target.index,
            start_time = new Date().getTime(),
            boundary = '------multipartformboundary' + (new Date()).getTime(),
            global_progress_index = global_progress.length,
            builder,
            newName = rename(file.name),
            mime = file.type;

        if (opts.withCredentials) {
          xhr.withCredentials = opts.withCredentials;
        }

        if (typeof newName === "string") {
          builder = getBuilder(newName, e.target.result, mime, boundary);
        } else {
          builder = getBuilder(file.name, e.target.result, mime, boundary);
        }

        upload.index = index;
        upload.file = file;
        upload.downloadStartTime = start_time;
        upload.currentStart = start_time;
        upload.currentProgress = 0;
        upload.global_progress_index = global_progress_index;
        upload.startData = 0;
        upload.addEventListener("progress", progress, false);

		// Allow url to be a method
		if (jQuery.isFunction(opts.url)) {
	        xhr.open("POST", opts.url(), true);
	    } else {
	    	xhr.open("POST", opts.url, true);
	    }
	    
        xhr.setRequestHeader('content-type', 'multipart/form-data; boundary=' + boundary);

        // Add headers
        $.each(opts.headers, function(k, v) {
          xhr.setRequestHeader(k, v);
        });

        xhr.sendAsBinary(builder);

        global_progress[global_progress_index] = 0;
        globalProgress();

        opts.uploadStarted(index, file, files_count);

        xhr.onload = function() {
            var serverResponse = null;

            if (xhr.responseText) {
              try {
                serverResponse = jQuery.parseJSON(xhr.responseText);
              }
              catch (e) {
                serverResponse = xhr.responseText;
              }
            }

            var now = new Date().getTime(),
                timeDiff = now - start_time,
                result = opts.uploadFinished(index, file, serverResponse, timeDiff, xhr);
            filesDone++;

            // Remove from processing queue
            processingQueue.forEach(function(value, key) {
              if (value === fileIndex) {
                processingQueue.splice(key, 1);
              }
            });

            // Add to donequeue
            doneQueue.push(fileIndex);

            // Make sure the global progress is updated
            global_progress[global_progress_index] = 100;
            globalProgress();

            if (filesDone === (files_count - filesRejected)) {
              afterAll();
            }
            if (result === false) {
              stop_loop = true;
            }
          

          // Pass any errors to the error option
          if (xhr.status < 200 || xhr.status > 299) {
            opts.error(xhr.statusText, file, fileIndex, xhr.status);
          }
        };
      };

      // Initiate the processing loop
      process();
    }

    function getIndexBySize(size) {
      for (var i = 0; i < files_count; i++) {
        if (files[i].size === size) {
          return i;
        }
      }

      return undefined;
    }

    function rename(name) {
      return opts.rename(name);
    }

    function beforeEach(file) {
      return opts.beforeEach(file);
    }

    function afterAll() {
      return opts.afterAll();
    }

    function dragEnter(e) {
      clearTimeout(doc_leave_timer);
      e.preventDefault();
      opts.dragEnter.call(this, e);
    }

    function dragOver(e) {
      clearTimeout(doc_leave_timer);
      e.preventDefault();
      opts.docOver.call(this, e);
      opts.dragOver.call(this, e);
    }

    function dragLeave(e) {
      clearTimeout(doc_leave_timer);
      opts.dragLeave.call(this, e);
      e.stopPropagation();
    }

    function docDrop(e) {
      e.preventDefault();
      opts.docLeave.call(this, e);
      return false;
    }

    function docEnter(e) {
      clearTimeout(doc_leave_timer);
      e.preventDefault();
      opts.docEnter.call(this, e);
      return false;
    }

    function docOver(e) {
      clearTimeout(doc_leave_timer);
      e.preventDefault();
      opts.docOver.call(this, e);
      return false;
    }

    function docLeave(e) {
      doc_leave_timer = setTimeout((function(_this) {
        return function() {
          opts.docLeave.call(_this, e);
        };
      })(this), 200);
    }

    return this;
  };

  function empty() {}

  try {
    if (XMLHttpRequest.prototype.sendAsBinary) {
        return;
    }
    XMLHttpRequest.prototype.sendAsBinary = function(datastr) {
      function byteValue(x) {
        return x.charCodeAt(0) & 0xff;
      }
      var ords = Array.prototype.map.call(datastr, byteValue);
      var ui8a = new Uint8Array(ords);
      this.send(ui8a.buffer);
    };
  } catch (e) {}

})(jQuery);
define("views/../../dev/vendors/jquery-filedrop/jquery.filedrop", function(){});

define('text!templates/uploadNew.html',[],function () { return '<form action="<%= url %>" method="post" class="dropbox" target="uploadIFrame"\n      enctype="multipart/form-data">\n    <div class="upload_box_header">\n        <div class="upload-layer">\n        </div>\n        <div class="upload-drag-indicator">\n            <i class="icon-upload"></i>\n        </div>\n        <h2> <%- $.i18n.__(\'upload_new_title\') %></h2>\n        <p>\n            <%- $.i18n.__(\'upload_info\', {size: upload_size}) %>\n        </p>\n    </div>\n    <div class="l-box-footer box-footer-form upload_box_footer">\n        <div style="position: relative;">\n            <!--\n                // To present a nice upload button we generate a dead button.\n                // Beneath the nice dummy button is the actual input file upload,\n                // but it\'s hidden behind the opacity:0 curtain div.\n              // z-index: 2000 to have the button above the jQuery UI modal.\n              -->\n            <button class="btn btn-submit" type="button">\n                <%- $.i18n.__("upload_btn") %>\n            </button>\n            <div style="position: absolute; z-index: 2000; top:0; right: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; overflow: hidden; " >\n                <input id="Upload0File"\n                       type="file" name="data[Upload][0][file]"\n                       style="width: 150px; height: 100%"\n                        >\n            </div>\n        </div>\n    </div>\n</form>\n<iframe id="uploadIFrame" name="uploadIFrame" src="about:blank" style="display: none;">\n    <html><head></head><body></body></html>\n</iframe>\n';});

define('text!templates/spinner.html',[],function () { return '<div class="spinner"></div>\n';});

define('views/uploadNew',[
    'jquery',
    'underscore',
    'backbone',
    '../../dev/vendors/jquery-filedrop/jquery.filedrop',
    'models/app',
    'text!templates/uploadNew.html',
    'text!templates/spinner.html',
    'humanize'
], function($, _, Backbone,
            Filedrop,
            App,
            uploadNewTpl, spinnerTpl,
            humanize
    ) {

    

    var UploadNewView = Backbone.View.extend({

        className: "box-content upload_box upload-new",

        wasChild: 'unset',

        events: {
            "change #Upload0File": "_uploadManual"
        },

        initialize: function(options) {
            this.uploadUrl = App.settings.get('webroot') + 'uploads/add';
            this.collection = options.collection;
        },

        _initDropUploader: function() {

            if (this._browserSupportsDragAndDrop() && window.FileReader) {
                this.$('.upload-layer').filedrop({
                    maxfiles: 1,
                    maxfilesize: App.settings.get('upload_max_img_size') / 1024,
                    url: this.uploadUrl,
                    paramname: "data[Upload][0][file]",
                    allowedfiletypes: [
                        'image/jpeg',
                        'image/jpg',
                        'image/png',
                        'image/gif'
                    ],
                    dragOver:_.bind(function(){this._showDragIndicator();}, this),
                    dragLeave:_.bind(function(){this._hideDragIndicator();}, this),
                    uploadFinished: _.bind(
                        function(i, file, response, time) {
                            this._postUpload(response);
                        },
                        this),
                    beforeSend: _.bind(
                        function(file, i, done) {
                            this._hideDragIndicator();
                            this._setUploadSpinner();
                            done();
                        },
                        this),
                    error: _.bind(function(err, file) {
                        var message;

                        this._hideDragIndicator();

                        switch(err) {
                            case 'FileTypeNotAllowed':
                                message = $.i18n.__('upload_fileTypeNotAllowed');
                                break;
                            case 'FileTooLarge':
                                message = $.i18n.__(
                                    'upload_fileToLarge',
                                    {name: file.name}
                                );
                                break;
                            case 'BrowserNotSupported':
                                message = $.i18n.__('upload_browserNotSupported');
                                break;
                            case 'TooManyFiles':
                                message = $.i18n.__('upload_toManyFiles');
                                break;
                            default:
                                message = err;
                                break;
                        }

                        App.eventBus.trigger(
                            'notification',
                            {
                                title: 'Error',
                                message: message,
                                type: 'error'
                            }
                        );
                    }, this)
                });
            } else {
                this.$('h2').html($.i18n.__('Upload'));
            }
        },

        _browserSupportsDragAndDrop: function() {
            var div = this.$('.upload-layer')[0];
            return ('draggable' in div) || ('ondragstart' in div && 'ondrop' in div);
        },

        _showDragIndicator: function() {
            this.$('.upload-drag-indicator').fadeIn();
        },

        _hideDragIndicator: function() {
            this.$('.upload-drag-indicator').fadeOut();
        },

        _setUploadSpinner: function() {
            this.$('.upload_box_header')
                .html(spinnerTpl);
        },

        _uploadManual: function(event) {
            var useAjax = true,
                formData,
                input;

            event.preventDefault();

            try {
                formData = new FormData();
                input = this.$('#Upload0File')[0];
                formData.append(
                    input.name,
                    input.files[0]
                );
            } catch (e) {
                useAjax = false;
            }

            this._setUploadSpinner();

            if (useAjax) {
                this._uploadAjax(formData);
            } else {
                this._uploadIFrame();
            }
        },

        // compatibility for
        // - iCab Mobile custom uploader on iOS
        // - <= IE 9
        _uploadIFrame: function() {
            var form = this.$('form'),
                iframe = this.$('#uploadIFrame');

            iframe.load(_.bind(function(){
                this._postUpload(iframe.contents().find('body').html());
                iframe.off('load');
            }, this));

            form.submit();
        },

        _uploadAjax: function(formData) {
            var xhr = new XMLHttpRequest();
            xhr.open(
                'POST',
                this.uploadUrl
            );
            xhr.onloadend = _.bind(function(request){
                this._postUpload(request.target.response);
            }, this);
            xhr.onerror = this._onUploadError;
            xhr.send(formData);
        },

        _onUploadError: function() {
            App.eventBus.trigger('notification', {
                type: "error",
                message: $.i18n.__("upload_genericError")
            });
        },

        _postUpload: function(data) {
            if (_.isString(data)) {
                try {
                    data = JSON.parse(data);
                } catch (e) {
                    this._onUploadError();
                }
            }
            App.eventBus.trigger('notification', data);
            this.collection.fetch({reset: true});
            this.render();

        },

        render: function() {
            this.$el.html(_.template(uploadNewTpl)({
                url: this.uploadUrl,
                upload_size: humanize
                    .filesize(App.settings.get('upload_max_img_size'))

            }));
            this._initDropUploader();
            return this;
        }
    });

    return UploadNewView;

});

define('text!templates/uploads.html',[],function () { return '<div id="upload_index" class="upload index">\n    <div class="content">\n    </div>\n</div>\n';});

define('views/uploads',[
    'jquery',
    'underscore',
    'backbone',
    'models/app',
    'collections/uploads', 'views/upload',
    'views/uploadNew',
    'text!templates/uploads.html'
], function($, _, Backbone,
            App,
    UploadsCollection, UploadView,
    UploadNewView,
    uploadsTpl
    ) {

    var UploadsView = Backbone.View.extend({

        events: {
            "click .current .btn-submit": "_closeDialog"
        },

        initialize: function(options) {
            this.textarea = options.textarea;

            this.collection = new UploadsCollection({
                url: App.settings.get('webroot')
            });

            this.listenTo(this.collection, "reset", this._addAll);

            this.$('.body').html(_.template(uploadsTpl));

            this.uploadNewView = new UploadNewView({
                collection: this.collection
            });
            this.$('.content').append(this.uploadNewView.el);

            this.render();
            this.collection.fetch({reset: true});
        },

        _addOne: function(upload) {
            var uploadView = new UploadView({
                model: upload,
                textarea: this.textarea
            });
            this.$(".upload-new").after(uploadView.render().el);
        },

        _addAll: function() {
            this._removeAll();
            this.collection.each(this._addOne, this);
        },

        _removeAll: function() {
            this.$('.upload_box.current').remove();
        },

        _setDialogSize: function() {
            this.$el.dialog("option", "width", window.innerWidth - 80 );
            this.$el.dialog("option", "height", window.innerHeight - 80 );
        },

        _closeDialog: function() {
            this.$el.dialog("close");
        },

        render: function() {
            this.uploadNewView.render();
            this.$el.dialog({
                title: $.i18n.__("Upload"),
                modal: true,
                draggable: false,
                resizable: false,
                position: [40, 40],
                hide: 'fade'
            });

            this._setDialogSize();
            $(window).resize(_.bind(function() {
                this._setDialogSize();
            }, this));
            window.onorientationchange = _.bind(function() {
                this._setDialogSize();
            }, this);
            return this;
        }

    });

    return UploadsView;

});

define('lib/saito/markItUp.media',['jquery', 'underscore'], function($, _) {

    

    var dropbox = {
        cleanUp: function(text) {
            // see: https://www.dropbox.com/help/201/en
            text = text.replace(/https:\/\/www\.dropbox\.com\//, 'https://dl.dropbox.com/');
            return text;
        }
    };

    var markItUp = {

        rawUrlCleaner: [dropbox],

        multimedia: function(text, options) {
            var textv = $.trim(text),
                patternEnd = "([\\/?]|$)",

                patternImage = new RegExp("\\.(png|gif|jpg|jpeg|webp)" + patternEnd, "i"),
                patternHtml = new RegExp("\\.(mp4|webm|m4v)" + patternEnd, "i"),
                patternAudio = new RegExp("\\.(m4a|ogg|mp3|wav|opus)" + patternEnd, "i"),
                patternFlash = /<object/i,
                patternIframe = /<iframe/i,

                out = '';

            options = options || {};
            _.defaults(options, { embedlyEnabled: false });

            _.each(this.rawUrlCleaner, function(cleaner) {
                textv = cleaner.cleanUp(textv);
            });

            if (patternImage.test(textv)) {
                out = markItUp._image(textv);
            } else if (patternHtml.test(textv)) {
                out = markItUp._videoHtml5(textv);
            } else if (patternAudio.test(textv)) {
                out = markItUp._audioHtml5(textv);
            } else if (patternIframe.test(textv)) {
                out = markItUp._videoIframe(textv);
            } else if (patternFlash.test(textv)) {
                out = markItUp._videoFlash(textv);
            } else {
                out = markItUp._videoFallback(textv);
            }

            if (options.embedlyEnabled === true && out === '') {
                out = markItUp._embedly(textv);
            }
            return out;
        },

        _image: function(text) {
            return	'[img]' + text + '[/img]';
        },

        _videoFlash: function(text) {
            var html = "[flash_video]URL|WIDTH|HEIGHT[/flash_video]";

            if (text !== null) {
                html = html.replace('WIDTH', /width="(\d+)"/.exec(text)[1]);
                html = html.replace('HEIGHT', /height="(\d+)"/.exec(text)[1]);
                html = html.replace('URL', /src="([^"]+)"/.exec(text)[1]);
                return html;
            }
            else {
                return '';
            }
        },

        _videoHtml5: function(text) {
            return	'[video]' + text + '[/video]';
        },

        _audioHtml5: function(text) {
            return	'[audio]' + text + '[/audio]';
        },

        _videoIframe: function(text) {
            var inner = /<iframe(.*?)>.*?<\/iframe>/i.exec(text)[1];
            inner = inner.replace(/["']/g, '');
            return '[iframe' + inner + '][/iframe]';
        },

        _videoFallback: function(text) {
            var out = '',
                videoId;

            // manually detect popular video services
            if ( /http/.test(text) === false ) {
                text = 'http://' + text;
            }
            if (/(http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/i.test(text)) {
                var domain = text.match(/(https?:\/\/)?(www\.)?(.[^\/:]+)/i).pop();
                // youtube shortener url
                if (domain === 'youtu.be') {
                    if (/youtu.be\/(.*?)(&.*)?$/.test(text)) {
                        videoId = text.match(/youtu.be\/(.*?)(&.*)?$/)[1];
                        out = markItUp._createIframe({
                            url: '//www.youtube.com/embed/' + videoId
                        });
                        out = markItUp._videoIframe(out);
                        return out;
                    }
                }
                // youtube url from browser bar
                if (domain === 'youtube.com') {
                    if (/v=(.*?)(&.*)?$/.test(text)) {
                        videoId = text.match(/v=(.*?)(&.*)?$/)[1];
                        out = markItUp._createIframe({
                            url: '//www.youtube.com/embed/' + videoId
                        });
                        out = markItUp._videoIframe(out);
                    }
                    return out;
                }
            }
            return out;
        },

        _embedly: function(text) {
            return '[embed]' + text + '[/embed]';
        },

        _createIframe: function(args) {
            return '<iframe src="' + args.url + '" width="425" height="349" frameborder="0" allowfullscreen></iframe>';
        }

    };

    return markItUp;

});

define('text!templates/mediaInsert.html',[],function () { return '<form action="#" style="width: 100%;" id="addForm" method="post" accept-charset="utf-8">\n    <label for="markitup_media_txta" class="c_markitup_label">\n        <%- $.i18n.__(\'Enter link to media or embedding code:\') %>\n    </label>\n    <textarea name="data[media]" id="markitup_media_txta" class="c_markitup_popup_txta" rows="6" columns="20">\n    </textarea>\n    <div class="clearfix"></div>\n    <br/>\n    <div class="submit">\n        <input style="float: right;" class="btn btn-submit"\n               id="markitup_media_btn" type="submit"\n               value="<%- $.i18n.__(\'Insert\') %>"/>\n    </div>\n    <div class="clearfix"></div>\n    <br/>\n    <div id="markitup_media_message" class="flash error" style="display: none;">\n        <%- $.i18n.__(\'Nothing recognized.\') %>\n    </div>\n</form>\n';});

define('views/mediaInsert',[
    'jquery',
    'underscore',
    'backbone',
    'models/app',
    'lib/saito/markItUp.media',
    'text!templates/mediaInsert.html'
], function($, _, Backbone, App, MarkItUpMedia, mediaInsertTpl) {

    

    return Backbone.View.extend({

        template:_.template(mediaInsertTpl),

        events: {
            "click #markitup_media_btn": "_insert"
        },

        initialize: function() {
            if (this.model !== undefined && this.model !== null) {
                this.listenTo(this.model, 'change:isAnsweringFormShown', this.remove);
            }
        },

        _insert: function(event) {
            var out,
                markItUpMedia;

            event.preventDefault();

            markItUpMedia = MarkItUpMedia;
            out = markItUpMedia.multimedia(
                this.$('#markitup_media_txta').val(),
                {embedlyEnabled: App.settings.get('embedly_enabled') === true}
            );

            if (out === '') {
                this._invalidInput();
            } else {
                $.markItUp({replaceWith: out});
                this._closeDialog();
            }
        },

        _hideErrorMessages: function() {
            this.$('#markitup_media_message').hide();
        },

        _invalidInput: function() {
            this.$('#markitup_media_message').show();
            this.$el
                .dialog()
                .parent()
                .effect("shake", {times: 2}, 250);
        },

        _closeDialog: function() {
            this.$el.dialog('close');
            this._hideErrorMessages();
            this.$('#markitup_media_txta').val('');
        },

        _showDialog: function() {
            this.$el.dialog({
                show: {effect: "scale", duration: 200},
                hide: {effect: "fade", duration: 200},
                title: $.i18n.__("Multimedia"),
                resizable: false,
                open: function() {
                    setTimeout(function() {$('#markitup_media_txta').focus();}, 210);
                },
                close: _.bind(function() {
                    this._hideErrorMessages();
                }, this)
            });
        },

        render: function() {
            this.$el.html(this.template);
            this._showDialog();
            return this;
        }

    });

});

define('models/preview',[
'underscore',
'backbone',
'models/app'
], function(_, Backbone, App) {

        

		var PreviewModel = Backbone.Model.extend({

            defaults: {
                rendered: "",
                data: "",
                fetchingData: 0
            },

            initialize: function() {
                this.webroot = App.settings.get('webroot');

                this.listenTo(this, 'change:data', this._fetchRendered);
            },

            _fetchRendered: function() {
                this.set('fetchingData', 1);
                $.post(
                    this.webroot + 'entries/preview/',
                    this.get('data'),
                    _.bind(function(data) {
                        this.set('fetchingData', 0);
                        this.set('rendered', data.html);
                        App.eventBus.trigger('notificationUnset', 'all');
                        App.eventBus.trigger(
                            'notification',
                            data
                        );
                    }, this),
                    'json'
                );
            }

		});

		return PreviewModel;
	});
define('views/preview',[
    'jquery',
    'underscore',
    'backbone',
    'text!templates/spinner.html'
], function ($, _, Backbone, spinnerTpl) {

    

    var PreviewView = Backbone.View.extend({

        initialize: function () {
            this.render();

            this.listenTo(this.model, "change:fetchingData", this._spinner);
            this.listenTo(this.model, "change:rendered", this.render);
        },

        _spinner: function (model) {
            if (model.get('fetchingData')) {
                this.$el.html(spinnerTpl);
            } else {
                this.$el.html('');
            }
        },

        render: function () {
            var rendered;
            rendered =  this.model.get('rendered');
            if (!rendered) {
                rendered = '';
            }
            this.$el.html(rendered);
            return this;
        }

    });

    return PreviewView;

});

(function($) {

    

    var helpers = {

        _scrollToTop: function(elem) {
            $('body').animate(
                {
                    scrollTop: elem.offset().top - 10,
                    easing: "swing"
                },
                300
            );
        },

        _scrollToBottom: function(elem) {

            $('body').animate(
                {
                    scrollTop: helpers._elementBottom(elem) - $(window).height() + 20,
                    easing: "swing"
                },
                300,
                function() {
                    if (helpers._isHeigherThanView(elem)) {
                        helpers._scrollToTop(elem);
                    }
                }
            );
        },

        _elementBottom: function(elem) {
            return elem.offset().top + elem.height();
        },

        /**
         * Checks if an element is completely visible in current browser window
         *
         * @param elem
         * @return {Boolean}
         * @private
         */
        _isScrolledIntoView: function(elem) {
            if ($(elem).length === 0) {
                return true;
            }
            var docViewTop = $(window).scrollTop();
            var docViewBottom = docViewTop + $(window).height();

            var elemTop = $(elem).offset().top;
            var elemBottom = helpers._elementBottom(elem);

            return ((elemBottom <= docViewBottom) && (elemTop >= docViewTop));
        },

        /**
         * Checks if an element is heigher than the current browser viewport
         *
         * @param elem
         * @return {Boolean}
         * @private
         */
        _isHeigherThanView: function(elem) {
            return ($(window).height() <= elem.height())	;
        }

    };

    var methods = {

        top: function() {
            var elem;
            elem = $(this);

            if (!helpers._isScrolledIntoView(elem)) {
                helpers._scrollToTop(elem);
            }

            return this;
        },

        bottom: function() {
            var elem;
            elem = $(this);

            if (!helpers._isScrolledIntoView(elem)) {
                helpers._scrollToBottom(elem);
            }

            return this;
        },

        isInView: function() {
            var elem;
            elem = $(this);

            return helpers._isScrolledIntoView(elem);
        }

    };

    $.fn.scrollIntoView = function(method) {

        if ( methods[method] ) {
            return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));
        } else if ( typeof method === 'object' || ! method ) {
            return methods.init.apply( this, arguments );
        } else {
            $.error( 'Method ' +  method + ' does not exist on jQuery.scrollIntoView' );
        }

    };

})(jQuery);
define("lib/saito/jquery.scrollIntoView", function(){});

define('views/answering',[
    'jquery',
    'underscore',
    'backbone',
    'models/app',
    'views/uploads', 'views/mediaInsert',
    'models/preview', 'views/preview',
    'lib/saito/jquery.scrollIntoView'
], function($, _, Backbone,
            App,
            UploadsView, MediaInsertView,
            PreviewModel, PreviewView
    ) {

    

    var AnsweringView = Backbone.View.extend({

        rendered: false,
        answeringForm: false,
        preview: false,
        mediaView: false,
        sendInProgress: false,

        /**
         * same model as the parent PostingView
         */
        model: null,

        events: {
            "click .btn-previewClose": "_closePreview",
            "click .btn-preview": "_showPreview",
            "click .btn-markItUp-Upload": "_upload",
            "click .btn-markItUp-Media": "_media",
            "click .btn-submit": "_send",
            "click .btn-cite": "_cite",
            "keypress .inp-subject": "_onKeyPressSubject"
        },

        initialize: function(options) {
            this.parentThreadline = options.parentThreadline || null;

            if (!this.parentThreadline) {
                //* view came directly from server and is ready without rendering
                this._setupTextArea();
            }

            // focus can only be set after element is visible in page
            this.listenTo(App.eventBus, "isAppVisible", this._focusSubject);

            // auto-open upload view for easy developing
            // this._upload(new Event({}));
        },

        _cite: function(event) {
            event.preventDefault();
            var citeContainer = this.$('.cite-container'),
                citeText = this.$('.btn-cite').data('text'),
                currentText = this.$textarea.val();

            this.$textarea.val(citeText + "\n\n" + currentText);
            citeContainer.slideToggle();
            this.$textarea.focus();
        },

        _onKeyPressSubject: function(event) {
            if (event.keyCode === 13) {
                this._send(event);
            }
        },

        _upload: function(event) {
            var uploadsView;
            event.preventDefault();
            uploadsView = new UploadsView({
                el: '#markitup_upload',
                textarea: this.$textarea[0]
            });
        },

        _media: function(event) {
            event.preventDefault();

            if(this.mediaView === false) {
                this.mediaView = new MediaInsertView({
                    el: '#markitup_media',
                    model: this.model
                });
            }
            this.mediaView.render();
        },

        _showPreview: function(event) {
            var previewModel;
            event.preventDefault();
            this.$('.preview').slideDown('fast');
            if (this.preview === false) {
                previewModel = new PreviewModel();
                this.preview = new PreviewView({
                    el: this.$('.preview .content'),
                    model: previewModel
                });
            }
            this.preview.model.set('data', this.$('form').serialize());
        },

        _closePreview: function(event) {
            event.preventDefault();
            this.$('.preview').slideUp('fast');
        },

        _setupTextArea: function() {
            this.$textarea = $('textarea#EntryText');
        },

        _requestAnsweringForm: function() {
            $.ajax({
                url: App.settings.get('webroot') + 'entries/add/' + this.model.get('id'),
                success: _.bind(function(data){
                    this.answeringForm = data;
                    this.render();
                }, this)
            });
        },

        _postRendering: function() {
            this.$el.scrollIntoView('bottom');
            this._focusSubject();
        },

        _focusSubject: function() {
            this.$('.postingform input[type=text]:first').focus();
        },

        _send: function(event) {
            if (this.sendInProgress) {
                event.preventDefault();
                return;
            }
            this.sendInProgress = true;
            if (this.parentThreadline) {
                this._sendInline(event);
            } else {
                this._sendRedirect(event);
            }
        },

        _sendRedirect: function(event) {
            var button = this.$('.btn-submit')[0];
            event.preventDefault();
            if (typeof button.validity === 'object' &&
                button.form.checkValidity() === false) {
                // we can't trigger JS validation messages via form.submit()
                // so we create and click this hidden dummy submit button
                var submit = _.bind(function() {
                    if (!this.checkValidityDummy) {
                        this.checkValidityDummy = $('<button></button>', {
                            type: 'submit',
                            style: 'display: none;'
                        });
                        $(button).after(this.checkValidityDummy);
                    }
                    this.checkValidityDummy.click();
                }, this);

                submit();
                this.sendInProgress = false;
            } else {
                button.disabled = true;
                button.form.submit();
            }
        },

        _sendInline: function(event) {
            event.preventDefault();
            $.ajax({
                url: App.settings.get('webroot') + "entries/add",
                type: "POST",
                dataType: 'json',
                data: this.$("#EntryAddForm").serialize(),
                beforeSend:_.bind(function() {
                    this.$('.btn.btn-submit').attr('disabled', 'disabled');
                }, this),
                success:_.bind(function(data) {
                    this.model.set({isAnsweringFormShown: false});
                    if(this.parentThreadline !== null) {
                        this.parentThreadline.set('isInlineOpened', false);
                    }
                    App.eventBus.trigger('newEntry', {
                        tid: data.tid,
                        pid: this.model.get('id'),
                        id: data.id
                    });
                }, this)
            });
        },

        render: function() {
            if (this.answeringForm === false) {
                this._requestAnsweringForm();
            } else if (this.rendered === false) {
                this.rendered = true;
                this.$el.html(this.answeringForm);
                this._setupTextArea();
                _.defer(function(caller) {
                    caller._postRendering();
                }, this);
            }
            return this;
        }

    });

    return AnsweringView;

});

define('views/postings',[
	'jquery',
	'underscore',
	'backbone',
    'models/app',
    'collections/geshis', 'views/geshi',
    'views/answering',
    'text!templates/spinner.html'
	], function(
        $, _, Backbone,
        App,
        GeshisCollection, GeshiView,
        AnsweringView,
        spinnerTpl
    ) {

        

		var PostingView = Backbone.View.extend({

			className: 'js-entry-view-core',
            answeringForm: false,

            events: {
                "click .js-btn-setAnsweringForm": "setAnsweringForm",
                "click .btn-answeringClose": "setAnsweringForm"
            },

			initialize: function(options) {
                this.collection = options.collection;
                this.parentThreadline = options.parentThreadline || null;

				this.listenTo(this.model, 'change:isAnsweringFormShown', this.toggleAnsweringForm);
                this.listenTo(this.model, 'change:html', this.render);

                // init geshi for entries/view when $el is already there
                this.initGeshi('.c_bbc_code-wrapper');
			},

            initGeshi: function(element_n) {
                var geshi_elements;

                geshi_elements = this.$(element_n);

                if (geshi_elements.length > 0) {
                    var geshis = new GeshisCollection();
                    geshi_elements.each(function(key, element) {
                        new GeshiView({
                            el: element,
                            collection: geshis
                        });
                    });
                }
            },

            setAnsweringForm: function(event) {
                event.preventDefault();
                this.model.toggle('isAnsweringFormShown');
            },

			toggleAnsweringForm: function() {
				if (this.model.get('isAnsweringFormShown')) {
					this._hideAllAnsweringForms();
					this._hideSignature();
					this._showAnsweringForm();
					this._hideBoxActions();
				} else {
					this._showBoxActions();
					this._hideAnsweringForm();
					this._showSignature();
				}
			},

            _showAnsweringForm: function() {
                App.eventBus.trigger('breakAutoreload');
                if (this.answeringForm === false) {
                    this.$('.posting_formular_slider').html(spinnerTpl);
                }
                this.$('.posting_formular_slider').slideDown('fast');
                if (this.answeringForm === false){
                    this.answeringForm = new AnsweringView({
                        el: this.$('.posting_formular_slider'),
                        model: this.model,
                        parentThreadline: this.parentThreadline
                    });
                }
                this.answeringForm.render();
            },

			_hideAnsweringForm: function() {
                var parent;
				$(this.el).find('.posting_formular_slider').slideUp('fast');

                // @td @bogus
                parent = $(this.el).find('.posting_formular_slider').parent();
                // @td @bogus inline answer
                if (this.answeringForm !== false) {
                    this.answeringForm.remove();
                    this.answeringForm.undelegateEvents();
                    this.answeringForm = false;
                }
                parent.append('<div class="posting_formular_slider"></div>');
			},

			_hideAllAnsweringForms: function() {
				// we have #id problems with more than one markItUp on a page
				this.collection.forEach(function(posting){
					if(posting.get('id') !== this.model.get('id')) {
						posting.set('isAnsweringFormShown', false);
					}
				}, this);
			},

			_showSignature: function() {
				$(this.el).find('.signature').slideDown('fast');
			},
			_hideSignature: function() {
				$(this.el).find('.signature').slideUp('fast');
			},

			_showBoxActions: function() {
				$(this.el).find('.l-box-footer').slideDown('fast');
			},
			_hideBoxActions: function() {
				$(this.el).find('.l-box-footer').slideUp('fast');
			},

            render: function() {
                this.$el.html(this.model.get('html'));
                // init geshi for entries opened inline
                this.initGeshi('.c_bbc_code-wrapper');
                return this;
            }

		});

		return PostingView;

	});
define('models/posting',[
    'underscore',
    'backbone',
    'models/app'
], function(_, Backbone, App) {

    

    var PostingModel = Backbone.Model.extend({

        defaults: {
            isAnsweringFormShown: false,
            html: ''
        },

        fetchHtml: function() {
            $.ajax({
                success: _.bind(function(data) {
                    this.set('html', data);
                }, this),
                type: "post",
                async: false,
                dateType: "html",
                url: App.settings.get('webroot') + 'entries/view/' + this.get('id')
            });
        }

    });

    return PostingModel;
});
define('views/threadlines',[
	'jquery',
	'underscore',
	'backbone',
    'models/app',
    'models/threadline',
	'views/threadline-spinner',
    'text!templates/threadline-spinner.html',
    'views/postings', 'models/posting',
    'lib/saito/jquery.scrollIntoView'
	], function($, _, Backbone, App, ThreadLineModel, ThreadlineSpinnerView,
                threadlineSpinnerTpl, PostingView, PostingModel) {

        

		var ThreadLineView = Backbone.View.extend({

			className: 'js-thread_line',
            tagName: 'li',

			spinnerTpl: _.template(threadlineSpinnerTpl),

            /**
             * Posting collection
             */
            postings: null,

			events: {
					'click .btn_show_thread': 'toggleInlineOpen',
					'click .link_show_thread': 'toggleInlineOpenFromLink'

					// is bound manualy after dom insert  in _toggleInlineOpened
					// to hightlight the correct click target in iOS
					// 'click .btn-strip-top': 'toggleInlineOpen'
			},

			initialize: function(options){
                this.postings = options.postings;

                this.model = new ThreadLineModel({id: options.id});
                if(options.el === undefined) {
                    this.model.fetch();
                } else {
                    this.model.set({html: this.el}, {silent: true});
                }
                this.collection.add(this.model, {silent: true});
                this.attributes = {'data-id': options.id};

				this.listenTo(this.model, 'change:isInlineOpened', this._toggleInlineOpened);
                this.listenTo(this.model, 'change:html', this.render);
			},

			toggleInlineOpenFromLink: function(event) {
				if (this.model.get('isAlwaysShownInline')) {
					this.toggleInlineOpen(event);
				}
			},

			/**
             * shows and hides the element that contains an inline posting
             */
			toggleInlineOpen: function(event) {
				event.preventDefault();
				if (!this.model.get('isInlineOpened')) {
					this.model.set({
						isInlineOpened: true
					});
				} else {
					this.model.set({
						isInlineOpened: false
					});
				}
			},

			_toggleInlineOpened: function(model, isInlineOpened) {
				if(isInlineOpened) {
					var id = this.model.id;

					if (!this.model.get('isContentLoaded')) {
						this.tlsV = new ThreadlineSpinnerView({
							el: this.$el.find('.thread_line-pre i')
						});
						this.tlsV.show();

						this.$el.find('.js-thread_line-content').after(this.spinnerTpl({
							id: id
						}));
                        // @bogus, why no listenTo?
						this.$el.find('.js-btn-strip').on('click', _.bind(this.toggleInlineOpen, this))	;

                        this._insertContent();
					} else {
						this._showInlineView();
					}
				} else {
					this._closeInlineView();
				}
			},

            _insertContent: function() {
                var id,
                    postingView;
                id = this.model.get('id');

                this.postingModel = new PostingModel({
                    id: id
                });
                this.postings.add(this.postingModel);

                postingView = new PostingView({
                    el: this.$('.t_s'),
                    model: this.postingModel,
                    collection: this.postings,
                    parentThreadline: this.model
                });

                this.postingModel.fetchHtml();

                this.model.set('isContentLoaded', true);
                this._showInlineView();
            },

			_showInlineView: function () {
                var postShow = _.bind(function() {
                    var shouldScrollOnInlineOpen = this.model.get('shouldScrollOnInlineOpen');
                    this.tlsV.hide();

                    if (shouldScrollOnInlineOpen) {
                        if (this.$el.scrollIntoView('isInView') === false) {
                            this.$el.scrollIntoView('bottom');
                        }
                    } else {
                        this.model.set('shouldScrollOnInlineOpen', true);
                    }
                }, this);

                this.$el.find('.js-thread_line-content').fadeOut(
                    100,
                    _.bind(
                        function() {
                            // performance: show() instead slide()
                            // this.$('.js-thread_inline.' + id).slideDown(0,
                            this.$('.js-thread_inline').show(0, postShow);
                        }, this)
                );
            },

			_closeInlineView: function() {
				// $('.js-thread_inline.' + id).slideUp('fast',
				this.$('.js-thread_inline').hide(0,
					_.bind(
						function() {
							this.$el.find('.js-thread_line-content').slideDown();
                            this._scrollLineIntoView();
						},
						this
					)
				);
			},

			/**
             * if the line is not in the browser windows at the moment
             * scroll to that line and highlight it
             */
			_scrollLineIntoView: function () {
                var thread_line = this.$('.js-thread_line-content');
                if (!thread_line.scrollIntoView('isInView')) {
                    thread_line.scrollIntoView('top')
                        .effect(
                            "highlight",
                            {
                                times: 1
                            },
                            3000);
                }
			},

            render: function() {
                var $oldEl,
                    newHtml,
                    $newEl;

                newHtml =  this.model.get('html');
                if (newHtml.length > 0) {
                    $oldEl = this.$el;
                    $newEl = $(this.model.get('html'));
                    this.setElement($newEl);
                    $oldEl.replaceWith($newEl);
                }
                return this;
            }
        });

		return ThreadLineView;

	});
define('models/thread',[
    'underscore',
    'backbone',
    'collections/threadlines'
], function(_, Backbone, ThreadLinesCollection) {

    

    var ThreadModel = Backbone.Model.extend({

        defaults: {
            isThreadCollapsed: false
        },

        initialize: function() {
            this.threadlines = new ThreadLinesCollection();
        }

    });
    return ThreadModel;
});
define('collections/threads',[
	'underscore',
	'backbone',
	'backboneLocalStorage',
	'models/thread'
	], function(_, Backbone, Store, ThreadModel) {
		var ThreadCollection = Backbone.Collection.extend({
			model: ThreadModel,
			localStorage: new Store('Threads')
		})
		return ThreadCollection;
	});
define('views/thread',[
	'jquery',
	'underscore',
	'backbone',
    'models/app',
    'collections/threadlines', 'views/threadlines'
	], function($, _, Backbone, App, ThreadLinesCollection, ThreadLineView) {

        

		var ThreadView = Backbone.View.extend({

			className: 'thread_box',

			events: {
				"click .btn-threadCollapse":  "collapseThread",
				"click .js-btn-openAllThreadlines": "openAllThreadlines",
				"click .js-btn-closeAllThreadlines": "closeAllThreadlines",
				"click .js-btn-showAllNewThreadlines": "showAllNewThreadlines"
			},

			initialize: function(options){
                this.postings = options.postings;

                this.$rootUl = this.$('ul.root');
                this.$subThreadRootIl = $(this.$rootUl.find('li:not(:first-child)')[0]);

                if (this.model.get('isThreadCollapsed')) {
                    this.hide();
                } else {
                    this.show();
                }

                this.listenTo(App.eventBus, 'newEntry', this._showNewThreadLine);
                this.listenTo(this.model, 'change:isThreadCollapsed', this.toggleCollapseThread);
			},

            _showNewThreadLine: function(options) {
                var threadLine;
                // only append to the id it belongs to
                if (options.tid !== this.model.get('id')) { return; }
                threadLine = new ThreadLineView({
                    id: options.id,
                    collection: this.model.threadlines,
                    postings: this.postings
                });
                this._appendThreadlineToThread(options.pid,threadLine.render().$el);
            },

            _appendThreadlineToThread: function(pid, $el) {
                var parent,
                    existingSubthread;
                parent = this.$('.js-thread_line[data-id="' + pid +'"]');
                existingSubthread = (parent.next().not('.js_threadline').find('ul:first'));
                if (existingSubthread.length === 0) {
                    $el.wrap("<ul></ul>").parent().wrap("<li></li>").parent().insertAfter(parent);
                } else {
                    existingSubthread.append($el);
                }
            },

			/**
			 * Opens all threadlines
			 */
			openAllThreadlines: function(event) {
				event.preventDefault();
				_.each(
					this.model.threadlines.where({
						isInlineOpened: false
					}), function(model) {
						model.set({
							isInlineOpened: true,
                            shouldScrollOnInlineOpen: false
						});
					}, this);

			},

			/**
			 * Closes all threadlines
			 */
			closeAllThreadlines: function(event) {
				if(event) {
					event.preventDefault();
				}
				_.each(
					this.model.threadlines.where({
						isInlineOpened: true
                    }), function(model) {
                        model.set({
                            isInlineOpened: false
                        });
                    }, this);
			},

			/**
			 * Toggles all threads marked as unread/new in a thread tree
			 */
			showAllNewThreadlines: function(event) {
				event.preventDefault();
				_.each(
					this.model.threadlines.where({
						isInlineOpened: false,
						isNewToUser: true
					}), function(model) {
                        model.set({
                            isInlineOpened: true,
                            shouldScrollOnInlineOpen: false
                        });
                    }, this);
			},

			collapseThread: function(event) {
				event.preventDefault();
				this.closeAllThreadlines();
				this.model.toggle('isThreadCollapsed');
				this.model.save();
			},

			toggleCollapseThread: function(model, isThreadCollapsed) {
				if(isThreadCollapsed) {
					this.slideUp();
				} else {
					this.slideDown();
				}
			},

			slideUp: function() {
				this.$subThreadRootIl.slideUp(300);
				this.markHidden();
			},

			slideDown: function() {
                this.$subThreadRootIl.slideDown(300);
				this.markShown();
//				$(this.el).find('.ico-threadOpen').removeClass('ico-threadOpen').addClass('ico-threadCollapse');
//				$(this.el).find('.btn-threadCollapse').html(this.l18n_threadCollapse);
			},

			hide: function() {
				this.$subThreadRootIl.hide();
				this.markHidden();
			},

			show: function() {
				this.$subThreadRootIl.show();
				this.markShown();
			},

			markShown: function() {
				$(this.el).find('.icon-thread-closed').removeClass('icon-thread-closed').addClass('icon-thread-open');
			},

			markHidden: function() {
				$(this.el).find('.icon-thread-open').removeClass('icon-thread-open').addClass('icon-thread-closed');
				// this.l18n_threadCollapse = $(this.el).find('.btn-threadCollapse').html();
				// $(this.el).find('.btn-threadCollapse').prepend('&bull;');
			}

		});

		return ThreadView;

	});
define('collections/postings',[
	'underscore',
	'backbone',
	'models/posting'
	], function(_, Backbone, PostingModel) {
		var PostingCollection = Backbone.Collection.extend({
			model: PostingModel
		});
		return PostingCollection;
	});
define('models/bookmark',[
    'underscore',
    'backbone',
    'models/app',
    'cakeRest'
], function (_, Backbone, App, cakeRest) {

    

    var BookmarkModel = Backbone.Model.extend({

        initialize: function () {
            this.webroot = App.settings.get('webroot') + 'bookmarks/';
        }

    });

    _.extend(BookmarkModel.prototype, cakeRest);

    return BookmarkModel;
});
define('collections/bookmarks',[
    'underscore',
    'backbone',
    'models/bookmark'
], function(_, Backbone, BookmarkModel) {
    var BookmarkCollection = Backbone.Collection.extend({
        model: BookmarkModel
    });
    return BookmarkCollection;
});

define('views/bookmark',[
    'jquery',
    'underscore',
    'backbone'
], function($, _, Backbone) {

    

    var BookmarkView = Backbone.View.extend({

        events: {
            'click .btn-bookmark-delete': 'deleteBookmark'
        },

        initialize: function() {
            _.bindAll(this, 'render');
            this.model.on('destroy', this.removeBookmark, this);
        },

        deleteBookmark: function(event) {
            event.preventDefault();
            this.model.destroy();
        },

        removeBookmark: function() {
            this.$el.hide("slide", null, 500, function(){ $(this).remove();});
        }

    });

    return BookmarkView;
});

define('views/bookmarks',[
    'jquery',
    'underscore',
    'backbone',
    'views/bookmark'
], function($, _, Backbone, BookmarkView) {

    

    var BookmarksView = Backbone.View.extend({

        initialize: function() {
            this.initCollectionFromDom('.js-bookmark', this.collection, BookmarkView);
        }

    });
    return BookmarksView;
});

define('views/helps',[
    'jquery',
    'underscore',
    'backbone'
], function($, _, Backbone) {

    

    var HelpsView = Backbone.View.extend({

        isHelpShown: false,

        events: function() {
            var out = {};
            out["click " + this.indicatorName] = "toggle";
            return out;
        },

        initialize: function(options) {
            this.indicatorName = options.indicatorName;
            this.elementName = options.elementName;

            this.activateHelpButton();
            this.placeHelp();
        },

        activateHelpButton: function() {
            if(this.isHelpOnPage()) {
                $(this.indicatorName).removeClass('no-color');
            }
        },

        placeHelp: function() {
            var defaults = {
                trigger: 'manual',
                html: true
            };
            var positions = ['bottom', 'right', 'left'];
            for (var i in positions) {
                $(this.elementName + '-' + positions[i]).popover(
                    $.extend(defaults, {placement: positions[i]})
                );
            }

            $(this.indicatorName).popover({
                placement:  'left',
                trigger:    'manual'
            });
        },

        isHelpOnPage: function() {
            return this.$el.find(this.elementName).length > 0;
        },

        toggle: function() {
            event.preventDefault();

            if (this.isHelpShown) {
                this.hide();
            } else {
                this.show();
            }
        },


        show: function() {
            this.isHelpShown = true;
            if(this.isHelpOnPage()) {
                $(this.elementName).popover('show');
            } else {
                $(this.indicatorName).popover('show');
            }
        },

        hide: function () {
            this.isHelpShown = false;
            $(this.elementName).popover('hide');
            $(this.indicatorName).popover('hide');
        }
    });

    return HelpsView;

});

define('views/categoryChooser',[
    'jquery',
    'underscore',
    'backbone'
], function($, _, Backbone) {

    

    return Backbone.View.extend({

        initialize: function() {
            this.$el.dialog({
                autoOpen: false,
                show: {effect: "scale", duration: 200},
                hide: {effect: "fade", duration: 200},
                width: 400,
                position: [$('#btn-category-chooser').offset().left + $('#btn-category-chooser').width() - $(window).scrollLeft() - 410, $('#btn-category-chooser').offset().top - $(window).scrollTop() + $('#btn-category-chooser').height()],
                title: $.i18n.__('Categories'),
                resizable: false
            });
        },

        toggle: function() {
            if (this.$el.dialog("isOpen")) {
                this.$el.dialog('close');
            } else {
                this.$el.dialog('open');
            }
        }


    });

});


define('models/slidetab',[
  'underscore',
  'backbone',
  'app/vent',
  'models/app'
], function(_, Backbone, EventBus, App) {

  

  var SlidetabModel = Backbone.Model.extend({

    defaults: {
      isOpen: false
    },

    initialize: function() {
      this.webroot = App.settings.get('webroot');
      this.listenTo(this, 'change:isOpen', this.onChangeIsOpen);
    },

    onChangeIsOpen: function() {
      EventBus.vent.trigger('slidetab:open', {
            slidetab: this.get('id'),
            open: this.get('isOpen')
          }
      );
    },

    sync: function() {
      $.ajax({
        url: this.webroot + "users/ajax_toggle/show_" + this.get('id')
      });
    }

  });

  return SlidetabModel;

});
define('collections/slidetabs',[
  'underscore',
  'backbone',
  'models/app',
  'models/slidetab'
], function(_, Backbone, App, SlidetabModel) {

  

  var SlidetabCollection = Backbone.Collection.extend({

    model: SlidetabModel,

    initialize: function() {
      App.reqres.setHandler('slidetab:open', _.bind(this.isOpen, this));
    },

    // returns if particular slidetab is open or not
    isOpen: function(id) {
      return this.get(id).get('isOpen');
    }

  });

  return SlidetabCollection;

});

define('views/slidetab',[
    'jquery',
    'underscore',
    'backbone'
], function($, _, Backbone, ShoutModel, ShoutsView) {

    

    var SlidetabView = Backbone.View.extend({

        events: {
            "click .slidetab-tab": "clickSlidetab"
        },

        initialize: function(options) {
            this.collection = options.collection;
            this.model.set({isOpen: this.isOpen()}, {silent: true});

            this.listenTo(this.model, 'change', this.toggleSlidetab);
        },

        isOpen: function() {
            return this.$el.find(".slidetab-content").is(":visible");
        },

        clickSlidetab: function(model) {
            this.model.save('isOpen', !this.model.get('isOpen'));
        },

        toggleSlidetab: function() {
            if (this.model.get('isOpen')) {
                this.show();
            } else {
                this.hide();
            }
            this.toggleSlidetabTabInfo();
        },

        show: function() {
            this.$el.animate({
                'width': 250
            });
            this.$el.find('.slidetab-content').css('display','block');
        },

        hide: function() {
            this.$el.animate(
                {
                    'width': 28
                },
                _.bind(function() {
                    this.$el.find('.slidetab-content').css('display', 'none');
                }, this)
            );
        },

        toggleSlidetabTabInfo: function() {
            this.$el.find('.slidetab-tab-info').toggle();
        }

    });

    return SlidetabView;

});

define('views/slidetabs',[
    'jquery',
    'underscore',
    'backbone',
    'models/app',
    'views/slidetab'
], function($, _, Backbone, App, SlidetabView, ShoutsView) {

    

    var SlidetabsView = Backbone.View.extend({

        initialize: function() {
            this.webroot = App.settings.get('webroot');

            this.initCollectionFromDom('.slidetab', this.collection, SlidetabView);

            this.makeSortable();

        },

        makeSortable: function() {
            var webroot = this.webroot;
            this.$el.sortable( {
                handle: '.slidetab-tab',
                start:_.bind(function(event, ui) {
                    this.$el.css('overflow', 'visible');
                }, this),
                stop:_.bind(function(event, ui) {
                    this.$el.css('overflow', 'hidden');
                }, this),
                update:function(event, ui) {
                    var slidetabsOrder = $(this).sortable(
                        'toArray', {attribute: "data-id"}
                    );
                    slidetabsOrder = slidetabsOrder.map(function(name){
                        return 'slidetab_' + name;
                    });
                    // @td make model
                    $.ajax({
                        type: 'POST',
                        url: webroot + 'users/ajax_set',
                        data: {
                            data : {
                                User: {
                                    slidetab_order: slidetabsOrder
                                }
                            }
                        },
                        dataType: 'json'
                    });
                }
            });
        }

    });

    return SlidetabsView;

});
define('views/app',[
	'jquery',
	'underscore',
	'backbone',
    'models/app',
	'collections/threadlines', 'views/threadlines',
	'collections/threads', 'views/thread',
	'collections/postings', 'views/postings',
    'collections/bookmarks', 'views/bookmarks',
    'views/helps', 'views/categoryChooser',
    'collections/slidetabs', 'views/slidetabs',
    'views/answering',
    'jqueryUi'
	], function(
		$, _, Backbone,
        App,
		ThreadLineCollection, ThreadLineView,
		ThreadCollection, ThreadView,
		PostingCollection, PostingView,
        BookmarksCollection, BookmarksView,
        HelpsView, CategoryChooserView,
        SlidetabsCollection, SlidetabsView,
        AnsweringView
		) {

        

		var AppView = Backbone.View.extend({

			el: $('body'),

            autoPageReloadTimer: false,

			events: {
				'click #showLoginForm': 'showLoginForm',
				'focus #header-searchField': 'widenSearchField',
                'click #btn-scrollToTop': 'scrollToTop',
                'click #btn-manuallyMarkAsRead': 'manuallyMarkAsRead',
                "click #btn-category-chooser": "toggleCategoryChooser"
			},

			initialize: function() {
				this.threads = new ThreadCollection();
				if (App.request.controller === 'entries' && App.request.action === 'index') {
					this.threads.fetch();
				}
                this.postings = new PostingCollection();
                // collection of threadlines not bound to thread (bookmarks, search results …)
				this.threadLines = new ThreadLineCollection();

                this.listenTo(App.eventBus, 'initAutoreload', this.initAutoreload);
                this.listenTo(App.eventBus, 'breakAutoreload', this.breakAutoreload);
                this.$el.on('dialogopen', this.fixJqueryUiDialog);
			},

            initFromDom: function(options) {
                $('.thread_box').each(_.bind(function(index, element) {
                    var threadView,
                        threadId;

                    threadId = parseInt($(element).attr('data-id'), 10);
                    if (!this.threads.get(threadId)) {
                        this.threads.add([{
                            id: threadId,
                            isThreadCollapsed: App.request.controller === 'entries' && App.request.action === 'index' && App.currentUser.get('user_show_thread_collapsed')
                        }], {silent: true});
                    }
                    threadView = new ThreadView({
                        el: $(element),
                        postings: this.postings,
                        model: this.threads.get(threadId)
                    });
                }, this));

                $('.js-entry-view-core').each(_.bind(function(a,element) {
                    var id,
                        postingView;

                    id = parseInt(element.getAttribute('data-id'), 10);
                    this.postings.add([{
                        id: id
                    }], {silent: true});
                    postingView = new PostingView({
                        el: $(element),
                        model: this.postings.get(id),
                        collection: this.postings
                    });
                }, this));

                $('.js-thread_line').each(_.bind(function(index, element) {
                    var threadLineView,
                        threadId,
                        threadLineId,
                        currentCollection;

                    threadId = parseInt(element.getAttribute('data-tid'), 10);

                    if(this.threads.get(threadId)) {
                        currentCollection = this.threads.get(threadId).threadlines;
                    } else {
                        currentCollection = this.threadLines;
                    }

                    threadLineId = parseInt(element.getAttribute('data-id'), 10);
                    threadLineView = new ThreadLineView({
                        el: $(element),
                        id: threadLineId,
                        postings: this.postings,
                        collection: currentCollection
                    });
                }, this));

                this.initAutoreload();
                this.initBookmarks('#bookmarks');
                this.initHelp('.shp');
                this.initSlidetabs('#slidetabs');
                this.initCategoryChooser('#category-chooser');

                if($('.entry.add-not-inline').length > 0) {
                    // init the entries/add form where answering is not
                    // appended to a posting
                    this.answeringForm = new AnsweringView({
                        el: this.$('.entry.add-not-inline'),
                        id: 'foo'
                    });
                }

                /*** All elements initialized, show page ***/

                App.initAppStatusUpdate();
                this._showPage(options.SaitoApp.timeAppStart, options.contentTimer);
                App.eventBus.trigger('notification', options.SaitoApp);

                // scroll to thread
                if (window.location.href.indexOf('/jump:') > -1) {
                    var results = /jump:(\d+)/.exec(window.location.href);
                    this.scrollToThread(results[1]);
                    window.history.replaceState(
                        'object or string',
                        'Title',
                        window.location.pathname.replace(/jump:\d+(\/)?/, '')
                    );
                }
            },

            _showPage: function(startTime, timer) {
                var triggerVisible = function() {
                    App.eventBus.trigger('isAppVisible', true);
                };

                if (App.request.isMobile || (new Date().getTime() - startTime) > 1500) {
                    $('#content').css('visibility', 'visible');
                    triggerVisible();
                } else {
                    $('#content')
                        .css({visibility: 'visible', opacity: 0})
                        .animate(
                        { opacity: 1 },
                        {
                            duration: 150,
                            easing: 'easeInOutQuart',
                            complete: triggerVisible
                        });
                }
                timer.cancel();
            },

            fixJqueryUiDialog: function(event, ui) {
                $('.ui-icon-closethick')
                    .attr('class', 'icon icon-close-widget icon-large')
                    .html('');
            },

            initBookmarks: function(element_n) {
                var bookmarksView;
                if ($(element_n).length) {
                    var bookmarks = new BookmarksCollection();
                    bookmarksView = new BookmarksView({
                        el: element_n,
                        collection: bookmarks
                    });
                }
            },

            initSlidetabs: function(element_n) {
                var slidetabs,
                    slidetabsView;
                slidetabs = new SlidetabsCollection();
                slidetabsView = new SlidetabsView({
                    el: element_n,
                    collection: slidetabs
                });
            },

            initCategoryChooser: function(element_n) {
                if ($(element_n).length > 0) {
                    this.categoryChooser = new CategoryChooserView({
                        el: element_n
                    });
                }
            },

            toggleCategoryChooser: function() {
               this.categoryChooser.toggle();
            },

            initHelp: function(element_n) {
                var helps = new HelpsView({
                    el: 'body',
                    elementName: element_n,
                    indicatorName: '#shp-show'
                });
            },

			scrollToThread: function(tid) {
                $('.thread_box[data-id=' + tid + ']')[0].scrollIntoView('top');
			},

            initAutoreload: function() {
                this.breakAutoreload();
                if (App.settings.get('autoPageReload')) {
                    this.autoPageReloadTimer = setTimeout(
                        _.bind(function() {
                            window.location = App.settings.get('webroot') + 'entries/';
                        }, this), App.settings.get('autoPageReload') * 1000);
                }

            },

            breakAutoreload: function() {
                if (this.autoPageReloadTimer !== false) {
                    clearTimeout(this.autoPageReloadTimer);
                    this.autoPageReloadTimer = false;
                }
            },

			/**
			* Widen search field
			*/
			widenSearchField: function(event) {
				var width = 350;
				event.preventDefault();
				if ($(event.currentTarget).width() < width) {
					$(event.currentTarget).animate({
						width: width + 'px'
					},
					"fast"
					);
				}
			},

			showLoginForm: function(event) {
                var modalLoginDialog;

				if((navigator.userAgent.match(/iPhone/i)) || (navigator.userAgent.match(/iPod/i))) {
					return;
				}

                modalLoginDialog =  $('#modalLoginDialog');

				event.preventDefault();
				modalLoginDialog.height('auto');
				var title= event.currentTarget.title;
				modalLoginDialog.dialog({
					modal: true,
					title: title,
					width: 420,
					show: 'fade',
					hide: 'fade',
					position: ['center', 120],
                    resizable: false
				});
			},

            scrollToTop: function(event) {
                event.preventDefault();
                window.scrollTo(0, 0);
            },

            manuallyMarkAsRead: function(event) {
                event.preventDefault();
                window.redirect(App.settings.get('webroot') + 'entries/update');
            }
		});

		return AppView;

	});
/*
 * jQuery Pines Notify (pnotify) Plugin 1.2.0
 *
 * http://pinesframework.org/pnotify/
 * Copyright (c) 2009-2012 Hunter Perrin
 *
 * Triple license under the GPL, LGPL, and MPL:
 *	  http://www.gnu.org/licenses/gpl.html
 *	  http://www.gnu.org/licenses/lgpl.html
 *	  http://www.mozilla.org/MPL/MPL-1.1.html
 */

(function($) {
	var history_handle_top,
		timer,
		body,
		jwindow = $(window),
		styling = {
			jqueryui: {
				container: "ui-widget ui-widget-content ui-corner-all",
				notice: "ui-state-highlight",
				// (The actual jQUI notice icon looks terrible.)
				notice_icon: "ui-icon ui-icon-info",
				info: "",
				info_icon: "ui-icon ui-icon-info",
				success: "ui-state-default",
				success_icon: "ui-icon ui-icon-circle-check",
				error: "ui-state-error",
				error_icon: "ui-icon ui-icon-alert",
				closer: "ui-icon ui-icon-close",
				pin_up: "ui-icon ui-icon-pin-w",
				pin_down: "ui-icon ui-icon-pin-s",
				hi_menu: "ui-state-default ui-corner-bottom",
				hi_btn: "ui-state-default ui-corner-all",
				hi_btnhov: "ui-state-hover",
				hi_hnd: "ui-icon ui-icon-grip-dotted-horizontal"
			},
			bootstrap: {
				container: "alert",
				notice: "",
				notice_icon: "icon-exclamation-sign",
				info: "alert-info",
				info_icon: "icon-info-sign",
				success: "alert-success",
				success_icon: "icon-ok-sign",
				error: "alert-error",
				error_icon: "icon-warning-sign",
				closer: "icon-remove",
				pin_up: "icon-pause",
				pin_down: "icon-play",
				hi_menu: "well",
				hi_btn: "btn",
				hi_btnhov: "",
				hi_hnd: "icon-chevron-down"
			}
		};
	// Set global variables.
	var do_when_ready = function(){
		body = $("body");
		jwindow = $(window);
		// Reposition the notices when the window resizes.
		jwindow.bind('resize', function(){
			if (timer)
				clearTimeout(timer);
			timer = setTimeout($.pnotify_position_all, 10);
		});
	};
	if (document.body)
		do_when_ready();
	else
		$(do_when_ready);
	$.extend({
		pnotify_remove_all: function () {
			var notices_data = jwindow.data("pnotify");
			/* POA: Added null-check */
			if (notices_data && notices_data.length) {
				$.each(notices_data, function(){
					if (this.pnotify_remove)
						this.pnotify_remove();
				});
			}
		},
		pnotify_position_all: function () {
			// This timer is used for queueing this function so it doesn't run
			// repeatedly.
			if (timer)
				clearTimeout(timer);
			timer = null;
			// Get all the notices.
			var notices_data = jwindow.data("pnotify");
			if (!notices_data || !notices_data.length)
				return;
			// Reset the next position data.
			$.each(notices_data, function(){
				var s = this.opts.stack;
				if (!s) return;
				s.nextpos1 = s.firstpos1;
				s.nextpos2 = s.firstpos2;
				s.addpos2 = 0;
				s.animation = true;
			});
			$.each(notices_data, function(){
				this.pnotify_position();
			});
		},
		pnotify: function(options) {
			// Stores what is currently being animated (in or out).
			var animating;

			// Build main options.
			var opts;
			if (typeof options != "object") {
				opts = $.extend({}, $.pnotify.defaults);
				opts.text = options;
			} else {
				opts = $.extend({}, $.pnotify.defaults, options);
			}
			// Translate old pnotify_ style options.
			for (var i in opts) {
				if (typeof i == "string" && i.match(/^pnotify_/))
					opts[i.replace(/^pnotify_/, "")] = opts[i];
			}

			if (opts.before_init) {
				if (opts.before_init(opts) === false)
					return null;
			}

			// This keeps track of the last element the mouse was over, so
			// mouseleave, mouseenter, etc can be called.
			var nonblock_last_elem;
			// This is used to pass events through the notice if it is non-blocking.
			var nonblock_pass = function(e, e_name){
				pnotify.css("display", "none");
				var element_below = document.elementFromPoint(e.clientX, e.clientY);
				pnotify.css("display", "block");
				var jelement_below = $(element_below);
				var cursor_style = jelement_below.css("cursor");
				pnotify.css("cursor", cursor_style != "auto" ? cursor_style : "default");
				// If the element changed, call mouseenter, mouseleave, etc.
				if (!nonblock_last_elem || nonblock_last_elem.get(0) != element_below) {
					if (nonblock_last_elem) {
						dom_event.call(nonblock_last_elem.get(0), "mouseleave", e.originalEvent);
						dom_event.call(nonblock_last_elem.get(0), "mouseout", e.originalEvent);
					}
					dom_event.call(element_below, "mouseenter", e.originalEvent);
					dom_event.call(element_below, "mouseover", e.originalEvent);
				}
				dom_event.call(element_below, e_name, e.originalEvent);
				// Remember the latest element the mouse was over.
				nonblock_last_elem = jelement_below;
			};

			// Get our styling object.
			var styles = styling[opts.styling];

			// Create our widget.
			// Stop animation, reset the removal timer, and show the close
			// button when the user mouses over.
			var pnotify = $("<div />", {
				"class": "ui-pnotify "+opts.addclass,
				"css": {"display": "none"},
				"mouseenter": function(e){
					if (opts.nonblock) e.stopPropagation();
					if (opts.mouse_reset && animating == "out") {
						// If it's animating out, animate back in really quickly.
						pnotify.stop(true);
						animating = "in";
						pnotify.css("height", "auto").animate({"width": opts.width, "opacity": opts.nonblock ? opts.nonblock_opacity : opts.opacity}, "fast");
					}
					if (opts.nonblock) {
						// If it's non-blocking, animate to the other opacity.
						pnotify.animate({"opacity": opts.nonblock_opacity}, "fast");
					}
					// Stop the close timer.
					if (opts.hide && opts.mouse_reset) pnotify.pnotify_cancel_remove();
					// Show the buttons.
					if (opts.sticker && !opts.nonblock) pnotify.sticker.trigger("pnotify_icon").css("visibility", "visible");
					if (opts.closer && !opts.nonblock) pnotify.closer.css("visibility", "visible");
				},
				"mouseleave": function(e){
					if (opts.nonblock) e.stopPropagation();
					nonblock_last_elem = null;
					pnotify.css("cursor", "auto");
					// Animate back to the normal opacity.
					if (opts.nonblock && animating != "out")
						pnotify.animate({"opacity": opts.opacity}, "fast");
					// Start the close timer.
					if (opts.hide && opts.mouse_reset) pnotify.pnotify_queue_remove();
					// Hide the buttons.
					if (opts.sticker_hover)
						pnotify.sticker.css("visibility", "hidden");
					if (opts.closer_hover)
						pnotify.closer.css("visibility", "hidden");
					$.pnotify_position_all();
				},
				"mouseover": function(e){
					if (opts.nonblock) e.stopPropagation();
				},
				"mouseout": function(e){
					if (opts.nonblock) e.stopPropagation();
				},
				"mousemove": function(e){
					if (opts.nonblock) {
						e.stopPropagation();
						nonblock_pass(e, "onmousemove");
					}
				},
				"mousedown": function(e){
					if (opts.nonblock) {
						e.stopPropagation();
						e.preventDefault();
						nonblock_pass(e, "onmousedown");
					}
				},
				"mouseup": function(e){
					if (opts.nonblock) {
						e.stopPropagation();
						e.preventDefault();
						nonblock_pass(e, "onmouseup");
					}
				},
				"click": function(e){
					if (opts.nonblock) {
						e.stopPropagation();
						nonblock_pass(e, "onclick");
					}
				},
				"dblclick": function(e){
					if (opts.nonblock) {
						e.stopPropagation();
						nonblock_pass(e, "ondblclick");
					}
				}
			});
			pnotify.opts = opts;
			// Create a container for the notice contents.
			pnotify.container = $("<div />", {"class": styles.container+" ui-pnotify-container "+(opts.type == "error" ? styles.error : (opts.type == "info" ? styles.info : (opts.type == "success" ? styles.success : styles.notice)))})
			.appendTo(pnotify);
			if (opts.cornerclass != "")
				pnotify.container.removeClass("ui-corner-all").addClass(opts.cornerclass);
			// Create a drop shadow.
			if (opts.shadow)
				pnotify.container.addClass("ui-pnotify-shadow");

			// The current version of Pines Notify.
			pnotify.pnotify_version = "1.2.0";

			// This function is for updating the notice.
			pnotify.pnotify = function(options) {
				// Update the notice.
				var old_opts = opts;
				if (typeof options == "string")
					opts.text = options;
				else
					opts = $.extend({}, opts, options);
				// Translate old pnotify_ style options.
				for (var i in opts) {
					if (typeof i == "string" && i.match(/^pnotify_/))
						opts[i.replace(/^pnotify_/, "")] = opts[i];
				}
				pnotify.opts = opts;
				// Update the corner class.
				if (opts.cornerclass != old_opts.cornerclass)
					pnotify.container.removeClass("ui-corner-all").addClass(opts.cornerclass);
				// Update the shadow.
				if (opts.shadow != old_opts.shadow) {
					if (opts.shadow)
						pnotify.container.addClass("ui-pnotify-shadow");
					else
						pnotify.container.removeClass("ui-pnotify-shadow");
				}
				// Update the additional classes.
				if (opts.addclass === false)
					pnotify.removeClass(old_opts.addclass);
				else if (opts.addclass !== old_opts.addclass)
					pnotify.removeClass(old_opts.addclass).addClass(opts.addclass);
				// Update the title.
				if (opts.title === false)
					pnotify.title_container.slideUp("fast");
				else if (opts.title !== old_opts.title) {
					if (opts.title_escape)
						pnotify.title_container.text(opts.title).slideDown(200);
					else
						pnotify.title_container.html(opts.title).slideDown(200);
				}
				// Update the text.
				if (opts.text === false) {
					pnotify.text_container.slideUp("fast");
				} else if (opts.text !== old_opts.text) {
					if (opts.text_escape)
						pnotify.text_container.text(opts.text).slideDown(200);
					else
						pnotify.text_container.html(opts.insert_brs ? String(opts.text).replace(/\n/g, "<br />") : opts.text).slideDown(200);
				}
				// Update values for history menu access.
				pnotify.pnotify_history = opts.history;
				pnotify.pnotify_hide = opts.hide;
				// Change the notice type.
				if (opts.type != old_opts.type)
					pnotify.container.removeClass(styles.error+" "+styles.notice+" "+styles.success+" "+styles.info).addClass(opts.type == "error" ? styles.error : (opts.type == "info" ? styles.info : (opts.type == "success" ? styles.success : styles.notice)));
				if (opts.icon !== old_opts.icon || (opts.icon === true && opts.type != old_opts.type)) {
					// Remove any old icon.
					pnotify.container.find("div.ui-pnotify-icon").remove();
					if (opts.icon !== false) {
						// Build the new icon.
						$("<div />", {"class": "ui-pnotify-icon"})
						.append($("<span />", {"class": opts.icon === true ? (opts.type == "error" ? styles.error_icon : (opts.type == "info" ? styles.info_icon : (opts.type == "success" ? styles.success_icon : styles.notice_icon))) : opts.icon}))
						.prependTo(pnotify.container);
					}
				}
				// Update the width.
				if (opts.width !== old_opts.width)
					pnotify.animate({width: opts.width});
				// Update the minimum height.
				if (opts.min_height !== old_opts.min_height)
					pnotify.container.animate({minHeight: opts.min_height});
				// Update the opacity.
				if (opts.opacity !== old_opts.opacity)
					pnotify.fadeTo(opts.animate_speed, opts.opacity);
				// Update the sticker and closer buttons.
				if (!opts.closer || opts.nonblock)
					pnotify.closer.css("display", "none");
				else
					pnotify.closer.css("display", "block");
				if (!opts.sticker || opts.nonblock)
					pnotify.sticker.css("display", "none");
				else
					pnotify.sticker.css("display", "block");
				// Update the sticker icon.
				pnotify.sticker.trigger("pnotify_icon");
				// Update the hover status of the buttons.
				if (opts.sticker_hover)
					pnotify.sticker.css("visibility", "hidden");
				else if (!opts.nonblock)
					pnotify.sticker.css("visibility", "visible");
				if (opts.closer_hover)
					pnotify.closer.css("visibility", "hidden");
				else if (!opts.nonblock)
					pnotify.closer.css("visibility", "visible");
				// Update the timed hiding.
				if (!opts.hide)
					pnotify.pnotify_cancel_remove();
				else if (!old_opts.hide)
					pnotify.pnotify_queue_remove();
				pnotify.pnotify_queue_position();
				return pnotify;
			};

			// Position the notice. dont_skip_hidden causes the notice to
			// position even if it's not visible.
			pnotify.pnotify_position = function(dont_skip_hidden){
				// Get the notice's stack.
				var s = pnotify.opts.stack;
				if (!s) return;
				if (!s.nextpos1)
					s.nextpos1 = s.firstpos1;
				if (!s.nextpos2)
					s.nextpos2 = s.firstpos2;
				if (!s.addpos2)
					s.addpos2 = 0;
				var hidden = pnotify.css("display") == "none";
				// Skip this notice if it's not shown.
				if (!hidden || dont_skip_hidden) {
					var curpos1, curpos2;
					// Store what will need to be animated.
					var animate = {};
					// Calculate the current pos1 value.
					var csspos1;
					switch (s.dir1) {
						case "down":
							csspos1 = "top";
							break;
						case "up":
							csspos1 = "bottom";
							break;
						case "left":
							csspos1 = "right";
							break;
						case "right":
							csspos1 = "left";
							break;
					}
					curpos1 = parseInt(pnotify.css(csspos1));
					if (isNaN(curpos1))
						curpos1 = 0;
					// Remember the first pos1, so the first visible notice goes there.
					if (typeof s.firstpos1 == "undefined" && !hidden) {
						s.firstpos1 = curpos1;
						s.nextpos1 = s.firstpos1;
					}
					// Calculate the current pos2 value.
					var csspos2;
					switch (s.dir2) {
						case "down":
							csspos2 = "top";
							break;
						case "up":
							csspos2 = "bottom";
							break;
						case "left":
							csspos2 = "right";
							break;
						case "right":
							csspos2 = "left";
							break;
					}
					curpos2 = parseInt(pnotify.css(csspos2));
					if (isNaN(curpos2))
						curpos2 = 0;
					// Remember the first pos2, so the first visible notice goes there.
					if (typeof s.firstpos2 == "undefined" && !hidden) {
						s.firstpos2 = curpos2;
						s.nextpos2 = s.firstpos2;
					}
					// Check that it's not beyond the viewport edge.
					if ((s.dir1 == "down" && s.nextpos1 + pnotify.height() > jwindow.height()) ||
						(s.dir1 == "up" && s.nextpos1 + pnotify.height() > jwindow.height()) ||
						(s.dir1 == "left" && s.nextpos1 + pnotify.width() > jwindow.width()) ||
						(s.dir1 == "right" && s.nextpos1 + pnotify.width() > jwindow.width()) ) {
						// If it is, it needs to go back to the first pos1, and over on pos2.
						s.nextpos1 = s.firstpos1;
						s.nextpos2 += s.addpos2 + (typeof s.spacing2 == "undefined" ? 25 : s.spacing2);
						s.addpos2 = 0;
					}
					// Animate if we're moving on dir2.
					if (s.animation && s.nextpos2 < curpos2) {
						switch (s.dir2) {
							case "down":
								animate.top = s.nextpos2+"px";
								break;
							case "up":
								animate.bottom = s.nextpos2+"px";
								break;
							case "left":
								animate.right = s.nextpos2+"px";
								break;
							case "right":
								animate.left = s.nextpos2+"px";
								break;
						}
					} else
						pnotify.css(csspos2, s.nextpos2+"px");
					// Keep track of the widest/tallest notice in the column/row, so we can push the next column/row.
					switch (s.dir2) {
						case "down":
						case "up":
							if (pnotify.outerHeight(true) > s.addpos2)
								s.addpos2 = pnotify.height();
							break;
						case "left":
						case "right":
							if (pnotify.outerWidth(true) > s.addpos2)
								s.addpos2 = pnotify.width();
							break;
					}
					// Move the notice on dir1.
					if (s.nextpos1) {
						// Animate if we're moving toward the first pos.
						if (s.animation && (curpos1 > s.nextpos1 || animate.top || animate.bottom || animate.right || animate.left)) {
							switch (s.dir1) {
								case "down":
									animate.top = s.nextpos1+"px";
									break;
								case "up":
									animate.bottom = s.nextpos1+"px";
									break;
								case "left":
									animate.right = s.nextpos1+"px";
									break;
								case "right":
									animate.left = s.nextpos1+"px";
									break;
							}
						} else
							pnotify.css(csspos1, s.nextpos1+"px");
					}
					// Run the animation.
					if (animate.top || animate.bottom || animate.right || animate.left)
						pnotify.animate(animate, {duration: 500, queue: false});
					// Calculate the next dir1 position.
					switch (s.dir1) {
						case "down":
						case "up":
							s.nextpos1 += pnotify.height() + (typeof s.spacing1 == "undefined" ? 25 : s.spacing1);
							break;
						case "left":
						case "right":
							s.nextpos1 += pnotify.width() + (typeof s.spacing1 == "undefined" ? 25 : s.spacing1);
							break;
					}
				}
			};

			// Queue the positiona all function so it doesn't run repeatedly and
			// use up resources.
			pnotify.pnotify_queue_position = function(milliseconds){
				if (timer)
					clearTimeout(timer);
				if (!milliseconds)
					milliseconds = 10;
				timer = setTimeout($.pnotify_position_all, milliseconds);
			};

			// Display the notice.
			pnotify.pnotify_display = function() {
				// If the notice is not in the DOM, append it.
				if (!pnotify.parent().length)
					pnotify.appendTo(body);
				// Run callback.
				if (opts.before_open) {
					if (opts.before_open(pnotify) === false)
						return;
				}
				// Try to put it in the right position.
				if (opts.stack.push != "top")
					pnotify.pnotify_position(true);
				// First show it, then set its opacity, then hide it.
				if (opts.animation == "fade" || opts.animation.effect_in == "fade") {
					// If it's fading in, it should start at 0.
					pnotify.show().fadeTo(0, 0).hide();
				} else {
					// Or else it should be set to the opacity.
					if (opts.opacity != 1)
						pnotify.show().fadeTo(0, opts.opacity).hide();
				}
				pnotify.animate_in(function(){
					if (opts.after_open)
						opts.after_open(pnotify);

					pnotify.pnotify_queue_position();

					// Now set it to hide.
					if (opts.hide)
						pnotify.pnotify_queue_remove();
				});
			};

			// Remove the notice.
			pnotify.pnotify_remove = function() {
				if (pnotify.timer) {
					window.clearTimeout(pnotify.timer);
					pnotify.timer = null;
				}
				// Run callback.
				if (opts.before_close) {
					if (opts.before_close(pnotify) === false)
						return;
				}
				pnotify.animate_out(function(){
					if (opts.after_close) {
						if (opts.after_close(pnotify) === false)
							return;
					}
					pnotify.pnotify_queue_position();
					// If we're supposed to remove the notice from the DOM, do it.
					if (opts.remove)
						pnotify.detach();
				});
			};

			// Animate the notice in.
			pnotify.animate_in = function(callback){
				// Declare that the notice is animating in. (Or has completed animating in.)
				animating = "in";
				var animation;
				if (typeof opts.animation.effect_in != "undefined")
					animation = opts.animation.effect_in;
				else
					animation = opts.animation;
				if (animation == "none") {
					pnotify.show();
					callback();
				} else if (animation == "show")
					pnotify.show(opts.animate_speed, callback);
				else if (animation == "fade")
					pnotify.show().fadeTo(opts.animate_speed, opts.opacity, callback);
				else if (animation == "slide")
					pnotify.slideDown(opts.animate_speed, callback);
				else if (typeof animation == "function")
					animation("in", callback, pnotify);
				else
					pnotify.show(animation, (typeof opts.animation.options_in == "object" ? opts.animation.options_in : {}), opts.animate_speed, callback);
			};

			// Animate the notice out.
			pnotify.animate_out = function(callback){
				// Declare that the notice is animating out. (Or has completed animating out.)
				animating = "out";
				var animation;
				if (typeof opts.animation.effect_out != "undefined")
					animation = opts.animation.effect_out;
				else
					animation = opts.animation;
				if (animation == "none") {
					pnotify.hide();
					callback();
				} else if (animation == "show")
					pnotify.hide(opts.animate_speed, callback);
				else if (animation == "fade")
					pnotify.fadeOut(opts.animate_speed, callback);
				else if (animation == "slide")
					pnotify.slideUp(opts.animate_speed, callback);
				else if (typeof animation == "function")
					animation("out", callback, pnotify);
				else
					pnotify.hide(animation, (typeof opts.animation.options_out == "object" ? opts.animation.options_out : {}), opts.animate_speed, callback);
			};

			// Cancel any pending removal timer.
			pnotify.pnotify_cancel_remove = function() {
				if (pnotify.timer)
					window.clearTimeout(pnotify.timer);
			};

			// Queue a removal timer.
			pnotify.pnotify_queue_remove = function() {
				// Cancel any current removal timer.
				pnotify.pnotify_cancel_remove();
				pnotify.timer = window.setTimeout(function(){
					pnotify.pnotify_remove();
				}, (isNaN(opts.delay) ? 0 : opts.delay));
			};

			// Provide a button to close the notice.
			pnotify.closer = $("<div />", {
				"class": "ui-pnotify-closer",
				"css": {"cursor": "pointer", "visibility": opts.closer_hover ? "hidden" : "visible"},
				"click": function(){
					pnotify.pnotify_remove();
					pnotify.sticker.css("visibility", "hidden");
					pnotify.closer.css("visibility", "hidden");
				}
			})
			.append($("<span />", {"class": styles.closer}))
			.appendTo(pnotify.container);
			if (!opts.closer || opts.nonblock)
				pnotify.closer.css("display", "none");

			// Provide a button to stick the notice.
			pnotify.sticker = $("<div />", {
				"class": "ui-pnotify-sticker",
				"css": {"cursor": "pointer", "visibility": opts.sticker_hover ? "hidden" : "visible"},
				"click": function(){
					opts.hide = !opts.hide;
					if (opts.hide)
						pnotify.pnotify_queue_remove();
					else
						pnotify.pnotify_cancel_remove();
					$(this).trigger("pnotify_icon");
				}
			})
			.bind("pnotify_icon", function(){
				$(this).children().removeClass(styles.pin_up+" "+styles.pin_down).addClass(opts.hide ? styles.pin_up : styles.pin_down);
			})
			.append($("<span />", {"class": styles.pin_up}))
			.appendTo(pnotify.container);
			if (!opts.sticker || opts.nonblock)
				pnotify.sticker.css("display", "none");

			// Add the appropriate icon.
			if (opts.icon !== false) {
				$("<div />", {"class": "ui-pnotify-icon"})
				.append($("<span />", {"class": opts.icon === true ? (opts.type == "error" ? styles.error_icon : (opts.type == "info" ? styles.info_icon : (opts.type == "success" ? styles.success_icon : styles.notice_icon))) : opts.icon}))
				.prependTo(pnotify.container);
			}

			// Add a title.
			pnotify.title_container = $("<h4 />", {
				"class": "ui-pnotify-title"
			})
			.appendTo(pnotify.container);
			if (opts.title === false)
				pnotify.title_container.hide();
			else if (opts.title_escape)
				pnotify.title_container.text(opts.title);
			else
				pnotify.title_container.html(opts.title);

			// Add text.
			pnotify.text_container = $("<div />", {
				"class": "ui-pnotify-text"
			})
			.appendTo(pnotify.container);
			if (opts.text === false)
				pnotify.text_container.hide();
			else if (opts.text_escape)
				pnotify.text_container.text(opts.text);
			else
				pnotify.text_container.html(opts.insert_brs ? String(opts.text).replace(/\n/g, "<br />") : opts.text);

			// Set width and min height.
			if (typeof opts.width == "string")
				pnotify.css("width", opts.width);
			if (typeof opts.min_height == "string")
				pnotify.container.css("min-height", opts.min_height);

			// The history variable controls whether the notice gets redisplayed
			// by the history pull down.
			pnotify.pnotify_history = opts.history;
			// The hide variable controls whether the history pull down should
			// queue a removal timer.
			pnotify.pnotify_hide = opts.hide;

			// Add the notice to the notice array.
			var notices_data = jwindow.data("pnotify");
			if (notices_data == null || typeof notices_data != "object")
				notices_data = [];
			if (opts.stack.push == "top")
				notices_data = $.merge([pnotify], notices_data);
			else
				notices_data = $.merge(notices_data, [pnotify]);
			jwindow.data("pnotify", notices_data);
			// Now position all the notices if they are to push to the top.
			if (opts.stack.push == "top")
				pnotify.pnotify_queue_position(1);

			// Run callback.
			if (opts.after_init)
				opts.after_init(pnotify);

			if (opts.history) {
				// If there isn't a history pull down, create one.
				var history_menu = jwindow.data("pnotify_history");
				if (typeof history_menu == "undefined") {
					history_menu = $("<div />", {
						"class": "ui-pnotify-history-container "+styles.hi_menu,
						"mouseleave": function(){
							history_menu.animate({top: "-"+history_handle_top+"px"}, {duration: 100, queue: false});
						}
					})
					.append($("<div />", {"class": "ui-pnotify-history-header", "text": "Redisplay"}))
					.append($("<button />", {
							"class": "ui-pnotify-history-all "+styles.hi_btn,
							"text": "All",
							"mouseenter": function(){
								$(this).addClass(styles.hi_btnhov);
							},
							"mouseleave": function(){
								$(this).removeClass(styles.hi_btnhov);
							},
							"click": function(){
								// Display all notices. (Disregarding non-history notices.)
								$.each(notices_data, function(){
									if (this.pnotify_history) {
										if (this.is(":visible")) {
											if (this.pnotify_hide)
												this.pnotify_queue_remove();
										} else if (this.pnotify_display)
											this.pnotify_display();
									}
								});
								return false;
							}
					}))
					.append($("<button />", {
							"class": "ui-pnotify-history-last "+styles.hi_btn,
							"text": "Last",
							"mouseenter": function(){
								$(this).addClass(styles.hi_btnhov);
							},
							"mouseleave": function(){
								$(this).removeClass(styles.hi_btnhov);
							},
							"click": function(){
								// Look up the last history notice, and display it.
								var i = -1;
								var notice;
								do {
									if (i == -1)
										notice = notices_data.slice(i);
									else
										notice = notices_data.slice(i, i+1);
									if (!notice[0])
										break;
									i--;
								} while (!notice[0].pnotify_history || notice[0].is(":visible"));
								if (!notice[0])
									return false;
								if (notice[0].pnotify_display)
									notice[0].pnotify_display();
								return false;
							}
					}))
					.appendTo(body);

					// Make a handle so the user can pull down the history tab.
					var handle = $("<span />", {
						"class": "ui-pnotify-history-pulldown "+styles.hi_hnd,
						"mouseenter": function(){
							history_menu.animate({top: "0"}, {duration: 100, queue: false});
						}
					})
					.appendTo(history_menu);

					// Get the top of the handle.
					history_handle_top = handle.offset().top + 2;
					// Hide the history pull down up to the top of the handle.
					history_menu.css({top: "-"+history_handle_top+"px"});
					// Save the history pull down.
					jwindow.data("pnotify_history", history_menu);
				}
			}

			// Mark the stack so it won't animate the new notice.
			opts.stack.animation = false;

			// Display the notice.
			pnotify.pnotify_display();

			return pnotify;
		}
	});

	// Some useful regexes.
	var re_on = /^on/,
		re_mouse_events = /^(dbl)?click$|^mouse(move|down|up|over|out|enter|leave)$|^contextmenu$/,
		re_ui_events = /^(focus|blur|select|change|reset)$|^key(press|down|up)$/,
		re_html_events = /^(scroll|resize|(un)?load|abort|error)$/;
	// Fire a DOM event.
	var dom_event = function(e, orig_e){
		var event_object;
		e = e.toLowerCase();
		if (document.createEvent && this.dispatchEvent) {
			// FireFox, Opera, Safari, Chrome
			e = e.replace(re_on, '');
			if (e.match(re_mouse_events)) {
				// This allows the click event to fire on the notice. There is
				// probably a much better way to do it.
				$(this).offset();
				event_object = document.createEvent("MouseEvents");
				event_object.initMouseEvent(
					e, orig_e.bubbles, orig_e.cancelable, orig_e.view, orig_e.detail,
					orig_e.screenX, orig_e.screenY, orig_e.clientX, orig_e.clientY,
					orig_e.ctrlKey, orig_e.altKey, orig_e.shiftKey, orig_e.metaKey, orig_e.button, orig_e.relatedTarget
				);
			} else if (e.match(re_ui_events)) {
				event_object = document.createEvent("UIEvents");
				event_object.initUIEvent(e, orig_e.bubbles, orig_e.cancelable, orig_e.view, orig_e.detail);
			} else if (e.match(re_html_events)) {
				event_object = document.createEvent("HTMLEvents");
				event_object.initEvent(e, orig_e.bubbles, orig_e.cancelable);
			}
			if (!event_object) return;
			this.dispatchEvent(event_object);
		} else {
			// Internet Explorer
			if (!e.match(re_on)) e = "on"+e;
			event_object = document.createEventObject(orig_e);
			this.fireEvent(e, event_object);
		}
	};

	$.pnotify.defaults = {
		// The notice's title.
		title: false,
		// Whether to escape the content of the title. (Not allow HTML.)
		title_escape: false,
		// The notice's text.
		text: false,
		// Whether to escape the content of the text. (Not allow HTML.)
		text_escape: false,
		// What styling classes to use. (Can be either jqueryui or bootstrap.)
		styling: "bootstrap",
		// Additional classes to be added to the notice. (For custom styling.)
		addclass: "",
		// Class to be added to the notice for corner styling.
		cornerclass: "",
		// Create a non-blocking notice. It lets the user click elements underneath it.
		nonblock: false,
		// The opacity of the notice (if it's non-blocking) when the mouse is over it.
		nonblock_opacity: .2,
		// Display a pull down menu to redisplay previous notices, and place the notice in the history.
		history: true,
		// Width of the notice.
		width: "300px",
		// Minimum height of the notice. It will expand to fit content.
		min_height: "16px",
		// Type of the notice. "notice", "info", "success", or "error".
		type: "notice",
		// Set icon to true to use the default icon for the selected style/type, false for no icon, or a string for your own icon class.
		icon: true,
		// The animation to use when displaying and hiding the notice. "none", "show", "fade", and "slide" are built in to jQuery. Others require jQuery UI. Use an object with effect_in and effect_out to use different effects.
		animation: "fade",
		// Speed at which the notice animates in and out. "slow", "def" or "normal", "fast" or number of milliseconds.
		animate_speed: "slow",
		// Opacity of the notice.
		opacity: 1,
		// Display a drop shadow.
		shadow: true,
		// Provide a button for the user to manually close the notice.
		closer: true,
		// Only show the closer button on hover.
		closer_hover: true,
		// Provide a button for the user to manually stick the notice.
		sticker: true,
		// Only show the sticker button on hover.
		sticker_hover: true,
		// After a delay, remove the notice.
		hide: true,
		// Delay in milliseconds before the notice is removed.
		delay: 8000,
		// Reset the hide timer if the mouse moves over the notice.
		mouse_reset: true,
		// Remove the notice's elements from the DOM after it is removed.
		remove: true,
		// Change new lines to br tags.
		insert_brs: true,
		// The stack on which the notices will be placed. Also controls the direction the notices stack.
		stack: {"dir1": "down", "dir2": "left", "push": "bottom", "spacing1": 25, "spacing2": 25}
	};
})(jQuery);
define("views/../../dev/vendors/pnotify/jquery.pnotify", function(){});

define('views/notification',[
    'jquery',
    'underscore',
    'backbone',
    'models/app',
    '../../dev/vendors/pnotify/jquery.pnotify'
], function($, _, Backbone,
            App
    ) {

    

    var NotificationView = Backbone.View.extend({

        initialize: function() {
            this.listenTo(App.eventBus, 'notification', this._showMessages);
            this.listenTo(App.eventBus, 'notificationUnset', this._unset);
        },

        /**
         * Handles message rendering
         *
         * options can be a single message:
         *
         * {
         *  `message` message to display,
         *  `title` "title (optional)",
         *  `type` "error|notice(default)|warning|success",
         *  `channel` "notification(default)|form"
         *  `element` ".input_selector" if `channel` is "form"
         * }
         *
         * or array with a msg property and a message list:
         *
         * {
         *  msg: [{message:…}, {message:…}]
         *  }
         *
         * @param options
         * @private
         */
        _showMessages: function(options) {
            if (options === undefined) {
                return;
            }
            if (options.msg === undefined) {
                if (options.message === undefined) {
                    return;
                }
                options = {
                    msg: [options]
                };
            } else if (options.msg.length === 0) {
                return;
            }

            _.each(options.msg, function(msg) {
                this._showMessage(msg);
            }, this);
        },

        /**
         * Renders a single message
         *
         * @param options single message
         * @private
         */
        _showMessage: function(msg) {
            msg.channel = msg.channel || "notification";
            // msg.title = msg.title || $.i18n.__(msg.type);

            switch(msg.channel) {
                case "form":
                    this._form(msg);
                    break;
                case "popover":
                    this._popover(msg);
                    break;
                default:
                    this._showNotification(msg);
                    break;
            }

        },

        _unset: function(msg) {
            if (msg === 'all') {
                $('.error-message').remove();
            }
        },

        _form: function(msg) {
            var tpl;
            tpl = _.template('<div class="error-message"><%= message %></div>');
            $(msg.element).after(tpl({message: msg.message}));
        },

        _showNotification: function(options) {
            var logOptions,
                delay;

            delay = 5000;

            logOptions = {
                    title: options.title,
                    text: options.message,
                    icon: false,
                    history: false,
                    addclass: "flash",
                    delay: delay
                };

            switch(options.type) {
                case 'success':
                    logOptions.addclass += " flash-success";
                    break;
                case 'warning':
                    logOptions.addclass += " flash-warning";
                    break;
                case 'error':
                    logOptions.addclass += " flash-error";
                    logOptions.delay = delay * 2;
                    // logOptions.hide = false;
                    break;
                default:
                    logOptions.addclass += " flash-notice";
                    break;
            }

            $.pnotify(logOptions);

        }

    });

    return NotificationView;

});

// moment.js language configuration
// language : german (de)
// author : lluchs : https://github.com/lluchs
// author: Menelion Elensúle: https://github.com/Oire

(function (factory) {
    if (typeof define === 'function' && define.amd) {
        define('moment-de',['moment'], factory); // AMD
    } else if (typeof exports === 'object') {
        module.exports = factory(require('../moment')); // Node
    } else {
        factory(window.moment); // Browser global
    }
}(function (moment) {
    function processRelativeTime(number, withoutSuffix, key, isFuture) {
        var format = {
            'm': ['eine Minute', 'einer Minute'],
            'h': ['eine Stunde', 'einer Stunde'],
            'd': ['ein Tag', 'einem Tag'],
            'dd': [number + ' Tage', number + ' Tagen'],
            'M': ['ein Monat', 'einem Monat'],
            'MM': [number + ' Monate', number + ' Monaten'],
            'y': ['ein Jahr', 'einem Jahr'],
            'yy': [number + ' Jahre', number + ' Jahren']
        };
        return withoutSuffix ? format[key][0] : format[key][1];
    }

    return moment.lang('de', {
        months : "Januar_Februar_März_April_Mai_Juni_Juli_August_September_Oktober_November_Dezember".split("_"),
        monthsShort : "Jan._Febr._Mrz._Apr._Mai_Jun._Jul._Aug._Sept._Okt._Nov._Dez.".split("_"),
        weekdays : "Sonntag_Montag_Dienstag_Mittwoch_Donnerstag_Freitag_Samstag".split("_"),
        weekdaysShort : "So._Mo._Di._Mi._Do._Fr._Sa.".split("_"),
        weekdaysMin : "So_Mo_Di_Mi_Do_Fr_Sa".split("_"),
        longDateFormat : {
            LT: "H:mm [Uhr]",
            L : "DD.MM.YYYY",
            LL : "D. MMMM YYYY",
            LLL : "D. MMMM YYYY LT",
            LLLL : "dddd, D. MMMM YYYY LT"
        },
        calendar : {
            sameDay: "[Heute um] LT",
            sameElse: "L",
            nextDay: '[Morgen um] LT',
            nextWeek: 'dddd [um] LT',
            lastDay: '[Gestern um] LT',
            lastWeek: '[letzten] dddd [um] LT'
        },
        relativeTime : {
            future : "in %s",
            past : "vor %s",
            s : "ein paar Sekunden",
            m : processRelativeTime,
            mm : "%d Minuten",
            h : processRelativeTime,
            hh : "%d Stunden",
            d : processRelativeTime,
            dd : processRelativeTime,
            M : processRelativeTime,
            MM : processRelativeTime,
            y : processRelativeTime,
            yy : processRelativeTime
        },
        ordinal : '%d.',
        week : {
            dow : 1, // Monday is the first day of the week.
            doy : 4  // The week that contains Jan 4th is the first week of the year.
        }
    });
}));

define('app/time',['moment', 'moment-de'], function(moment) {

  // @todo language switcher

  

  // remove `Uhr` from german short time format
  var longDateFormat = moment().lang()._longDateFormat;
  longDateFormat.LT = 'H:mm';
  moment.lang('de', {longDateFormat: longDateFormat});

});
/*
 * jQuery i18n plugin
 * @requires jQuery v1.1 or later
 *
 * See http://recursive-design.com/projects/jquery-i18n/
 *
 * Licensed under the MIT license:
 *   http://www.opensource.org/licenses/mit-license.php
 *
 * Version: @VERSION (@DATE)
 */
 (function($) {
  /**
   * i18n provides a mechanism for translating strings using a jscript dictionary.
   *
   */

  /*
   * i18n property list
   */
  $.i18n = {
	
  	dict: null,
	
    /**
     * setDictionary()
     *
     * Initialises the dictionary.
     *
     * @param  property_list i18n_dict : The dictionary to use for translation.
     */
  	setDictionary: function(i18n_dict) {
  		this.dict = i18n_dict;
  	},
	
    /**
     * _()
     *
     * Looks the given string up in the dictionary and returns the translation if 
     * one exists. If a translation is not found, returns the original word.
     *
     * @param  string str           : The string to translate.
     * @param  property_list params : params for using printf() on the string.
     *
     * @return string               : Translated word.
     */
  	_: function (str, params) {
  		var result = str;
  		if (this.dict && this.dict[str]) {
  			result = this.dict[str];
  		}
  		
  		// Substitute any params.
  		return this.printf(result, params);
  	},

    /*
     * printf()
     *
     * Substitutes %s with parameters given in list. %%s is used to escape %s.
     *
     * @param  string str    : String to perform printf on.
     * @param  string args   : Array of arguments for printf.
     *
     * @return string result : Substituted string
     */
  	printf: function(str, args) {
  		if (!args) return str;

  		var result = '';
  		var search = /%(\d+)\$s/g;
		
  		// Replace %n1$ where n is a number.
  		var matches = search.exec(str);
  		while (matches) {
  			var index = parseInt(matches[1], 10) - 1;
  			str       = str.replace('%' + matches[1] + '\$s', (args[index]));
  		  matches   = search.exec(str);
  		}
  		var parts = str.split('%s');

  		if (parts.length > 1) {
  			for(var i = 0; i < args.length; i++) {
  			  // If the part ends with a '%' chatacter, we've encountered a literal
  			  // '%%s', which we should output as a '%s'. To achieve this, add an
  			  // 's' on the end and merge it with the next part.
  				if (parts[i].length > 0 && parts[i].lastIndexOf('%') == (parts[i].length - 1)) {
  					parts[i] += 's' + parts.splice(i + 1, 1)[0];
  				}
  				
  				// Append the part and the substitution to the result.
  				result += parts[i] + args[i];
  			}
  		}
		
  		return result + parts[parts.length - 1];
  	}

  };

  /*
   * _t()
   *
   * Allows you to translate a jQuery selector.
   *
   * eg $('h1')._t('some text')
   * 
   * @param  string str           : The string to translate .
   * @param  property_list params : Params for using printf() on the string.
   * 
   * @return element              : Chained and translated element(s).
  */
  $.fn._t = function(str, params) {
    return $(this).text($.i18n._(str, params));
  };

})(jQuery);


define("lib/jquery.i18n/jquery.i18n", function(){});

/**
 * Extension for i18n for CakePHP/Saito
 */
define('lib/jquery.i18n/jquery.i18n.extend',[
    'jquery',
    'lib/jquery.i18n/jquery.i18n'
], function($) {

    $.extend($.i18n, {

        currentString: '',

        setDict: function(dict) {
           this.dict = dict;
        },

        setUrl: function(dictUrl) {
            this.dictUrl = dictUrl;
            this._loadDict();
        },

        _loadDict: function() {
            return $.ajax({
                url: this.dictUrl,
                dataType: 'json',
                async: false,
                cache: true,
                success: $.proxy(function(data) {
                    this.dict = data;
                }, this)
            });
        },

        /**
         * Localice string with tokens
         *
         * Token replacement compatible to CakePHP's String::insert()
         *
         */
        __: function(string, tokens) {
            var out = '';

            if (typeof this.dict[string] === 'string' && this.dict[string] !== "") {
                out = this.dict[string];
                if (typeof tokens === 'object') {
                    out = this._insert(out, tokens);
                }
            } else {
                out = string;
            }

            return out;

        },

        _insert: function(string, tokens) {
            return string.replace(/:([-\w]+)/g, function(token, match, number, text){
                if(typeof tokens[match] !== "undefined") {
                    return tokens[match];
                }
                return token;
            });
        }
    });

});

(function (root, factory) {
    if (typeof define === "function" && define.amd) {
        define('lib/saito/backbone.initHelper',["underscore","backbone"], function(_, Backbone) {
            return factory(_ || root._, Backbone || root.Backbone);
        });
    } else {
        factory(_, Backbone);
    }
})(this, function(_, Backbone) {

    /**
     * Init all subviews (models and views) from DOM elements
     *
     * @param element
     * @param collection
     * @param view
     */
    Backbone.View.prototype.initCollectionFromDom = function(element, collection, view) {
        var createElement = function(collection, id, element) {
            collection.add({
                id: id
            });
            new view({
                el: element,
                model: collection.get(id)
            })
        };

        $(element).each(function(){
                createElement(collection, $(this).data('id'), this);
            }
        );
    };

});

(function (root, factory) {

    

    if (typeof define === "function" && define.amd) {
        define('lib/saito/backbone.modelHelper',["underscore","backbone"], function(_, Backbone) {
            return factory(_ || root._, Backbone || root.Backbone);
        });
    } else {
        factory(_, Backbone);
    }
})(this, function(_, Backbone) {

    

    /**
     * Bool toggle attribute of model
     *
     * @param attribute
     */
    Backbone.Model.prototype.toggle = function(attribute) {
        this.set(attribute, !this.get(attribute));
    };

});

define('modules/html5-notification/views/notification',['underscore', 'backbone', 'models/app'], function(_, Backbone, App) {

  

  var NotificationView = Backbone.View.extend({
    // @todo test browser support
    _enabled: true,

    /**
     * hide notification after this seconds
     */
    _hideAfter: 4,

    initialize: function() {
      this.listenTo(App.eventBus, 'html5-notification', this.notification);
      App.commands.setHandler('app:html5-notification:activate', this._activate);
      App.reqres.setHandler('app:html5-notification:available', _.bind(this._isEnabled, this));
    },

    notification: function(data) {
      data = _.defaults(data, {
        // @todo
        icon: 'http://macnemo.de/wiki/uploads/Main/macnemo_iphone2.png',
        always: false
      });

      if (data.always || this._isAppHidden()) {
        // @todo browser support
        var notification = window.webkitNotifications.createNotification(
            data.icon,
            data.title,
            data.message
        );
        notification.show();
        // hide the notification after
        setTimeout(function(){
          notification.close();
        }, this._hideAfter * 1000);
      }
    },

    _isAppHidden: function() {
      // @todo browser support
      var hidden, isHidden = false;
      if (typeof document.hidden !== "undefined") { // Opera 12.10 and Firefox 18 and later support
        hidden = "hidden";
      } else if (typeof document.webkitHidden !== "undefined") {
        hidden = "webkitHidden";
      }
      if (document[hidden]) {
        isHidden = document[hidden];
      }
      return isHidden;
    },

    _activate: function() {
      if (window.webkitNotifications.checkPermission() !== 0) {
        window.webkitNotifications.requestPermission();
      }
    },

    _isEnabled: function() {
      return this._enabled;
    }

  });

  return NotificationView;

});
define('modules/html5-notification/html5-notification',[
  'app/app',
  'marionette',
  'modules/html5-notification/views/notification'
],
    function(Application, Marionette, Notification) {

      

      var Html5Notification = Application.module("html5-notification");

      Html5Notification.addInitializer(function(options) {
        var html5Notification = new Notification();
      });

      return Html5Notification;
    });
define(
    'app/app',['marionette', 'app/vent'],
    function(Marionette, EventBus) {

    // @todo
    //noinspection JSHint
    var AppInitData = SaitoApp;

    //noinspection JSHint
    var whenReady = function(callback) {
        require(['jquery', 'domReady'], function($, domReady) {
            if ($.isReady) {
                callback();
            } else {
                domReady(function() {
                    callback();
                });
            }
        });
    };

    var app = {

        bootstrapShoutbox: function(options) {
            whenReady(function() {
                require(
                    ['modules/shoutbox/shoutbox'],
                    function(ShoutboxModule) {
                        ShoutboxModule.start();
                    });
            });
        },

        bootstrapApp: function(options) {
            require([
                'domReady', 'views/app', 'backbone', 'jquery', 'models/app',
                'views/notification',

                'app/time',

                'lib/jquery.i18n/jquery.i18n.extend',
                'bootstrap', 'lib/saito/backbone.initHelper',
                'lib/saito/backbone.modelHelper', 'fastclick'
            ],
                function(domReady, AppView, Backbone, $, App, NotificationView) {
                    var appView,
                        appReady;

                    App.settings.set(options.SaitoApp.app.settings);
                    App.currentUser.set(options.SaitoApp.currentUser);
                    App.request = options.SaitoApp.request;

                    //noinspection JSHint
                    new NotificationView();

                    window.addEventListener('load', function() {
                        //noinspection JSHint
                        new FastClick(document.body);
                    }, false);

                    // init i18n
                    $.i18n.setUrl(App.settings.get('webroot') + "saitos/langJs");

                    appView = new AppView();

                    appReady = function() {
                        // we need the App object initialized
                        // @todo decouple
                        if ('shouts' in AppInitData) {
                          app.bootstrapShoutbox();
                        }
                        appView.initFromDom({
                            SaitoApp: options.SaitoApp,
                            contentTimer: options.contentTimer
                        });
                    };

                    whenReady(appReady);
                }
            );
        },

        bootstrapTest: function(options) {
            require(['domReady', 'views/app', 'backbone', 'jquery'],
                function(domReady, AppView, Backbone, $) {
                    // prevent appending of ?_<timestamp> requested urls
                    $.ajaxSetup({ cache: true });
                    // override local storage store name - for testing
                    window.store = "TestStore";

                    var jasmineEnv = jasmine.getEnv();
                    jasmineEnv.updateInterval = 1000;

                    var htmlReporter = new jasmine.HtmlReporter();

                    jasmineEnv.addReporter(htmlReporter);
                    jasmineEnv.specFilter = function(spec) {
                        return htmlReporter.specFilter(spec);
                    };

                    var specs = [
                        'models/AppStatusModelSpec.js',
                        'models/BookmarkModelSpec.js',
                        'models/SlidetabModelSpec.js',
                        'models/StatusModelSpec.js',
                        'models/UploadModelSpec.js',
                        'lib/MarkItUpSpec.js',
                        'lib/jquery.i18n.extendSpec.js',
                        // 'views/AppViewSpec.js',
                        'views/ThreadViewSpec.js'
                    ];

                    specs = _.map(specs, function(value) {
                        return options.SaitoApp.app.settings.webroot + 'js/tests/' + value;
                    });

                    $(function() {
                        require(specs, function() {
                            jasmineEnv.execute();
                        });
                    });
                }
            );
        }
    };

    var Application = new Marionette.Application();

      if (AppInitData.app.runJsTests === undefined) {
        Application.addInitializer(app.bootstrapApp);
        Application.addInitializer(function() {
          require(['modules/html5-notification/html5-notification'],
              function(Html5NotificationModule) {
                Html5NotificationModule.start();
              });
        });
      } else {
        Application.addInitializer(app.bootstrapTest);
      }
      Application.start({
        contentTimer: contentTimer,
        SaitoApp: AppInitData
      });

      EventBus.reqres.setHandler('webroot', function() {
        return AppInitData.app.settings.webroot;
      });
      EventBus.reqres.setHandler('apiroot', function() {
        return AppInitData.app.settings.webroot + 'api/v1/';
      });

    return Application;

});

require.config({
  // paths necessary until file is migrated into common.js
  paths: {
    // Comment to load all common.js files separately from
    // bower_components/ or vendors/.
    // Run `grunt dev-setup` to install bower components first.
    common: '../dist/common',
    // moment
    moment: '../dev/bower_components/momentjs/js/moment',
    'moment-de': '../dev/bower_components/momentjs/lang/de'
  }
});

if (typeof jasmine === "undefined") {
    jasmine = {};
}

// Camino doesn't support console at all
if (typeof console === "undefined") {
    console = {};
    console.log = function(message) {
        return;
    };
    console.error = console.debug = console.info =  console.log;
}

// fallback if dom does not get ready for some reason to show the content eventually
var contentTimer = {
    show: function() {
        $('#content').css('visibility', 'visible');
        console.warn('DOM ready timed out: show content fallback used.');
        delete this.timeoutID;
    },

    setup: function() {
        this.cancel();
        var self = this;
        this.timeoutID = window.setTimeout(function() {
            self.show();
        }, 5000);
    },

    cancel: function() {
        if(typeof this.timeoutID === "number") {
            window.clearTimeout(this.timeoutID);
            delete this.timeoutID;
        }
    }
};
contentTimer.setup();

(function(window, SaitoApp, contentTimer, jasmine) {

  

  /**
   * Redirects current page to a new url destination without changing browser history
   *
   * This also is also the mock to test redirects
   *
   * @param destination url to redirect to
   */
  window.redirect = function(destination) {
    document.location.replace(destination);
  };

  // prevent caching of ajax results
  $.ajaxSetup({cache: false});

  define('jquery', [],function() { return jQuery; });


  require(['common'], function() {
    require(['app/app']);
  });

})(this, SaitoApp, contentTimer, jasmine);
define("main", function(){});
