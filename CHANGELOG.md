# Changelog
All notable changes to this project will be documented in this file from version 2021043000, release 0.9.0 and onwards.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0).

## [0.9.0] - 2021043000
### Added
- This changelog :)

### Changed
- Version and release in version file.
- Plugin install script to add fields customtext1 to customtext10 to the table mdl_groups to store additional selma intake data.
- Plugin upgrade script to add fields customtext1 to customtext10 to the table mdl_groups to store additional selma intake data.
- Webservice function add_intake_to_course to accept a new parameter with custom text data.
- Function in locallib file enrol_selma_add_intake_to_course to accept the new customfields, and then apply the values to the group.