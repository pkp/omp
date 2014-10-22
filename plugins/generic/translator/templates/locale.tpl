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

{url|assign:miscFileGridUrl router=$smarty.const.ROUTE_COMPONENT component="plugins.generic.translator.controllers.grid.MiscTranslationFileGridHandler" op="fetchGrid" locale=$locale tabsSelector=$tabsSelector escape=false}
{load_url_in_div id="miscFileGridContainer-"|uniqid url=$miscFileGridUrl}

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
