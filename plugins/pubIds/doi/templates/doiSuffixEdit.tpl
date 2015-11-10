{**
 * @file plugins/pubIds/doi/templates/doiSuffixEdit.tpl
 *
 * Copyright (c) 2014 Simon Fraser University Library
 * Copyright (c) 2003-2014 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Edit DOI meta-data.
 *}

{if $pubObject}
{assign var=pubObjectType value=$pubIdPlugin->getPubObjectType($pubObject)}
{assign var=storedPubId value=$pubObject->getStoredPubId($pubIdPlugin->getPubIdType())}
{fbvFormArea id="pubIdDOIFormArea" class="border" title="plugins.pubIds.doi.editor.doi"}
{if !$excludeDoi}
	{if $pubIdPlugin->getSetting($currentPress->getId(), 'doiSuffix') == 'customId' || $storedPubId}
		{if empty($storedPubId)}
				{fbvFormSection}
					<p class="pkp_help">{translate key="plugins.pubIds.doi.manager.settings.doiSuffixDescription"}</p>
					{fbvElement type="text" label="plugins.pubIds.doi.manager.settings.doiPrefix" id="doiPrefix" disabled=true value=$pubIdPlugin->getSetting($currentPress->getId(), 'doiPrefix') size=$fbvStyles.size.SMALL}
					{fbvElement type="text" label="plugins.pubIds.doi.manager.settings.doiSuffix" id="doiSuffix" value=$doiSuffix size=$fbvStyles.size.MEDIUM}
				{/fbvFormSection}
		{else}
			{$storedPubId|escape}
			{fbvFormSection list="true"}
				{capture assign=translatedObjectType}{translate key="plugins.pubIds.doi.editor.doiObjectType"|cat:$pubObjectType}{/capture}
				{capture assign=clearCheckBoxLabel}{translate key="plugins.pubIds.doi.editor.doiReassign.description" pubObjectType=$translatedObjectType}{/capture}
				{fbvElement type="checkbox" id="clear_doi" name="clear_doi" value="1" label=$clearCheckBoxLabel translate=false}
			{/fbvFormSection}
		{/if}
	{else}
		{$pubIdPlugin->getPubId($pubObject, true)|escape} <br />
		<br />
		{capture assign=translatedObjectType}{translate key="plugins.pubIds.doi.editor.doiObjectType"|cat:$pubObjectType}{/capture}
		{translate key="plugins.pubIds.doi.editor.doiNotYetGenerated" pubObjectType=$translatedObjectType}
	{/if}
{/if}
{fbvFormSection list="true"}
	{if $excludeDoi}
		{assign var="checked" value=true}
	{else}
		{assign var="checked" value=false}
	{/if}
	{capture assign=translatedObjectType}{translate key="plugins.pubIds.doi.editor.doiObjectType"|cat:$pubObjectType}{/capture}
	{capture assign=excludeCheckBoxLabel}{translate key="plugins.pubIds.doi.editor.excludePubObject" pubObjectType=$translatedObjectType}{/capture}
	{fbvElement type="checkbox" id="excludeDoi" name="excludeDoi" value="1" checked=$checked label=$excludeCheckBoxLabel translate=false}
{/fbvFormSection}
{/fbvFormArea}
{/if}
