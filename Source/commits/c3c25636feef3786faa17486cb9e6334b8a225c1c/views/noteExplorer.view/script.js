var jq = jQuery.noConflict();
jq(document).one("ready", function() {
	// Search notes
	jq(document).on("keyup", ".noteExplorer .searchContainer .searchInput", function(ev) {
		// Get input and search notes
		var search = jq(this).val();
		searchNotes(search);
	});
	
	// Enable search
	jq(document).on("focusin", ".noteExplorer .searchContainer .searchInput", function(ev) {
		// Get input and search notes
		var search = jq(this).val();
		searchNotes(search);
	});
	
	// Search all projects
	function searchNotes(search) {
		// If search is empty, show all notes
		if (search == "")
			jq(".noteExplorer .noteList .ntrow").show();
		
		// Create the regular expression
		var regEx = new RegExp(jq.map(search.trim().split(' '), function(v) {
			return '(?=.*?' + v + ')';
		}).join(''), 'i');
		
		// Select all note rows, hide and filter by the regex then show
		jq(".noteExplorer .noteList .ntrow").hide().find(".nttitle").filter(function() {
			return regEx.exec(jq(this).text());
		}).each(function() {
			jq(this).closest(".ntrow").show();
		});
	}
});