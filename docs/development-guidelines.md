# Development guidelines

We're currently using [Laravel 8.x](https://laravel.com/docs/8.x/releases)

## Working on an issue

### Before starting to work on an issue:

- make sure that you have assigned that issue to yourself
- give a rough time estimate for that issue (add comment e.g. `/estimate 1w 3d 2h 15m`, see [Quick actions](https://gitlab.cs.ttu.ee/help/user/project/quick_actions))

### Create a new branch:

- fetch latest source from the `develop` branch
- create a new branch following
  - the pattern: `(feature/bugfix)/(ticket nr)-(few words describing the branch separated by a hyphen)`
  - e.g. `feature/414-show-future-labs`
  
### While writing code:

- Try to follow the coding style already present and if you counter a contradictory example then prefer:
  - PHP: [PSR-1](https://www.php-fig.org/psr/psr-1/), [PSR-12](https://www.php-fig.org/psr/psr-12/)
  - JS: what ever seems more prevalent in the project `¯\_(ツ)_/¯`
- Avoid calling Moodle source directly since its difficult to test - use a proxy or a facade and mock it in your tests
- Write tests :)
- Prefer to add separate commits between distinct and coherent parts of your development
- Start your commit messages with the `#ticketnr` and write enough description to clear up any non-obvious design decisions or considerations

#### Modifying DB tables:

- [install.xml](/db/install.xml) < should include complete DDL for clean installation
- [version.php](/version.php) < increase plugin version
- [upgrade.php](/db/upgrade.php) < should include migrations per version

#### Adding constraints to database table

Moodle does not accept foreign keys in install.xml. If you need to add some to table, don't use install.xml for this. Add these in function apply_constraint() in [install.php](/db/install.php). Use existing records as sample.

In [upgrade.php](/db/upgrade.php) foreign keys should be added same way as before

### Testing your code locally:

- We are currently missing testing setup for javascript
- PHP unit tests should run without any additional setup via your IDE in folder [/plugin/tests/Unit](/plugin/tests/Unit)
- PHP integration and feature tests require you to specify your database connection parameters (Since usually they would be provided via Moodle config.php file)
  - you can do so via your `.env` file, that is located on our projects, main folder. Add following lines to the .env file:
  ```
  DB_DATABASE=bitnami_moodle
  DB_USERNAME=bn_moodle
  DB_PASSWORD=dev
  DB_TABLE_PREFIX=mdl_
  ```
  - you also need to set APP_URL to ```http://localhost``` to run feature tests successfully.

#### Configuring PhpStorm:

Setting the configuration for running the tests in PHP Storm:
- Choose ```Edit configurations``` for the file you want to run
- From the left pane you should choose PHPUnit
- Under Test Runner options choose option Defined in the configuration file
- Tick ```use alternative configuration file```
- set path to phpunit.xml (located in your project "charon" folder) as an alternative configuration file
- Check that interpreter is also set and confirm the configuration

Using XDebug is not obligatory but may be useful.
Installation for Windows: https://xdebug.org/wizard
If getting errors not finding the file, check if some parts of the path need to be deleted from the suggested path, that wizard suggests adding to php.ini.

After this in the same configuration dialog as above, set:
- Preferred Coverage engine ```XDebug```

### Once you're done with your development:

- Verify that your code works
- If `develop` branch has moved ahead, merge or rebase to avoid conflicts
- If your development changed anything notable affecting the end user then add an entry to the `Unreleased` section of the [CHANGELOG](/CHANGELOG.md) file (follow the format inside)
- Open a merge request towards `develop` branch at ([branches view](https://gitlab.cs.ttu.ee/ained/charon/-/branches))
- Start the merge request title with the `#ticketnr`, give an overview in the description (may also just list commit messages if they're helpful enough)
- Make sure the tests pass on the CI pipeline
- Mark yourself as the `Assignee`, find a `Reviewer` and wait for feedback or approval
- Once your code is merged - close your issue and mark the amount of time you spent on it (add comment e.g. `/spend 2h 15m`, see [Quick actions](https://gitlab.cs.ttu.ee/help/user/project/quick_actions))
- Merges to `develop` branch are automatically deployed to development environment

## Reviewing a merge request

- Read the issue the merge request is connected to and try to identify if the merge request covers what the issue requested
  - if you are still unclear about that after reviewing the code and commit messages then ask the author to specify the things that cause you doubt in the merge request description
- Be clear, constructive and polite in your feedback
- Provide suggestions or examples if possible
- Mark the amount of time you spent on the review under the issue the merge request is connected to

## Tags and releases

In order to create a release for live deployment
- Look up what the next release number ought to be (see [CHANGELOG](/CHANGELOG.md))
- Create a new branch from `develop` following the pattern `release/x.y.z`
  - If all upcoming changes are not in `develop` already, then merge them into the release branch

- Review the changes and try to identify anything which should be kept in mind during the deployment, things like
  - are there non-trivial database structure changes or data modification migration which may prompt a need for a backup before deploying?
  - does the new version require any manual modifications to the live environment? (e.g. env variable, filesystem, PHP extension changes)
  - talk to the project manager if such things are found

- Update [version](/version.php)
  - Use the date the release is being created
  - Make sure latest additions to `upgrade.php` also check the same version

- Update [CHANGELOG](/CHANGELOG.md)
  - Below the `[Unreleased]` section add the version number and date
  - (temporarily skipped) Add a link to the version diff to previous version at the bottom of the file following previous examples
  - Add any missing descriptions of things _added/changed/fixed_ in the version currently being released

- Create a new tag and release using the [Tags](https://gitlab.cs.ttu.ee/ained/charon/-/tags) view
  - Pick your release branch for the _Create from_ field
  - Use your release number as the _Tag name_
  - Copy the latest version content from [CHANGELOG](/CHANGELOG.md) to the _Release notes_ field

- Go and edit the automatically created release using the [Releases](https://gitlab.cs.ttu.ee/ained/charon/-/releases) view
  - Add current date to the end of 'Release title'. The release title should end up like this: x.y.z (YYYY-MM-DD)
  - Add a link to the release for downloading its artifacts
    - Go to the [Branches](https://gitlab.cs.ttu.ee/ained/charon/-/branches) view and locate your release branch
    - Select branch's _Download_ option and copy the link of `build_release`
    - Paste the link to the URL field in release editing view
    - Also add link title: Release x.y.z artifacts

- Create a merge request towards `master` with title `Release x.y.z` and the latest release content in the description
- Ask project manager to deploy the release branch to pre-live environment for testing
  - If any issues are found: fix them and repeat this step
- Merge your release branch to master (and master also back to develop to get any changes there too)
