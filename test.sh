#!/bin/bash
LOGIN1=$(grep 'login' .settings.php);
LOGIN2=${LOGIN1%\'*};
LOGIN=${LOGIN2#\'*};
DB=$(awk '/database/{print substr($NF, 2, length($NF) - 3)}' .settings.php);
PASSWORD=$(awk '/password/{print substr($NF, 2, length($NF) - 3)}' .settings.php);
echo mysqldump -u $LOGIN -p$PASSWORD $DB;
