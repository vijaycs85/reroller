# REROLLER
Checks if latest patch on RTBCed issues are still apply with HEAD on Drupal.org

## Usage

```php
<?php
// index.php
require __DIR__ . '/vendor/autoload.php';

use Vijaycs85\Reroller\Controller;

$branch = '8.4.x';
$drupal_root = '/var/www/html/drupal8';
$debug = TRUE;
$query = [
  // Drupal core project.
  'field_project' => 3060,
  // Status RTBC.
  'field_issue_status' => 14,
  // Number of issues to fetch.
  'limit' => 1,
  // 8.4.x branch.
  'field_issue_version' => $branch . '-dev',
];

$controller = Controller::create($drupal_root, $debug);
$controller->byBranch($query, $branch);
```

### Result
#### Debug ON

```bash
# Success
➜  reroller php index.php
Already on '8.4.x'
M	core/modules/user/src/Controller/UserController.php
M	core/modules/user/src/Tests/UserLoginTest.php
Your branch is up-to-date with 'origin/8.4.x'.
HEAD is now at b5742e6 Issue #2795051 by legovaer, Lendude, dawehner: Move \Drupal\simpletest\WebTestBase::drupalBuildEntityView into a trait and make it available in BTB
From https://git.drupal.org/project/drupal
 * branch            8.4.x      -> FETCH_HEAD
Current branch 8.4.x is up to date.
patch in 992540 is green!

# Error
➜  reroller php index.php
Already on '8.4.x'
M	core/modules/user/src/Controller/UserController.php
M	core/modules/user/src/Tests/UserLoginTest.php
Your branch is up-to-date with 'origin/8.4.x'.
error: patch failed: core/modules/user/src/Controller/UserController.php:6
error: core/modules/user/src/Controller/UserController.php: patch does not apply
error: patch failed: core/modules/user/src/Tests/UserLoginTest.php:63
error: core/modules/user/src/Tests/UserLoginTest.php: patch does not apply
Can't apply patch in https://www.drupal.org/node/992540
```
### Debug OFF
```bash

# Success
➜  reroller php index.php
patch in 992540 is green!

# Error
➜  reroller php index.php
Can't apply the latest patch at https://www.drupal.org/node/992540
```
