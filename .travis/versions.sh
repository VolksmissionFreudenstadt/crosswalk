#!/bin/bash
export TRAVIS_DATE=`date -u +"%d.%m.%Y, %H:%M Uhr"`
export BASE_VERSION=`cat .travis/BASE_VERSION`
if ["$TRAVIS_BRANCH" == 'master']
then
	echo -n "This is the master branch. Preparing files for a release."
	export $VERSION_STRING="$BASE_VERSION (build $TRAVIS_BUILD)"
	export $FILE_VERSION="$BASE_VERSION-$TRAVIS_BUILD"
	export $DEV_VERSION_STRING=''
else
	export $VERSION_STRING="$BASE_VERSION-$TRAVIS_BRANCH (build $TRAVIS_BUILD vom $TRAVIS_DATE)"
	export $FILE_VERSION="$TRAVIS_BRANCH-$BASE_VERSION-$TRAVIS_BUILD_NUMBER"
	export $DEV_VERSION_STRING="$TRAVIS_BRANCH-$BASE_VERSION-$TRAVIS_BUILD_NUMBER vom $TRAVIS_DATE"	
fi
echo -n "Version string: $VERSION_STRING"
echo -n "File version: $FILE_VERSION"
echo -n "Developer version watermark: $DEV_VERSION_STRING"
sed -i "s/###VERSION###/$VERSION_STRING vom $TRAVIS_DATE/g" Heft.sla
sed -i "s/###DEV_VERSION###/$DEV_VERSION_STRING/g" Heft.sla
