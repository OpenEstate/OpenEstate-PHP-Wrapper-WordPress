#!/bin/bash

XGETTEXT="xgettext"
NAME="openestate-php-wrapper"
VERSION="0.3.0"
AUTHOR_NAME="OpenEstate.org"
AUTHOR_EMAIL="info@openestate.org"

PROJECT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

echo "creating \"$NAME.pot\""
cd "$PROJECT_DIR/src"
$XGETTEXT \
  --default-domain="$NAME" \
  --language=PHP \
  --keyword=__ \
  --keyword=_e \
  --sort-by-file \
  --from-code="UTF-8" \
  --copyright-holder="$AUTHOR_NAME" \
  --package-name="$NAME" \
  --package-version="$VERSION" \
  --msgid-bugs-address="$AUTHOR_EMAIL" \
  --output="$PROJECT_DIR/src/languages/$NAME.pot" \
  *.php
