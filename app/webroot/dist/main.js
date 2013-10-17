
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
    'cakeRest'
], function(_, Backbone, cakeRest) {

    

    var AppStatusModel = Backbone.Model.extend({

        initialize: function() {
            this.methodToCakePhpUrl = _.clone(this.methodToCakePhpUrl);
            this.methodToCakePhpUrl.read = 'status/';
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
    'models/appSetting',
    'models/appStatus',
    'models/currentUser'
], function (_, Backbone,
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
            this.eventBus = _.extend({}, Backbone.Events);
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
    'models/app'
], function(_, Backbone, App) {

    

    var SlidetabModel = Backbone.Model.extend({

        defaults: {
            isOpen: false
        },

        initialize: function() {
            this.webroot = App.settings.get('webroot');
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
    'models/slidetab'
], function(_, Backbone, SlidetabModel) {
    var SlidetabCollection = Backbone.Collection.extend({
        model: SlidetabModel
    });
    return SlidetabCollection;
});

define('models/shout',[
    'underscore',
    'backbone',
    'models/app'
], function(_, Backbone, App) {

    

    var ShoutModel = Backbone.Model.extend({
        defaults: {
            html: '',
            newShout: ''
        },

        initialize: function() {
            this.webroot = App.settings.get('webroot') + 'shouts/';

            this.listenTo(this, "change:newShout", this.send);
        },

        fetch: function() {
            $.ajax({
                url: this.webroot + 'index',
                dataType: 'html',
                success: _.bind(function(data) {
                    if (data.length > 0) {
                        this.set({html: data});
                    }
                }, this)
            });
        },

        send: function() {
            $.ajax({
                url: this.webroot + 'add',
                type: "post",
                data: {
                    text: this.get('newShout')
                },
                success: _.bind(function() {
                    this.fetch();
                }, this)
            });
        }
    });

    return ShoutModel;
});

define('views/shouts',[
    'jquery',
    'underscore',
    'backbone',
    'models/app',
    'jqueryAutosize'
], function($, _, Backbone, App, jqueryAutosize) {

    

    var ShoutboxView = Backbone.View.extend({

        isPrerendered: true,

        events: {
            "keyup form": "formUp",
            "keydown form": "formDown"
        },

        initialize: function(options) {
            this.webroot = App.settings.get('webroot') + 'shouts/';
            this.shouts = this.$el.find('.shouts');
            this.textarea =  this.$el.find('textarea');
            this.slidetabModel = options.slidetabModel;

            this.listenTo(App.status, "change:lastShoutId", this.fetch);
            this.listenTo(this.slidetabModel, "change:isOpen", this.fetch);
            this.listenTo(this.model, "change:html", this.render);

            this.textarea.autosize();
        },

        formDown: function(event) {
            if (event.keyCode === 13 && event.shiftKey === false) {
                this.submit();
                this.clearForm();
                event.preventDefault();
            }
        },

        formUp: function() {
            if (this.textarea.val().length > 0) {
                App.eventBus.trigger('breakAutoreload');
            } else if (this.textarea.val().length === 0) {
                App.eventBus.trigger('initAutoreload');
            }
        },

        clearForm: function() {
            this.textarea.val('').trigger('autosize');
        },

        submit: function() {
            this.model.set('newShout', this.textarea.val());
        },

        fetch: function() {
            // update shoutbox only if tab is open
            if(this.slidetabModel.get('isOpen')) {
                this.model.fetch();
            }
        },

        render: function(data) {
            if (this.isPrerendered) {
                this.isPrerendered = false;
            } else {
                $(this.shouts).html(this.model.get('html'));
            }
            return this;
        }

    });

    return ShoutboxView;
});

define('views/slidetab',[
    'jquery',
    'underscore',
    'backbone',
    'models/shout', 'views/shouts'
], function($, _, Backbone, ShoutModel, ShoutsView) {

    

    var SlidetabView = Backbone.View.extend({

        events: {
            "click .slidetab-tab": "clickSlidetab"
        },

        initialize: function(options) {
            this.collection = options.collection;
            this.model.set('isOpen', this.isOpen());

            this.listenTo(this.model, 'change', this.toggleSlidetab);

            if (this.model.get('id') === 'shoutbox') {
                this.initShoutbox();
            }

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
        },

        initShoutbox: function() {
            new ShoutsView({
                el: this.$('#shoutbox'),
                slidetabModel: this.model,
                model: new ShoutModel()
            });
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

require.config({
  // paths necessary until file is migrated into common.js
  paths: {
    // comment to load all common.js files separately from
    // bower_components/ or vendors/
    common: '../dist/common'
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

    var app = {
        bootstrapApp: function(options) {
            require([
                'domReady', 'views/app', 'backbone', 'jquery', 'models/app',
                'views/notification',

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

                    new NotificationView();

                    window.addEventListener('load', function() {
                        new FastClick(document.body);
                    }, false);

                    // init i18n
                    $.i18n.setUrl(App.settings.get('webroot') + "saitos/langJs");

                    appView = new AppView();

                    appReady = function() {
                        appView.initFromDom({
                            SaitoApp: options.SaitoApp,
                            contentTimer: options.contentTimer
                        });
                    };

                    if ($.isReady) {
                        appReady();
                    } else {
                        domReady(function() {
                            appReady();
                        });
                    }

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

    // jquery is already included in the page when require.js starts
    define('jquery', [],function() { return jQuery; });

  require(['common'], function() {
    require(['marionette'], function(Marionette) {
      var Application = new Marionette.Application();
      if (SaitoApp.app.runJsTests === undefined) {
        Application.addInitializer(app.bootstrapApp);
      } else {
        Application.addInitializer(app.bootstrapTest);
      }
      Application.start({
        contentTimer: contentTimer,
        SaitoApp: SaitoApp
      });
    });
  });

})(this, SaitoApp, contentTimer, jasmine);
define("main", function(){});
