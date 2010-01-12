{**
 * ArtworkInsert.tpl
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Subtemplate defining the artwork form.
 *
 * $Id$
 *}
{literal}
<script type="text/javascript">
<!--
// Show/hide element
function show(id) {
	var info = document.getElementById(id);
	if(info.style.display=='block') info.style.display='none';
	else info.style.display='block';
}
//-->
</script>
{/literal}
<h3>{translate key="submission.art"}</h3>
<br />
{foreach from=$components item=component}
<strong>{$component->getLocalizedTitle()|escape}</strong>
	{foreach from=$component->getAssocObjects() item=artwork}
	{assign var="file" value=$artwork->getFile()}
	{assign var="permissionFile" value=$artwork->getPermissionFile()}
	<table>
	<tr valign="top">
	<td>
	<a target="_blank" href="{url op="viewFile" path=$submission->getMonographId()|to_array:$file->getFileId():$file->getRevision()}">
	  <img class="thumbnail" width="50" src="{url op="viewFile" path=$submission->getMonographId()|to_array:$artwork->getFileId()}" />
	</a>
	</td>
	<td>
	<table>
	<tr><td>{translate key="common.originalFileName"}:&nbsp;</td><td>{$file->getOriginalFileName()|escape}</td></tr>
	<tr><td>{translate key="common.fileSize"}:&nbsp;</td><td>{$file->getNiceFileSize()}</td></tr>
	<tr><td>{translate key="common.type"}:&nbsp;</td><td>{$file->getExtension()|escape}</td></tr>
	<tr><td>{translate key="common.dateUploaded"}:&nbsp;</td><td>{$file->getDateUploaded()}</td></tr>
	<tr><td colspan="2"><input type="submit" name="removeArtwork[{$artwork->getId()}]" value="{translate key="common.delete"}" class="button" /></td></tr>
	</table>
	</td>
	</tr>
	</table>
	<input type="hidden" name="artwork[{$artwork->getId()|escape}][file_id]" value="{$file->getFileId()|escape}"/>
	<input type="hidden" name="artwork[{$artwork->getId()|escape}][artwork_id]" value="{$artwork->getId()|escape}"/>
	<input type="hidden" name="artwork[{$artwork->getId()|escape}][permission_file_id]" value="{$artwork->getPermissionFileId()|escape}"/>
	<a href="javascript:show('artwork-{$artwork->getId()|escape}-display')">{translate key="common.details"}</a>
	<div id="artwork-{$artwork->getId()|escape}-display" style="display:none;" class="newItemContainer">
		<table width="100%" class="data">
			<tr>
				<td>{translate key="common.fileName"}</td><td>{$file->getFileName()|escape}</td>
			</tr>
			<tr>
				<td>{translate key="submission.artwork.replaceFile"}</td><td><input type="file" name="artwork_file-{$artwork->getId()|escape}" size="10" class="uploadField" /></td>
			</tr>
			<tr>
				<td><strong>{translate key="submission.artwork.metadata"}</strong></td><td>&nbsp;</td>
			</tr>
			<tr>
				<td>{translate key="submission.artwork.caption"}</td><td><input type="text" name="artwork[{$artwork->getId()|escape}][artwork_caption]"  value="{$artwork->getCaption()|escape}"/></td>
			</tr>
			<tr>
				<td>{translate key="submission.artwork.credit"}</td><td><input type="text" name="artwork[{$artwork->getId()|escape}][artwork_credit]" value="{$artwork->getCredit()|escape}" /></td>
			</tr>
			<tr>
				<td>{translate key="submission.artwork.copyrightOwner"}</td><td><input type="text" name="artwork[{$artwork->getId()|escape}][artwork_copyrightOwner]" value="{$artwork->getCopyrightOwner()|escape}" /></td>
			</tr>
			<tr>
				<td>&nbsp;</td><td>{translate key="submission.artwork.copyrightContact"}: <input type="text" name="artwork[{$artwork->getId()|escape}][artwork_copyrightOwnerContact]" value="{$artwork->getCopyrightOwnerContactDetails()|escape}" /></td>
			</tr>
			<tr>
				<td>{translate key="submission.artwork.permissionTerms"}</td><td><input type="text" name="artwork[{$artwork->getId()|escape}][artwork_permissionTerms]" value="{$artwork->getPermissionTerms()|escape}" /></td>
			</tr>
			<tr>
				<td>{translate key="common.fileName"}</td><td>{if $permissionFile}{$permissionFile->getFileName()|escape}{else}<em>{translate key="common.none"}</em>{/if}</td>
			</tr>
			<tr>
				<td>{translate key="submission.artwork.permissionForm"}</td><td><input type="file" name="artwork_permissionForm-{$artwork->getId()|escape}" size="10" class="uploadField" /></td>
			</tr>
			<tr>
				<td valign="top">{translate key="submission.artwork.type"}</td>
				<td>
					<input type="radio" name="artwork[{$artwork->getId()|escape}][artwork_type]" value="{$smarty.const.MONOGRAPH_ARTWORK_TYPE_TABLE}" {if $artwork->getType() == MONOGRAPH_ARTWORK_TYPE_TABLE}checked="checked"{/if}/> {translate key="submission.artwork.type.table"}<br />
					<input type="radio" name="artwork[{$artwork->getId()|escape}][artwork_type]" value="{$smarty.const.MONOGRAPH_ARTWORK_TYPE_FIGURE}" {if $artwork->getType() == MONOGRAPH_ARTWORK_TYPE_FIGURE}checked="checked"{/if}/> {translate key="submission.artwork.type.figure"}<br />
					<input type="radio" name="artwork[{$artwork->getId()|escape}][artwork_type]" value="{$smarty.const.MONOGRAPH_ARTWORK_TYPE_OTHER}" {if $artwork->getType() == MONOGRAPH_ARTWORK_TYPE_OTHER}checked="checked"{/if}/> {translate key="common.other"} &nbsp; <input type="text" name="artwork[{$artwork->getId()|escape}][artwork_otherType]" value="{$artwork->getCustomType()|escape}" />
				</td>
			</tr>
			<tr>
				<td valign="top">{translate key="submission.artwork.placement"}</td>
				<td>
					<input type="radio" name="artwork[{$artwork->getId()|escape}][artwork_placementType]" {if $artwork->getComponentId()}checked="checked" {/if}value="{$smarty.const.MONOGRAPH_ARTWORK_PLACEMENT_BY_CHAPTER}"/> {translate key="submission.chapter"} 
					<select name="artwork[{$artwork->getId()|escape}][artwork_componentId]">
					<option value="0">--{translate key="common.select"}--</option>
					{foreach from=$submission->getComponents() item=component}
						<option value="{$component->getId()}"{if $artwork->getComponentId() == $component->getId()} selected="selected"{/if}>{$component->getLocalizedTitle()}</option>
					{/foreach}
					</select> {translate key="submission.artwork.placementDetail"} &nbsp; <input type="text" size="4" name="artwork[{$artwork->getId()|escape}][artwork_placement]" value="{if $artwork->getComponentId()}{$artwork->getPlacement()|escape}{/if}" />
					<br />
					<input type="radio" name="artwork[{$artwork->getId()|escape}][artwork_placementType]" value="{$smarty.const.MONOGRAPH_ARTWORK_PLACEMENT_OTHER}"{if !$artwork->getComponentId()} checked="checked"{/if}/> {translate key="common.other"} &nbsp; <input type="text" name="artwork[{$artwork->getId()|escape}][artwork_otherPlacement]" value="{if !$artwork->getComponentId()}{$artwork->getPlacement()|escape}{/if}"/>
				</td>
			</tr>
			<tr>
				<td>{translate key="submission.artwork.contactAuthor"}</td>
				<td>
					<select name="artwork[{$artwork->getId()|escape}][artwork_contact]">
					<option value="0">--{translate key="common.select"}--</option>
					{foreach from=$submission->getAuthors() item=author}
						<option value="{$author->getId()}" {if $author->getId() == $artwork->getContactAuthor()}selected="selected"{/if}>{$author->getFullName()|escape}</option>
					{/foreach}
					</select> 
				</td>
			</tr>
			<tr>
				<td></td><td><input type="submit" name="updateArtwork[{$artwork->getId()|escape}]" value="{translate key="common.upload"}" class="button" /></td>
			</tr>
		</table>
	</div><br /><br />
	{foreachelse}
		<br /><em>{translate key="common.none"}</em><br /><br />
	{/foreach}
	<hr />
{/foreach}

