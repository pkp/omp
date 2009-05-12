<?php

/**
 * @file classes/mail/EmailTemplateDAO.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class EmailTemplateDAO
 * @ingroup mail
 * @see EmailTemplate
 *
 * @brief Operations for retrieving and modifying Email Template objects.
 */

// $Id$


import('mail.EmailTemplate');

class EmailTemplateDAO extends DAO {
	/**
	 * Retrieve a base email template by key.
	 * @param $emailKey string
	 * @param $pressId int
	 * @return BaseEmailTemplate
	 */
	function &getBaseEmailTemplate($emailKey, $pressId) {
		$result =& $this->retrieve(
			'SELECT	d.email_key,
				d.can_edit,
				d.can_disable,
				COALESCE(e.enabled, 1) AS enabled,
				e.email_id,
				e.press_id,
				d.from_role_id,
				d.to_role_id
			FROM	email_templates_default d
				LEFT JOIN email_templates e ON (d.email_key = e.email_key AND e.press_id = ?)
			WHERE	d.email_key = ?',
			array($pressId, $emailKey)
		);

		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner =& $this->_returnBaseEmailTemplateFromRow($result->GetRowAssoc(false));
		}

		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Retrieve localized email template by key.
	 * @param $emailKey string
	 * @param $pressId int
	 * @return LocaleEmailTemplate
	 */
	function &getLocaleEmailTemplate($emailKey, $pressId) {
		$result =& $this->retrieve(
			'SELECT	d.email_key,
				d.can_edit,
				d.can_disable,
				COALESCE(e.enabled, 1) AS enabled,
				e.email_id,
				e.press_id,
				d.from_role_id,
				d.to_role_id
			FROM	email_templates_default d
				LEFT JOIN email_templates e ON (d.email_key = e.email_key AND e.press_id = ?)
			WHERE	d.email_key = ?',
			array($pressId, $emailKey)
		);

		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner =& $this->_returnLocaleEmailTemplateFromRow($result->GetRowAssoc(false));
		} else {
			$result->Close();
			unset($result);

			// Check to see if there's a custom email template. This is done in PHP to avoid
			// having to do a full outer join or union in SQL.
			$result =& $this->retrieve(
				'SELECT	e.email_key,
					1 AS can_edit,
					1 AS can_disable,
					e.enabled,
					e.email_id,
					e.press_id,
					NULL AS from_role_id,
					NULL AS to_role_id
				FROM	email_templates e
					LEFT JOIN email_templates_default d ON (e.email_key = d.email_key)
				WHERE	d.email_key IS NULL AND
					e.press_id = ? AND
					e.email_key = ?',
				array($pressId, $emailKey)
			);
			if ($result->RecordCount() != 0) {
				$returner =& $this->_returnLocaleEmailTemplateFromRow($result->GetRowAssoc(false));
			}
		}

		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Retrieve an email template by key.
	 * @param $emailKey string
	 * @param $locale string
	 * @param $pressId int
	 * @return EmailTemplate
	 */
	function &getEmailTemplate($emailKey, $locale, $pressId) {
		$result =& $this->retrieve(
			'SELECT	COALESCE(ed.subject, dd.subject) AS subject,
				COALESCE(ed.body, dd.body) AS body,
				COALESCE(e.enabled, 1) AS enabled,
				d.email_key, d.can_edit, d.can_disable,
				e.press_id, e.email_id,
				dd.locale,
				d.from_role_id, d.to_role_id
			FROM	email_templates_default d
				LEFT JOIN email_templates_default_data dd ON (dd.email_key = d.email_key)
				LEFT JOIN email_templates e ON (d.email_key = e.email_key AND e.press_id = ?)
				LEFT JOIN email_templates_data ed ON (ed.email_key = e.email_key AND ed.press_id = e.press_id AND ed.locale = dd.locale)
			WHERE	d.email_key = ? AND
				dd.locale = ?',
			array($pressId, $emailKey, $locale)
		);

		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner =& $this->_returnEmailTemplateFromRow($result->GetRowAssoc(false));
			$returner->setCustomTemplate(false);
		} else {
			$result->Close();
			unset($result);

			// Check to see if there's a custom email template. This is done in PHP to avoid
			// having to do a full outer join or union in SQL.
			$result =& $this->retrieve(
				'SELECT	ed.subject,
					ed.body,
					1 AS enabled,
					e.email_key,
					1 AS can_edit,
					0 AS can_disable,
					e.press_id,
					e.email_id,
					ed.locale,
					NULL AS from_role_id,
					NULL AS to_role_id
				FROM	email_templates e
					LEFT JOIN email_templates_data ed ON (ed.email_key = e.email_key AND ed.press_id = e.press_id)
					LEFT JOIN email_templates_default d ON (e.email_key = d.email_key)
				WHERE	d.email_key IS NULL AND
					e.press_id = ? AND
					e.email_key = ? AND
					ed.locale = ?',
				array($pressId, $emailKey, $locale)
			);
			if ($result->RecordCount() != 0) {
				$returner =& $this->_returnEmailTemplateFromRow($result->GetRowAssoc(false));
				$returner->setCustomTemplate(true);
			}
		}

		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Internal function to return an email template object from a row.
	 * @param $row array
	 * @return BaseEmailTemplate
	 */
	function &_returnBaseEmailTemplateFromRow(&$row) {
		$emailTemplate =& new BaseEmailTemplate();
		$emailTemplate->setEmailId($row['email_id']);
		$emailTemplate->setPressId($row['preess_id']);
		$emailTemplate->setEmailKey($row['email_key']);
		$emailTemplate->setEnabled($row['enabled'] == null ? 1 : $row['enabled']);
		$emailTemplate->setCanDisable($row['can_disable']);
		$emailTemplate->setFromRoleId($row['from_role_id']);
		$emailTemplate->setToRoleId($row['to_role_id']);

		HookRegistry::call('EmailTemplateDAO::_returnBaseEmailTemplateFromRow', array(&$emailTemplate, &$row));

		return $emailTemplate;
	}

