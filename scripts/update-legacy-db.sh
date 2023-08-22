#!/bin/bash

target=$1
read -p "Ready to update $target. Continue? (y n)" -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]
then
    echo "Canceled by user."
    exit 1
fi

# Test for wp-cli on local or prod
if command -v wp &> /dev/null
then
  wpcli=wp
else
  wpcli=/usr/local/bin/wp/wp-cli.phar
fi

basedir="$PWD"
LINE=''
YELLOW='\033[0;33m'
NC='\033[0m' # No Color
now=`date`

# Echo the progress and write to log
function log()
{
  eval string="$1"
  echo -e "${YELLOW}$string${NC}"
  echo $string >> update.log
}

# Begin Phase 2
LINE="["$(date +"%T")"] ----------- Beginning Phase 2: Modify database and do cleanup. -----------"
log "\${LINE}"

record_check=$($wpcli db query 'SELECT * FROM wp_sitemeta WHERE meta_key like "ms_files_rewriting";')

if [[ $record_check == "" ]]; then
  $wpcli db query 'INSERT INTO wp_sitemeta (meta_id, site_id, meta_key, meta_value) VALUES (NULL, "1", "ms_files_rewriting", "0");'
else
  $wpcli db query 'UPDATE wp_sitemeta SET meta_value = "0" WHERE wp_sitemeta.meta_key = "ms_files_rewriting";'
fi

# Drop legacy upload paths
LINE="["$(date +"%T")"] Dropping legacy upload paths."
log "\${LINE}"

for site in $($wpcli site list --field=url); do
  LINE="["$(date +"%T")"] Setting $site upload_path to default."
  log "\${LINE}"
  $wpcli option set upload_path "" --url=$site --skip-plugins --skip-themes
done

LINE="["$(date +"%T")"] Finished dropping legacy upload paths."
log "\${LINE}"

LINE="["$(date +"%T")"] Moving files to new directory structure."
log "\${LINE}"

root_home=$($wpcli option get home)
for site_id in $($wpcli site list --field=blog_id); do
  # Skip root site
  if [[ $site_id == "1" ]]; then
    continue
  fi

  home_url=$($wpcli db query "SELECT option_value from wp_${site_id}_options where option_name = 'home';" --skip-column-names --batch)
  # Require site to end in /
  if [[ $home_url != *"/" ]]; then
    home_url="${home_url}/"
  fi

  LINE="["$(date +"%T")"] Replacing database entries for ${home_url}."
  log "\${LINE}"

  if [[ $home_url == "https"* ]]; then
    site_name=$(basename $home_url)
    filepath=$basedir/wp-content/blogs.dir/$site_id/files/
    if [ -d "$filepath" ]; then
      $wpcli search-replace ${site_name}/files/ wp-content/uploads/sites/${site_id}/ wp_${site_id}_* --network --report-changed-only --skip-plugins --skip-themes
      $wpcli search-replace wp-content/blogs.dir/${site_id}/files/ wp-content/uploads/sites/${site_id}/ --network --report-changed-only --skip-plugins --skip-themes
    fi
  fi
done

LINE="["$(date +"%T")"] Finished moving files to new directory structure."
log "\${LINE}"

#covering site ID 1
LINE="["$(date +"%T")"] Search and replace all occurrences of file paths in the database."
log "\${LINE}"

$wpcli search-replace 'https://$target/files/' 'https://$target/wp-content/uploads/' --all-tables --report-changed-only --skip-plugins --skip-themes
rsync wp-content/blogs.dir/1/files/ wp-content/uploads/ -avh --remove-source-files

LINE="["$(date +"%T")"] Search and replace completed."
log "\${LINE}"

LINE="["$(date +"%T")"] ----------- Finished Phase 2: Modify database and do cleanup. -----------"
log "\${LINE}"
