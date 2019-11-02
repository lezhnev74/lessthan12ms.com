<?php
require __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
/*
 * This script is called via a webhook upon new PUSH to the repository.
 * It should redeploy the website to catch up on new changes.
 */

chdir(base_path());
`git fetch --all`; // this fetches all the updates
`git reset --hard origin/master`; // this would drop local changes if any

// and now wipe out the cache
`rm -rf ./web/cache/*`;

`docker exec -it textsite_app /bin/bash -c "cd /var/www && composer install"`;
`docker-compose -f docker-compose.prod.yml restart app`;
// Done!