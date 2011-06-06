{**
 * controllers/tab/settings/formImageView.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Form image view.
 *
 *}

<br />
<img src="{$publicFilesDir}/{$file.uploadName|escape:"url"}?{$file.dateUploaded}" width="{$file.width|escape}" height="{$file.height|escape}" style="border: 0;" alt={$commonAltText} />
<br />
{translate key="common.fileName"}: {$file.name|escape} {$file.dateUploaded|date_format:$datetimeFormatShort}
<div id="{$deleteLinkAction->getId()}" class="pkp_linkActions">
	{include file="linkAction/linkAction.tpl" action=$deleteLinkAction contextId=$fileSettingName}
</div>