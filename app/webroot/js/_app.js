function _isScrolledIntoView(elem) {
	if ($(elem).length == 0) return true;
	var docViewTop = $(window).scrollTop();
	var docViewBottom = docViewTop + $(window).height();

	var elemTop = $(elem).offset().top;
	var elemBottom = elemTop + $(elem).height();

	return ((elemBottom >= docViewTop) && (elemTop <= docViewBottom));
};

function _isHeigherThanView(elem)
{
	return ($(window).height() <= $(elem).height())	;
};

function scrollToBottom(elem) {
	$(window).delay(1600).scrollTo(elem, 300, {
		'offset': -$(window).height()+30,
		easing: 'swing'
	})
};

function scrollToTop(elem) {
	$(window).scrollTo(elem , 300, {
		'offset': 0,
		easing: 'swing'
	});
};
