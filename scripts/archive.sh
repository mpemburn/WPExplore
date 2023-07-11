#!/bin/bash

site=$1

echo Restoring Blog ID $site...

# Set the 'archived' flag
wp site archive $site

# Create an archive directory for this site
mkdir -p wp-content/archive/{$site}
# Move the files
rsync wp-content/uploads/sites/{$site} wp-content/archive/{$site} -avh --remove-source-files
