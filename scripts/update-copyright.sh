#!/bin/bash
#
# Usage: ./scripts/update-copyright.sh
#

PCRE_MATCH_COPYRIGHT="Copyright \(c\) [-0-9]* Upwind24"
YEAR=$(date +%Y)

echo -n "Updating copyright headers to ${YEAR}... "
grep -rPl "${PCRE_MATCH_COPYRIGHT}"    | xargs sed -ri "s/${PCRE_MATCH_COPYRIGHT}/Copyright (c) ${YEAR} Upwind24/g"
echo "[OK]"

