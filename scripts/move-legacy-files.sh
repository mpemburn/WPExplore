#!/bin/bash

# Test for wp-cli on local or prod
if command -v wp &> /dev/null
then
  wpcli=wp
else
  wpcli=/usr/local/bin/wp/wp-cli.phar
fi

LINE=''
YELLOW='\033[0;33m'
NC='\033[0m' # No Color
now=`date`
echo "*** Update Log for $now ***" > update.log

# Echo the progress and write to log
function log()
{
  eval string="$1"
  echo -e "${YELLOW}$string${NC}"
  echo $string >> update.log
}
# Begin Phase 1
LINE="["$(date +"%T")"] ----------- Beginning Phase 1: Move files to new directory structure.. -----------"
log "\${LINE}"

for site_id in $($wpcli site list --field=blog_id); do
  # Skip root site
  if [[ $site_id == "1" ]]; then
    continue
  fi

  home_url=$($wpcli db query "SELECT option_value from wp_${site_id}_options where option_name = 'home';" --skip-column-names --batch)

  LINE="["$(date +"%T")"] Moving files for ${home_url}."
  log "\${LINE}"

  #Move files to correct directory structure
  mkdir -p wp-content/uploads/sites/${site_id}
  rsync wp-content/blogs.dir/${site_id}/files/ wp-content/uploads/sites/${site_id} -avh --remove-source-files

done

LINE="["$(date +"%T")"]  ----------- Finished Phase 1: Move files to new directory structure.. -----------"
log "\${LINE}
