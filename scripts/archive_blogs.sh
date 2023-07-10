#!/bin/bash

string=79,90,99,139,218,223,224,226,345,346,348,349,354,357,359,361,363,366,369,370,371,373,395,397,399,401,403,404,406,407,408,410,411,412,414,415,417,418,419,420,421,422,>

IFS=', ' read -r -a array <<< "$string"

for site in "${array[@]}"
do
    echo "Archiving blog ID: $site"
    wp site archive $site
done
