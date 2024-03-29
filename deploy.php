<?php
/*
 * This script is called via a webhook upon new PUSH to the repository.
 * It should redeploy the website to catch up on new changes.
 */

chdir(__DIR__);
`git fetch --all`; // this fetches all the updates
`git reset --hard origin/master`; // this would drop local changes if any

// update dependencies
`composer install`;

// and now wipe out the cache
`rm -rf ./web/cache/*`;
`rm -rf ./storage/tmp/*`;

// Done!