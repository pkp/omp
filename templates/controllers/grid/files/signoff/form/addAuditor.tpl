{**
 * templates/controllers/grid/files/signoff/form/addAuditor.tpl
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
		{if $publicationFormatId}
			<input type="hidden" name="publicationFormatId" value="{$publicationFormatId|escape}" />
		{/if}

		<!-- User autocomplete -->
		<div id="userAutocomplete">
			{fbvFormSection}
				{fbvElement type="autocomplete" autocompleteUrl=$autocompleteUrl id="userId-GroupId" name="copyeditUserAutocomplete" label="editor.monograph.addAuditor" value=$userNameString|escape}
			{/fbvFormSection}
		</div>

		<!-- Available files listbuilder -->
		{if $fileStage == $smarty.const.MONOGRAPH_FILE_COPYEDIT}
			{url|assign:filesListbuilderUrl router=$smarty.const.ROUTE_COMPONENT component="listbuilder.files.CopyeditingFilesListbuilderHandler" op="fetch" monographId=$monographId}
			{assign var="filesListbuilderId" value="copyeditingFilesListbuilder"}
		{else $fileStage == $smarty.const.MONOGRAPH_FILE_PROOF}
			{url|assign:filesListbuilderUrl router=$smarty.const.ROUTE_COMPONENT component="listbuilder.files.ProofFilesListbuilderHandler" op="fetch" monographId=$monographId publicationFormatId=$publicationFormatId escape=false}
			{assign var="filesListbuilderId" value="proofFilesListbuilder"}
		{/if}

		{load_url_in_div id=$filesListbuilderId url=$filesListbuilderUrl}

		{fbvFormSection}
			{fbvElement type="text" id="responseDueDate" name="responseDueDate" label="editor.responseDueDate" value=$responseDueDate }
		{/fbvFormSection}

		<!-- Message to user -->
		{fbvFormSection}
			{fbvElement type="textarea" name="personalMessage" id="personalMessage" required=true class="required" label="editor.monograph.copyediting.personalMessageTouser" value=$personalMessage size=$fbvStyles.size.MEDIUM}
		{/fbvFormSection}

		<!-- skip email checkbox -->
		{fbvFormSection for="skipEmail" size=$fbvStyles.size.MEDIUM list=true}
			{fbvElement type="checkbox" id="skipEmail" name="skipEmail" label="editor.monograph.fileAuditor.skipEmail"}
		{/fbvFormSection}
		{fbvFormButtons}
	</form>
</div>

