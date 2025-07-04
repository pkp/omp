<?php

/**
 * @file classes/file/LibraryFileManager.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class LibraryFileManager
 *
 * @ingroup file
 *
 * @brief Wrapper class for uploading files to a site/context' library directory.
 */

namespace APP\file;

use PKP\context\LibraryFile;
use PKP\file\PKPLibraryFileManager;

class LibraryFileManager extends PKPLibraryFileManager
{
    /**
     * Get the file suffix for the given file type
     *
     * @param int $type LIBRARY_FILE_TYPE_...
     */
    public function getFileSuffixFromType($type)
    {
        $typeSuffixMap = & $this->getTypeSuffixMap();
        return $typeSuffixMap[$type];
    }

    /**
     * Get the type => suffix mapping array
     *
     * @return array
     */
    public function &getTypeSuffixMap()
    {
        static $map = [
            LibraryFile::LIBRARY_FILE_TYPE_CONTRACT => 'CON',
        ];
        $parent = parent::getTypeSuffixMap();
        $map = array_merge($map, $parent);
        return $map;
    }

    /**
     * Get the type => locale key mapping array
     *
     * @return array
     */
    public function &getTypeTitleKeyMap()
    {
        static $map = [
            LibraryFile::LIBRARY_FILE_TYPE_CONTRACT => 'settings.libraryFiles.category.contracts',
        ];
        $parent = parent::getTypeTitleKeyMap();
        $map = array_merge($map, $parent);
        return $map;
    }

    /**
     * Get the type => name mapping array
     *
     * @return array
     */
    public function &getTypeNameMap()
    {
        static $map = [
            LibraryFile::LIBRARY_FILE_TYPE_CONTRACT => 'contacts',
        ];
        $parent = parent::getTypeNameMap();
        $map = array_merge($map, $parent);
        return $map;
    }
}
