{**
 * plugins/importexport/onix30/index.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * List of monographs to potentially export
 *
 *}
{strip}
{assign var="pageTitle" value="plugins.importexport.onix30.selectMonograph"}
{assign var="pageCrumbTitle" value="plugins.importexport.onix30.selectMonograph"}
{include file="common/header.tpl"}
{/strip}

<br/>

<div id="monographs">
<form class="pkp_form" id="informationCollectionForm" method="post" action="{url path=$urlPath}">

	{fbvFormArea id="addresseeInfo"}
		{fbvFormSection title="plugins.importexport.onix30.form.addresseeField" for="addressee" description="plugins.importexport.onix30.form.addresseeField.tip"}
			{fbvElement type="text" id="addressee" size=$fbvStyles.size.MEDIUM}
		{/fbvFormSection}
	{/fbvFormArea}

	<table width="100%" class="listing">
		<tr>
			<td colspan="3" class="headseparator">&nbsp;</td>
		</tr>
		<tr class="heading" valign="bottom">
			<th width="50%">{translate key="monograph.title"}</th>
			<th width="25%">{translate key="plugins.importexport.onix30.exportFormats"}</th>
			<th width="25%">{translate key="plugins.importexport.onix30.validityStatus"}</th>
		</tr>
		<tr>
			<td colspan="3" class="headseparator">&nbsp;</td>
		</tr>
		
		{iterate from=monographs item=monograph}
			{if $monograph->hasAssignedPublicationFormats()}
				{assign var="formats" value=$monograph->getAssignedPublicationFormats()}
				<tr valign="top">
					<td rowspan={$formats|@count}>
						{$monograph->getLocalizedPrefix()|concat:" ":$monograph->getLocalizedTitle()|escape}<br /><em>{$monograph->getAuthorString(true)|escape}</em>
					</td>
					{foreach from=$formats item=format name=formats}
						{if !$smarty.foreach.formats.first}<tr>{/if}
						<td>
							{assign var="formatId" value=$format->getAssignedPublicationFormatId()|escape}
							{fbvFormSection list="true"}
								{fbvElement type="radio" required=true name="assignedPublicationFormatId" id='format'|concat:$formatId value=$formatId label=$format->getLocalizedTitle() translate=false}
							{/fbvFormSection}
						</td>
						<td>
							{assign var="errorKeys" value=$format->hasNeededONIXFields()}
							{if $errorKeys|@count == 0}
								<span class="pkp_form_success">{translate key="plugins.importexport.onix30.formatValid"}</span>
							{else}
								<label class="error" title="{foreach from=$errorKeys item=key name=errors}{translate key=$key} {/foreach}">
								{translate key="plugins.importexport.onix30.formatInvalid"}</label>
							{/if}
						</td>
					</tr>
					{/foreach}
				<tr>
					<td colspan="3" class="{if $monographs->eof()}end{/if}separator">&nbsp;</td>
				</tr>
			{/if}
		{/iterate}
		{if $monographs->wasEmpty()}
			<tr>
				<td colspan="3" class="nodata">{translate key="common.none"}</td>
			</tr>
			<tr>
				<td colspan="3" class="endseparator">&nbsp;</td>
			</tr>
		{else}
			<tr>
				<td align="left">{page_info iterator=$monographs} (<em>{translate key="plugins.importexport.onix30.noFormats"}</em>)</td>
				<td align="right">{page_links anchor="monographs" name="monographs" iterator=$monographs}</td>
			</tr>
		{/if}
	</table>
	{fbvFormButtons hideCancel="true" submitText="plugins.importexport.onix30.exportButton"}
</form>
</div>
{include file="common/footer.tpl"}