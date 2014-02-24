#!/bin/sh

#
#  STEP 2:
#  convert all Stud.IP message strings into a binary format
#

LOCALE_RELATIVE_PATH="locale"

for language in en
do
    test -f "$LOCALE_RELATIVE_PATH/$language/LC_MESSAGES/garudaplugin.mo" && mv "$LOCALE_RELATIVE_PATH/$language/LC_MESSAGES/garudaplugin.mo" "$LOCALE_RELATIVE_PATH/$language/LC_MESSAGES/garudaplugin.mo.old"
    msgfmt "$LOCALE_RELATIVE_PATH/$language/LC_MESSAGES/garudaplugin.po" --output-file="$LOCALE_RELATIVE_PATH/$language/LC_MESSAGES/garudaplugin.mo"
done
