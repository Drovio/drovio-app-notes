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
	
	
	// Reload note list
	jq(document).on("notes.list.reload", function() {
		jq(".noteExplorerContainer").trigger("reload");
	});
	
	// Set interval to reload the notes every 5 or 10 seconds
	setInterval(function() {
		jq(".noteExplorerContainer").trigger("reload");
	}, 5000);
	
	
	// Clean note container
	jq(document).on("notes.remove", function(ev, noteID) {
		// Remove ntrow
		var ntrow = jq(".noteExplorer .ntrow#"+noteID);
		if (ntrow.hasClass("selected"))
			jq(".noteContainer").html("");
		
		// Remove row
		ntrow.remove();
		
		// Check if there are note rows remaining
		if (jq(".ntrow").length == 0)
			jq(".noteExplorerContainer").trigger("reload");
	});
	
	// Trigger events when the content is modified
	jq(document).on("content.modified", function() {
		// Get note editor note id and select row (if any)
		var noteID = jq(".noteEditorContainer").attr("id");
		jq(".ntrow#"+noteID).addClass("selected");
		
		// Set listeners for all remote note forms
		jq(".ntrow .removeNoteForm").off("submit");
		jq(".ntrow .removeNoteForm").on("submit", function(ev) {
			// Confirm to delete the note
			return confirmRemoveNote(ev);
		});
	});
	
	jq(".ntrow .removeNoteForm").on("submit", function(ev) {
		// Confirm to delete the note
		return confirmRemoveNote(ev);
	});
	
	function confirmRemoveNote(ev) {
		// Confirm to delete the note
		var status = confirm("Are you sure you want to delete this note?");
		if (!status) {
			ev.preventDefault();
			return false;
		}
	}
});