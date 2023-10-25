#!/bin/bash

declare -A sites
sites["www.clarku.edu"]="dom28151"
sites["www.training.clarku.edu"]="dom25404"
sites["www.testing.clarku.edu"]="dom32150"
sites["www.dev.clarku.edu"]="dom25405"
sites["www.golive.clarku.edu"]="dom45055"
sites["news.clarku.edu"]="dom25963"
sites["news.test.clarku.edu"]="dom26417"
sites["sites.clarku.edu"]="dom26330"
sites["sites.test.clarku.edu"]="dom30842"
sites["wordpress.clarku.edu"]="dom29121"
sites["wordpress.test.clarku.edu"]="dom28811"
sites["future.clarku.edu"]="dom35865"
sites["future.dev.clarku.edu"]="dom38064"
sites["future.testing.clarku.edu"]="dom38065"
sites["future.training.clarku.edu"]="dom38066"

select menu in "${!sites[@]}"; do
    site=$menu
    app=${sites[$menu]}
    break
done

echo "Going to log directory for $site (/data/s890087/$app/mnt/log)"

cd "/data/s890087/$app/mnt/log"
exec bash
