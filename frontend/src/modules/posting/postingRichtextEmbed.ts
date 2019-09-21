import { Model } from 'backbone';
import { View } from 'backbone.marionette';
import * as _ from 'underscore';

class PostingRichtextEmbedModel extends Model {
    public defaults() {
        return {
            description: null,
            html: null,
            image: null,
            providerIcon: null,
            providerName: null,
            providerUrl: null,
            title: null,
        };
    }
}

class PostingRichtextEmbedView extends View<Model> {
    /**
     * Ma
     */
    public onRender() {
        const html = this.model.get('html');
        if (html) {
            /// append included script tags so that they are executed
            const scriptTags = html.match(/<script[\s\S]*?>([\s\S]*?)<\/script>/g);
            this.$el.html(html);
            _.each(scriptTags, (scriptTag: string) => {
                // find src-attribute in script-tag
                const src = scriptTag.match(/<script.*?src=['"](.*?)['"].*?>/);
                if (!src) {
                    return;
                }
                const executedScriptTag = document.createElement('script');
                executedScriptTag.async = true;
                executedScriptTag.src = src[1];
                executedScriptTag.type = 'text/javascript';
                this.$el.append(executedScriptTag);
            });
        }
    }

    /**
     * Ma
     */
    public getTemplate() {
        // HTML is provided (oembed)
        if (this.model.get('html')) {
            return _.noop;
        }

        // not enough information for widget, just render a link
        if (!this.model.get('title') && !this.model.get('description') && !this.model.get('image')) {
            return _.template('<a href="<%= url %>" target="_blank"><%= url %></a>');
        }

        // main template
        return _.template(`
<div class="card richtext-embed">
    <% if (image) { %>
        <img src="<%= image %>" class="card-img-top">
    <% } %>
    <div class="card-body">
        <% if (title) { %>
            <a href="<%= url %>" class="card-title" target="_blank">
                <h5><%- title %></h5>
            </a>
        <% } %>
        <p class="card-text"><%- description %></p>
        <% if (providerIcon || providerName) { %>
            <div class="richtext-embed-provider">
                <% if (providerIcon) { %>
                    <img src="<%= providerIcon %>" class="richtext-embed-provider-icon">
                <% } %>
                <% if (providerName) { %>
                    <% if (providerUrl) { %><a href="<%= providerUrl %>" target="_blank"><% } %>
                        <%- providerName %>
                    <% if (providerUrl) { %></a><% } %>
                <% } %>
            </div>
        <% } %>
    </div>
</div>
        `);

    }
}

export {
    PostingRichtextEmbedModel,
    PostingRichtextEmbedView,
};
