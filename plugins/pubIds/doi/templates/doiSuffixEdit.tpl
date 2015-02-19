{**
 * @file plugins/pubIds/doi/templates/doiSuffixEdit.tpl
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Edit DOI meta-data.
 *}

{assign var=storedPubId value=$pubObject->getStoredPubId($pubIdPlugin->getPubIdType())}
{fbvFormArea id="pubIdDOIFormArea" class="border" title="plugins.pubIds.doi.editor.doi"}
{if $pubIdPlugin->getSetting($currentPress->getId(), 'doiSuffix') == 'customId' || $storedPubId}
	{if empty($storedPubId)}
			{fbvFormSection}
				<p class="pkp_help">{translate key="plugins.pubIds.doi.manager.settings.doiSuffixDescription"}</p>
				{fbvElement type="text" label="plugins.pubIds.doi.manager.settings.doiPrefix" id="doiPrefix" disabled=true value=$pubIdPlugin->getSetting($currentPress->getId(), 'doiPrefix') size=$fbvStyles.size.SMALL}
				{fbvElement type="text" label="plugins.pubIds.doi.manager.settings.doiSuffix" id="doiSuffix" value=$doiSuffix size=$fbvStyles.size.MEDIUM}
			{/fbvFormSection}
	{else}
		{$storedPubId|escape}
	{/if}
{else}
	{$pubIdPlugin->getPubId($pubObject, true)|escape} <br />
	<br />
	{capture assign=translatedObjectType}{translate key="plugins.pubIds.doi.editor.doiObjectType"|cat:$pubObjectType}{/capture}
	{translate key="plugins.pubIds.doi.editor.doiNotYetGenerated" pubObjectType=$translatedObjectType}
{/if}
{/fbvFormArea}
