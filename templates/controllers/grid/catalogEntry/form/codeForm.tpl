{**
 * templates/controllers/grid/catalogEntry/form/codeForm.tpl
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
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
	<input type="hidden" name="submissionId" value="{$submissionId|escape}" />
	<input type="hidden" name="publicationFormatId" value="{$publicationFormatId|escape}" />
	<input type="hidden" name="identificationCodeId" value="{$identificationCodeId|escape}" />
	{fbvFormArea id="addCode"}
		{fbvFormSection title="grid.catalogEntry.identificationCodeValue" for="value" required="true"}
			{fbvElement type="text" id="value" value=$value size=$fbvStyles.size.MEDIUM}
		{/fbvFormSection}
		{fbvFormSection title="grid.catalogEntry.identificationCodeType" for="code" required="true" size=$fbvStyles.size.MEDIUM}
			{fbvElement type="select" from=$identificationCodes selected=$code id="code" translate=false}
		{/fbvFormSection}
		{fbvFormButtons}
	{/fbvFormArea}
</form>
<p><span class="formRequired">{translate key="common.requiredField"}</span></p>
