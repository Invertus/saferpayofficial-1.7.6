# Maintainers Guide - Saferpay Official PrestaShop Module

## Table of Contents
- [Development Setup](#development-setup)
- [Project Structure](#project-structure)
- [Coding Standards](#coding-standards)
- [Testing Guidelines](#testing-guidelines)
- [Development Workflow](#development-workflow)
- [Release Process](#release-process)
- [Troubleshooting](#troubleshooting)
- [Security Guidelines](#security-guidelines)

## Development Setup

### Prerequisites
- **PHP 7.4+** (compatible with PrestaShop 1.7.6.1+)
- **Composer** for dependency management
- **Git** for version control
- **Docker** (recommended for local development)
- **PrestaShop 1.7.6.1+** test environment

### Local Development Environment

1. **Clone the Repository**
   ```bash
   git clone https://github.com/Invertus/saferpayofficial.git
   cd saferpayofficial
   ```

2. **Install Dependencies**
   ```bash
   composer install
   composer install --dev  # for development dependencies
   ```

3. **Setup PrestaShop Test Environment**
   ```bash
   # Using Docker (recommended)
   docker-compose up -d

   # Or manual setup
   # Copy module to your PrestaShop modules directory
   cp -r . /path/to/prestashop/modules/saferpayofficial/
   ```

4. **Configure Development Environment**
   ```bash
   # Copy environment configuration
   cp .env.example .env

   # Update configuration for your local setup
   # Edit .env file with your local settings
   ```

## Project Structure

```
saferpayofficial/
├── src/                          # Core module logic
│   ├── Core/                     # Domain layer
│   │   ├── Entity/              # Domain entities
│   │   ├── Service/             # Business logic services
│   │   ├── Repository/          # Data access interfaces
│   │   └── Exception/           # Domain exceptions
│   ├── Infrastructure/          # Infrastructure layer
│   │   ├── Adapter/             # PrestaShop adapters
│   │   ├── Repository/          # Repository implementations
│   │   ├── Hook/                # Hook handlers
│   │   └── Cache/               # Caching implementations
│   └── Presentation/            # Presentation layer
│       ├── Form/                # Form definitions
│       ├── Controller/          # Controllers
│       └── ViewModel/           # View models
├── views/                        # Templates and assets
│   ├── templates/               # Smarty templates
│   ├── css/                     # Stylesheets
│   ├── js/                      # JavaScript files
│   └── img/                     # Images
├── tests/                       # Test suite
│   ├── Unit/                    # Unit tests
│   ├── Integration/             # Integration tests
│   └── E2E/                     # End-to-end tests
├── config/                      # Configuration files
├── translations/                # Language files
├── upgrade/                     # Database upgrade scripts
└── docs/                        # Documentation
```

## Coding Standards

### PHP Standards
- **PSR-12** coding standards
- **Strict typing** (`declare(strict_types=1);`)
- **PSR-4** autoloading
- **PHP 7.4+** features (typed properties, arrow functions)

### Code Style Examples

```php
<?php

declare(strict_types=1);

namespace SaferpayOfficial\Core\Service;

use SaferpayOfficial\Core\Entity\Payment;
use SaferpayOfficial\Core\Repository\PaymentRepositoryInterface;
use SaferpayOfficial\Core\Exception\PaymentProcessingException;

class PaymentService
{
    private PaymentRepositoryInterface $paymentRepository;

    public function __construct(PaymentRepositoryInterface $paymentRepository)
    {
        $this->paymentRepository = $paymentRepository;
    }

    public function processPayment(Payment $payment): bool
    {
        if (!$payment->isValid()) {
            throw new PaymentProcessingException('Invalid payment data');
        }

        return $this->paymentRepository->save($payment);
    }
}
```

### Naming Conventions
- **Classes**: PascalCase (`PaymentService`)
- **Methods**: camelCase (`processPayment`)
- **Variables**: camelCase (`$paymentData`)
- **Constants**: UPPER_SNAKE_CASE (`PAYMENT_STATUS_PENDING`)
- **Files**: Match class names exactly

### Documentation Standards
- **PHPDoc** for all public methods
- **API documentation** for endpoints
- **Inline comments** for complex logic
- **README updates** for new features

## Testing Guidelines

### Test Structure
```php
<?php

declare(strict_types=1);

namespace Tests\SaferpayOfficial\Unit\Core\Service;

use PHPUnit\Framework\TestCase;
use SaferpayOfficial\Core\Service\PaymentService;
use SaferpayOfficial\Core\Entity\Payment;

class PaymentServiceTest extends TestCase
{
    private PaymentService $paymentService;
    private MockObject $paymentRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->paymentRepository = $this->createMock(PaymentRepositoryInterface::class);
        $this->paymentService = new PaymentService($this->paymentRepository);
    }

    public function testItProcessesValidPayment(): void
    {
        $payment = new Payment();
        $payment->setAmount(100.00);

        $this->paymentRepository
            ->expects($this->once())
            ->method('save')
            ->with($payment)
            ->willReturn(true);

        $result = $this->paymentService->processPayment($payment);

        self::assertTrue($result);
    }
}
```

### Test Categories
- **Unit Tests**: Individual components (80%+ coverage)
- **Integration Tests**: Component interactions
- **Functional Tests**: End-to-end workflows
- **Hook Tests**: PrestaShop hook functionality

### Running Tests
```bash
# Run all tests
composer test

# Run specific test suite
composer test:unit
composer test:integration
composer test:e2e

# Generate coverage report
composer test:coverage
```

## Development Workflow

### Branch Strategy
- **main**: Production-ready code
- **develop**: Integration branch
- **feature/***: New features
- **bugfix/***: Bug fixes
- **hotfix/***: Critical fixes

### Commit Guidelines
```
[TICKET-ID] Brief description of changes

- Detailed bullet points of changes
- Reference related issues
- Include breaking changes if any
```

### Pull Request Process
1. **Create Feature Branch**
   ```bash
   git checkout -b feature/new-payment-method
   ```

2. **Make Changes**
   - Follow coding standards
   - Add tests for new functionality
   - Update documentation

3. **Test Locally**
   ```bash
   composer test
   composer lint
   composer cs-fix
   ```

4. **Submit PR**
   - Clear description of changes
   - Link related issues
   - Request code review

## Release Process

### Version Management
- **Semantic Versioning**: MAJOR.MINOR.PATCH
- **Changelog**: Update CHANGELOG.md
- **Tagging**: Create git tags for releases

### Pre-release Checklist
- [ ] All tests passing
- [ ] Code quality checks passed
- [ ] Documentation updated
- [ ] Changelog updated
- [ ] Version numbers updated
- [ ] Security review completed

### Release Steps
1. **Update Version**
   ```bash
   # Update version in module class
   sed -i 's/version = ".*"/version = "1.2.0"/' saferpayofficial.php
   ```

2. **Create Release Branch**
   ```bash
   git checkout -b release/1.2.0
   git commit -am "Bump version to 1.2.0"
   ```

3. **Tag Release**
   ```bash
   git tag -a v1.2.0 -m "Release version 1.2.0"
   git push origin v1.2.0
   ```

4. **Create GitHub Release**
   - Upload compiled module
   - Add release notes
   - Mark as latest release

## Troubleshooting

### Common Issues

#### Module Not Installing
```bash
# Check PrestaShop version compatibility
php -r "echo _PS_VERSION_;"

# Verify module structure
ls -la modules/saferpayofficial/

# Check PHP requirements
php -m | grep -E "(curl|json|openssl)"
```

#### Hook Issues
```bash
# Clear PrestaShop cache
rm -rf var/cache/*
rm -rf cache/*

# Reinstall module
php bin/console prestashop:module:uninstall saferpayofficial
php bin/console prestashop:module:install saferpayofficial
```

#### Test Failures
```bash
# Check test environment
composer test:env-check

# Run tests with verbose output
composer test -- --verbose

# Check database connection
php bin/console doctrine:database:create --if-not-exists
```

### Debug Tools
- **PrestaShop Debug Mode**: Enable in config/defines.inc.php
- **Xdebug**: For step-by-step debugging
- **Logs**: Check var/logs/ for errors
- **Database**: Use PrestaShop's debug tools

## Security Guidelines

### Code Security
- **Input Validation**: Validate all user inputs
- **SQL Injection**: Use prepared statements
- **XSS Prevention**: Escape output data
- **CSRF Protection**: Use PrestaShop tokens
- **Authentication**: Verify user permissions

### Security Checklist
- [ ] No hardcoded credentials
- [ ] Input sanitization implemented
- [ ] Output escaping applied
- [ ] SQL queries parameterized
- [ ] File uploads validated
- [ ] Error messages don't leak information

### Security Testing
```bash
# Run security checks
composer security:check

# Static analysis
composer analyze

# Dependency vulnerability scan
composer audit
```

## Performance Guidelines

### Optimization Tips
- **Database Queries**: Minimize N+1 queries
- **Caching**: Use PrestaShop cache system
- **Assets**: Minify CSS/JS for production
- **Images**: Optimize image sizes
- **Lazy Loading**: Load resources on demand

### Performance Testing
```bash
# Run performance tests
composer test:performance

# Check memory usage
composer test:memory

# Database query analysis
composer test:queries
```

## Documentation

### Required Documentation
- **API Documentation**: For all public methods
- **Installation Guide**: Step-by-step setup
- **Configuration Guide**: All module settings
- **Troubleshooting Guide**: Common issues
- **Changelog**: Version history

### Documentation Standards
- **Clear and concise** language
- **Code examples** for complex features
- **Screenshots** for UI elements
- **Regular updates** with releases

## Support and Maintenance

### Issue Management
- **GitHub Issues**: For bug reports and feature requests
- **Priority Levels**: Critical, High, Medium, Low
- **Response Times**: 24h for critical, 72h for others
- **Resolution Tracking**: Update issue status

### Maintenance Schedule
- **Weekly**: Code review and updates
- **Monthly**: Security updates and patches
- **Quarterly**: Major feature releases
- **Annually**: Compatibility updates

### Support Channels
- **GitHub Issues**: Technical support
- **Email**: Critical issues
- **Documentation**: Self-service support
- **Community**: Forum discussions

---

## Quick Reference

### Useful Commands
```bash
# Development
composer install
composer test
composer lint
composer cs-fix

# Release
composer release:prepare
composer release:tag
composer release:deploy

# Maintenance
composer security:check
composer update:deps
composer cleanup
```

### Important Files
- `saferpayofficial.php`: Main module class
- `composer.json`: Dependencies and scripts
- `config/services.yml`: Service configuration
- `tests/`: Test suite
- `docs/`: Documentation

### Contact Information
- **Repository**: https://github.com/Invertus/saferpayofficial
- **Issues**: https://github.com/Invertus/saferpayofficial/issues
- **Documentation**: https://github.com/Invertus/saferpayofficial/wiki
