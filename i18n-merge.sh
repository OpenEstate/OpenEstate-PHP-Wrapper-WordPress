#!/usr/bin/env bash
#
# Copyright 2010-2019 OpenEstate.org
#

MSGMERGE="msgmerge"
MSGMERGE_PARAMS="--update"
NAME="openestate-php-wrapper"

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
LANGUAGES_DIR="$DIR/src/languages"

find "$LANGUAGES_DIR" \
    -type f \
    -name "*.po" \
    -exec "$MSGMERGE" ${MSGMERGE_PARAMS} {} "$LANGUAGES_DIR/$NAME.pot" \;
