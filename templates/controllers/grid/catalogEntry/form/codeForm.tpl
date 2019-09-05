{**
 * templates/controllers/grid/catalogEntry/form/codeForm.tpl
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Identification Code form.
 *}

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#addIdentificationCodeForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

<form class="pkp_form" id="addIdentificationCodeForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT component="grid.catalogEntry.IdentificationCodeGridHandler" op="updateCode"}">
	{csrf}
	<input type="hidden" name="submissionId" value="{$submissionId|escape}" />
	<input type="hidden" name="publicationId" value="{$publicationId|escape}" />
	<input type="hidden" name="representationId" value="{$representationId|escape}" />
	<input type="hidden" name="identificationCodeId" value="{$identificationCodeId|escape}" />
	{fbvFormArea id="addCode"}
		{fbvFormSection title="grid.catalogEntry.identificationCodeValue" for="value" required="true"}
			{fbvElement type="text" id="value" value=$value size=$fbvStyles.size.MEDIUM required="true"}
		{/fbvFormSection}
		{fbvFormSection title="grid.catalogEntry.identificationCodeType" for="code" required="true" size=$fbvStyles.size.MEDIUM}
			{fbvElement type="select" from=$identificationCodes selected=$code id="code" translate=false required="true"}
		{/fbvFormSection}
		{fbvFormButtons}
	{/fbvFormArea}
</form>
<p><span class="formRequired">{translate key="common.requiredField"}</span></p>
