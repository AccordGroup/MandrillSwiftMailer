# Release Policy

## Semantic Versioning

As of 2016-04-27, this project follows Semantic Versioning, which uses the MAJOR.MINOR.PATCH pattern.

* MAJOR - Changes which introduce backwards-compatibility breaking changes, or remove deprecated features
* MINOR - Changes which introduce new features while maintaining backwards-compatibility
* PATCH - Bug fixes, or changes to improve performance or usability while maintaining backwards-compatibility

## Release Schedule

* MAJOR - Release dates for major changes are decided by the AccordGroup/MandrillSwiftMailer team members
* MINOR - A minor release will be created on the first Monday after a commit is pushed if it meets the following criteria
    * The commit matches the description of MINOR under the Semantic Versioning section shown above
    * The latest Travis CI build for master is passing
* PATCH
    * Critical bug fixes: released as soon as the Travis CI build passes on master
    * Edge cases: Released on the following Monday. If more than one person comments that they've experiencing this issue in a production environment, the release should be treated as a critical bug (see above)
    * Improvement/refactoring: Released on the following Monday 