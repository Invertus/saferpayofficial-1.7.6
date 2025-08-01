<p align="center">
    <a href="https://www.six-payment-services.com" target="_blank">
        <img src="./views/img/readme/img.png" />
    </a>
</p>

> [!NOTE]
> This module is only compatible with PrestaShop versions **lower than 1.7.5.2.**\
> A module compatible with higher versions can be found here: [Saferpayofficial](https://github.com/Invertus/saferpayofficial)

## Prerequisites

Before installing the Saferpay module, ensure you have:

### Required Accounts & Credentials
- **Saferpay Backoffice Account** with valid username and password
- **API Credentials** for Saferpay Live and/or Test environments
- **Active Saferpay Terminal** for payment processing
- **Terminal ID** (Terminal ID parameter)
- **Customer ID** (CustomerId parameter)
- **Valid Acceptance Agreement** for credit cards or other payment methods

### Technical Requirements
- **Composer** installed on your system
  - Download from: https://getcomposer.org/download/
- **PrestaShop <=1.7.5.2** installed and configured

## Installation

1. **Download the Module**
   - Get the latest version from [releases page](https://github.com/Invertus/saferpayofficial/releases)

2. **Install Dependencies**
   ```bash
   composer install
   ```

3. **Upload to PrestaShop**
   - Upload the module to your `modules/` directory
   - Install through PrestaShop admin panel

4. **Configure Module**
   - Navigate to Modules > Module Manager
   - Find "Saferpay Official" and click Configure
   - Enter your API credentials and terminal information

## Account Setup

### Test Environment
- **Test Account Information:** https://www.six-payment-services.com/en/site/e-commerce-developer/testaccount.html
- Use test credentials for development and testing

### Live Environment
- **Register for Live Account:** https://www.six-payment-services.com/en/site/e-commerce/solutions/paymentsolution.html
- Contact SIX Payment Services for production credentials

## About SIX Payment Services

SIX Payment Services has been part of Worldline since 2018, making it Europe's largest technology partner for banks and merchants. Worldline is the European market leader in payment technology with:

- **45+ years** of experience in payment processing
- **11,000+** payment experts across 30+ countries
- **Complete value chain** coverage for cashless payment transactions
- **Highly secure** payment and transaction services

<p align="center">
    <a href="https://www.six-payment-services.com" target="_blank">
        <img src="./views/img/readme/02.png" alt="SIX Payment Services Features" />
    </a>
</p>

## Support

For technical support and questions:
- **Documentation:** Check the module documentation in your PrestaShop admin
- **SIX Payment Services:** Contact their support team for account-related issues
- **GitHub Issues:** Report bugs or feature requests on the GitHub repository

## License

This module requires a valid Saferpay license. Please contact SIX Payment Services for licensing information.
