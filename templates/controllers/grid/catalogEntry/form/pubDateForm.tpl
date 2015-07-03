{**
 * templates/controllers/grid/catalogEntry/form/pubDateForm.tpl
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Publication Date form.
 *}

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#addPubDateForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

<form class="pkp_form" id="addPubDateForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT component="grid.catalogEntry.PublicationDateGridHandler" op="updateDate"}">
	<input type="hidden" name="submissionId" value="{$submissionId|escape}" />
	<input type="hidden" name="representationId" value="{$representationId|escape}" />
	<input type="hidden" name="publicationDateId" value="{$publicationDateId|escape}" />
	{fbvFormArea id="addDate"}
		{fbvFormSection title="grid.catalogEntry.dateValue" for="date" required="true"}
			{fbvElement type="text" id="date" value=$date size=$fbvStyles.size.MEDIUM}
		{/fbvFormSection}
		{fbvFormSection title="grid.catalogEntry.dateFormat" for="dateFormat" required="true" size=$fbvStyles.size.MEDIUM}
			{fbvElement type="select" from=$publicationDateFormats selected=$dateFormat id="dateFormat" translate=false}
		{/fbvFormSection}
		{fbvFormSection title="grid.catalogEntry.dateRole" for="role" required="true" size=$fbvStyles.size.MEDIUM}
			{fbvElement type="select" from=$publicationDateRoles selected=$role id="role" translate=false}
		{/fbvFormSection}
		{fbvFormButtons}
	{/fbvFormArea}
</form>
<p><span class="formRequired">{translate key="common.requiredField"}</span></p>
