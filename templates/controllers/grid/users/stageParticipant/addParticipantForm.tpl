{**
 * templates/controllers/grid/users/stageParticipant/addParticipantForm.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Form that holds the stage participants list
 *
 *}

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#addParticipantForm').pkpHandler('$.pkp.controllers.grid.users.stageParticipant.form.AddParticipantFormHandler');
	{rdelim});
</script>

<p>{translate key="editor.monograph.addStageParticipant.description"}</p>
<form class="pkp_form" id="addParticipantForm" action="{url op="saveParticipant"}" method="post">
	{fbvFormArea id="addParticipant"}
		<input type="hidden" name="monographId" value="{$monographId|escape}" />
		<input type="hidden" name="stageId" value="{$stageId|escape}" />
		{fbvFormSection title="user.group"}
			{fbvElement type="select" id="userGroupId" from=$userGroupOptions translate=false size=$fbvStyles.size.MEDIUM}
		{/fbvFormSection}
		{fbvFormSection title="user.name"}
			{url|assign:addParticipantUserUrl op="userAutocomplete" monographId=$monographId stageId=$stageId userGroupId=$selectedUserGroupId escape=false}
			{fbvElement type="autocomplete"  id="userId" autocompleteUrl=$addParticipantUserUrl size=$fbvStyles.size.MEDIUM}
		{/fbvFormSection}
		{fbvFormButtons}
	{/fbvFormArea}
</form>