	/**
	 * Internal function to return an email template object from a row.
	 * @param $row array
	 * @return LocaleEmailTemplate
	 */
	function &_returnLocaleEmailTemplateFromRow(&$row) {
		$emailTemplate =& new LocaleEmailTemplate();
		$emailTemplate->setEmailId($row['email_id']);
		$emailTemplate->setPressId($row['press_id']);
		$emailTemplate->setEmailKey($row['email_key']);
		$emailTemplate->setEnabled($row['enabled'] == null ? 1 : $row['enabled']);
		$emailTemplate->setCanDisable($row['can_disable']);
		$emailTemplate->setFromRoleId($row['from_role_id']);
		$emailTemplate->setToRoleId($row['to_role_id']);

		$emailTemplate->setCustomTemplate(false);

		if (!HookRegistry::call('EmailTemplateDAO::_returnLocaleEmailTemplateFromRow', array(&$emailTemplate, &$row))) {
			$result =& $this->retrieve(
				'SELECT	dd.locale,
					dd.description,
					COALESCE(ed.subject, dd.subject) AS subject,
					COALESCE(ed.body, dd.body) AS body
				FROM	email_templates_default_data dd
					LEFT JOIN email_templates_data ed ON (dd.email_key = ed.email_key AND dd.locale = ed.locale AND ed.press_id = ?)
				WHERE	dd.email_key = ?',
				array($row['press_id'], $row['email_key'])
			);

			while (!$result->EOF) {
				$dataRow =& $result->GetRowAssoc(false);
				$emailTemplate->addLocale($dataRow['locale']);
				$emailTemplate->setSubject($dataRow['locale'], $dataRow['subject']);
				$emailTemplate->setBody($dataRow['locale'], $dataRow['body']);
				$emailTemplate->setDescription($dataRow['locale'], $dataRow['description']);
				$result->MoveNext();
			}
			$result->Close();
			unset($result);

			// Retrieve custom email contents as well; this is done in PHP to avoid
			// using a SQL outer join or union.
			$result =& $this->retrieve(
				'SELECT	ed.locale,
					ed.subject,
					ed.body
				FROM	email_templates_data ed
					LEFT JOIN email_templates_default_data dd ON (ed.email_key = dd.email_key AND dd.locale = ed.locale)
				WHERE	ed.press_id = ? AND
					ed.email_key = ? AND
					dd.email_key IS NULL',
				array($row['press_id'], $row['email_key'])
			);

			while (!$result->EOF) {
				$dataRow =& $result->GetRowAssoc(false);
				$emailTemplate->addLocale($dataRow['locale']);
				$emailTemplate->setSubject($dataRow['locale'], $dataRow['subject']);
				$emailTemplate->setBody($dataRow['locale'], $dataRow['body']);
				$result->MoveNext();

				$emailTemplate->setCustomTemplate(true);
			}

			$result->Close();
			unset($result);
		}

		return $emailTemplate;
	}

