{**
 * controllers/grid/files/selectableSubmissionFileListCategoryGridFilter.tpl
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Filter template for selectable submission file list category grid.
 *}
<script type="text/javascript">
	// Attach the form handler to the form.
	$('#fileListFilterForm').pkpHandler('$.pkp.controllers.form.ClientFormHandler',
		{ldelim}
			trackFormChanges: false
		{rdelim}
	);
</script>
<form class="pkp_form" id="fileListFilterForm" action="{url router=$smarty.const.ROUTE_COMPONENT op="fetchGrid"}" method="post">
	{fbvFormArea id="allStagesFilterArea"}
		{fbvFormSection list="true"}
			{fbvElement type="checkbox" id="allStages" checked=$filterSelectionData.allStages label="editor.monograph.fileList.includeAllStages" size=$fbvStyles.size.LARGE}
			{fbvElement type="submit" id="submitFormButton" label="common.search" inline=true}
		{/fbvFormSection}
	{/fbvFormArea}
</form>

