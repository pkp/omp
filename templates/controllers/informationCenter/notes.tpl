{**
 * notes.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display submission file notes/note form.
 *}
<script type="text/javascript">
	{literal}
	$(function() {
		$('.button').button();
		$('#newNoteForm').ajaxForm({
			dataType: 'json',
	        success: function(returnString) {
	    		if (returnString.status == true) {
		    		$("#newNote").val('');
		    		if($('#noNotes').length != 0) { // This is the first note
						$('#noNotes').hide();
						$('#noNotes').remove();
		    		}
					$(returnString.content).hide().prependTo("#existingNotes").fadeIn();
	    		} else {
	    			var localizedButton = ['{/literal}{translate key="common.ok"}{literal}'];
	    			modalAlert(returnString.content, localizedButton);
	    		}
	        }
	    });
	});
	{/literal}
</script>
<div id="informationCenterNotesTab">
	<div id="newNoteContainer">
		<form name="newNoteForm" id="newNoteForm" action="{url router=$smarty.const.ROUTE_COMPONENT op="saveNote" monographId=$monographId itemId=$itemId itemType=$itemType}" method="post">
			{fbvElement type="textarea" id="newNote" size=$fbvStyles.size.SMALL measure=$fbvStyles.measure.3OF4}<br/>
			<div style="float:right;">{fbvButton type="submit" label="informationCenter.postNote"}</div>
		</form>
	</div>
	<br />
	<hr />
	<div id="existingNotes">
		{iterate from=notes item=note}
			{include file="controllers/informationCenter/note.tpl"}
		{/iterate}
		{if $notes->wasEmpty()}
			<h5 id="noNotes" class="text_center">{translate key="informationCenter.noNotes"}</h5>
		{/if}
	</div>
</div>


