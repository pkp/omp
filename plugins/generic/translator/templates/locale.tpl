{**
 * templates/locale.tpl
 *
 * Copyright (c) 2014 Simon Fraser University Library
 * Copyright (c) 2003-2014 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * List of component locales to edit for a particular locale
 *}
<p>{translate key="plugins.generic.translator.localeDescription"}</p>

<a name="localeFiles"></a>

{url|assign:localeFileGridUrl router=$smarty.const.ROUTE_COMPONENT component="plugins.generic.translator.controllers.grid.LocaleFileGridHandler" op="fetchGrid" locale=$locale tabsSelector=$tabsSelector escape=false}
{load_url_in_div id="localeFileGridContainer-"|uniqid url=$localeFileGridUrl}

<a name="miscFiles"></a>

<h3>{translate key="plugins.generic.translator.miscFiles"}</h3>
<table class="pkp_listing" width="100%">
	<tr><td colspan="2" class="headseparator">&nbsp;</td></tr>
	<tr class="heading" valign="bottom">
		<td width="85%">{translate key="plugins.generic.translator.file.filename"}</td>
		<td width="15%">{translate key="common.action"}</td>
	</tr>
	<tr><td colspan="2" class="headseparator">&nbsp;</td></tr>

{assign var=needsAsteriskNote value=0}
{iterate from=miscFiles item=filename}
{assign var=filenameEscaped value=$filename|escape:"url"|escape:"url"}
{if file_exists($filename)}
	{assign var=fileExists value=1}
{else}
	{assign var=fileExists value=0}
	{assign var=needsAsteriskNote value=1}
{/if}
	<tr valign="top">
		<td>
			{if $fileExists}
				<a href="{url router=$smarty.const.ROUTE_PAGE op="downloadLocaleFile" path=$locale|to_array:$filenameEscaped}">{$filename|escape}</a>
			{else}
				{$filename|escape}&nbsp;*
			{/if}
			</td>
		<td>
			{if $fileExists}
				<a href="{url router=$smarty.const.ROUTE_PAGE op="editMiscFile" path=$locale|to_array:$filenameEscaped}" class="action">{translate key="common.edit"}</a>
			{else}
				{url|assign:"redirectUrl" router=$smarty.const.ROUTE_PAGE op="editMiscFile" path=$locale|to_array:$filenameEscaped}
				<a href="{url router=$smarty.const.ROUTE_PAGE op="createFile" path=$locale|to_array:$filenameEscaped redirectUrl=$redirectUrl}" class="action" onclick='return confirm("{translate|escape:"javascript" key="plugins.generic.translator.file.confirmCreate" filename=$filename}")'>{translate key="common.create"}</a>
			{/if}
		</td>
	</tr>
	<tr>
		<td colspan="2" class="{if $miscFiles->eof()}end{/if}separator">&nbsp;</td>
	</tr>
{/iterate}

{if $miscFiles->wasEmpty()}
	<tr>
		<td colspan="2" class="nodata">{translate key="common.none"}</td>
	</tr>
	<tr>
		<td colspan="2" class="endseparator">&nbsp;</td>
	</tr>
{else}
	<tr>
		<td align="left">{page_info iterator=$miscFiles}</td>
		<td align="right">{page_links anchor="miscFiles" name="miscFiles" iterator=$miscFiles}</td>
	</tr>
{/if}

</table>

{if $needsAsteriskNote}
	<span class="instruct">{translate key="plugins.generic.translator.file.doesNotExistNote"}</span>
{/if}

<a name="emails"></a>

<h3>{translate key="plugins.generic.translator.emails"}</h3>
<table class="pkp_listing" width="100%">
	<tr><td colspan="3" class="headseparator">&nbsp;</td></tr>
	<tr class="heading" valign="bottom">
		<td width="35%">{translate key="manager.emails.emailKey"}</td>
		<td width="50%">{translate key="plugins.generic.translator.file.filename"}</td>
		<td width="15%">{translate key="common.action"}</td>
	</tr>
	<tr><td colspan="3" class="headseparator">&nbsp;</td></tr>

{iterate from=emails key=emailKey item=email}
	<tr valign="top">
		<td>{$emailKey|escape}</td>
		<td>{$email.subject|escape}</td>
		<td>
			<a href="{url router=$smarty.const.ROUTE_PAGE op="editEmail" path=$locale|to_array:$emailKey}" class="action">{translate key="common.edit"}</a>&nbsp;|&nbsp;<a href="{url router=$smarty.const.ROUTE_PAGE op="deleteEmail" path=$locale|to_array:$emailKey}" class="action" onclick="return confirm('{translate|escape:"jsparam" key="plugins.generic.translator.confirmDelete"}')">{translate key="common.delete"}</a>
		</td>
	</tr>
	<tr>
		<td colspan="3" class="{if $emails->eof()}end{/if}separator">&nbsp;</td>
	</tr>
{/iterate}

{if $emails->wasEmpty()}
	<tr>
		<td colspan="3" class="nodata">{translate key="common.none"}</td>
	</tr>
	<tr>
		<td colspan="3" class="endseparator">&nbsp;</td>
	</tr>
{else}
	<tr>
		<td align="left">{page_info iterator=$emails}</td>
		<td colspan="2" align="right">{page_links anchor="emails" name="emails" iterator=$emails}</td>
	</tr>
{/if}

</table>
