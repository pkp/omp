{**
 * templates/catalog/preview.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display a monograph preview.
 *}

<div class="pkp_pages_catalog_previewAbstract">
	{$publishedMonograph->getLocalizedAbstract()|strip_unsafe_html}
</div>