	/**
	 * Internal function to return an email template object from a row.
	 * @param $row array
	 * @return EmailTemplate
	 */
	function &_returnEmailTemplateFromRow(&$row, $isCustomTemplate=null) {
		$emailTemplate =& new EmailTemplate();
		$emailTemplate->setEmailId($row['email_id']);
		$emailTemplate->setPressId($row['press_id']);
		$emailTemplate->setEmailKey($row['email_key']);
		$emailTemplate->setLocale($row['locale']);
		$emailTemplate->setSubject($row['subject']);
		$emailTemplate->setBody($row['body']);
		$emailTemplate->setEnabled($row['enabled'] == null ? 1 : $row['enabled']);
		$emailTemplate->setCanDisable($row['can_disable']);
		$emailTemplate->setFromRoleId($row['from_role_id']);
		$emailTemplate->setToRoleId($row['to_role_id']);

		if ($isCustomTemplate !== null) {
			$emailTemplate->setCustomTemplate($isCustomTemplate);
		}

		HookRegistry::call('EmailTemplateDAO::_returnEmailTemplateFromRow', array(&$emailTemplate, &$row));

		return $emailTemplate;
	}

	/**
	 * Insert a new base email template.
	 * @param $emailTemplate BaseEmailTemplate
	 */	
	function insertBaseEmailTemplate(&$emailTemplate) {
		return $this->update(
			'INSERT INTO email_templates
				(press_id, email_key, enabled)
				VALUES
				(?, ?, ?)',
			array(
				$emailTemplate->getPressId(),
				$emailTemplate->getEmailKey(),
				$emailTemplate->getEnabled() == null ? 0 : 1
			)
		);
		$emailTemplate->setEmailId($this->getInsertEmailId());
		return $emailTemplate->getEmailId();
	}

	/**
	 * Update an existing base email template.
	 * @param $emailTemplate BaseEmailTemplate
	 */
	function updateBaseEmailTemplate(&$emailTemplate) {
		return $this->update(
			'UPDATE	email_templates
			SET	enabled = ?
			WHERE	email_id = ?',
			array(
				$emailTemplate->getEnabled() == null ? 0 : 1,
				$emailTemplate->getEmailId()
			)
		);
	}

	/**
	 * Insert a new localized email template.
	 * @param $emailTemplate LocaleEmailTemplate
	 */	
	function insertLocaleEmailTemplate(&$emailTemplate) {
		$this->insertBaseEmailTemplate($emailTemplate);
		return $this->updateLocaleEmailTemplateData($emailTemplate);
	}

	/**
	 * Update an existing localized email template.
	 * @param $emailTemplate LocaleEmailTemplate
	 */
	function updateLocaleEmailTemplate(&$emailTemplate) {
		$this->updateBaseEmailTemplate($emailTemplate);
		return $this->updateLocaleEmailTemplateData($emailTemplate);
	}

	/**
	 * Insert/update locale-specific email template data.
	 * @param $emailTemplate LocaleEmailTemplate
	 */
	function updateLocaleEmailTemplateData(&$emailTemplate) {
		foreach ($emailTemplate->getLocales() as $locale) {
			$result =& $this->retrieve(
				'SELECT	COUNT(*)
				FROM	email_templates_data
				WHERE	email_key = ? AND
					locale = ? AND
					press_id = ?',
				array($emailTemplate->getEmailKey(), $locale, $emailTemplate->getPressId())
			);

			if ($result->fields[0] == 0) {
				$this->update(
					'INSERT INTO email_templates_data
					(email_key, locale, press_id, subject, body)
					VALUES
					(?, ?, ?, ?, ?)',
					array($emailTemplate->getEmailKey(), $locale, $emailTemplate->getPressId(), $emailTemplate->getSubject($locale), $emailTemplate->getBody($locale))
				);

			} else {
				$this->update(
					'UPDATE	email_templates_data
					SET	subject = ?,
						body = ?
					WHERE	email_key = ? AND
						locale = ? AND
						press_id = ?',
					array($emailTemplate->getSubject($locale), $emailTemplate->getBody($locale), $emailTemplate->getEmailKey(), $locale, $emailTemplate->getPressId())
				);
			}

			$result->Close();
			unset($result);
		}
	}

	/**
	 * Delete an email template by key.
	 * @param $emailKey string
	 * @param $pressId int
	 */
	function deleteEmailTemplateByKey($emailKey, $pressId) {
		$this->update(
			'DELETE FROM email_templates_data WHERE email_key = ? AND press_id = ?',
			array($emailKey, $pressId)
		);
		return $this->update(
			'DELETE FROM email_templates WHERE email_key = ? AND press_id = ?',
			array($emailKey, $pressId)
		);
	}

