{**
 * templates/workflow/productionFormatsAccordion.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Production workflow stage accordion contents
 *}

{iterate from=publicationFormats item=publicationFormat}
	<h3><a href="#">{$publicationFormat->getLocalizedTitle()|escape}</a></h3>
	<div>
		{url|assign:publicationFormatUrl router=$smarty.const.ROUTE_PAGE op="fetchPublicationFormat" monographId=$monograph->getId() publicationFormatId=$publicationFormat->getId() escape=false}
		{load_url_in_div id="publicationFormatDiv-"|concat:$publicationFormat->getId() class="stageParticipantGridContainer" url=$publicationFormatUrl}
	</div>
{/iterate}
