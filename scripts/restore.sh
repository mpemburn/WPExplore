#!/bin/bash

site=$1

echo Restoring Blog ID $site...

# Remove the 'archived' flag
wp site unarchive $site

# Re-create the directory for this site_id
mkdir -p wp-content/uploads/sites/${site}
# Move the files
rsync wp-content/archive/${site} wp-content/uploads/sites/${site} -avh --remove-source-files
