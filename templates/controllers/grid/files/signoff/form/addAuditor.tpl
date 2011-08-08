
{**
 * templates/controllers/grid/files/copyedit/addCopyeditingUser.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Allows editor to add a user who should give feedback about copyedited files.
 *}

<script type="text/javascript">
	// Attach the file upload form handler.
	$(function() {ldelim}
		$('#addAuditorForm').pkpHandler(
			'$.pkp.controllers.grid.files.signoff.form.AddAuditorFormHandler'
		);
	{rdelim});
</script>

<div id="addUserContainer">
	<form class="pkp_form" id="addAuditorForm" action="{url op="saveAddAuditor"}" method="post">
		<input type="hidden" name="monographId" value="{$monographId|escape}" />

		<!-- User autocomplete -->
		<div id="userAutocomplete">
			{fbvFormSection}
				{fbvElement type="autocomplete" autocompleteUrl=$autocompleteUrl id="userId-GroupId" name="copyeditUserAutocomplete" label="user.role.copyeditor" value=$userNameString|escape}
			{/fbvFormSection}
		</div>

		<!-- Available copyediting files listbuilder -->
		{url|assign:copyeditingFilesListbuilderUrl router=$smarty.const.ROUTE_COMPONENT component="listbuilder.files.CopyeditingFilesListbuilderHandler" op="fetch" monographId=$monographId}
		{load_url_in_div id="copyeditingFilesListbuilder" url=$copyeditingFilesListbuilderUrl}

		{fbvFormSection}
			{fbvElement type="text" id="responseDueDate" name="responseDueDate" label="editor.responseDueDate" value=$responseDueDate }
		{/fbvFormSection}

		<!-- Message to user -->
		{fbvFormSection}
			{fbvElement type="textarea" name="personalMessage" id="personalMessage" required=true class="required" label="editor.monograph.copyediting.personalMessageTouser" value=$personalMessage size=$fbvStyles.size.MEDIUM}
		{/fbvFormSection}
		{fbvFormButtons}
	</form>
</div>

