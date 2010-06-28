{**
 * addReviewFile.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Allows editor to add more file to the review (that weren't added when the submission was sent to review)
 *}

<script type="text/javascript">
	{literal}
	$(function() {
		$('.button').button();
		// Put the 2 file containers in their own accordion groups
		$("#addFileContainer").accordion({
			autoHeight: false,
			collapsible: true
		});
		// Handle upload form
	    $('#uploadForm').ajaxForm({
	        target: '#uploadOutput',  // target identifies the element(s) to update with the server response
			iframe: true,
			dataType: 'json',
			beforeSubmit: function() {
				$('#loading').show();
				$('#loadingText').fadeIn('slow');
	    	},
	        // success identifies the function to invoke when the server response
	        // has been received; here we show a success message and enable the continue button
	        success: function(returnString) {
    			$('#loading').hide();
	    		if (returnString.status == true) {
	    			$('#newFile').attr("disabled", "disabled");
	    			$('#newFileSubmit').button("option", "disabled", true);
	    		}
	    		$('#loadingText').text(returnString.content);  // Set to error or success message
	        }
	    });
	});
	{/literal}
</script>

	
<!-- Current review files -->
<h4>{translate key="editor.submissionArchive.currentFiles" round=$round}</h4>
{url|assign:currentReviewFilesGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.files.reviewFiles.ReviewFilesGridHandler" op="fetchGrid" monographId=$monographId reviewType=$reviewType round=$round escape=false}
{load_url_in_div id="currentReviewFilesGrid" url=$currentReviewFilesGridUrl}

<div id="addFileContainer" style="margin:7px;">
	<h3><a href="#">{translate key="editor.submissionArchive.availableFiles"}</a></h3>
	<div id="existingFilesContainer">
		<form name="addReviewFilesForm" id="addReviewFilesForm" action="{url component="grid.files.reviewFiles.ReviewFilesGridHandler" op="updateReviewFiles" monographId=$monographId|escape reviewType=$reviewType|escape round=$round|escape}" method="post">
			<input type="hidden" name="monographId" value="{$monographId|escape}" />
		
			<!-- Available submission files -->
			{url|assign:availableReviewFilesGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.files.reviewFiles.ReviewFilesGridHandler" op="fetchGrid" isSelectable=1 monographId=$monographId reviewType=$reviewType round=$round escape=false}
			{load_url_in_div id="availableReviewFilesGrid" url=$availableReviewFilesGridUrl}
		</form>
	</div>
	
	<!--  Upload new file -->
	
	<h3><a href="#">{translate key="author.submit.newFile"}</a></h3>
	<div id="newFileContainer">
		<form name="uploadForm" id="uploadForm" action="{url component="grid.files.reviewFiles.ReviewFilesGridHandler" op="uploadReviewFile" monographId=$monographId|escape}"  method="post">
			<input type="file" id="newFile" name="newFile" />
			<input type="submit" name="newFileSubmit" id="newFileSubmit" value="{translate key="common.upload"}" class="button uploadFile" />
		
			<div id="uploadOutput">
				<div id='loading' class='throbber' style="margin: 0px;"></div>
				<ul><li id='loadingText' style='display:none;'>{translate key='submission.loadMessage'}</li></ul>
			</div>
		</form>
	</div>
</div>
