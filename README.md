# Peeap Pay



<p align="center">
  <a href="https://pay.peeap.com/home" target="_blank">
    <img src="https://img.shields.io/badge/demo-watch%20video-blue" alt="Watch Demo Video">
  </a>
  <a href="https://peeap.com"><img src="https://img.shields.io/badge/platform-Peeap-brightgreen" alt="Platform"></a>
  <a href="https://github.com/peeap/peeap"><img src="https://img.shields.io/github/stars/peeap/peeap" alt="GitHub Stars"></a>
  <a href="https://github.com/peeap/peeap/releases"><img src="https://img.shields.io/github/v/release/peeap/peeap" alt="Latest Release"></a>
  <a href="https://opensource.org/licenses/MIT"><img src="https://img.shields.io/badge/license-MIT-blue.svg" alt="License"></a>
</p>



Peeap Pay is an innovative and secure payment gateway that simplifies financial transactions in Sierra Leone and beyond. It’s built to serve businesses, governments, and individuals, enabling seamless payments with cutting-edge features tailored to local and global needs. Peeap Pay combines reliability, scalability, and flexibility to meet the demands of a diverse user base.

### Key Features:
- **Multi-channel Payments**: Supports mobile money platforms like Orange Money and Africell, alongside traditional payment methods such as credit cards.
- **Seamless Integration**: Easy integration with e-commerce platforms, SaaS applications, and custom solutions using Peeap Pay APIs.
- **Advanced Reporting**: Provides detailed transaction insights to help businesses track their income and growth.
- **Multi-currency Support**: Enables transactions in local currencies and international payments.
- **Enhanced Security**: Implements strong encryption standards for safe and secure transactions.

### Peeap Pay Services:
1. **E-commerce Payments**: Simplify online shopping with secure checkout solutions.
2. **Mobile Money Integration**: Integrates with Orange Money and Africell for quick payments.
3. **School Payments**: Helps schools manage tuition fees, donations, and other financial transactions.
4. **Government Solutions**: Streamlines tax collection and fund disbursement for government projects.
5. **Custom APIs**: Offers REST APIs for businesses to integrate Peeap Pay into their own platforms.
6. **Vendor Management**: Supports multi-vendor marketplaces with tools to manage payments and commissions.

Peeap Pay is designed to empower businesses and institutions with the tools they need to thrive in the digital economy.

---

## Download and Install

Follow these steps to download and install Peeap Pay from GitHub:

### Prerequisites:
- **Git**: [Install Git](https://git-scm.com/downloads)
- **Composer**: [Install Composer](https://getcomposer.org/download/)

### Installation Steps:

Run the following commands in your terminal or command prompt:

```bash
# Clone the repository
git clone https://github.com/peeap/peeap.git

# Navigate to the project directory
cd peeap

# Install dependencies
composer install

# Copy .env.example to .env
cp .env.example .env

# Generate application key
php artisan key:generate

# Run database migrations (optional, if required)
php artisan migrate

# Start the development server
php artisan serve
