#!/bin/bash

MSGFMT="msgfmt"

PROJECT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
LANGUAGES_DIR="$PROJECT_DIR/src/languages"

for f in $LANGUAGES_DIR/*.po
do
  NAME="$(basename -s .po $f)"
  echo "compiling \"$NAME.po\" to \"$NAME.mo\""
  $MSGFMT --output-file="$LANGUAGES_DIR/$NAME.mo" $LANGUAGES_DIR/$NAME.po
done
