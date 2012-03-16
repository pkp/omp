{**
 * templates/controllers/grid/catalogEntry/form/representativeForm.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Supplier form.
 *}

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#representativeForm').pkpHandler('$.pkp.controllers.modals.catalogEntry.form.RepresentativeFormHandler');
	{rdelim});
</script>

<form class="pkp_form" id="representativeForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT component="grid.catalogEntry.RepresentativesGridHandler" op="updateRepresentative"}">
	{include file="controllers/notification/inPlaceNotification.tpl" notificationId="representativesFormNotification"}
	<input type="hidden" name="monographId" value="{$monographId|escape}" />
	<input type="hidden" name="representativeId" value="{$representativeId|escape}" />
	{fbvFormArea id="addSupplier"}
		{fbvFormSection list="true" title="grid.catalogEntry.representativeType" required="true"}
			{fbvElement type="radio" id="agent" name="isSupplier" value="0" label="grid.catalogEntry.agent" inline="true" checked=!$isSupplier required="true"}
			{fbvElement type="radio" id="supplier" name="isSupplier" value="1" label="grid.catalogEntry.supplier" checked=$isSupplier inline="true"}
		{/fbvFormSection}
		{fbvFormSection title="grid.catalogEntry.representativeRole" for="role" required="true"}
			{if $isSupplier}{assign var="agentClass" value="hidden"}{/if}
			{fbvElement type="select" from=$agentRoleCodes selected=$role id="agentRole" translate=false inline="true" class=$agentClass size=$fbvStyles.size.MEDIUM defaultValue="" defaultLabel=""}
			{if !$isSupplier}{assign var="supplierClass" value="hidden"}{/if}
			{fbvElement type="select" from=$supplierRoleCodes selected=$role id="supplierRole" translate=false inline="true" class=$supplierClass size=$fbvStyles.size.MEDIUM defaultValue="" defaultLabel=""}
		{/fbvFormSection}
		{fbvFormSection title="grid.catalogEntry.representativeName" for="name" required="true"}
			{fbvElement type="text" id="name" value=$name|escape size=$fbvStyles.size.MEDIUM}
		{/fbvFormSection}
		{fbvFormSection}
			{fbvElement type="select" label="grid.catalogEntry.representativeIdType" from=$idTypeCodes selected=$representativeIdType id="representativeIdType" defaultValue="" defaultLabel="" translate=false size=$fbvStyles.size.MEDIUM inline="true"}
			{fbvElement type="text" id="representativeIdValue" label="grid.catalogEntry.representativeIdValue" value=$representativeIdValue|escape size=$fbvStyles.size.MEDIUM inline="true"}
		{/fbvFormSection}
		{fbvFormSection}
			{fbvElement type="text" id="phone" label="grid.catalogEntry.representativePhone" value=$phone|escape size=$fbvStyles.size.MEDIUM inline="true"}
			{fbvElement type="text" id="fax" label="grid.catalogEntry.representativeFax" value=$fax|escape size=$fbvStyles.size.MEDIUM inline="true"}
		{/fbvFormSection}
		{fbvFormSection}
			{fbvElement type="text" id="email" label="grid.catalogEntry.representativeEmail" value=$email|escape size=$fbvStyles.size.MEDIUM inline="true"}
			{fbvElement type="text" id="url" label="grid.catalogEntry.representativeWebsite" value=$url|escape size=$fbvStyles.size.MEDIUM inline="true"}
		{/fbvFormSection}
		{fbvFormButtons}
	{/fbvFormArea}
</form>
