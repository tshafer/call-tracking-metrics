# Testing Guide for CallTrackingMetrics WordPress Plugin

This directory contains comprehensive tests for the CallTrackingMetrics WordPress plugin, with a focus on the new duplicate prevention system.

## 🧪 **Testing Framework**

We use **PHPUnit** as our testing framework, which provides:
- **Standard PHP testing** with WordPress compatibility
- **Powerful assertions** and test organization
- **Comprehensive coverage** reporting
- **Professional testing** standards

## 📁 **Test Structure**

```
tests/
├── Feature/                    # Feature/integration tests
│   ├── DuplicatePreventionFeatureTest.php
│   ├── FormIntegrationTest.php
│   └── AdminSettingsTest.php
├── Unit/                      # Unit tests
│   └── DuplicatePreventionUnitTest.php
├── TestCase.php              # Base test case with WordPress mocks
├── phpunit.xml               # PHPUnit configuration
└── README.md                 # This file
```

## 🚀 **Running Tests**

### **Prerequisites**
```bash
# Install dependencies
composer install

# Ensure PHPUnit is available
composer require --dev phpunit/phpunit
```

### **Basic Test Commands**
```bash
# Run all tests
composer test

# Run specific test suites
composer test:unit          # Unit tests only
composer test:feature       # Feature tests only

# Run with coverage
composer test:coverage      # Console coverage
composer test:coverage-html # HTML coverage report
```

### **Individual Test Files**
```bash
# Run specific test files
./vendor/bin/phpunit tests/Unit/DuplicatePreventionUnitTest.php
./vendor/bin/phpunit tests/Feature/FormIntegrationTest.php

# Run specific test methods
./vendor/bin/phpunit --filter="test_is_duplicate_submission_returns_false_for_first_submission"
```

## 🧩 **Test Categories**

### **1. Unit Tests** (`tests/Unit/`)
- **Purpose**: Test individual components in isolation
- **Coverage**: Service classes, utility functions, business logic
- **Examples**: `DuplicatePreventionUnitTest.php`

**Key Tests**:
- Transient key generation
- Duplicate detection logic
- Settings management
- Edge case handling

### **2. Feature Tests** (`tests/Feature/`)
- **Purpose**: Test complete features and integrations
- **Coverage**: Form submissions, admin settings, JavaScript injection
- **Examples**: `FormIntegrationTest.php`, `AdminSettingsTest.php`

**Key Tests**:
- Contact Form 7 integration
- Gravity Forms integration
- Admin settings rendering
- Form submission prevention

## 🔧 **Test Configuration**

### **Base TestCase Class**
All tests extend `Tests\TestCase` which provides:
- **WordPress function mocking** via Brain Monkey
- **Common test utilities** and helpers
- **Automatic setup/teardown** for each test
- **Mock creation helpers** for forms and entries

### **WordPress Function Mocking**
```php
// Mock WordPress functions
$this->mockWordPressFunction('get_transient', false);
$this->mockWordPressFunction('set_transient', true);

// Mock with callbacks
$this->mockWordPressFunctionWithCallback('get_option', function($key) {
    return $key === 'ctm_enabled' ? true : false;
});
```

### **Assertion Helpers**
```php
// WordPress function assertions
$this->assertWordPressFunctionCalled('get_transient');
$this->assertWordPressFunctionNotCalled('update_option');

// Transient assertions
$this->assertTransientSet('ctm_duplicate_', time(), 60);
$this->assertTransientRetrieved('ctm_duplicate_');
$this->assertTransientDeleted('ctm_duplicate_');
```

## 📝 **Writing New Tests**

### **Test Method Naming**
```php
// Use descriptive names that explain the test scenario
public function test_is_duplicate_submission_returns_true_for_duplicate()
public function test_handles_missing_ctm_session_id_gracefully()
public function test_generates_unique_transient_keys_for_different_forms()
```

### **Test Structure**
```php
public function test_example_test()
{
    // Arrange - Set up test data and mocks
    $this->mockWordPressFunction('get_transient', false);
    $formId = 'form123';
    $formType = 'cf7';
    
    // Act - Execute the code being tested
    $result = $this->service->isDuplicateSubmission($formId, $formType);
    
    // Assert - Verify the expected outcome
    $this->assertFalse($result);
    $this->assertTransientSet('ctm_duplicate_', time(), 60);
}
```

### **Test Organization**
```php
class DuplicatePreventionTest extends TestCase
{
    public function test_prevents_duplicate_submissions()
    {
        // Test implementation
    }
    
    public function test_allows_submissions_after_expiration()
    {
        // Test implementation
    }
    
    public function test_handles_edge_cases()
    {
        // Test implementation
    }
}
```

## 🎯 **Test Coverage Goals**

### **Duplicate Prevention System**
- ✅ **Service Layer**: 100% coverage
- ✅ **Form Integration**: 100% coverage
- ✅ **Admin Settings**: 100% coverage
- ✅ **JavaScript Injection**: 100% coverage
- ✅ **Error Handling**: 100% coverage

### **Core Functionality**
- ✅ **Transient Management**: 100% coverage
- ✅ **Session Tracking**: 100% coverage
- ✅ **IP Fallback**: 100% coverage
- ✅ **Configuration**: 100% coverage

## 🐛 **Debugging Tests**

### **Enable Verbose Output**
```bash
# Run tests with verbose output
./vendor/bin/phpunit --verbose

# Run specific test with debug info
./vendor/bin/phpunit --filter="test_name" --verbose
```

### **Common Issues**
1. **WordPress Function Not Mocked**: Ensure function is mocked in `TestCase::setupWordPressMocks()`
2. **Namespace Issues**: Check autoloading in `composer.json`
3. **Mock Expectations**: Verify mock setup matches test expectations

### **Test Isolation**
- Each test runs in isolation
- Global variables are reset between tests
- WordPress function mocks are reset automatically
- No database or file system dependencies

## 📊 **Coverage Reports**

### **Generate Coverage**
```bash
# Console coverage
composer test:coverage

# HTML coverage report
composer test:coverage-html
```

### **Coverage Targets**
- **Overall Coverage**: >90%
- **Critical Paths**: 100%
- **Error Handling**: 100%
- **Admin Interface**: 100%

## 🔄 **Continuous Integration**

### **GitHub Actions**
Tests run automatically on:
- **Pull Requests**: Full test suite
- **Main Branch**: Full test suite + coverage
- **Scheduled**: Daily test runs

### **Pre-commit Hooks**
```bash
# Install pre-commit hooks
composer install-hooks

# Run tests before commit
composer test:quick
```

## 📚 **Additional Resources**

- **PHPUnit Documentation**: https://phpunit.de/
- **Brain Monkey Documentation**: https://brain-wp.github.io/BrainMonkey/
- **WordPress Testing**: https://developer.wordpress.org/plugins/testing/

## 🆘 **Getting Help**

If you encounter issues with testing:

1. **Check the logs**: Look for error messages in test output
2. **Verify dependencies**: Ensure all packages are installed
3. **Check PHP version**: Tests require PHP 8.0+
4. **Review mocks**: Ensure WordPress functions are properly mocked

For complex issues, refer to the test examples in this directory or check the main plugin documentation.
