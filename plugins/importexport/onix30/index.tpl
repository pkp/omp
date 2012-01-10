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
			<td colspan="2" class="headseparator">&nbsp;</td>
		</tr>
		<tr class="heading" valign="bottom">
			<td width="60%">{translate key="monograph.title"}</td>
			<td width="40%">{translate key="plugins.importexport.onix30.exportFormats"}</td>
		</tr>
		<tr>
			<td colspan="2" class="headseparator">&nbsp;</td>
		</tr>
		
		{iterate from=monographs item=monograph}
			{if $monograph->hasAssignedPublicationFormats()}
				<tr valign="top">
					<td>
						{$monograph->getLocalizedPrefix()|concat:" ":$monograph->getLocalizedTitle()|escape}<br /><em>{$monograph->getAuthorString(true)|escape}</em>
					</td>
					<td>{assign var="formats" value=$monograph->getAssignedPublicationFormats()}
						{foreach from=$formats item=format name="formats"}
							{assign var="formatId" value=$format->getAssignedPublicationFormatId()|escape}
							{fbvFormSection list="true"}
								{fbvElement type="radio" required=true name="assignedPublicationFormatId" id='format'|concat:$formatId value=$formatId label=$format->getLocalizedTitle() translate=false}
							{/fbvFormSection}
						{/foreach}
					</td>
				</tr>
				<tr>
					<td colspan="2" class="{if $monographs->eof()}end{/if}separator">&nbsp;</td>
				</tr>
			{/if}
		{/iterate}
		{if $monographs->wasEmpty()}
			<tr>
				<td colspan="2" class="nodata">{translate key="common.none"}</td>
			</tr>
			<tr>
				<td colspan="2" class="endseparator">&nbsp;</td>
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