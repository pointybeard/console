# Change Log
All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

**View all [Unreleased][] changes here**

## [1.1.0][]
#### Changed
-   Updated namespace from `Symphony\Console` to `pointybeard\Symphony\Extensions\Console` in efforts to standardise namespaces across multiple projects

## [1.0.1][]
#### Added
-   Added `CommandIterator` and `CommandIteratorIterator` classes

#### Changed
-   Updated `AbstractCommand` to use `CommandIterator` instead of `CommandAutoloader::fetch()`
-   Removed `CommandAutoloader::fetch()` method in favour of using `CommandIterator` and `CommandIteratorIterator`

## [1.0.0][]
#### Changed
-   Major rewrite to use `pointybeard/helpers` meta packages.
-   Namespace changed from `Symphony\Console\Lib` to `Symphony\Console\Console`
-   Commands are now stored in `commands` folder instead of `bin`
-   Vastly improved option and argument handling provided by `pointybeard/helpers-cli-input` package

## 0.1.0
#### Added
-   Initial release

[Unreleased]: https://github.com/pointybeard/console/compare/1.1.0...integration
[1.1.0]: https://github.com/pointybeard/console/compare/1.0.1...1.1.0
[1.0.1]: https://github.com/pointybeard/console/compare/1.0.0...1.0.1
[1.0.0]: https://github.com/pointybeard/console/compare/0.1.0...1.0.0
