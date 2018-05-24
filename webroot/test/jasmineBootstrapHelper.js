import $ from 'jquery';
import 'lib/jquery.i18n/jquery.i18n.extend.js';

// prevent appending of ?_<timestamp> requested urls
$.ajaxSetup({cache: true});

// make empty dict available for test cases
$.i18n.setDict({});
