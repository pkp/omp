{**
 * stageParticipantForm.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Submission Participant grid form.
 *
 *}

<script type='text/javascript'>
	$(function() {ldelim}
		$('#addStageParticipant').pkpHandler(
			'$.pkp.controllers.grid.users.stageParticipant.form.StageParticipantFormHandler',
			{ldelim}
				listBuilderUrl: '{url router=$smarty.const.ROUTE_COMPONENT component="listbuilder.users.StageParticipantListbuilderHandler" op="fetch" monographId=$monographId stageId=$stageId escape=false}'
			{rdelim}
		);
	{rdelim});
</script>

<form id="addStageParticipant" method="post" action="#">
	{include file="common/formErrors.tpl"}

	<p>{translate key="submission.submit.addStageParticipant.description"}</p>

	<span style="padding-left:10px;">{fbvSelect name="userGroupId" id="userGroupId" from=$userGroupOptions translate=false}</span>

	{url|assign:submissionParticipantsUrl router=$smarty.const.ROUTE_COMPONENT component="listbuilder.users.StageParticipantListbuilderHandler" op="fetch" userGroupId=$firstUserGroupId monographId=$monographId stageId=$stageId escape=false}
	{load_url_in_div id="submissionParticipantsContainer" url=$submissionParticipantsUrl}

	{fbvFormArea id="buttons"}
	    {fbvFormSection}
	        {fbvButton type="submit" id="submitFormButton" label="common.ok" align=$fbvStyles.align.RIGHT}
	    {/fbvFormSection}
	{/fbvFormArea}
</form>