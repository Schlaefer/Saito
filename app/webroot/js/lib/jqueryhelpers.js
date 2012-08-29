jQuery.fn.isScrolledIntoView = function() {
	var elem = $(this[0]);
	var args = arguments[0] || {};

	if ($(elem).length == 0) return true;
	var docViewTop = $(window).scrollTop();
	var docViewBottom = docViewTop + $(window).height();

	var elemTop = $(elem).offset().top;
	var elemBottom = elemTop + $(elem).height();

	return ((elemBottom >= docViewTop) && (elemTop <= docViewBottom));
};
