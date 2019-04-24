#!/usr/bin/env bash
#
# Copyright 2010-2019 OpenEstate.org
#

XGETTEXT="xgettext"
NAME="openestate-php-wrapper"
VERSION="0.4.0"
AUTHOR_NAME="OpenEstate.org"
AUTHOR_EMAIL="info@openestate.org"

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
SRC_DIR="$DIR/src"
LANGUAGES_DIR="$DIR/src/languages"

echo "creating \"$NAME.pot\""
cd "$SRC_DIR"
"$XGETTEXT" \
  --default-domain="$NAME" \
  --language="PHP" \
  --keyword="__" \
  --keyword="_e" \
  --keyword="esc_html__" \
  --sort-by-file \
  --from-code="UTF-8" \
  --copyright-holder="$AUTHOR_NAME" \
  --package-name="$NAME" \
  --package-version="$VERSION" \
  --msgid-bugs-address="$AUTHOR_EMAIL" \
  --output="$LANGUAGES_DIR/$NAME.pot" \
  *.php
