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

<form method="post" action="{url op="submitArtwork" path=$submission->getMonographId()}"  enctype="multipart/form-data">

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

</form>
