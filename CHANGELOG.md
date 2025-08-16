# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2024-12-17

### Added
- **PHP 7.4-8.4 Support**: Full compatibility with modern PHP versions
- **Strict Type Safety**: Added type hints and return types throughout the codebase
- **Comprehensive Testing**: Added extensive test coverage with 13 tests and 30 assertions
- **Modern HTTP Client**: Replaced raw cURL with proper HTTP client implementation
- **Enhanced Security**: Added comprehensive input/output validation
- **Complete Documentation**: Added detailed README with usage examples
- **PSR Compliance**: Ensured code follows PSR standards
- **Composer Scripts**: Added test, check-style, and fix-style scripts
- **Development Dependencies**: Added PHPUnit, CodeSniffer, and Mockery

### Changed
- **Breaking**: Minimum PHP version is now 7.4
- **Improved**: Enhanced error handling and response validation
- **Modernized**: Updated all classes with modern PHP features
- **Refactored**: AbstractRequest now uses HTTP client instead of raw cURL
- **Enhanced**: Better parameter validation and sanitization
- **Updated**: Aligned with latest toyyibPay API specifications
- **Improved**: Response handling with proper type checking

### Fixed
- **Security**: Fixed potential security vulnerabilities in API communication
- **Validation**: Added proper input validation for all parameters
- **Error Handling**: Improved error messages and exception handling
- **Response Parsing**: Fixed potential issues with API response parsing
- **Type Safety**: Eliminated type-related bugs with strict typing

### Technical Details
- Updated composer.json with PHP 7.4+ requirement
- Added Guzzle HTTP client dependency
- Implemented comprehensive PHPUnit test suite
- Added PHP CodeSniffer for code quality
- Enhanced PHPDoc documentation
- Improved autoloading configuration

### Migration Guide
- **PHP Version**: Ensure your environment runs PHP 7.4 or higher
- **Dependencies**: Run `composer update` to install new dependencies
- **API Usage**: No breaking changes to public API methods
- **Testing**: Run `composer test` to verify installation

---

## [Unreleased]

### Planned
- Additional payment methods support
- Enhanced webhook handling
- Performance optimizations

---

**Note**: This is the first stable release of the modernized omnipay-toyyibpay library.
Previous versions were development releases and are not recommended for production use.