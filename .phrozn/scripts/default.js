$(document).ready(function() { 
  $("table").tablesorter({
    sortList: [[2,1], [1,1]],
    textExtraction: function(node) { 
      return node.innerHTML.replace(/€/g, ''); 
    }
  })
    .addClass('tablesorter table table-striped');
});
