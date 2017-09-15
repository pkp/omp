#!/bin/bash

#
# buildpkg.sh
#
# Copyright (c) 2014-2017 Simon Fraser University
# Copyright (c) 2003-2017 John Willinsky
# Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
#
# Script to create an OMP package for distribution.
#
# Usage: buildpkg.sh <version> [<tag>]
#

GITREP=git://github.com/pkp/omp.git

if [ -z "$1" ]; then
	echo "Usage: $0 <version> [<tag>-<branch>]";
	exit 1;
fi

VERSION=$1
TAG=$2
PREFIX=omp
BUILD=$PREFIX-$VERSION
TMPDIR=`mktemp -d $PREFIX.XXXXXX` || exit 1

EXCLUDE="dbscripts/xml/data/locale/en_US/sample.xml		\
dbscripts/xml/data/locale/te_ST					\
dbscripts/xml/data/sample.xml					\
docs/dev							\
docs/doxygen							\
lib/adodb/CHANGED_FILES						\
lib/adodb/diff							\
lib/smarty/CHANGED_FILES					\
lib/smarty/diff							\
locale/te_ST							\
cache/*.php							\
tools/buildpkg.sh						\
tools/genLocaleReport.sh					\
tools/genTestLocale.php						\
tools/test							\
lib/pkp/tools/travis						\
plugins/generic/translator					\
plugins/generic/customBlockManager/.git				\
plugins/generic/emailLogger					\
plugins/generic/staticPages/.git				\
lib/pkp/plugins/*/*/tests					\
plugins/*/*/tests						\
tests								\
lib/pkp/tests							\
.git								\
.openshift							\
.travis.yml							\
lib/pkp/.git							\
lib/pkp/lib/components/*.js					\
lib/pkp/lib/components/*.css					\
lib/pkp/lib/vendor/components					\
lib/pkp/lib/vendor/composer					\
lib/pkp/lib/vendor/ezyang/htmlpurifier/art			\
lib/pkp/lib/vendor/ezyang/htmlpurifier/benchmarks		\
lib/pkp/lib/vendor/ezyang/htmlpurifier/configdog		\
lib/pkp/lib/vendor/ezyang/htmlpurifier/docs			\
lib/pkp/lib/vendor/ezyang/htmlpurifier/extras			\
lib/pkp/lib/vendor/ezyang/htmlpurifier/maintenance		\
lib/pkp/lib/vendor/ezyang/htmlpurifier/smoketests		\
lib/pkp/lib/vendor/ezyang/htmlpurifier/tests			\
lib/pkp/lib/vendor/kriswallsmith				\
lib/pkp/lib/vendor/leafo/lessphp/tests				\
lib/pkp/lib/vendor/leafo/lessphp/docs				\
lib/pkp/lib/vendor/moxiecode/plupload/examples			\
lib/pkp/lib/vendor/phpmailer/phpmailer/docs			\
lib/pkp/lib/vendor/phpmailer/phpmailer/examples			\
lib/pkp/lib/vendor/phpmailer/phpmailer/test			\
lib/pkp/lib/vendor/robloach					\
lib/pkp/lib/vendor/smarty/smarty/demo				\
lib/pkp/lib/vendor/symfony					\
lib/pkp/lib/vendor/phpunit					\
lib/pkp/lib/vendor/phpdocumentor/reflection-docblock		\
lib/pkp/lib/vendor/doctrine/instantiator/tests			\
lib/pkp/lib/vendor/sebastian/global-state/tests			\
lib/pkp/lib/vendor/sebastian/comparator/tests			\
lib/pkp/lib/vendor/sebastian/diff/tests				\
lib/pkp/lib/vendor/oyejorge/less.php/test			\
lib/pkp/js/lib/pnotify/build-tools				\
lib/pkp/lib/swordappv2/.git					\
lib/pkp/lib/swordappv2/test					\
.babelrc          \
.editorconfig     \
.eslintignore     \
.eslintrc.js      \
.postcssrc.js     \
package.json      \
webpack.config.js \
lib/ui-library"

cd $TMPDIR

echo -n "Cloning $GITREP and checking out tag $TAG ... "
git clone -b $TAG --depth 1 -q -n $GITREP $BUILD || exit 1
cd $BUILD
git checkout -q $TAG || exit 1
echo "Done"

echo -n "Checking out corresponding submodules ... "
git submodule -q update --init --recursive >/dev/null || exit 1
echo "Done"

echo -n "Installing composer dependencies ... "
cd lib/pkp
composer.phar install
cd ../..

echo -n "Run webpack build process"
npm run build
echo "Done"

echo -n "Preparing package ... "
cp config.TEMPLATE.inc.php config.inc.php
find . \( -name .gitignore -o -name .gitmodules -o -name .keepme \) -exec rm '{}' \;
rm -rf $EXCLUDE
echo "Done"

cd ..

echo -n "Creating archive $BUILD.tar.gz ... "
tar -zhcf ../$BUILD.tar.gz $BUILD
echo "Done"

cd ..

rm -r $TMPDIR
