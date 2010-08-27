<!-- templates/controllers/modals/editorDecision/form/initiateReviewForm.tpl -->

{**
 * initiateReviewForm.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Form used to initiate the first review round.
 *
 *}
{assign var='timeStamp' value=$smarty.now}
{modal_title id="#initiateReview-$timeStamp" key='editor.monograph.initiateReview' iconClass="fileManagement" canClose=1}

<p>{translate key="editor.monograph.initiateReviewDescription"}</p>
<form name="initiateReview" id="initiateReview-{$timeStamp}" method="post" action="{url op="saveInitiateReview"}" >
	<input type="hidden" name="monographId" value="{$monographId|escape}" />

	<!-- Available submission files -->
	{url|assign:availableReviewFilesGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.files.reviewFiles.ReviewFilesGridHandler" op="fetchGrid" isSelectable=1 canUpload=1 monographId=$monographId reviewType=$reviewType round=$round escape=false}
	{load_url_in_div id="availableReviewFilesGrid-$timeStamp" url=$availableReviewFilesGridUrl}
</form>

{init_button_bar id="#initiateReview-$timeStamp" cancelId="#cancelButton-initiateReview-$timeStamp" submitId="#okButton-initiateReview-$timeStamp"}
{fbvFormArea id="buttons"}
    {fbvFormSection}
        {fbvLink id="cancelButton-initiateReview-$timeStamp" label="common.cancel"}
        {fbvButton id="okButton-initiateReview-$timeStamp" label="editor.monograph.createNewRound" align=$fbvStyles.align.RIGHT}
    {/fbvFormSection}
{/fbvFormArea}

<!-- / templates/controllers/modals/editorDecision/form/initiateReviewForm.tpl -->

