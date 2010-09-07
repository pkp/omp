{**
 * stageParticipantForm.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Submission Participant grid form
 *
 *}

{modal_title id="#addStageParticipant" key="submission.submit.addStageParticipant" iconClass="fileManagement" canClose=1}

{literal}
<script type='text/javascript'>
	// Handle the user group drop-down change event
	$(function(){
		$('#userGroupId').change(function() {
			$.post(
				'{/literal}{url router=$smarty.const.ROUTE_COMPONENT component="listbuilder.users.StageParticipantListbuilderHandler" op="fetch"}{literal}',

				$(this.form).serialize(),
				function(jsonData) {
					if (jsonData.status == false) {
						// Display error message (if any)
						alert(jsonData.content);
					} else {
						// Load new listbuilder into #submissionParticipantsContainer
						$("#submissionParticipantsContainer").html(jsonData.content);
					}
				},
				"json"
			);
		});
	});
</script>
{/literal}

<form name="addStageParticipantForm" id="addStageParticipant" method="post" action="{url op="saveStageParticipant" monographId=$monographId}">
	{include file="common/formErrors.tpl"}

	<p>{translate key="submission.submit.addStageParticipant.description"}</p>

	<span style="padding-left:10px;">{fbvSelect name="userGroupId" id="userGroupId" from=$userGroupOptions translate=false}</span>

	{url|assign:submissionParticipantsUrl router=$smarty.const.ROUTE_COMPONENT component="listbuilder.users.StageParticipantListbuilderHandler" op="fetch" userGroupId=$firstUserGroupId monographId=$monographId stageId=$stageId escape=false}
	{load_url_in_div id="submissionParticipantsContainer" url=$submissionParticipantsUrl}

{if $monographId}
	<input type="hidden" name="monographId" value="{$monographId|escape}" />
{/if}
</form>

{init_button_bar id="#addStageParticipant"}