	/**
	 * Retrieve all presses.
	 * @param $locale string
	 * @param $pressId int
	 * @param $rangeInfo object optional
	 * @return array Presses ordered by sequence
	 */
	function &getEmailTemplates($locale, $pressId, $rangeInfo = null) {
		$emailTemplates = array();

		$result =& $this->retrieveRange(
			'SELECT	COALESCE(ed.subject, dd.subject) AS subject,
				COALESCE(ed.body, dd.body) AS body,
				COALESCE(e.enabled, 1) AS enabled,
				d.email_key, d.can_edit, d.can_disable,
				e.press_id, e.email_id,
				dd.locale,
				d.from_role_id, d.to_role_id
			FROM	email_templates_default d
				LEFT JOIN email_templates_default_data dd ON (dd.email_key = d.email_key)
				LEFT JOIN email_templates e ON (d.email_key = e.email_key AND e.press_id = ?)
				LEFT JOIN email_templates_data ed ON (ed.email_key = e.email_key AND ed.press_id = e.press_id AND ed.locale = dd.locale)
			WHERE	dd.locale = ?',
			array($pressId, $locale),
			$rangeInfo
		);

		while (!$result->EOF) {
			$emailTemplates[] =& $this->_returnEmailTemplateFromRow($result->GetRowAssoc(false), false);
			$result->moveNext();
		}
		$result->Close();
		unset($result);

		// Fetch custom email templates as well; this is done in PHP
		// to avoid a union or full outer join call in SQL.
		$result =& $this->retrieve(
			'SELECT	ed.subject,
				ed.body,
				e.enabled,
				e.email_key,
				1 AS can_edit,
				1 AS can_disable,
				e.press_id,
				e.email_id,
				ed.locale,
				NULL AS from_role_id,
				NULL AS to_role_id
			FROM	email_templates e
				LEFT JOIN email_templates_data ed ON (e.email_key = ed.email_key AND ed.press_id = e.press_id AND ed.locale = ?)
				LEFT JOIN email_templates_default d ON (e.email_key = d.email_key)
			WHERE	e.press_id = ? AND
				d.email_key IS NULL',
			array($locale, $pressId)
		);

		while (!$result->EOF) {
			$row = $result->getRowAssoc(false);
			$emailTemplates[] =& $this->_returnEmailTemplateFromRow($result->GetRowAssoc(false), true);
			$result->moveNext();
		}
		$result->Close();
		unset($result);

		// Sort all templates by email key.
		$compare = create_function('$t1, $t2', 'return strcmp($t1->getEmailKey(), $t2->getEmailKey());');
		usort ($emailTemplates, $compare);

		return $emailTemplates;
	}

	/**
	 * Get the ID of the last inserted email template.
	 * @return int
	 */
	function getInsertEmailId() {
		return $this->getInsertId('email_templates', 'emailId');
	}

	/**
	 * Delete all email templates for a specific press.
	 * @param $pressId int
	 */
	function deleteEmailTemplatesByPress($pressId) {
		$this->update(
			'DELETE FROM email_templates_data WHERE press_id = ?', $pressId
		);
		return $this->update(
			'DELETE FROM email_templates WHERE press_id = ?', $pressId
		);
	}

	/**
	 * Delete all email templates for a specific locale.
	 * @param $locale string
	 */
	function deleteEmailTemplatesByLocale($locale) {
		$this->update(
			'DELETE FROM email_templates_data WHERE locale = ?', $locale
		);
	}

	/**
	 * Delete all default email templates for a specific locale.
	 * @param $locale string
	 */
	function deleteDefaultEmailTemplatesByLocale($locale) {
		$this->update(
			'DELETE FROM email_templates_default_data WHERE locale = ?', $locale
		);
	}

	/**
	 * Check if a template exists with the given email key for a press.
	 * @param $emailKey string
	 * @param $pressId int
	 * @return boolean
	 */
	function templateExistsByKey($emailKey, $pressId) {
		$result =& $this->retrieve(
			'SELECT	COUNT(*)
			FROM	email_templates
			WHERE	email_key = ? AND
				press_id = ?',
			array(
				$emailKey,
				$pressId
			)
		);
		if (isset($result->fields[0]) && $result->fields[0] != 0) {
			$result->Close();
			unset($result);
			return true;
		}

		$result->Close();
		unset($result);

		$result =& $this->retrieve(
			'SELECT COUNT(*)
				FROM email_templates_default
				WHERE email_key = ?',
			$emailKey
		);
		if (isset($result->fields[0]) && $result->fields[0] != 0) {
			$returner = true;
		} else {
			$returner = false;
		}

		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Check if a custom template exists with the given email key for a press.
	 * @param $emailKey string
	 * @param $pressId int
	 * @return boolean
	 */
	function customTemplateExistsByKey($emailKey, $pressId) {
		$result =& $this->retrieve(
			'SELECT	COUNT(*)
			FROM	email_templates e
				LEFT JOIN email_templates_default d ON (e.email_key = d.email_key)
			WHERE	e.email_key = ? AND
				d.email_key IS NULL AND
				e.press_id = ?',
			array(
				$emailKey,
				$pressId
			)
		);
		$returner = (isset($result->fields[0]) && $result->fields[0] != 0);

		$result->Close();
		unset($result);

		return $returner;
	}
}

?>
