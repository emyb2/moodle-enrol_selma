# Development

- [Logging](#logging)
- [Guzzle](#guzzle)
- [PHPUnit](#phpunit)
- [Grunt](#grunt)
- [TravisCI](#travisci)
- [GitLabCI](#gitlabci)

## Logging

The plugin uses Monolog logging library for PHP.

The official repository can be found on GitHub at https://github.com/Seldaek/monolog

Monolog is available on Packagist ([monolog/monolog](http://packagist.org/packages/monolog/monolog))
and as such installable via [Composer](http://getcomposer.org/).

Logging events is simple as. Get an instance of logger and log away.
```php
$logger = \enrol_selma\local\plugin_logger::get_logger();
$logger->critical('This is very very bad!');
```
___
## Guzzle

The plugin uses Guzzle to send HTTP requests and receive HTTP responses.

Guzzle's code repository is available at https://github.com/guzzle/guzzle

Guzzle is available at Packagist [(guzzlehttp/guzzle](https://packagist.org/packages/guzzlehttp/guzzle))
and installable via [Composer](http://getcomposer.org/).

**Usage:**
```php
// Based on Guzzle docs.
// Create a client with a base URI
$client = new GuzzleHttp\Client(['base_uri' => 'https://foo.com/api/']);

// Send request with parameters.
$response = $client->request('GET', 'http://httpbin.org?foo=bar');

// Get a header from the response.
echo $response->getHeader('Content-Length')[0];
```

[Read more...](https://readthedocs.org/projects/guzzle/).
___
## PHPUnit
___
## Grunt
___
## TravisCI
___
## GitLabCI
GitLab is also used and makes testing the plugin locally easier.

#### QUICK SETUP:
* Install Runner - [https://docs.gitlab.com/runner/install/](https://docs.gitlab.com/runner/install/)
  * During installation/registration, use URL & token given in GitLab project > Settings > CI/CD > Runners. E.g.:<br>
    [https://gitlab.com/myproject/-/settings/ci_cd](https://<gitlabdomain>/<project>/-/settings/ci_cd)
  * Choose `docker` as executor (or whichever you prefer)
  * You may need to set a default image (used if `.gitlab-ci.yml` doesn't specify one). Use any lampstack image from DockerHub. I used `marekhadas9/lampstack`
* (Optional) Enable the runner to also pick up jobs without tags (to run jobs for feature branches, etc)<br>
  Edit the runner from the same location as above. Tick "Run untagged jobs" checkbox, save

#### Running Jobs Locally (Do above, then these)
* Install Docker - for Mac run `brew cask install docker`<br>- credit: [https://stackoverflow.com/a/44719239](https://stackoverflow.com/a/44719239)
* Run Docker app (via GUI)
* Run `gitlab-runner exec docker <job>`. Job name (e.g. `job1`) can be found in `.gitlab-ci.yml`<br>
  (Can only run one job at a time) - [https://docs.gitlab.com/runner/commands/#gitlab-runner-exec](https://docs.gitlab.com/runner/commands/#gitlab-runner-exec)
  
##### Debugging:
See: [https://www.lullabot.com/articles/debugging-jobs-gitlab-ci](https://www.lullabot.com/articles/debugging-jobs-gitlab-ci)