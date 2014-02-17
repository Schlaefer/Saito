# Customizing and Theming #

## Mobile Version ##

### CSS ###

You may overwrite the default css by using `app/Plugin/M/webroot/dist/theme.css`.

You should create a corresponding file in your theme directory and edit it there: `<theme-dir>/webroot/M/dist/theme.css`

### Append to the header ###

You may append additional content to the `<head>`-tag by adding it to `app/Plugin/M/View/Elements/custom_html_header.ctp`.

You should create a corresponding file in your theme directory and edit it there: `<theme-dir>/Plugin/M/Elements/custom_html_header.ctp`.

### Callbacks ###

The following callbacks are available and may be set in `custom_html_header.ctp`:

    <script>
      window.Saito.callbacks = {
        afterEntriesIndex: function() {
          …
        },
        afterEntriesMix: function() {
          …
        },
        afterAppmenu: function() {
          …
        }
      }
    </script>
