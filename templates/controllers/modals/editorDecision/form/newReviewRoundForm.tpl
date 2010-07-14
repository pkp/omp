{**
 * sendReviewsForm.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Form used to send reviews to author
 *
 *}

{modal_title id="#newRound" key='editor.monograph.newRound' iconClass="fileManagement" canClose=1}

<p>{translate key="editor.monograph.newRoundDescription"}</p>
<form name="newRound" id="newRound" method="post" action="{url op="saveNewReviewRound"}" >
	<input type="hidden" name="monographId" value="{$monographId|escape}" />

	<!-- All submission files -->
	{url|assign:availableReviewFilesGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.files.reviewFiles.ReviewFilesGridHandler" op="fetchGrid" isSelectable=1 monographId=$monographId reviewType=1 round=$round escape=false}
	{load_url_in_div id="availableReviewFilesGrid" url=$availableReviewFilesGridUrl}
</form>

{init_button_bar id="#newRound" cancelId="#cancelButton-newRound" submitId="#okButton-newRound"}
{fbvFormArea id="buttons"}
    {fbvFormSection}
        {fbvLink id="cancelButton-newRound" label="common.cancel"}
        {fbvButton id="okButton-newRound" label="editor.monograph.createNewRound" align=$fbvStyles.align.RIGHT}
    {/fbvFormSection}
{/fbvFormArea}
