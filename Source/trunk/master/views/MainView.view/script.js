var jq = jQuery.noConflict();
jq(document).one("ready", function() {
	// Update the time
	setInterval(function() {
		// Get current time
		var current_time = Math.round((new Date).getTime());
		var context = (new Date(current_time)).format("F d, Y, H:i");
		jq(".noteOuterContainer .current_time").html(context);
	}, 30000);
});