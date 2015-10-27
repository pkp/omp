{**
 * templates/frontend/objects/publicationFormat.tpl
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @brief Display a publication format's details
 *
 * @publicationFormat PublicationFormat The publication format to display
 * @identificationCodes IdentificationCode
 *}
<div class="obj_publication_format">
	<div class="title">
		{$publicationFormat->getLocalizedName()|escape}
	</div>

	<ul class="details">
		<li class="dimensions">
			<span class="label">
				{translate key="monograph.publicationFormat.productDimensions"}
			</span>
			<span class="value">
				{$publicationFormat->getDimensions()|escape}
			</span>
		</li>

		{assign var=identificationCodes value=$publicationFormat->getIdentificationCodes()}
		{assign var=identificationCodes value=$identificationCodes->toArray()}
		{if $identificationCodes}
			{foreach from=$identificationCodes item=identificationCode}
				<li class="identification_code {$identificationCode->getCode()|escape}">
					<span class="label">
						{$identificationCode->getNameForONIXCode()|escape}
					</span>
					<span class="value">
						{$identificationCode->getValue()|escape}
					</span>
				</li>
			{/foreach}
		{/if}

		{assign var=publicationDates value=$publicationFormat->getPublicationDates()}
		{assign var=publicationDates value=$publicationDates->toArray()}
		{if $publicationDates}
			{foreach from=$publicationDates item=publicationDate}
				<li class="date {$publicationDate->getId()|escape}">
					<span class="label">
						{$publicationDate->getNameForONIXCode()|escape}
					</span>
					<span class="value">
						{assign var=dates value=$publicationDate->getReadableDates()}
						{* note: these dates have dateFormatShort applied to them in getReadableDates() if they need it *}
						{if $publicationDate->isFreeText() || $dates|@count == 1}
							{$dates[0]|escape}
						{else}
							{* @todo the &mdash; ought to be translateable *}
							{$dates[0]|escape}&mdash;{$dates[1]|escape}
						{/if}
						{* @todo the parentheses ought to be translateable. Alternatively, wrap the text in a span for styling *}
						{if $publicationDate->isHijriCalendar()}({translate key="common.dateHijri"}){/if}
				</li>
			{/foreach}
		{/if}

		{if $enabledPubIdTypes|@count}
			{foreach from=$enabledPubIdTypes item=pubIdType}
				{assign var=storedPubId value=$publicationFormat->getStoredPubId($pubIdType)}
				{if $storePubId != ''}
					<li class="pubid {$publicationFormat->getId()|escape}">
						<span class="label">
							{$pubIdType}
						</span>
						<span class="value">
							{$storedPubId|escape}
						</span>
					</li>
				{/if}
			{/foreach}
		{/if}

		{assign var="representationId" value=$publicationFormat->getId()}
		{if !empty($availableFiles.$representationId)}
			{* <li class="ecommerce"> *}
				{if $availableFiles.$representationId|@count == 1}
					{* FIXME: unimplemented. One file available; shortcut to purchase *}
				{else}
					{* FIXME: unimplemented. Several files available; display options *}
				{/if}
			{* </li> *}
		{/if}{* !empty($availableFiles) *}

	</div><!-- .info -->

</div><!-- .obj_monograph_full -->
