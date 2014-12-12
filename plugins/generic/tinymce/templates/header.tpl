{**
 * templates/header.tpl
 *
 * Copyright (c) 2014 Simon Fraser University Library
 * Copyright (c) 2003-2014 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Header content for loading and configuring the TinyMCE control.
 *}
<script type="text/javascript" src="{$baseUrl}/lib/pkp/lib/vendor/tinymce/tinymce/{if $useMinifiedJavaScript}tinymce.min.js{else}tinymce.js{/if}"></script>
<script type="text/javascript">
	tinymce.PluginManager.load('jbimages', '{$baseUrl}/plugins/generic/tinymce/plugins/justboil.me/{if $useMinifiedJavaScript}plugin.min.js{else}plugin.js{/if}');
	tinymce.PluginManager.load('pkpTags', '{$baseUrl}/plugins/generic/tinymce/plugins/pkpTags/plugin.js');
	tinymce.init({ldelim}
		width: "100%",
		entity_encoding: "raw",
		plugins: "paste,fullscreen,link,code,-jbimages,-pkpTags,noneditable",
		language: "{$currentLocale|substr:0:2}",
		relative_urls: false,
		forced_root_block: "p",
		paste_auto_cleanup_on_paste: true,
		apply_source_formatting: false,
		theme : "modern",
		menubar: false,
		statusbar: false,
		toolbar: "cut copy paste | bold italic underline bullist numlist | link unlink code fullscreen | jbimages | pkpTags",
		init_instance_callback: $.pkp.controllers.SiteHandler.prototype.triggerTinyMCEInitialized,
		setup: $.pkp.controllers.SiteHandler.prototype.triggerTinyMCESetup,
		content_css: "{$baseUrl}/plugins/generic/tinymce/styles/content.css"
	{rdelim});
</script>
