{**
 * information.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Press information page.

 *}
{if !$contentOnly}
	{strip}
	{include file="common/header.tpl"}
	{/strip}
{/if}

<p>{$content|nl2br}</p>

{if !$contentOnly}
	{include file="common/footer.tpl"}
{/if}

