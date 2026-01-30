# 🏷️ PRICEDOM - Smart Price Comparison Platform

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-11.9-red?style=for-the-badge&logo=laravel" alt="Laravel">
  <img src="https://img.shields.io/badge/PHP-8.2+-blue?style=for-the-badge&logo=php" alt="PHP">
  <img src="https://img.shields.io/badge/Tailwind-CSS-38B2AC?style=for-the-badge&logo=tailwind-css" alt="Tailwind">
  <img src="https://img.shields.io/badge/OpenAI-GPT4-412991?style=for-the-badge&logo=openai" alt="OpenAI">
</p>

**PRICEDOM** is a comprehensive Laravel-based platform that combines intelligent price comparison, social food sharing, and nutrition tracking. Built with modern web technologies and AI-powered features.

## ✨ Key Features

### 🏷️ **Smart Price Comparison**
- **Intelligent Search**: AI-powered product name matching with fuzzy search
- **Real-time Data**: Access to millions of food prices worldwide
- **Advanced Filters**: Search by price range, location, store, and date
- **Visual Results**: Product images and detailed information display
- **Statistics Dashboard**: Min/max/average prices, store counts, and trends

### 🍽️ **Social Food Platform**
- **Food Feed**: Share and discover food experiences
- **AI Nutrition Analysis**: Automatic nutritional information using GPT-4 Vision
- **Community Interaction**: Likes, comments, and reactions system
- **User Profiles**: Customizable profiles with avatars and tags

### 💸 **Price Contributions**
- **OCR Ticket Scanning**: Upload receipts for automatic price extraction
- **Manual Entry**: Add prices manually with validation
- **Gamification**: Badge system for active contributors
- **Image Compression**: Optimized image handling for uploads

### 👑 **Admin Dashboard**
- **Analytics**: Comprehensive statistics and insights
- **User Management**: User administration and moderation
- **Data Export**: Excel exports for all platform data
- **Content Moderation**: Review and approve contributions

## 🚀 Quick Deployment

### Railway Deployment (Recommended)

1. **Fork this repository**
2. **Connect to Railway**
3. **Set Environment Variables:**
   ```bash
   APP_KEY=base64:your-app-key-here
   APP_ENV=production
   APP_DEBUG=false
   DB_CONNECTION=sqlite
   DB_DATABASE=/tmp/database.sqlite
   OPENAI_API_KEY=your-openai-api-key
   ```
4. **Deploy** - Railway will automatically detect Laravel!

### Manual Deployment

```bash
# Clone repository
git clone https://github.com/M13E-LAB/Pricedom.git
cd Pricedom

# Install dependencies
composer install
npm install

# Environment setup
cp .env.example .env
php artisan key:generate

# Database setup
touch database/database.sqlite
php artisan migrate

# Build assets
npm run build

# Start server
php artisan serve
```

## 🔧 Configuration

### Required Environment Variables

```bash
# Application
APP_NAME=PRICEDOM
APP_ENV=production
APP_KEY=base64:your-generated-key
APP_DEBUG=false
APP_URL=https://your-domain.com

# Database
DB_CONNECTION=sqlite
DB_DATABASE=/tmp/database.sqlite

# OpenAI Integration
OPENAI_API_KEY=sk-your-openai-api-key

# Mail Configuration (optional)
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email
MAIL_PASSWORD=your-password

# AWS S3/Cloudflare R2 (for image storage)
AWS_ACCESS_KEY_ID=your-access-key
AWS_SECRET_ACCESS_KEY=your-secret-key
AWS_DEFAULT_REGION=auto
AWS_BUCKET=your-bucket-name
```

## 🏗️ Architecture

### Backend Stack
- **Framework**: Laravel 11.9
- **Language**: PHP 8.2+
- **Database**: SQLite (production), PostgreSQL (optional)
- **APIs**: Open Food Facts, Open Prices, OpenAI GPT-4

