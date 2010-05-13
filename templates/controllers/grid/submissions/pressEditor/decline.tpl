{**
 * decline.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Form to decline the submission and send a message to the author
 *}
 
<h4>{translate key="You are about to decline this submission"}</h4>

<p>{translate key="Personal Message"}</p>
{fbvFormSection title="Personal Message" for="personalMessage" float=$fbvStyles.float.LEFT}
	{fbvElement type="textarea" id="personalMessage" size=$fbvStyles.size.SMALL measure=$fbvStyles.measure.3OF4}<br/>
{/fbvFormSection}