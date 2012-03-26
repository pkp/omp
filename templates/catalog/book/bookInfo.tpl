{**
 * templates/catalog/book/bookInfo.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display the information pane of a public-facing book view in the catalog.
 *}

<script type="text/javascript">
	// Attach the tab handler.
	$(function() {ldelim}
		$('#bookInfoTabs').pkpHandler(
			'$.pkp.controllers.TabHandler'
		);
	{rdelim});
</script>

<div class="bookInfo">
	<h3>{$publishedMonograph->getLocalizedTitle()|strip_unsafe_html}</h3>
	<div class="authorName">{$publishedMonograph->getAuthorString()}</div>
	Here's some more stuff.

	<div id="bookInfoTabs">
		<ul>
			<li><a href="#abstractTab">{translate key="submission.synopsis"}</a></li>
			<li><a href="#sharingTab">{translate key="submission.sharing"}</a></li>
		</ul>

		<div id="abstractTab">
			{$publishedMonograph->getLocalizedAbstract()|strip_unsafe_html}
		</div>
		<div id="sharingTab">
			{call_hook name="Templates::Catalog::Book::BookInfo::Sharing"}
		</div>
	</div>
</div>
