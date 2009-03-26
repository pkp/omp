{**
 * summary.tpl
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Subtemplate defining the submission summary table.
 *
 * $Id$
 *}
<h3>{translate key="manuscript.artwork"}</h3>

<table>
{foreach from=$artworks item=artwork}
<table>
<tr valign="middle">
<td>
<a target="_blank" href="{url op="viewFile" path=$submission->getMonographId()|to_array:$artwork->getFileId():$artwork->getRevision()}">
  <img class="thumbnail" width="50" src="{url op="viewFile" path=$submission->getMonographId()|to_array:$artwork->getFileId():$artwork->getRevision()}" />
</a>
</td>
<td>
{$artwork->getFileName()}<br />
Revision: {$artwork->getRevision()}<br />
<a href="{url op="removeArtworkFile" path=$submission->getMonographId()|to_array:$artwork->getFileId()}">Remove</a>
</td>
</tr>
{/foreach}
</table>

<h3>Upload Artwork</h3>

<form method="post" action="{url op="uploadArtworkFile"}"  enctype="multipart/form-data">
	<input type="hidden" name="from" value="submissionArt" />
	<input type="hidden" name="monographId" value="{$submission->getMonographId()}" />
	<input type="file" name="artworkFile" size="10" class="uploadField" />
	<input type="submit" value="{translate key="common.upload"}" class="button" />
</form>

</div>