<?php

/**
 * @file classes/publicationFormat/PublicationDate.inc.php
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PublicationDate
 * @ingroup publicationFormat
 * @see PublicationDateDAO
 *
 * @brief Basic class describing a publication date for a format (used on the ONIX templates for publication formats)
 */

class PublicationDate extends DataObject {

	/** @var $dateFormats the formats for this publication date */
	var $dateFormats;

	/**
	 * Constructor
	 */
	function __construct() {

		$onixCodelistItemDao = DAORegistry::getDAO('ONIXCodelistItemDAO');
		$this->dateFormats =& $onixCodelistItemDao->getCodes('List55');

		parent::__construct();
	}

	/**
	 * get publication format id
	 * @return int
	 */
	function getPublicationFormatId() {
		return $this->getData('representationId');
	}

	/**
	 * set publication format id
	 * @param $representationId int
	 */
	function setPublicationFormatId($representationId) {
		return $this->setData('representationId', $representationId);
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
		$onixCodelistItemDao = DAORegistry::getDAO('ONIXCodelistItemDAO');
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
		$format = $this->dateFormats[$this->getDateFormat()];
		if (stristr($format, '(H)')) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * determines whether or not the date should be parsed out with a date format.
	 * @return boolean
	 */
	function isFreeText() {
		$format = $this->dateFormats[$this->getDateFormat()];
		if (stristr($format, 'string')) {
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
	function getReadableDates() {
		$format = $this->dateFormats[$this->getDateFormat()];
		$dateFormatShort = Config::getVar('general', 'date_format_short');

		if ($this->isHijriCalendar()) {
			$format = preg_replace('/\s*\(H\)/i', '', $format);
		}

		// store the dates we parse.
		$dates = array();

		if (!$this->isFreeText()) { // this is not a free-form code
			// assume that the characters in the format match up with
			// the characters in the entered date.  Iterate until the end.

			$numbers = str_split($this->getDate());

			// these two should be the same length.
			assert(count($numbers) == count(str_split($format)));

			// Some date codes have two dates (ie, a range).
			// Split these up into both dates.
			if (substr_count($format, 'Y') == 8) {
				preg_match('/^(YYYY.*)(YYYY.*)$/', $format, $matches);
				$dateFormats = array($matches[1], $matches[2]);
			} else {
				$dateFormats = array($format);
			}

			foreach ($dateFormats as $format) {
				$formatCharacters = str_split($format);
				$previousFormatCharacter = '';
				$thisDate = '';
				$separator = '-';
				$containsMonth = false;

				for ($i = 0 ; $i < count($formatCharacters) ; $i ++) {
					switch ($formatCharacters[$i]) {
						// if there is a Time included, change the separator.
						// Do not include the number, add a space instead.
						case 'T':
							$separator = ':';
							$thisDate .= ' ';
							break;
						case 'M': // falls through to default. This is just a marker.
							$containsMonth = true;
						default:
							if ($i > 0 && $previousFormatCharacter != $formatCharacters[$i] && $previousFormatCharacter != 'T') {
							$thisDate .= $separator;
						}
						$thisDate .= $numbers[$i];
						break;
					}

					$previousFormatCharacter = $formatCharacters[$i];
				}

				// Perform date formatting here instead of in the template since
				// testing is easier.
				if ($containsMonth) {
					$thisDate = strftime($dateFormatShort, strtotime($thisDate));
				}

				$dates[] = $thisDate;
				// remove the first date from the numbers and extract again.
				$numbers = array_slice($numbers, count($formatCharacters));
			}

		} else {
			$dates[] = $this->getDate();
		}
		return $dates;
	}

	/**
	 * Return a best guess of the UNIX time corresponding to this date
	 * @return int? Number of seconds since the UNIX epoch, or null if it could not be determined
	 * FIXME: Hirji support
	 */
	function getUnixTime() {
		$date = $this->getDate();
		switch ($this->getDateFormat()) {
			case '12': return strtotime($date);
			case '05': return strtotime("$date-01-01");
			case '01': return strtotime("$date-01");
			case '13': // FIXME: improve resolution below day
			case '14': // FIXME: improve resolution below day
			case '06': // FIXME: improve resolution below day
			case '00': return strtotime(substr($date, 0, 4) . '-' . substr($date, 4, 2) . '-' . substr($date, 6, 2));
		}
		return null;
	}
}


