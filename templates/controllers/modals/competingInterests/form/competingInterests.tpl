{**
 * competingInterests.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display a press' competing interests policy
 *
 *}
{modal_title id="#competingInterests" key='reviewer.competingInterests' iconClass="fileManagement" canClose=1}

{fbvFormArea id="competingInterests"}
	{fbvFormSection}
		{$press->getLocalizedSetting('competingInterestGuidelines')|escape}
	{/fbvFormSection}
{/fbvFormArea}

{include file="form/formButtons.tpl"}

