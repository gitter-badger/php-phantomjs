/**
 * Created by iEC on 3/23/2015.
 */
var page = require('webpage').create();
var url = require('system').args[1];
var errors = [];

page.onError = function(msg, trace){
	var msgStack = ['ERROR: ' + msg];

	if (trace && trace.length) {
		msgStack.push('TRACE:');
		trace.forEach(function(t) {
			msgStack.push(' -> ' + t.file + ': ' + t.line + (t.function ? ' (in function "' + t.function +'")' : ''));
		});
	}
	errors.push(msgStack);
};

page.open(url, function () {
	console.log(JSON.stringify(errors));
	phantom.exit();
});