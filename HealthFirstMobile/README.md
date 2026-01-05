# 🏥 HealthFirst Mobile App

A comprehensive mobile application for hospital employees built with React Native, designed specifically for the Ethiopian healthcare system.

## 📱 Overview

HealthFirst Mobile provides role-based access to essential HR and health service functions for hospital employees, including:

- **HR Officers**: Employee management, leave requests, payroll processing
- **Health Officers**: Patient management, appointments, inventory tracking
- **Employees**: Self-service portal for leave requests, schedules, and profile management

## 🚀 Features

### 🔐 Authentication & Security
- Email/password and biometric authentication
- JWT token-based security with refresh tokens
- Role-based access control
- Secure data storage with encryption
- Session management and automatic logout

### 👥 Employee Management (HR Officers)
- Complete employee directory with search and filters
- Add/edit employee profiles with photo capture
- Document upload and management
- Employee status tracking (active, on-leave, inactive)
- Bulk operations and data export

### 🏖️ Leave Management
- Submit and track leave requests
- Real-time leave balance calculation
- Approval workflow with swipe actions
- Leave calendar and conflict detection
- Multiple leave types (annual, sick, maternity, etc.)

### 🏥 Patient Management (Health Officers)
- Patient registration and profile management
- Medical history tracking
- Insurance information management
- Quick patient search and access
- QR code generation for patient identification

### 📅 Appointment Management
- Interactive appointment scheduler
- Calendar views (daily, weekly, monthly)
- Doctor assignment and availability
- Appointment status tracking
- Automated reminders and notifications

### 💊 Inventory Management
- Stock level monitoring with alerts
- Barcode scanning for quick item identification
- Expiry date tracking
- Supplier information and ordering
- Usage pattern analytics

### 📊 Reports & Analytics
- Interactive charts and visualizations
- Exportable reports (PDF, CSV)
- Real-time dashboard statistics
- Custom date range filtering
- Performance metrics and KPIs

### 🔔 Notifications
- Push notifications for critical updates
- In-app notification center
- Customizable notification preferences
- Rich notifications with action buttons
- Deep linking to relevant screens

### 🌐 Offline Capabilities
- Core functions work offline
- Automatic data synchronization
- Conflict resolution for data changes
- Offline form completion
- Background sync when connected

## 🏗️ Technical Architecture

### Technology Stack
- **Framework**: React Native 0.72+
- **State Management**: Redux Toolkit + RTK Query
- **Navigation**: React Navigation 6
- **UI Components**: React Native Paper + Custom Components
- **Database**: SQLite (offline) + MySQL (server sync)
- **Authentication**: JWT + React Native Biometrics
- **Push Notifications**: Firebase Cloud Messaging
- **Charts**: Victory Native + React Native Chart Kit
- **File Storage**: React Native FS + Secure Storage

### Project Structure
```
HealthFirstMobile/
├── src/
│   ├── components/          # Reusable UI components
│   ├── screens/            # Screen components
│   │   ├── auth/           # Authentication screens
│   │   ├── hr/             # HR Officer screens
│   │   ├── health/         # Health Officer screens
│   │   ├── employee/       # Employee self-service
│   │   └── shared/         # Shared screens
│   ├── navigation/         # Navigation configuration
│   ├── services/          # API services and utilities
│   ├── store/             # Redux store and slices
│   ├── utils/             # Helper functions
│   ├── theme/             # App theme and styling
│   ├── i18n/              # Internationalization
│   └── assets/            # Images, fonts, etc.
├── android/               # Android-specific code
├── ios/                   # iOS-specific code
└── docs/                  # Documentation
```

## 🛠️ Installation & Setup

### Prerequisites
- Node.js 16+ and npm/yarn
- React Native CLI
- Android Studio (for Android development)
- Xcode (for iOS development)
- Java Development Kit (JDK) 11+

### Installation Steps

1. **Clone the repository**
   ```bash
   git clone https://github.com/your-org/healthfirst-mobile.git
   cd healthfirst-mobile
   ```

2. **Install dependencies**
   ```bash
   npm install
   # or
   yarn install
   ```

3. **Install iOS dependencies (iOS only)**
   ```bash
   cd ios && pod install && cd ..
   ```

4. **Configure environment**
   ```bash
   cp .env.example .env
   # Edit .env with your configuration
   ```

5. **Start Metro bundler**
   ```bash
   npm start
   # or
   yarn start
   ```

6. **Run the app**
   ```bash
   # Android
   npm run android
   # or
   yarn android

   # iOS
   npm run ios
   # or
   yarn ios
   ```

### Environment Configuration

Create a `.env` file in the root directory:

