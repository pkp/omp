{**
 * sendReviewsForm.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Form used to send reviews to author
 *
 *}

{modal_title id="#resubmit" key='editor.monograph.decision.resubmit' iconClass="fileManagement" canClose=1}

<p>{translate key="editor.monograph.decision.resubmitDescription"}</p>
<form name="resubmit" id="resubmit" method="post" action="{url op="saveResubmit"}" >
	<input type="hidden" name="monographId" value="{$monographId|escape}" />

	<!-- All submission files -->
	{url|assign:availableReviewFilesGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.files.reviewFiles.ReviewFilesGridHandler" op="fetchGrid" isSelectable=1 canUpload=1 monographId=$monographId reviewType=$reviewType round=$round escape=false}
	{load_url_in_div id="#availableReviewFilesGrid" url=$availableReviewFilesGridUrl}

	<!-- Listbuilder of current reviewers (allow the editor to control which reviewers automatically get added to next round) -->
	{url|assign:reSelectReviewersUrl router=$smarty.const.ROUTE_COMPONENT component="listbuilder.users.ReSelectReviewersListbuilderHandler" op="fetch" monographId=$monographId reviewType=$reviewType round=$round escape=false}
	{load_url_in_div id="#reSelectReviewersContainer" url=$reSelectReviewersUrl}
</form>
