/*global Backbone jQuery _ Markdown */

(function($, _, Backbone) {

    "use strict";

    var AppModel = Backbone.Model.extend({
        base: "",
        fetch: function() {
            $.ajax({
                url: this.base + this.id,
                dataType: "html",
                async: false,
                error: _.bind(function() {
                    this.set("source", this.id + " not found");
                }, this),
                success: _.bind(function(data) {
                    this.set("source", data);
                }, this)
            });
        }
    });

    var AppPage = AppModel.extend({base: "pages/"});
    var AppElement = AppModel.extend({base: "elements/"});
    var AppLayout = AppModel.extend({base: "layouts/"});
    var AppElements = Backbone.Collection.extend({model: AppElement});
    var AppPages = Backbone.Collection.extend({model: AppPage});
    var AppLayouts = Backbone.Collection.extend({model: AppLayout});

    var AppView = Backbone.View.extend({

        el: "body",
        tpl: "layout.html",
        headTpl: "html-head.tpl",

        initialize: function(options) {
            this.pages = options.pages;
            this.elements = options.elements;
            this.layouts = options.layouts;

            this.setHead();
        },

        setHead: function() {
            $("head").append(this.getElement('html-head'));
        },

        setTitle: function(newTitle) {
            var base,
                title;

            base = $("title").data("default") || "";

            if (base.length > 0) {
                title = base;
                if (newTitle.length > 0) {
                    title = newTitle + " - " + title;
                }
            } else {
                title = newTitle;
            }
            $("title").html(title);
        },

        setPage: function(pageId) {
              if (_.isString(pageId) === false || pageId.length === 0) {
                  pageId = "index";
              }
              this.pageId = pageId;
              this.pageId = this.pageId + ".md";
              this.fetchPage();
              return this;
         },

        getElement: function(id) {
            return this.getTemplate(id, this.elements);
        },

        getLayout: function(id) {
            return this.getTemplate(id, this.layouts);
        },

        getTemplate: function(id, collection) {
            var template;

            id = id + ".tpl";
            template = collection.get(id);
            if (template === undefined) {
                collection.add({
                    id: id,
                    isNew: false
                });
                template = collection.get(id);
                template.fetch();
            }
            return template.get('source');
        },

        fetchPage: function() {
            if (this.pages.get(this.pageId) === undefined) {
                this.pages.add(new AppPage({
                    id: this.pageId,
                    isNew: false
                }));
                this.pages.get(this.pageId).fetch();
             }
             return this;
          },

        replaceElements: function(content, preloaded) {
            var elementRegex,
                replace;

            elementRegex = /\{\{(.*?)\}\}/g;

            replace = _.bind(function(match, name) {
                    var out;
                    if (preloaded !== undefined && preloaded[name] !== undefined) {
                        out = (preloaded[name]);
                    } else {
                        out = (this.getElement(name));
                    }
                    return out;
                }, this);

            while(content.match(elementRegex)) {
                content = content.replace(/\{\{(.*?)\}\}/g, replace);
            }
            return content;
        },

        extractMetaData: function(content) {
            var meta,
                extract,
                defaults;

            meta = {};
            defaults = {
                layout: "default"
            };

            extract = function(content, regex) {
                var out;
                out = content.match(regex);
                if (out !== null) {
                    out = out[1];
                }
                return out;
            };

            meta.layout = extract(content, /layout:\s+?(.*?)[\s\n\r]/);
            meta.title = extract(content, /title:\s+?(.*?)[\n\r]/);

            meta.layout = meta.layout || "default";
            meta.title = meta.title || "";

            meta = _.extend(defaults, meta);

            content = content.replace(/[\s\S]*?---/, '');

            this.pages.get(this.pageId).set(meta);
            return content;
        },

        render: function() {
            var content,
                pageData,
                layout;

            // simple caching
            pageData =  this.pages.get(this.pageId);

            if (_.isString(pageData.get('html')) === false) {
                content = pageData.get('source');
                content = this.extractMetaData(content);
                content = Markdown(content);
                content = this.replaceElements(content);

                layout = this.getLayout(pageData.get('layout'));
                content = this.replaceElements(layout, {content: content});

                content = _.template(content)({
                    title: pageData.get('title')
                });

                pageData.set('html', content);
            } else {
                content = pageData.get('html');
            }

            this.setTitle(pageData.get('title'));
            this.$el.html(content);
            this.$el.css('display', 'block');
            return this;
        }

    });

    var AppRouter = Backbone.Router.extend({

        routes: {
            "*page": "page"
        },

        initialize: function() {
            var elements = new AppElements();
            var pages = new AppPages();
            var layouts = new AppLayouts();
            this.appView = new AppView({
                elements: elements,
                pages: pages,
                layouts: layouts
            });
        },

        page: function(page) {
            this.appView.setPage(page).render();
        }
    });

    var appRouter = new AppRouter();

    Backbone.history.start();

})(jQuery, _, Backbone);
