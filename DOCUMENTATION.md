# Hilal - Islamic Moon Sighting Platform

## Documentation

### Table of Contents
1. [Overview](#overview)
2. [Architecture](#architecture)
3. [Main Features](#main-features)
4. [Components](#components)
5. [API Reference](#api-reference)
6. [Development Setup](#development-setup)
7. [Production Deployment](#production-deployment)
8. [Configuration](#configuration)

---

## Overview

Hilal is a comprehensive Islamic Moon Sighting Platform designed for New Zealand Muslims. It provides:
- Accurate Hijri calendar with confirmed/estimated month dates
- Prayer times for 30+ NZ mosques with iqamah times
- Qibla direction finder
- Moon sighting report submission
- Bilingual support (English/Arabic)
- Cross-platform mobile app

### Tech Stack

| Component | Technology |
|-----------|------------|
| Backend | WordPress 6.4+, PHP 8.2 |
| Database | MySQL 8.0 |
| Mobile App | React Native (Expo 54), TypeScript |
| Authentication | JWT (JSON Web Tokens) |
| API | WordPress REST API |

---

## Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                      CLIENTS                                 │
├─────────────────────┬───────────────────────────────────────┤
│   Mobile App        │        Web Portal                     │
│   (React Native)    │        (WordPress Theme)              │
└─────────┬───────────┴───────────────┬───────────────────────┘
          │                           │
          │      REST API             │
          │  /?rest_route=/hilal/v1   │
          │                           │
┌─────────▼───────────────────────────▼───────────────────────┐
│                    WORDPRESS BACKEND                         │
├─────────────────────────────────────────────────────────────┤
│  hilal-plugin/                                               │
│  ├── API Layer (REST Endpoints)                             │
│  │   ├── Calendar API      /today, /hijri-calendar          │
│  │   ├── Prayer Times API  /prayer-times, /mosques          │
│  │   ├── Qibla API         /qibla                           │
│  │   ├── Announcements API /announcements                   │
│  │   └── Sighting API      /sighting-report                 │
│  │                                                           │
│  ├── Custom Post Types                                       │
│  │   ├── hijri_month       Hijri calendar months            │
│  │   ├── announcement      News & announcements             │
│  │   ├── sighting_report   Moon sighting reports            │
│  │   └── islamic_event     Islamic calendar events          │
│  │                                                           │
│  └── Helpers                                                 │
│      ├── Hijri Date Calculator                              │
│      └── Prayer Times Calculator                            │
├─────────────────────────────────────────────────────────────┤
│  hilal-theme/                                                │
│  └── WordPress Theme (Web Portal)                           │
└─────────────────────────────────────────────────────────────┘
          │
┌─────────▼───────────────────────────────────────────────────┐
│                       DATABASE                               │
├─────────────────────────────────────────────────────────────┤
│  wp_posts, wp_postmeta      Custom post type data           │
│  wp_hilal_device_tokens     Push notification tokens        │
│  wp_hilal_subscribers       Email subscribers               │
└─────────────────────────────────────────────────────────────┘
```

### Data Flow

1. **Mobile App** makes REST API calls to WordPress backend
2. **WordPress Plugin** processes requests through API classes
3. **Custom Post Types** store calendar, announcements, sightings
4. **Helpers** calculate prayer times and Hijri dates
5. **Response** returned as JSON to client

---

## Main Features

### 1. Hijri Calendar
- Full Hijri year display (1400-1500 AH)
- Gregorian date mappings
- Confirmed vs estimated month status
- Islamic events marking (Ramadan, Eid, etc.)

### 2. Prayer Times
- 30+ New Zealand mosques
- Multiple calculation methods (MWL, ISNA, Egypt, etc.)
- Iqamah times (mosque-specific)
- Next prayer countdown
- Location-based calculation
- My-Masjid.com integration

### 3. Qibla Direction
- Compass visualization
- Bearing in degrees
- Distance to Kaaba
- Location-based or manual input

### 4. Moon Sighting Reports
- Public submission form
- PDF attachment support
- Admin approval workflow
- Rate limiting (5/day per IP)

### 5. Announcements
- Bilingual content (EN/AR)
- Priority levels (high/medium/low)
- Types: month_start, moon_sighting, islamic_event, general
- Push notifications

### 6. Mobile App Features
- Dark/Light theme
- English/Arabic language
- RTL support
- Offline capability
- Push notifications

---

## Components

### WordPress Plugin (`hilal-plugin/`)

```
hilal-plugin/
├── hilal-plugin.php          # Main plugin file
├── includes/
│   ├── api/
│   │   ├── class-api-base.php
│   │   ├── class-calendar-api.php
│   │   ├── class-announcements-api.php
│   │   ├── class-prayer-times-api.php
│   │   ├── class-qibla-api.php
│   │   └── class-sighting-api.php
│   ├── post-types/
│   │   ├── class-hijri-month.php
│   │   ├── class-announcement.php
│   │   ├── class-sighting-report.php
│   │   └── class-islamic-event.php
│   ├── helpers/
│   │   ├── class-hijri-date.php
│   │   └── class-prayer-calculator.php
│   ├── admin/
│   │   ├── class-admin-dashboard.php
│   │   └── class-admin-columns.php
│   └── notifications/
│       ├── class-push-notifications.php
│       └── class-email-notifications.php
├── acf-json/                  # ACF field definitions
└── languages/                 # Translation files
```

### WordPress Theme (`hilal-theme/`)

```
hilal-theme/
├── style.css
├── functions.php
├── header.php
├── footer.php
├── front-page.php
├── page-calendar.php
├── page-prayer-times.php
├── page-qibla.php
├── page-sighting-report.php
└── page-announcements.php
```

### Mobile App (`hilal-mobile/`)

```
hilal-mobile/
├── App.tsx                    # Main entry point
├── app.json                   # Expo configuration
├── src/
│   ├── api/
│   │   └── client.ts          # API client
│   ├── context/
│   │   └── AppContext.tsx     # Global state
│   ├── screens/
│   │   ├── HomeScreen.tsx
│   │   ├── CalendarScreen.tsx
│   │   ├── SightingsScreen.tsx
│   │   ├── AnnouncementsScreen.tsx
│   │   ├── AnnouncementDetailScreen.tsx
│   │   ├── PrayerTimesScreen.tsx
│   │   ├── QiblaScreen.tsx
│   │   └── SettingsScreen.tsx
│   ├── data/
│   │   └── nzMosques.ts       # NZ mosque data
│   ├── services/
│   │   └── notifications.ts
│   └── utils/
│       └── theme.ts           # Colors & styling
└── assets/                    # Images & icons
```

---

## API Reference

Base URL: `http://your-domain.com/?rest_route=/hilal/v1`

### Calendar Endpoints

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/today` | GET | Today's Hijri date, upcoming events |
| `/hijri-calendar` | GET | Current year calendar |
| `/hijri-calendar/{year}` | GET | Specific year (1400-1500) |
| `/islamic-events` | GET | All Islamic events |
| `/upcoming-events?limit=5` | GET | Next N events |

### Prayer Times Endpoints

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/prayer-times?lat=X&lng=Y` | GET | Prayer times by coordinates |
| `/prayer-times?city=auckland` | GET | Prayer times by city |
| `/prayer-times/city/{city}` | GET | City-specific times |
| `/prayer-times/cities` | GET | List all NZ cities |
| `/prayer-times/methods` | GET | Calculation methods |
| `/prayer-times/mosques` | GET | All NZ mosques |
| `/prayer-times/mosque/{id}` | GET | Mosque times with iqamah |

### Qibla Endpoints

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/qibla?lat=X&lng=Y` | GET | Qibla direction |
| `/qibla/city/{city}` | GET | Qibla for city |

### Announcements Endpoints

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/announcements` | GET | Paginated list |
| `/announcements/{id}` | GET | Single announcement |
| `/announcements/latest` | GET | Most recent |

### Sighting Endpoints

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/sighting-report` | POST | Submit report |
| `/sighting/upload-attachment` | POST | Upload PDF |
| `/sightings/approved` | GET | Approved sightings |

### Authentication

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/jwt-auth/v1/token` | POST | Get JWT token |
| `/subscribe` | POST | Email subscription |

---

## Development Setup

### Prerequisites

- Node.js 18+
- Docker Desktop
- Xcode (for iOS simulator)
- Android Studio (for Android emulator)

### Step 1: Clone and Install

```bash
# Navigate to project
cd "/Users/mohamedhamdi/Work/Hilal Sighting Apps"

# Install root dependencies
npm install

# Install mobile app dependencies
cd hilal-mobile && npm install && cd ..
```

### Step 2: Start WordPress (wp-env)

```bash
# Start WordPress with Docker
npx wp-env start

# WordPress will be available at:
# - Site: http://localhost:8888
# - Admin: http://localhost:8888/wp-admin
# - Credentials: admin / password
```

### Step 3: Activate Theme

```bash
npx wp-env run cli wp theme activate hilal-theme
```

### Step 4: Configure Mobile App

Update `hilal-mobile/app.json`:

```json
{
  "expo": {
    "extra": {
      "wpBaseUrl": "http://YOUR_LOCAL_IP:8888",
      "useRestRoute": true
    }
  }
}
```

Get your local IP:
```bash
ipconfig getifaddr en0
```

### Step 5: Start Mobile App

```bash
cd hilal-mobile
npx expo start

# Press 'i' for iOS simulator
# Press 'a' for Android emulator
# Scan QR code for physical device
```

### Development URLs

| Service | URL |
|---------|-----|
| WordPress Site | http://localhost:8888 |
| WordPress Admin | http://localhost:8888/wp-admin |
| REST API | http://localhost:8888/?rest_route=/hilal/v1/ |
| Expo Dev Server | http://localhost:8081 |

### Useful Commands

```bash
# Stop WordPress
npx wp-env stop

# Restart WordPress
npx wp-env start

# View WordPress logs
npx wp-env logs

# Run WP-CLI commands
npx wp-env run cli wp plugin list
npx wp-env run cli wp theme list

# Reset WordPress (fresh install)
npx wp-env destroy
npx wp-env start
```

---

## Production Deployment

### WordPress Deployment

#### Option 1: Traditional Hosting (cPanel, Plesk)

1. **Upload WordPress**
   - Download WordPress from wordpress.org
   - Upload to your hosting via FTP/File Manager
   - Create MySQL database

2. **Install WordPress**
   - Navigate to your domain
   - Complete WordPress installation wizard

3. **Upload Plugin & Theme**
   ```
   wp-content/
   ├── plugins/
   │   └── hilal-plugin/    # Upload entire folder
   └── themes/
       └── hilal-theme/     # Upload entire folder
   ```

4. **Install Required Plugins**
   - Advanced Custom Fields
   - JWT Authentication for WP REST API
   - WP Mail SMTP

5. **Configure wp-config.php**
   ```php
   // Add JWT secret key
   define('JWT_AUTH_SECRET_KEY', 'your-secure-secret-key-here');
   define('JWT_AUTH_CORS_ENABLE', true);
   ```

6. **Set Permalinks**
   - Go to Settings > Permalinks
   - Select "Post name" (/%postname%/)
   - Save

7. **Activate Plugin & Theme**
   - Plugins > Activate "Hilal"
   - Appearance > Themes > Activate "Hilal Theme"

#### Option 2: Docker Deployment

```yaml
# docker-compose.yml
version: '3.8'

services:
  wordpress:
    image: wordpress:6.4-php8.2-apache
    ports:
      - "80:80"
    environment:
      WORDPRESS_DB_HOST: db
      WORDPRESS_DB_USER: hilal
      WORDPRESS_DB_PASSWORD: ${DB_PASSWORD}
      WORDPRESS_DB_NAME: hilal_db
    volumes:
      - wordpress_data:/var/www/html
      - ./hilal-plugin:/var/www/html/wp-content/plugins/hilal-plugin
      - ./hilal-theme:/var/www/html/wp-content/themes/hilal-theme
    depends_on:
      - db

  db:
    image: mysql:8.0
    environment:
      MYSQL_DATABASE: hilal_db
      MYSQL_USER: hilal
      MYSQL_PASSWORD: ${DB_PASSWORD}
      MYSQL_ROOT_PASSWORD: ${DB_ROOT_PASSWORD}
    volumes:
      - db_data:/var/lib/mysql

volumes:
  wordpress_data:
  db_data:
```

#### Option 3: Cloud Deployment (AWS, GCP, Azure)

**AWS Example:**
1. Launch EC2 instance (t3.small or larger)
2. Install Docker & Docker Compose
3. Configure security groups (ports 80, 443)
4. Set up RDS MySQL instance
5. Configure Route 53 for domain
6. Set up SSL with Let's Encrypt

### Mobile App Deployment

#### iOS (App Store)

1. **Configure app.json**
   ```json
   {
     "expo": {
       "ios": {
         "bundleIdentifier": "com.hilal.moonsighting",
         "buildNumber": "1"
       },
       "extra": {
         "wpBaseUrl": "https://api.hilal.nz",
         "useRestRoute": false
       }
     }
   }
   ```

2. **Build for iOS**
   ```bash
   # Install EAS CLI
   npm install -g eas-cli

   # Login to Expo
   eas login

   # Configure build
   eas build:configure

   # Build for iOS
   eas build --platform ios
   ```

3. **Submit to App Store**
   ```bash
   eas submit --platform ios
   ```

#### Android (Google Play)

1. **Configure app.json**
   ```json
   {
     "expo": {
       "android": {
         "package": "com.hilal.moonsighting",
         "versionCode": 1
       }
     }
   }
   ```

2. **Build for Android**
   ```bash
   eas build --platform android
   ```

3. **Submit to Google Play**
   ```bash
   eas submit --platform android
   ```

### Production Checklist

#### WordPress
- [ ] Change default admin password
- [ ] Generate secure JWT secret key
- [ ] Configure SMTP for emails
- [ ] Set up SSL certificate
- [ ] Enable caching (WP Super Cache, W3 Total Cache)
- [ ] Configure CDN for assets
- [ ] Set up automated backups
- [ ] Configure security plugin (Wordfence, Sucuri)
- [ ] Disable WP_DEBUG
- [ ] Set proper file permissions

#### Mobile App
- [ ] Update API URL to production
- [ ] Set `useRestRoute: false` for pretty permalinks
- [ ] Configure push notification credentials
- [ ] Update app icons and splash screens
- [ ] Test on multiple devices
- [ ] Set up crash reporting (Sentry)
- [ ] Configure analytics

---

## Configuration

### WordPress Options

| Option | Default | Description |
|--------|---------|-------------|
| `hilal_region` | nz | Region code |
| `hilal_prayer_method` | mwl | Prayer calculation method |
| `hilal_default_language` | en | Default language |
| `hilal_fcm_server_key` | - | Firebase Cloud Messaging key |
| `hilal_email_from_name` | Site Name | Email sender name |
| `hilal_email_from_address` | Admin Email | Email sender address |

### Prayer Calculation Methods

| Method | Full Name |
|--------|-----------|
| mwl | Muslim World League |
| isna | Islamic Society of North America |
| egypt | Egyptian General Authority |
| makkah | Umm Al-Qura University |
| karachi | University of Islamic Sciences, Karachi |
| tehran | Institute of Geophysics, Tehran |
| jafari | Shia Ithna-Ashari |
| singapore | Singapore |

### Environment Variables

```bash
# .env (for Docker deployment)
DB_PASSWORD=secure_password_here
DB_ROOT_PASSWORD=secure_root_password
JWT_SECRET_KEY=your-256-bit-secret-key
FCM_SERVER_KEY=your-firebase-key
```

---

## Support

For issues and feature requests, please contact the development team.

**Version:** 1.0.0
**Last Updated:** February 2026
