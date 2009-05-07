{**
 * copyedit.tpl
 *
 * Copyright (c) 2003-2009 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Subtemplate defining the copyediting table.
 *
 * $Id$
 *}
<div id="copyedit">
<h3>{translate key="submission.productionEditor"}</h3>

<table width="100%" class="data">
	<tr>
		<td width="20%" class="label">{translate key="user.role.productionEditor"}</td>
		{if $submission->getUserIdBySignoffType('SIGNOFF_PRODUCTION_INITIAL')}<td width="20%" class="value"></td>{/if}
		<td class="value"><a href="{url op="assignProductionEditor" path=$submission->getMonographId()}" class="action">{translate key="editor.monograph.assignProductionEditor"}</a></td>
	</tr>
</table>
{if $productionEditor}
<table width="100%" class="info">
	<tr>
		<td width="28%" colspan="2"></td>
		<td width="18%" class="heading">{translate key="submission.request"}</td>
		<td width="18%" class="heading">{translate key="submission.underway"}</td>
		<td width="18%" class="heading">{translate key="submission.complete"}</td>
		<td width="18%" class="heading">{translate key="submission.acknowledge"}</td>
	</tr>
	<tr>
		<td width="18%">{$productionEditor->getFullName()|escape}</td>
		{assign var="productionEditorSignoff" value=$submission->getSignoff('SIGNOFF_PRODUCTION_INITIAL')}

		<td width="10%">{translate key="common.file"}</td>
		<td>
			{if $submission->getUserIdBySignoffType('SIGNOFF_PRODUCTION_INITIAL') && $initialProductionFile}
				{url|assign:"url" op="notifyProductionEditor" monographId=$submission->getMonographId()}
				{if $productionEditorSignoff->getDateUnderway()}
					{translate|escape:"javascript"|assign:"confirmText" key="sectionEditor.copyedit.confirmRenotify"}
					{icon name="mail" onclick="return confirm('$confirmText')" url=$url}
				{else}
					{icon name="mail" url=$url}
				{/if}
			{else}
				{icon name="mail" disabled="disable"}
			{/if}

			{$productionEditorSignoff->getDateNotified()|date_format:$dateFormatShort|default:""}
		</td>
		<td>
			{$productionEditorSignoff->getDateUnderway()|date_format:$dateFormatShort|default:"&mdash;"}
		</td>
		<td>
			{$productionEditorSignoff->getDateCompleted()|date_format:$dateFormatShort|default:"&mdash;"}

		</td>
		<td>
			{if $submission->getUserIdBySignoffType('SIGNOFF_PRODUCTION_INITIAL') &&  $productionEditorSignoff->getDateNotified() && !$productionEditorSignoff->getDateAcknowledged()}
				{url|assign:"url" op="thankProductionEditor" monographId=$submission->getMonographId()}
				{icon name="mail" url=$url}
			{else}
				{icon name="mail" disabled="disable"}
			{/if}
			{$productionEditorSignoff->getDateAcknowledged()|date_format:$dateFormatShort|default:""}
		</td>
	</tr>
</table> 
{/if}