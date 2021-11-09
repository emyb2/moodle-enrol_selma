# Changelog
All notable changes to this project will be documented in this file from version 2021043000, release 0.9.0 and onwards.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0).

## [Release 0.13.1]
Version bump for changes to enrolment processing. Do not use release 0.13.0.

### Changed
- Removed check of user in intake in enrol_selma_add_user_to_intake(). This enables the plugin to check if the user
  is enrolled into the intakes courses
- Update enroL_selma_relate_user_to_intake(). Relate the user to the intake if the user is not already related by
  checking if the record exists. This enables the plugin to run the conditional enrolment check.

## [~~Release 0.13.0~~]
Do not use this release. Changes listed were made, but do not use this release. Go straight to 0.13.1.

### Changed
- Ensure enrolments are processed each time SELMA makes a webservice call. This ensures that new users are enrolled in the intakes course

## [Release 0.12.0]
### Added
- Update group custom field data when a group exists.

### Changed
- This changelog :joy: :man_facepalming:

## [Release 0.11.0] - Version 2021043004
### Added
- Warning responses for a course that doesn't have the gradebook configured correctly for grademax
- Only supporting value grade type scales
- Warning for a grade that is greater than the grademax. Defaulting to truncating to the grademax

### Changed
- Changed grade student function to accept a grade
- Changed the language strings from completion to grade
- This changelog

## [Release 0.10.0] - Version 2021043003
### Added
- Webservice function to mark a students course in SELMA as complete (100) or not complete (0)
- New language strings for describing the parameters for the webservice function
- Webservice function to the plugins external services definition file
- Added the webservice function to the list of functions available to the defined enrol_selma services

### Changed
- Version and release numbers using Semantic Versioning

## [Release 0.9.0] - Version 2021043000
### Added
- This changelog :)

### Changed
- Version and release in version file.
- Plugin install script to add fields customtext1 to customtext10 to the table mdl_groups to store additional selma intake data.
- Plugin upgrade script to add fields customtext1 to customtext10 to the table mdl_groups to store additional selma intake data.
- Webservice function add_intake_to_course to accept a new parameter with custom text data.
- Function in locallib file enrol_selma_add_intake_to_course to accept the new customfields, and then apply the values to the group.