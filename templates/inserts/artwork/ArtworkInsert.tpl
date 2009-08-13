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

<h4>Visuals</h4>
<table>
<td></td>
<td></td>

{foreach from=$artworks item=artwork}

<tr valign="middle">
<td>
<a target="_blank" href="{url op="viewFile" path=$submission->getMonographId()|to_array:$artwork->getFileId():$artwork->getRevision()}">
  <img class="thumbnail" width="50" src="{url op="viewFile" path=$submission->getMonographId()|to_array:$artwork->getFileId():$artwork->getRevision()}" />
</a>
</td>
<td>
$artwork->getIdentifier()<br />
$artwork->getMonographComponentTitle()<br />
Revision: $artwork->getRevision()<br />
<input type="submit" name="removeArtwork[{$artwork->getFileId()}]" value="{translate key="common.delete"}" class="button" />
</td>
</tr>
{foreachelse}
<em>No artwork files have been uploaded yet!</em>
{/foreach}
</table>

<h4>Upload Artwork</h4>
	<table>
	<tr>
		<td>Identifier</td><td><input type="text" name="identifier" /></td>
	</tr>
	<tr>
		<td>Monograph Component</td>
		<td>
			<select name="componentId">
			<option>--Select--</option>
			{foreach from=$submission->getComponents() item=component}
				<option value="{$component->getId()}">{$component->getLocalizedTitle()}</option>
			{/foreach}
			</select>
		</td>
	</tr>
	<tr>
		<td>File</td><td><input type="file" name="artworkFile" size="10" class="uploadField" /></td>
	</tr>
	<tr>
		<td></td><td><input type="submit" name="uploadNewArtwork" value="{translate key="common.upload"}" class="button" /></td>
	</tr>
	</table>

<!--	Type <select>
	<option>--Select--</option>
	<option>Map</option>
	<option>Illustration</option>
	<option>Image</option>
	<option>Graph</option>
	<option>Other...</option>
	</select>
	<br />
	<input type="checkbox" /> Permitted Use
	<br />
	<br />-->