<div class="newItemContainer">

<h4>{translate key="submission.artwork.add"}</h4>

<table width="100%" class="data">
	<tr>
		<td>{translate key="common.fileName"}</td><td><input type="file" name="artwork_file" size="10" class="uploadField" /></td>
	</tr>
	<tr>
		<td><strong>{translate key="submission.artwork.metadata"}</strong></td><td>&nbsp;</td>
	</tr>
	<tr>
		<td>{translate key="submission.artwork.caption"}</td><td><input type="text" name="artwork_caption" /></td>
	</tr>
	<tr>
		<td>{translate key="submission.artwork.credit"}</td><td><input type="text" name="artwork_credit" /></td>
	</tr>
	<tr>
		<td>{translate key="submission.artwork.copyrightOwner"}</td><td><input type="text" name="artwork_copyrightOwner" /></td>
	</tr>
	<tr>
		<td>&nbsp;</td><td>{translate key="submission.artwork.copyrightContact"}: <input type="text" name="artwork_copyrightOwnerContact" /></td>
	</tr>
	<tr>
		<td>{translate key="submission.artwork.permissionTerms"}</td><td><input type="text" name="artwork_permissionTerms" /></td>
	</tr>
	<tr>
		<td>{translate key="submission.artwork.permissionForm"}</td><td><input type="file" name="artwork_permissionForm" size="10" class="uploadField" /></td>
	</tr>
	<tr>
		<td valign="top">{translate key="submission.artwork.type"}</td>
		<td>
			<input type="radio" name="artwork_type" value="{$smarty.const.MONOGRAPH_ARTWORK_TYPE_TABLE}" checked="checked"/> {translate key="submission.artwork.type.table"}<br />
			<input type="radio" name="artwork_type" value="{$smarty.const.MONOGRAPH_ARTWORK_TYPE_FIGURE}" /> {translate key="submission.artwork.type.figure"}<br />
			<input type="radio" name="artwork_type" value="{$smarty.const.MONOGRAPH_ARTWORK_TYPE_OTHER}" /> {translate key="common.other"} &nbsp; <input type="text" name="artwork_otherType" />
		</td>
	</tr>
	<tr>
		<td valign="top">{translate key="submission.artwork.placement"}</td>
		<td>
			<input type="radio" name="artwork_placementType" value="{$smarty.const.MONOGRAPH_ARTWORK_PLACEMENT_BY_CHAPTER}" /> {translate key="submission.chapter"}
			<select name="artwork_componentId">
			<option value="0">--{translate key="common.select"}--</option>
			{foreach from=$submission->getComponents() item=component}
				<option value="{$component->getId()}">{$component->getLocalizedTitle()}</option>
			{/foreach}
			</select>{translate key="submission.artwork.placementDetail"} &nbsp; <input type="text" size="4" name="artwork_placement" />
			<br />
			<input type="radio" name="artwork_placementType" value="{$smarty.const.MONOGRAPH_ARTWORK_PLACEMENT_OTHER}" checked="checked"/> {translate key="common.other"} &nbsp; <input type="text" name="artwork_otherPlacement" />
		</td>
	</tr>
	<tr>
		<td>{translate key="submission.artwork.contactAuthor"}</td>
		<td>
			<select name="artwork_contact">
			<option value="0">--{translate key="common.select"}--</option>
			{foreach from=$submission->getAuthors() item=author}
				<option value="{$author->getId()}">{$author->getFullName()|escape}</option>
			{/foreach}
			</select> 
		</td>
	</tr>
	<tr>
		<td></td><td><input type="submit" name="uploadNewArtwork" value="{translate key="common.upload"}" class="button" /></td>
	</tr>
</table>

</div>
