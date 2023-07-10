#!/bin/bash

# All subsites that need to be archived
blogs=79,90,99,139,218,223,224,226,345,346,348,349,354,357,359,361,363,366,369,370,371,373,395,397,399,401,403,404,406,407,408,410,411,412,414,415,417,418,419,420,421,422,447,448,449,451,452,453,454,455,456,457,458,459,460,461,462,463,464,465,466,467,468,469,470,471,472,473,474,475,476,518,519,520,521,522,523,524,525,526,527,528,529,530,531,532,533,534,535,537,538,540,541,542,543,544,545,546,547,549,563,564,588,590,604,101,402,536,539,548

# Create an array from the blog IDs
IFS=', ' read -r -a array <<< "$blogs"

cd wp-content/uploads/sites

for site_id in "${array[@]}"
do
    # Create an archive directory for this site_id
    mkdir -p wp-content/archive/${site_id}
    # Move the files into the archive
    rsync wp-content/uploads/sites/${site_id} wp-content/archive/${site_id} -avh --remove-source-files
done
