{**
 * templates/controllers/informationCenter/history.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display submission file history.
 *}

<div id="informationCenterHistoryTab">
<table width="100%" class="listing">
	<tr><td class="headseparator" colspan="5">&nbsp;</td></tr>
	<tr valign="top" class="heading">
		<td>{translate key="common.date"}</td>
		<td>{translate key="common.user"}</td>
		<td>{translate key="common.event"}</td>
	</tr>
	<tr><td class="headseparator" colspan="5">&nbsp;</td></tr>
{iterate from=eventLogEntries item=logEntry}
	<tr valign="top">
		<td>{$logEntry->getDateLogged()|date_format:$dateFormatShort}</td>
		<td>{$logEntry->getUserFullName()|escape}</td>
		<td>{translate key=$logEntry->getMessage() params=$logEntry->getParams()}</td>
	</tr>
{/iterate}
{if $eventLogEntries->wasEmpty()}
	<tr valign="top">
		<td colspan="5" class="nodata pkp_helpers_text_center">{translate key="informationCenter.history.noItems"}</td>
	</tr>
{/if}
</table>
</div>

