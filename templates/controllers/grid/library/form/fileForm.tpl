{**
 * fileForm.tpl
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Files grid form
 *
 * $Id$
 *}

<h3>{translate key="manager.setup.submissionLibrary"}</h3>
<form name="uploadForm" action="{url router=$smarty.const.ROUTE_COMPONENT component="grid.library.LibraryFileRowHandler" op="uploadFile"}" id="uploadForm" method="post">
	<!-- Max file size of 5 MB -->
	<input type="hidden" name="MAX_FILE_SIZE" value="5242880" />
	{translate key='common.file'} <input type="file" name="libraryFile" />
	<input type="submit" value="{translate key='form.submit'}" />
	<div id="uploadOutput">
		{if $libraryFile}
			{include file="controllers/grid/library/form/fileInfo.tpl"}
		{/if}
	</div>
</form>
{url|assign:fileUploadUrl router=$smarty.const.ROUTE_COMPONENT component="grid.library.LibraryFileGridHandler" op="uploadFile"}
{ajax_upload url=$fileUploadUrl form="uploadForm"}    


{if $gridId}
	<input type="hidden" name="gridId" value="{$gridId|escape}" />	
{/if}
{if $rowId}
	<input type="hidden" name="rowId" value="{$rowId|escape}" />
{/if}
<br />
</form>