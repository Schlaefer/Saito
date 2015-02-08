  Backbone.emulateJSON = true;

  var NavbarPage = Backbone.Model.extend({
    destroy: function() {
      var model = this;
      $.post('destroy', {file: this.get('url')}, function(data) {
        Backbone.Model.prototype.destroy.apply(model, arguments);
      });
    },
    get: function (attr) {
      var method = 'get' + attr.charAt(0).toUpperCase() + attr.slice(1);
      if (typeof this[method] === 'function') {
        return this[method]();
      }
      return Backbone.Model.prototype.get.call(this, attr);
    }
  });
  var NavbarPages = Backbone.Collection.extend({
    model: NavbarPage,
    comparator: function(a, b) {
      var dirnameA = a.get('folder');
      var dirnameB = b.get('folder');
      var basenameA = a.get('file');
      var basenameB = b.get('file');

      //= mix root id entries with folder
      if (dirnameA === '/') {
        dirnameA = basenameA;
      }
      if (dirnameB === '/') {
        dirnameB = basenameB;
      }

      //= sort for folder
      if (dirnameA < dirnameB) {
        return -1;
      } else if (dirnameA > dirnameB ) {
        return 1;
      }

      //= sort for name if folder is equal
      if (basenameA < basenameB) {
        return -1;
      } else if (basenameA > basenameB ) {
        return 1;
      }

      return 0;
    },
    getFolders: function() {
      var folders = new Backbone.Collection;
      this.each(function(model) {
        var folder = model.get('folder');
        if (!folders.get('folder')) {
          folders.add({id: folder});
        }
      });
      return folders;
    }
  });
  var NavbarPageView = Backbone.Marionette.ItemView.extend({
    tagName: 'li',
    ui: {
      'edit': '.post',
      'delete': '.delete',
      'view': '.view'
    },
    events: {
      'click @ui.delete': 'onClickDelete',
      'click @ui.edit': 'onClickEdit',
      'click @ui.view': 'onClickView'
    },
    modelEvents: {
      'destroy': 'onModelDestroy'
    },
    initialize: function() {
      this.listenTo(app.vent, 'editor:shows', this.onEventEditorShows);
    },
    onClickDelete: function(event) {
      event.preventDefault();
      if (!confirm('Are you sure you want to delete this file?')) {
        return;
      }
      this.model.destroy();
    },
    onClickEdit: function(event) {
      event.preventDefault();
      app.commands.execute('editor:show', this.model.get('url'));
    },
    onClickView: function(event) {
      event.preventDefault();
      var url = this.model.get('url');
      var baseUrl = app.request('settings', 'baseUrl');
      window.open(baseUrl + '/' + url, '_blank');
    },
    onEventEditorShows: function(id) {
      if (this.model.get('url') === id) {
        this.$el.addClass('open');
      } else {
        this.$el.removeClass('open');
      }
    },
    onModelDestroy: function() {
      var editorPageId = app.reqres.request('editor:openPageId');
      if (editorPageId === this.model.get('url')) {
        app.reqres.request('editor:clear');
      }
      this.$el.removeClass('open');
    },
    template: function(data) {
      var template = $('#navbarPageView').html();
      return _.template(template, data);
    }
  });
  var NavbarPagesView = Backbone.Marionette.CompositeView.extend({
    childView: NavbarPageView,
    childViewContainer: 'ul',
    template: '#navbarPagesView',
    ui: {
      'new': '.new'
    },
    events: {
      'click @ui.new': 'onClickNew'
    },
    onClickNew: function(event) {
      var collection, id, error, model, success, title;
      event.preventDefault();
      title = prompt('Please enter a file title.');
      if (_.isEmpty(title)) {
        return;
      }

      id = this.model.get('id');
      if (id !== '/') {
        title = id + '/' + title;
      }

      model = new NavbarPage;
      collection = this.collection;

      success = function(model, response, options) {
        collection.add(model);
        app.commands.execute('editor:show', model.get('url'));
      };
      error = function(model, response, options) {
        alert(response.responseJSON.error);
      };
      model.save(
        {title: title},
        {error: error, success: success, url: 'create'}
      );
    }
  });
  var NavbarFoldersView = Backbone.Marionette.CollectionView.extend({
    childView: NavbarPagesView,
    pages: null,
    initialize: function(options) {
      this.pages = options.pages;
    },
    childViewOptions: function(model, index) {
      var collection = new NavbarPages(this.pages.where({folder: model.get('id')}));
      return {
        collection: collection
      }
    }
  });

  var ControlsView = Backbone.Marionette.ItemView.extend({
    template: '#controlsView'
  });

  var EditorModel = Backbone.Model.extend({
    defaults: {
      content: null,
      // ID of currently shown page
      show: null,
      unsaved: false
    },
    initialize: function() {
      app.reqres.setHandler('editor:openPageId', _.bind(function() {
        return this.get('show');
      }, this));
      this.listenTo(this, 'change:show', _.bind(function() {
        app.vent.trigger('editor:shows', this.get('show'));
      }, this));
    }
  });
  var EditorView = Backbone.Marionette.ItemView.extend({
    config: null,
    editor: null,
    id: 'epiceditor',
    template: _.template('<div></div>'),
    modelEvents: {
      'change:show': 'onModelChangeShow',
      'change:unsaved': 'onModelChangeUnsaved'
    },
    initialize: function(options) {
      this.config = options.config;
      app.commands.setHandler('editor:show', _.bind(this.onEditPage, this));
      app.reqres.setHandler('editor:clear', _.bind(this.onEditorClear, this));
    },
    onDomRefresh: function() {
      // EpicEditor needs root element rendered in document-DOM for init
      this._initEditor();
    },
    _initEditor: function() {
      this.config.container = this.el;
      var editor = this.editor = new EpicEditor(this.config).load();

      // mark as dirty
      $(editor.getElement('editor')).on('keyup', _.bind(function() {
        this.onKeyUp();
      }, this));

      // Save on preview
      editor.on('preview', function() { editor.emit('autosave'); });

      //= autosave
      var success = _.bind(function(model, response, options) {
        this.model.set('unsaved', false);
        this.endSavingDialogSuccess();
      }, this);
      var error = _.bind(function(model, response, options) {
        this.endSavingDialogError();
      }, this);
      editor.on('autosave', _.bind(function() {
        this.startSavingDialog();
        this.model.save(
          {content: editor.exportFile()},
          {error: error, success: success, url: 'save'}
        );
      }, this));

      //= resize
      var resize = _.bind(function() {
        this.$el.height($(window).height());
        editor.reflow();
      }, this);
      resize();
      $(window).resize(resize);

      editor.unload();
    },
    onModelChangeShow: function(model, url) {
      var editor = this.editor;

      //= empty editor
      if (url === null) {
        editor.importFile('epiceditor', '');
        editor.unload();
        return;
      }

      //= load file
      // @todo
      $.post('open', {file: url}, function(data) {
        editor.load();
        editor.importFile('epiceditor', data);
        if (!editor.is('edit')) {
          editor.edit();
        }

      });
    },
    onModelChangeUnsaved: function(model) {
      var unsaved = model.get('unsaved');
      if (unsaved) {
        document.title += ' *';
      } else {
        document.title = document.title.replace(' *', '');
      }
    },
    onEditorClear: function() {
      this.model.set({ show: null, unsaved: false });
    },
    onEditPage: function(id) {
      var unsaved = this.model.get('unsaved');
      if (unsaved) {
        var confirmed =  confirm('You have unsaved changes. Are you sure you want to leave this post?');
        if (!confirmed) {
          return false;
        }
      }
      this.model.set('show', id);
      this.model.set('unsaved', false);
    },
    onKeyUp: function() {
      var unsaved = this.model.get('unsaved');
      if (!unsaved) {
        this.model.set('unsaved', true);
      }
    },
    startSavingDialog: function() {
      $('#saving').css('display', 'block').text('Saving...').addClass('active');
    },
    endSavingDialogError: function() {
      $('#saving').text('Saving to server failed!')
        .addClass('error');
      setTimeout(function() {
        $('#saving').removeClass('active error');
      }, 2000);
    },
    endSavingDialogSuccess: function() {
      $('#saving').text('Saved')
        .addClass('success');
      setTimeout(function() {
        $('#saving').removeClass('active success');
      }, 1000);
    }
  });

  var AppView = Backbone.Marionette.LayoutView.extend({
    el: '#app',
    regions: {
      controls: '#controls',
      editor: '#editor',
      sideBar: '#nav'
    }
  });
  var App = Marionette.Application.extend({
    settings: null,
    _initSettings: function(settings) {
      var settings = this.settings = new Backbone.Model(settings);
      this.reqres.setHandler('settings', function(key) {
        return settings.get(key);
      });
    }
  });
  var app = window.app = new App;
  app.on('start', function(options) {
    var pages = new NavbarPages(options.pages);
    var folders = pages.getFolders();
    var appView = new AppView();
    var editor = new EditorModel;
    var editorView = new EditorView({ config: options.editorConfig, model: editor });

    appView.getRegion('controls')
      .show(new ControlsView({collection: pages}));
    appView.getRegion('editor').show(editorView);
    appView.getRegion('sideBar')
      .show(new NavbarFoldersView({collection: folders, pages: pages}));

    this._initSettings(options.settings);

    // layout
    var resize = function() {
      $('body,#main').height($(window).height());
    };
    resize();
    $(window).resize(resize);
  });
