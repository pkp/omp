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

<h4>{translate key="submission.artwork.add"}</h4>

<form id="artworkUploadForm" action="{url router=$smarty.const.ROUTE_COMPONENT component="grid.artworkFile.ArtworkFileGridHandler" op="updateArtworkFile" monographId="1"}" method="post" enctype="multipart/form-data">

<table width="100%" class="data">
	<tr>
		<td>{translate key="common.file"}</td>
		<td>
			<input type="file" name="artwork_file" size="10" class="uploadField" />
			<input type="submit" name="uploadArtworkFile" value="{translate key='common.upload'}" />
		</td>
	</tr>
</table>

<div id="uploadOutput">



</div>


{if $gridId}
	<input type="hidden" name="gridId" value="{$gridId|escape}" />	
{/if}
{if $rowId}
	<input type="hidden" name="rowId" value="{$rowId|escape}" />
{/if}

</form>

{url|assign:fileUploadUrl router=$smarty.const.ROUTE_COMPONENT component="grid.artworkFile.ArtworkFileGridHandler" op="uploadArtworkFile"}
{ajax_upload url=$fileUploadUrl form="artworkUploadForm"}


