# Development guidelines

We're currently using [Laravel 5.5](https://laravel.com/docs/5.5/)

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

### Testing your code locally:

- We are currently missing testing setup for javascript
- PHP unit tests should run without any additional setup via your IDE in folder [/plugin/tests/Unit](/plugin/tests/Unit)
- PHP integration and feature tests require you to specify your database connection parameters (Since usually they would be provided via Moodle config.php file)
  - you can do so via your `.env` file
  ```
  DB_HOST=127.0.0.1
  DB_DATABASE=bitnami_moodle
  DB_USERNAME=bn_moodle
  DB_PASSWORD=dev
  DB_TABLE_PREFIX=mdl_
  ```

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

- TODO
