{**
 * templates/catalog/monograph.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Present a monograph.
 *}
<li class="pkp_catalog_monograph monograph_id_{$monograph->getId()|escape}">
	<div class="pkp_catalog_monograph_image">
		<!-- FIXME: Image goes here -->
	</div>
	<div class="pkp_catalog_monograph_title">
		{$monograph->getLocalizedTitle()|escape}
	</div>
	<div class="pkp_catalog_monograph_authorship">
		{$monograph->getAuthorString()|escape}
	</div>
	<div class="pkp_catalog_monograph_date">
		{$monograph->getDatePublished()|date_format:$dateFormatShort}
	</div>
	<div class="pkp_catalog_monograph_series">
		{$monograph->getSeriesTitle()|escape}
	</div>
	<div class="pkp_catalog_monograph_abstract">
		<span class="pkp_catalog_monograph_abstractLabel">{translate key="submission.synopsis"}:</span>
		{$monograph->getLocalizedAbstract()|strip_unsafe_html|truncate:80}
	</div>
</li>
