{**
 * userFormHeader.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Common header for forms for creating/editing a user.
 *}
{literal}
<script type="text/javascript">
	<!--
	$(document).ready(function(){
		// Handle user details form toggle
		$("#toggleFormMore").show();
		$("#userFormExtendedContainer").hide();

		$("#toggleMore").click(function() {
			$("#toggleFormMore").hide();
			$("#toggleFormLess").show();
			$("#userFormExtendedContainer").show('slow');
			return false;
		});

		$("#toggleLess").click(function() {
			$("#toggleFormLess").hide();
			$("#toggleFormMore").show();
			$("#userFormExtendedContainer").hide('slow');
			return false;
		});

		// Handle interests keywords
		$("#interestsTextOnly").hide();
		$("#interests").tagit({
			{/literal}{if $existingInterests}{literal}
			// This is the list of interests in the system used to populate the autocomplete
			availableTags: [{/literal}{foreach name=existingInterests from=$existingInterests item=interest}"{$interest|escape|escape:'javascript'}"{if !$smarty.foreach.existingInterests.last}, {/if}{/foreach}{literal}],{/literal}{/if}
			// This is the list of the user's interests that have already been saved
			{if $interestsKeywords}{literal}currentTags: [{/literal}{foreach name=currentInterests from=$interestsKeywords item=interest}"{$interest|escape|escape:'javascript'}"{if !$smarty.foreach.currentInterests.last}, {/if}{/foreach}{literal}]{/literal}
			{else}{literal}currentTags: []{/literal}{/if}{literal}
		});
	});
	// -->
</script>
{/literal}

{if !$userId}
{assign var="passwordRequired" value="true"}

{literal}
<script type="text/javascript">
	<!--
	function setGenerateRandom(value) {
		var userForm = document.getElementById('userForm');
		if (value) {
			userForm.password.value='********';
			userForm.password2.value='********';
			userForm.password.disabled=1;
			userForm.password2.disabled=1;
			userForm.sendNotify.checked=1;
			userForm.sendNotify.disabled=1;
		} else {
			userForm.password.disabled=0;
			userForm.password2.disabled=0;
			userForm.sendNotify.disabled=0;
			userForm.password.value='';
			userForm.password2.value='';
			userForm.password.focus();
		}
	}

	function enablePasswordFields() {
		var userForm = document.getElementById('userForm');
		userForm.password.disabled=0;
		userForm.password2.disabled=0;
	}

	function generateUsername() {
		var userForm = document.getElementById('userForm');
		var req = makeAsyncRequest();

		if (userForm.lastName.value == "") {
			alert("{/literal}{translate key="grid.user.mustProvideName"}{literal}");
			return;
		}

		req.onreadystatechange = function() {
			if (req.readyState == 4) {
				userForm.username.value = req.responseText;
			}
		}
		sendAsyncRequest(req, '{/literal}{url op="suggestUsername" firstName="REPLACE1" lastName="REPLACE2" escape=false}{literal}'.replace('REPLACE1', escape(userForm.firstName.value)).replace('REPLACE2', escape(userForm.lastName.value)), null, 'get');
	}

	// -->
</script>
{/literal}
{/if} {* !$userId *}