### Frontend Stack
- **Templates**: Blade (Laravel's templating engine)
- **Styling**: Tailwind CSS
- **Build Tool**: Vite
- **JavaScript**: Vanilla JS with modern ES6+

### AI & External Services
- **OCR**: Python script with GPT-4 Vision
- **Nutrition Analysis**: OpenAI GPT-4 Vision API
- **Price Data**: Open Prices API
- **Product Data**: Open Food Facts API

## 📱 User Interface

### 🎨 Modern Design
- **Responsive**: Mobile-first design with Tailwind CSS
- **Dark Theme**: Beautiful gradient backgrounds and glassmorphism effects
- **Intuitive Navigation**: Clear menu structure and breadcrumbs
- **Visual Feedback**: Loading states, animations, and transitions

### 🔍 Smart Search
- **Auto-complete**: Intelligent product name suggestions
- **Visual Results**: Product images and detailed information
- **Filter System**: Multiple filter options with real-time updates
- **Pagination**: Efficient large dataset navigation

## 🧠 AI Features

### 🤖 GPT-4 Vision Integration
- **Receipt OCR**: Extract prices from receipt photos
- **Nutrition Analysis**: Analyze food photos for nutritional content
- **Smart Categorization**: Automatic product categorization
- **Content Moderation**: AI-assisted content review

### 🔍 Intelligent Search
- **Fuzzy Matching**: Find products even with typos
- **Multi-language**: Support for French and English product names
- **Contextual Results**: Prioritize relevant results based on user location

## 📊 Analytics & Insights

### 📈 Platform Metrics
- **User Engagement**: Active users, contributions, social interactions
- **Price Trends**: Historical price data and market insights
- **Geographic Data**: Price variations by location and store
- **Product Analytics**: Most searched products and categories

### 📋 Export Capabilities
- **Excel Reports**: Comprehensive data exports
- **API Access**: RESTful API for external integrations
- **Real-time Data**: Live updates and notifications

## 🛡️ Security & Privacy

### 🔒 Security Features
- **Authentication**: Secure login with password reset
- **Data Validation**: Server-side validation for all inputs
- **CSRF Protection**: Laravel's built-in CSRF protection
- **Rate Limiting**: API rate limiting to prevent abuse

### 🔐 Privacy
- **Data Minimization**: Collect only necessary user data
- **Secure Storage**: Encrypted sensitive information
- **GDPR Compliance**: User data rights and privacy controls

## 🤝 Contributing

We welcome contributions! Please follow these steps:

1. **Fork the repository**
2. **Create a feature branch**: `git checkout -b feature/amazing-feature`
3. **Commit changes**: `git commit -m 'Add amazing feature'`
4. **Push to branch**: `git push origin feature/amazing-feature`
5. **Open a Pull Request**

### Development Guidelines
- Follow PSR-12 coding standards
- Write tests for new features
- Update documentation as needed
- Use conventional commit messages

## 📝 API Documentation

### Price Search API
```php
GET /api/prices/search
Parameters:
- product_name: string (optional)
- product_code: string (optional)
- location: string (optional)
- price_min: float (optional)
- price_max: float (optional)
- date_from: date (optional)
- date_to: date (optional)
```

### Social Feed API
```php
GET /api/social/feed
POST /api/social/posts
Parameters:
- content: string (required)
- image: file (optional)
- location: string (optional)
```

## 🔧 Troubleshooting

### Common Issues

**Database Connection Error**
```bash
# Ensure SQLite file exists and has proper permissions
touch database/database.sqlite
chmod 664 database/database.sqlite
```

**OpenAI API Error**
```bash
# Verify your API key is set correctly
php artisan config:clear
php artisan cache:clear
```

**Asset Build Issues**
```bash
# Rebuild assets
npm run build
php artisan storage:link
```


## 🙏 Acknowledgments

- **Laravel Team** - For the amazing framework
- **Open Food Facts** - For the comprehensive food database
- **Open Prices** - For the collaborative price database
- **OpenAI** - For GPT-4 Vision API
- **Tailwind CSS** - For the utility-first CSS framework

## 📞 Support

- **Issues**: [GitHub Issues](https://github.com/M13E-LAB/Pricedom/issues)
- **Documentation**: [Wiki](https://github.com/M13E-LAB/Pricedom/wiki)
- **Community**: [Discussions](https://github.com/M13E-LAB/Pricedom/discussions)

---

<p align="center">
  Made with ❤️ by <a href="https://github.com/M13E-LAB">M13E-LAB</a>
</p>