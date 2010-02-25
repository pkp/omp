{**
 * fileForm.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Files grid form
 *
 * $Id$
 *}
<script type="text/javascript">
	{literal}
	$(function() {
		$("#fileUploadTabs").tabs();
	});
	{/literal}
</script>

<h3>{translate key="manager.setup.submissionLibrary"}</h3>
	<div id="fileUploadTabs" class="ui-tabs ui-widget ui-widget-content ui-corner-all">
		<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
			<li class="ui-state-default ui-corner-top"><a href="#fileUploadTab1">{translate key="1. Upload"}</a></li>
			<li class="ui-state-default ui-corner-top"><a href="#fileUploadTab2">{translate key="2. Metadata"}</a></li>
			<li class="ui-state-default ui-corner-top"><a href="#fileUploadTab3">{translate key="3. Finishing Up"}</a></li>
		</ul>
		<div id="fileUploadTab1">
		<form name="uploadForm" action="{url router=$smarty.const.ROUTE_COMPONENT component="grid.submit.submissionFiles.SubmissionFilesGridHandler" op="uploadFile"}" id="uploadForm" method="post">
			<h3>{translate key="1. Upload"}</h3></form>  
			
		</div>
		<div id="fileUploadTab2">
			<h3>{translate key="2. Metadata"}</h3>
		</div>
		<div id="fileUploadTab3">
			<h3>{translate key="3. Finishing Up"}</h3>
		</div>
	</div>


{if $gridId}
	<input type="hidden" name="gridId" value="{$gridId|escape}" />	
{/if}
{if $rowId}
	<input type="hidden" name="rowId" value="{$rowId|escape}" />
{/if}
<br />
</form>