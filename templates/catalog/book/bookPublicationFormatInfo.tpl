{**
 * templates/catalog/book/bookPublicationFormatInfo.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Publication format information to be presented in book page.
 *}

<div class="bookDimensionSpecs">
{assign var=notFirst value=0}
{if $publicationFormat->getWidth()}
	{$publicationFormat->getWidth()|escape} {$publicationFormat->getWidthUnitCode()|escape}
	{assign var=notFirst value=1}
{/if}
{if $publicationFormat->getHeight()}
	{if $notFirst} x {/if}
	{$publicationFormat->getHeight()|escape} {$publicationFormat->getHeightUnitCode()|escape}
	{assign var=notFirst value=1}
{/if}
{if $publicationFormat->getThickness()}
	{if $notFirst} x {/if}
	{$publicationFormat->getThickness()|escape} {$publicationFormat->getThicknessUnitCode()|escape}
	{assign var=notFirst value=1}
{/if}
</div>
{assign var=identificationCodes value=$publicationFormat->getIdentificationCodes()}
{assign var=identificationCodes value=$identificationCodes->toArray()}
{if $identificationCodes}
	<div class="bookIdentificationSpecs">
	{foreach from=$identificationCodes item=identificationCode}
		<div id="bookIdentificationSpecs-{$identificationCode->getCode()|escape}">
			{$identificationCode->getNameForONIXCode()|escape}: {$identificationCode->getValue()|escape}
		</div>
	{/foreach}{* identification codes *}
	</div>
{/if}{* $identificationCodes *}
{assign var="publicationFormatId" value=$publicationFormat->getId()}
{if !empty($availableFiles.$publicationFormatId)}
	<div class="ecommerce">
		{if $availableFiles.$publicationFormatId|@count == 1}
			{* FIXME: unimplemented. One file available; shortcut to purchase *}
		{else}
			{* FIXME: unimplemented. Several files available; display options *}
		{/if}
	</div>
{/if}{* !empty($availableFiles) *}