```env
# API Configuration
API_BASE_URL=http://localhost/ehealth
API_TIMEOUT=30000

# Firebase Configuration
FIREBASE_API_KEY=your_firebase_api_key
FIREBASE_AUTH_DOMAIN=your_project.firebaseapp.com
FIREBASE_PROJECT_ID=your_project_id

# App Configuration
APP_NAME=HealthFirst
APP_VERSION=1.0.0
ENVIRONMENT=development

# Security
ENCRYPTION_KEY=your_encryption_key
```

## 🧪 Testing

### Running Tests
```bash
# Unit tests
npm test
# or
yarn test

# E2E tests
npm run test:e2e
# or
yarn test:e2e

# Test coverage
npm run test:coverage
# or
yarn test:coverage
```

### Testing Strategy
- **Unit Tests**: Component and function testing with Jest
- **Integration Tests**: API and service testing
- **E2E Tests**: Complete user flow testing with Detox
- **Performance Tests**: Load and stress testing
- **Security Tests**: Vulnerability assessment

## 📱 Building for Production

### Android Build
```bash
# Generate signed APK
npm run build:android
# or
yarn build:android

# Generate AAB for Play Store
cd android
./gradlew bundleRelease
```

### iOS Build
```bash
# Build for App Store
npm run build:ios
# or
yarn build:ios
```

## 🌐 Localization

The app supports multiple languages:
- **English** (Primary)
- **Amharic** (አማርኛ)
- **Oromo** (Afaan Oromoo)
- **Tigrinya** (ትግርኛ)

### Adding New Languages
1. Create translation files in `src/i18n/locales/`
2. Add language configuration in `src/i18n/i18n.ts`
3. Update language selector in settings

## 🔒 Security Features

### Data Protection
- AES-256 encryption for sensitive data
- Secure keychain/keystore for credentials
- Certificate pinning for API calls
- Data masking in app switcher
- Automatic session timeout

### Privacy Compliance
- Clear privacy policy and consent
- Data minimization principles
- User control over data
- Audit logging for data access
- GDPR/HIPAA-like compliance

## 📊 Performance Optimization

### App Performance
- Lazy loading for screens and components
- Image optimization and caching
- Memory management and leak prevention
- Battery usage optimization
- Background task management

### Network Optimization
- Request batching and caching
- Gzip compression for API responses
- Retry logic for failed requests
- Offline-first architecture
- Progressive data loading

## 🚀 Deployment

### App Store Distribution
- **iOS App Store**: Enterprise or public distribution
- **Google Play Store**: Internal testing and production
- **Enterprise Distribution**: Direct APK/IPA distribution
- **Over-the-Air Updates**: CodePush integration

### CI/CD Pipeline
```yaml
# Example GitHub Actions workflow
name: Build and Deploy
on:
  push:
    branches: [main, develop]
jobs:
  build:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - name: Setup Node.js
        uses: actions/setup-node@v2
        with:
          node-version: '16'
      - name: Install dependencies
        run: npm install
      - name: Run tests
        run: npm test
      - name: Build Android
        run: npm run build:android
```

## 📈 Monitoring & Analytics

### Performance Monitoring
- Crash reporting with detailed stack traces
- Performance metrics and bottleneck identification
- User behavior analytics
- API response time monitoring
- Battery and memory usage tracking

### Business Analytics
- Feature usage statistics
- User engagement metrics
- Conversion funnel analysis
- A/B testing capabilities
- Custom event tracking

## 🤝 Contributing

### Development Workflow
1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### Code Standards
- Follow React Native and TypeScript best practices
- Use ESLint and Prettier for code formatting
- Write comprehensive tests for new features
- Document complex functions and components
- Follow conventional commit messages

### Pull Request Guidelines
- Provide clear description of changes
- Include screenshots for UI changes
- Ensure all tests pass
- Update documentation if needed
- Request review from maintainers

## 📞 Support & Documentation

### Getting Help
- **Documentation**: Check the `/docs` folder for detailed guides
- **Issues**: Report bugs and feature requests on GitHub
- **Discussions**: Join community discussions for questions
- **Wiki**: Access the project wiki for additional resources

### Troubleshooting
- **Build Issues**: Check the troubleshooting guide in `/docs/troubleshooting.md`
- **Performance**: Review performance optimization tips
- **Debugging**: Use Flipper for debugging and profiling
- **Logs**: Check device logs for error details

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- Ethiopian Ministry of Health for healthcare system requirements
- React Native community for excellent libraries and tools
- Healthcare professionals who provided domain expertise
- Open source contributors and maintainers

---

**🏥 HealthFirst Mobile** - Empowering healthcare workers with mobile-first technology for efficient patient care and workforce management.

For more information, visit our [documentation site](https://healthfirst-docs.example.com) or contact the development team.