<?php

/**
 * @file classes/publicationFormat/PublicationDate.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PublicationDate
 * @ingroup publicationFormat
 * @see PublicationDateDAO
 *
 * @brief Basic class describing a publication date for a format (used on the ONIX templates for publication formats)
 */

class PublicationDate extends DataObject {
	/**
	 * Constructor
	 */
	function PublicationDate() {
		parent::DataObject();
	}

	/**
	 * get publication format id
	 * @return int
	 */
	function getPublicationFormatId() {
		return $this->getData('publicationFormatId');
	}

	/**
	 * set publication format id
	 * @param $pressId int
	 */
	function setPublicationformatId($publicationFormatId) {
		return $this->setData('publicationFormatId', $publicationFormatId);
	}

	/**
	 * Set the ONIX code for this publication date
	 * @param $role string
	 */
	function setRole($role) {
		$this->setData('role', $role);
	}

	/**
	 * Get the ONIX code for the publication date
	 * @return string
	 */
	function getRole() {
		return $this->getData('role');
	}

	/**
	 * Set the date format for this publication date (ONIX Codelist List55)
	 * @param $format string
	 */
	function setDateFormat($format) {
		$this->setData('dateFormat', $format);
	}

	/**
	 * Get the date format for the publication date
	 * @return string
	 */
	function getDateFormat() {
		return $this->getData('dateFormat');
	}

	/**
	 * Get the human readable name for this ONIX code
	 * @return string
	 */
	function getNameForONIXCode() {
		$onixCodelistItemDao =& DAORegistry::getDAO('ONIXCodelistItemDAO');
		$codes =& $onixCodelistItemDao->getCodes('List163'); // List163 is for Publication date, Embargo date, Announcement date, etc
		return $codes[$this->getRole()];
	}

	/**
	 * Set the date for this publication date
	 * @param $date string
	 */
	function setDate($date) {
		$this->setData('date', $date);
	}

	/**
	 * Get the date for the publication date
	 * @return string
	 */
	function getDate() {
		return $this->getData('date');
	}

	/**
	 * Determines if this date is from the Hijri calendar.
	 * @return boolean
	 */
	function isHijriCalendar() {
		$onixCodelistItemDao =& DAORegistry::getDAO('ONIXCodelistItemDAO');
		$dateFormats =& $onixCodelistItemDao->getCodes('List55');
		$format = $dateFormats[$this->getDateFormat()];
		if (stristr($format, '(H)')) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * returns a readable version of the entered date, based on
	 * the format specified from List55.  Necessary, so it can be
	 * parsed correctly in the template.
	 * @return string
	 */
	function getReadableDate() {
		$onixCodelistItemDao =& DAORegistry::getDAO('ONIXCodelistItemDAO');
		$dateFormats =& $onixCodelistItemDao->getCodes('List55');
		$format = $dateFormats[$this->getDateFormat()];

		if ($this->isHijriCalendar()) {
			$format = preg_replace('/\s*\(H\)/i', '', $format);
		}

		if (!stristr($format, 'string')) { // this is not a free-form code
			// assume that the characters in the format match up with
			// the characters in the entered date.  Iterate until the end.

			$numbers = str_split($this->getDate());
			$formatCharacters = str_split($format);

			// these two should be the same length.
			assert(count($numbers) == count($formatCharacters));

			$previousFormatCharacter = '';
			$date = '';
			for ($i = 0 ; $i < count($numbers) ; $i ++) {

				if ($i > 0 && $previousFormatCharacter != $formatCharacters[$i]) {
					$date .= '-';
				}

				$date .= $numbers[$i];
				$previousFormatCharacter = $formatCharacters[$i];
			}
		} else {
			$date = $this->getDate();
		}

		return $date;
	}
}

?>
