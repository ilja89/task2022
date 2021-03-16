# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Fixed
- \#399 Tester callback failed with duplicate exception for group submissions when the main author's username was
  submitted along the group usernames as `uniid@ttu.ee` while having the uniid field of the submission as `uniid`.

## [1.1.2] - 2021-03-13

### Fixed
- \#399 Submission retesting works on group submissions and uses original submission timestamp

## [1.1.1] - 2021-03-12

### Fixed
- \#412 Commit hash link was broken if the repository name was longer than `Course.short_name` in Submission grading view
- \#399 Submission retesting won't fail on absent optional fields

## [1.1.0] - 2021-03-07

### Changed
- \#401 Students participating in a Group Submission can now be graded separately in Submission grading view

### Fixed
- \#401 "Save" button in Submission grading view will now become active regardless of how the points were changed
- \#395 Remove (most) non-existing registration times previously shown to students

## [1.0.0] - 2021-02-24

### Added
- \#378 External Grades can now be used in Charon calculation formula
- \#391 Labs can now be named

### Changed
- \#394 Submission result colors in Student view are less random and reflect score % 

### Fixed
- \#378 Total grade calculation in Grading view no longer shows constant 0 when forward slash is present in Grade idNumber
- \#410 "Add new preset" button displays the preset for and no longer just reloads the page


[Unreleased]: https://gitlab.cs.ttu.ee/ained/charon/-/compare/master...develop
[1.1.2]: https://gitlab.cs.ttu.ee/ained/charon/-/compare/1.1.1...1.1.2
[1.1.1]: https://gitlab.cs.ttu.ee/ained/charon/-/compare/1.1.0...1.1.1
[1.1.0]: https://gitlab.cs.ttu.ee/ained/charon/-/compare/1.0.0...1.1.0
[1.0.0]: https://gitlab.cs.ttu.ee/ained/charon/-/compare/889d5abbbc38491f5b2370f0d62f212a8ce52bd6...1.0.0
