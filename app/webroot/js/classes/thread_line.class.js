/**
 * Class ThreadLine
 *
 * A single thread/line in a thread tree
 */
function ThreadLine(id) {
  this.id = id;

  this.id_btn_show_thread = '#btn_show_thread_' + id;
  this.id_btn_hide_thread = '#btn_hide_thread_' + id;
  this.id_thread_line			= '.thread_line.' + id;
  this.id_thread_inline		= '.thread_inline.' 	+ id;
  this.id_thread_slider		=	'#t_s_' + id;
  this.id_bottom					= '#posting_formular_slider_bottom_' + id;
};

/**
 * if the line is not in the browser windows at the moment
 * scroll to that line and highlight it
 */
ThreadLine.prototype.scrollLineIntoView = function () {
  var p = this;
  if (!_isScrolledIntoView(this.id_thread_line)) {
    $(window).scrollTo(
      this.id_thread_line,
      400,
      {
        'offset': -40,
        easing: 'swing',
        onAfter: function() {
          $(p.id_thread_line).effect(
            "highlight",
            {
              times: 1
            },
            3000);
        } //end onAfter
      }
      );
  }
};

/**
 * shows and hides the element that contains an inline posting
 */
ThreadLine.prototype.toggle_inline_view = function (scroll) {
  if (typeof scroll == 'undefined' ) scroll = true;
  var id = this.id;
  var p  = this;
  if ($(p.id_thread_inline).css('display') != 'none') {
    // hide inline posting
    p.closeInlineView(scroll);
  }
  else {
    // show inline posting
    p.showInlineView(false);
  }
};

ThreadLine.prototype.showInlineView = function (scroll) {
  var id = this.id;
  var p  = this;
  if (typeof scroll == 'undefined' ) scroll = true;
  $(p.id_thread_line).fadeOut(
    100,
    function() {
      // performance: show instead slide
      // $(p.id_thread_inline).slideDown(null,

      $(p.id_thread_inline).show(0,
        function() {
          if (scroll && !_isScrolledIntoView(p.id_bottom)) {
            if(_isHeigherThanView(this)) {
              scrollToTop(this);
            }
            else {
              scrollToBottom(p.id_bottom);
            }
          }
        }
        );
    }
    );
};

ThreadLine.prototype.closeInlineView = function(scroll) {
  var id = this.id;
  var p  = this;
  if (typeof scroll == 'undefined' ) scroll = true;
  $(this.id_thread_inline).slideUp(
    'fast',
    function() {
      $(p.id_thread_line).slideDown();
      if (scroll) {
        p.scrollLineIntoView();
      }
    }
    );
};

/**
 * loads a posting inline via ajax and shows it
 */
ThreadLine.prototype.load_inline_view = function (scroll) {
  if (typeof scroll == 'undefined' ) scroll = true;
  var id = this.id;
  var p = this;

  if ($(p.id_thread_inline).length === 0) {
    var spinner = '<div class="thread_inline '+id+'"> <div data-id="'+id+'" class="btn-strip btn-strip-top">&nbsp;</div><div id="t_s_'+id+'" class="t_s"><div class="spinner"></div></div> </div>';
    $(p.id_thread_line).after(spinner);
    jQuery.ajax(
    {
      beforeSend:function(request) {
        request.setRequestHeader('X-Update', 't_s_' + id );
        p.toggle_inline_view(scroll);
      },
      complete:function(request, textStatus) {
        // show inline posting
        p.showInlineView(scroll);
      },
      success:function(data, textStatus) {
        jQuery( p.id_thread_slider ).html(data);
        initViewPosting(id);
				history.pushState(null, '', $(p.id_thread_line).find('a.thread_line-content').attr('href'));
      },
      async:true,
      type:'post',
      url: webroot + 'entries/view/'  + id
    }
    );
  }
  else {
    p.toggle_inline_view(scroll);
  }
};

/**
 * Adds an new thread as answer after the current and fills it with `data`
 */
ThreadLine.prototype.insertNewLineAfter = function (data) {
  this.toggle_inline_view();
  $('<li>'+data+'</li>').insertAfter('#ul_thread_' + this.id + ' > li:last-child');
  initViewLine();
};