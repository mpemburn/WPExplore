#!/bin/bash

sites=(
    "www_clarku"
    "news_clarku"
    "sites_clarku"
)

echo
echo "Select destination by number:"

#COLUMNS=20

select menu in "${sites[@]}";
do
    site=$menu
    break
done

#Params: plugin-name version

#Make .zip file
zip -r "$1-$2.zip"  "$1"
#Move to plugin_archive directory
mv "$1-$2.zip" "/data/s890087/home/client_mpemburn/$site/plugin_archive"
