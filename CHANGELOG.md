# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Changed
- \#438 Update Laravel version from 5.5 to 8.x (It will update laravel to the latest version of 8.x whenever 
  php composer install/update is run)

### Added
- \#514 My registration shows the registration status
- \#503 In teacher's defense registrations view the time of defense is replaced by the defense's number in queue
- \#501 Removed the ability to choose time-slots for defense registration and disabled 'My teacher' functionality
- \#504 UI for teacher for registering a student for defence
- \#488 When a teacher creates a deadline for charon, then it displayed the event to everyone enrolled in calendar.
- \#454 Added posting to tester from inline submission
- \#418 Charon footer shows Charon version in use and has a link to Changelog
- \#431 Added charon total points in grading view
- \#351 Confirming registration deletion alert shows additional information about the registration
- \#396 Inform the teacher about the number of registrations lost when confirming a Lab deletion
- \#397 Ask confirmation if a Lab change would result in loss of active registrations
- \#429 Option to connect a Lab with specific groups
- \#392 Persistent grades marked with asterisk
- \#210 Charon deadline events are visible in Moodle calendar
- \#449 Tester url and token can be changed for specific course
- \#492 Integrated templates management frontend with backend endpoints
- \#490 Added templates management view in charon settings, where templates can be added to charon, updated or deleted
- \#474 Added ability to make submitions from charon student view
- \#542 Now a submission is made to tester, its response is handled and submissions are created.
- \#522 Show code editor always if there is at least one template (student view)
- \#523 Rename show code editor checkbox to "Allow code submission on page", if it is checked, "Submit" button (which send the code to backend) is shown, otherwise is hidden and code editor works in read-only mode
- \#539 Substitute Ace Editor with textarea


### Fixed
- \#528 In grading view show "Total points: 0" if there is no result for charon
- \#382 In Firefox latest submissions in dashboard do not break out of their containers anymore
- \#389 All grade components are now always visible in students' charon submission view
- \#434 Current points in submission view show selected submission's points now
- \#402 Folder structure for deep files works better now
- \#417 "Charon popup:" added to popup window title
- \#352 Submissions in students report view are now clickable
- \#359 Popup settings sliders' thumbs are always visible
- \#437 Fixed persistent value saving
- \#349 Fixed calculation for "Undefended amount" in submissions table
- \#523 If "Allow code submission on page" is checked a submit button appears and the text can be edited otherwise not

## [1.2.4] - 2021-05-19

### Fixed
- \#427 Avoid faulty Submission creation when tester sends `null` file/stdout/stderr fields by making them optional

## [1.2.3] - 2021-05-15

### Added
- \#419 Scroll bar appears in the "My registrations" popup when registrations don't fit on the screen

### Fixed
- \#416 Teacher overview now counts only defenses for the given course

## [1.2.2] - 2021-05-03

### Changed
- \#414 Labs can now be filtered by their starting date and future labs are shown by default in Labs overview

### Fixed
- \#423 File with empty content is now shown correctly in submission view

## [1.2.1] - 2021-04-17

### Added
- \#371 Lab creation view has an optional set of predefined lab durations to pick from
- \#381 Show group Submission members as a list under Submission info in Submission grading view

### Fixed
- \#420 Lab defense registration checks now take different Charon defense durations into account

## [1.2.0] - 2021-03-20

### Added
- \#400 Latest Submissions for every student for a given Charon can now be submitted to a re-test under individual
  Charon settings view. Submissions are queued and sent to tester with a delay interval specified
  by CHARON_CRON_RETEST_DELAY env variable (defaults to 20 seconds if not set)

### Changed
- \#387 Browser window title displays Course name

## [1.1.4] - 2021-03-16

### Fixed
- \#399 Triggering result recalculation previously reset the persistent grade result value to 0 

## [1.1.3] - 2021-03-16

### Fixed
- \#399 Tester callback failed with duplicate exception for group submissions when the main author's username was
  submitted along the group usernames as `uniid@ttu.ee` while having the uniid field of the submission as `uniid`

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
[1.2.4]: https://gitlab.cs.ttu.ee/ained/charon/-/compare/1.2.3...1.2.4
[1.2.3]: https://gitlab.cs.ttu.ee/ained/charon/-/compare/1.2.2...1.2.3
[1.2.2]: https://gitlab.cs.ttu.ee/ained/charon/-/compare/1.2.1...1.2.2
[1.2.1]: https://gitlab.cs.ttu.ee/ained/charon/-/compare/1.2.0...1.2.1
[1.2.0]: https://gitlab.cs.ttu.ee/ained/charon/-/compare/1.1.4...1.2.0
[1.1.4]: https://gitlab.cs.ttu.ee/ained/charon/-/compare/1.1.3...1.1.4
[1.1.3]: https://gitlab.cs.ttu.ee/ained/charon/-/compare/1.1.2...1.1.3
[1.1.2]: https://gitlab.cs.ttu.ee/ained/charon/-/compare/1.1.1...1.1.2
[1.1.1]: https://gitlab.cs.ttu.ee/ained/charon/-/compare/1.1.0...1.1.1
[1.1.0]: https://gitlab.cs.ttu.ee/ained/charon/-/compare/1.0.0...1.1.0
[1.0.0]: https://gitlab.cs.ttu.ee/ained/charon/-/compare/889d5abbbc38491f5b2370f0d62f212a8ce52bd6...1.0.0
