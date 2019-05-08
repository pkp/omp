{**
 * templates/controllers/grid/catalogEntry/editFormat.tpl
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * The "edit publication format" tabset.
 *}
<script type="text/javascript">
	// Attach the JS file tab handler.
	$(function() {ldelim}
		$('#editPublicationFormatMetadataTabs').pkpHandler('$.pkp.controllers.TabHandler');
	{rdelim});
</script>
<div id="editPublicationFormatMetadataTabs">
	<ul>
		<li><a href="{url router=$smarty.const.ROUTE_COMPONENT op="editFormatTab" submissionId=$submissionId representationId=$representationId submissionVersion=$submissionVersion}">{translate key="grid.action.editMetadata"}</a></li>
		{if !$remoteRepresentation}
			<li><a href="{url router=$smarty.const.ROUTE_COMPONENT op="identifiers" submissionId=$submissionId representationId=$representationId}">{translate key="submission.identifiers"}</a></li>
		{/if}
	</ul>
</div>
