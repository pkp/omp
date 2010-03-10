{**
 * artworkFileForm.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Template defining the artwork file form.
 *
 * $Id$
 *}

<h4>{translate key="grid.artworkFile.form.title"}</h4>

<form id="artworkUploadForm{if $artworkFile}-{$artworkFile->getId()|escape}{/if}" action="{url router=$smarty.const.ROUTE_COMPONENT component="grid.artworkFile.ArtworkFileGridHandler" op="updateArtworkFile" monographId="1"}" method="post" enctype="multipart/form-data">

{if !$artworkFile}
<div id="artworkUploadInput">
<table width="100%" class="data">
	<tr>
		<td>{translate key="common.file"}</td>
		<td>
			<input type="file" name="artwork_file" size="10" class="uploadField" />
			<input type="submit" name="uploadArtworkFile" value="{translate key='common.upload'}" />
		</td>
	</tr>
</table>
</div>
{/if}

<div id="uploadOutput{if $artworkFile}-{$artworkFile->getId()|escape}{/if}">
{if $artworkFile}
	{include file="controllers/grid/artworkFile/form/fileInfo.tpl"}
{/if}
</div>


{if $gridId}
	<input type="hidden" name="gridId" value="{$gridId|escape}" />	
{/if}
{if $artworkFileId}
	<input type="hidden" name="artworkFileId" value="{$artworkFileId|escape}" />
{/if}

</form>

{url|assign:fileUploadUrl router=$smarty.const.ROUTE_COMPONENT component="grid.artworkFile.ArtworkFileGridHandler" op="uploadArtworkFile"}
<script type="text/javascript">
<!--
{literal}
// remove artwork file upload
var callback = function removeArtworkUploadInput() {
	$("#artworkUploadInput").children().remove();
}
{/literal}
ajaxUpload('{$fileUploadUrl}', 'artworkUploadForm', callback);
//-->
</script>



