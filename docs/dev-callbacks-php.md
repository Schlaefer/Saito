# PHP #

## Event.Saito.Model.afterConstruct ##

When: after a model class is constructed

Subject: Model object

## Event.Saito.User.afterIgnore ##

When: Fired when a user is ignored

Subject: UserIgnore Model object

Data:

- blockedUserId
- userId

## Request.Saito.User.View.beforeTable ##

When: before table is rendered in users/view

Subject: View Object

Data:

- user: user data as array

Returns: table items to be appended into users/view table

## Request.Saito.Posting.viewFooterItems ##

When: In panel footer of a posting view.

Subject: View Object

Data:

- posting: entry data as array

Returns: items to be inserted in 

## Request.Saito.ThreadLine.beforeRender ##

When: before threadline is rendered

Subject: View object

Data:

- node
- css
- style

Returns:

- append
- css
- prepend
- style

# JS #

## Application Vent.Posting.View.afterRender ##

When: after posting view is rendered and initialized

## Init ##


```
SaitoApp.callbacks.beforeAppInit.push(function() {
	…
});


SaitoApp.callbacks.afterAppInit.push(function() {
	…
});

SaitoApp.callbacks.afterViewInit.push(function() {
	…
});
```


## Deprecated ##

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
