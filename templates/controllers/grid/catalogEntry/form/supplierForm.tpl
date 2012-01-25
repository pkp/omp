{**
 * templates/controllers/grid/catalogEntry/form/supplierForm.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Supplier form.
 *}

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#supplierForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

<form class="pkp_form" id="supplierForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT component="grid.catalogEntry.SuppliersGridHandler" op="updateSupplier"}">
	<input type="hidden" name="monographId" value="{$monographId|escape}" />
	<input type="hidden" name="supplierId" value="{$supplierId|escape}" />
	{fbvFormArea id="addSupplier"}
		{fbvFormSection title="grid.catalogEntry.supplierRole" for="role" required="true" size=$fbvStyles.size.MEDIUM}
			{fbvElement type="select" from=$roleCodes selected=$role id="role" translate=false}
		{/fbvFormSection}
		{fbvFormSection title="grid.catalogEntry.supplierName" for="name" required="true"}
			{fbvElement type="text" id="name" value=$name|escape size=$fbvStyles.size.MEDIUM}
		{/fbvFormSection}
		{fbvFormSection}
			{fbvElement type="select" label="grid.catalogEntry.supplierIdType" from=$idTypeCodes selected=$supplierIdType id="supplierIdType" defaultValue="" defaultLabel="" translate=false size=$fbvStyles.size.MEDIUM inline="true"}
			{fbvElement type="text" id="supplierIdValue" label="grid.catalogEntry.supplierIdValue" value=$supplierIdValue|escape size=$fbvStyles.size.MEDIUM inline="true"}
		{/fbvFormSection}
		{fbvFormSection}
			{fbvElement type="text" id="phone" label="grid.catalogEntry.supplierPhone" value=$phone|escape size=$fbvStyles.size.MEDIUM inline="true"}
			{fbvElement type="text" id="fax" label="grid.catalogEntry.supplierFax" value=$fax|escape size=$fbvStyles.size.MEDIUM inline="true"}
		{/fbvFormSection}
		{fbvFormSection}
			{fbvElement type="text" id="email" label="grid.catalogEntry.supplierEmail" value=$email|escape size=$fbvStyles.size.MEDIUM inline="true"}
			{fbvElement type="text" id="url" label="grid.catalogEntry.supplierWebsite" value=$url|escape size=$fbvStyles.size.MEDIUM inline="true"}
		{/fbvFormSection}
		{fbvFormButtons}
	{/fbvFormArea}
</form>
