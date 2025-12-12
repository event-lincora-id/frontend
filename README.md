# Event Connect - Frontend Application

<<<<<<< HEAD
> **⚠️ This is a frontend-only Laravel application** that renders user interfaces and connects to a separate backend API.

A comprehensive event management system frontend built with Laravel 12.33.0. This application provides the user interface for event browsing, participation, and management, while all business logic and data operations are handled by the backend API.
=======
A comprehensive event management system frontend built with Laravel 12.33.0 and Tailwind CSS. This application connects to an external Event Connect API backend for all data operations.

## 🎯 Purpose

This is a **frontend/client application** that provides a user interface for:
- Event discovery and browsing
- Event participation management
- Admin dashboard for event organizers
- QR-based attendance tracking
- User profile and notification management

All data is fetched from and synchronized with the Event Connect API backend.
>>>>>>> 6ab51db305b4ba42a975571239ef7da3f44d1787

## 🚀 Features

### User Features
- User registration, login, and profile management
- Event search, filtering, and discovery
- Event participation (join/cancel)
- Feedback submission and ratings
- Notification viewing
- Certificate download

### Organizer/Admin Features
- Create and manage events
- View event participants
- QR code generation for attendance
- Event statistics and analytics
- Participant attendance tracking
- Event editing and publishing

### General
- Responsive design with Tailwind CSS
- Event categories and filtering
- User authentication via token
- Payment gateway integration (Xendit)

## 🛠️ Technology Stack

<<<<<<< HEAD
- **Framework:** Laravel 12.33.0 (Frontend)
- **Views:** Blade Templates
- **Styling:** Tailwind CSS
- **HTTP Client:** Laravel HTTP Client (via BackendApiService)
- **Session Storage:** File-based (no database required)
- **Backend API:** Separate Laravel API (required)
=======
- **Frontend Framework:** Laravel 12.33.0 with Blade Templates
- **Styling:** Tailwind CSS
- **JavaScript:** ES6+ with Vite bundling
- **HTTP Client:** GuzzleHTTP (for API requests)
- **Backend API:** External Event Connect API (staging)
- **Database:** None (frontend only; all data via API)
>>>>>>> 6ab51db305b4ba42a975571239ef7da3f44d1787

## 📋 Requirements

- PHP 8.3+
- Composer
<<<<<<< HEAD
- Backend API server (running separately)
- File write permissions (for sessions/cache)
=======
- Node.js & npm (for frontend assets)
- Modern web browser
>>>>>>> 6ab51db305b4ba42a975571239ef7da3f44d1787

## 🚀 Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd event-connect
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install frontend dependencies**
   ```bash
   npm install
   ```

4. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

<<<<<<< HEAD
4. **Configure Backend API**
   Update `.env` file with your backend API URL:
   ```env
   APP_URL=http://localhost:8000
   API_BASE_URL=https://staging-eventconnect.sre-telkomuniversity-pwt.org/api
   VITE_API_BASE_URL=https://staging-eventconnect.sre-telkomuniversity-pwt.org/api

   # Sessions & Cache (file-based, no database needed)
   SESSION_DRIVER=file
   CACHE_STORE=file
   QUEUE_CONNECTION=sync
   ```

5. **Clear configuration cache**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```

6. **Start the frontend server**
=======
5. **Configure API endpoint**
   Update `.env` with your backend API URL:
   ```env
   API_BASE_URL=https://staging-api.eventconnect.com
   API_TIMEOUT=30
   ```

6. **Build frontend assets**
   ```bash
   npm run build
   ```

7. **Start the development server**
>>>>>>> 6ab51db305b4ba42a975571239ef7da3f44d1787
   ```bash
   php artisan serve --port=8000
   ```

## 🔗 API Integration

This frontend communicates with the Event Connect API backend via `BackendApiService`. The service handles:
- Authentication token management
- API request/response formatting
- Error handling and logging

Key API endpoints used:
- `/auth/login` - User authentication
- `/events` - Event listing and details
- `/events/my-events` - Organizer's events
- `/participants` - Event participation
- `/attendance` - QR-based attendance tracking
- `/users/profile` - User profile management

## 🧪 Development

### Running the development server
```bash
php artisan serve
```

### Building assets in watch mode
```bash
npm run dev
```

### Build for production
```bash
npm run build
```

## 📁 Project Structure

```
resources/
├── views/          # Blade templates
│   ├── admin/      # Admin dashboard views
│   ├── user/       # User-facing views
│   └── layouts/    # Layout templates
├── css/            # Tailwind CSS
├── js/             # JavaScript files
app/
├── Http/
│   ├── Controllers/    # Route controllers
│   └── Middleware/     # HTTP middleware
├── Services/
│   └── BackendApiService.php  # API client
└── Models/         # Data models (used locally)
config/
├── services.php    # API configuration
└── ...
```

## 🔐 Authentication

Users authenticate via the Event Connect API:
1. Submit login credentials
2. Receive API token from backend
3. Token stored in session
4. Included in all subsequent API requests

## 🐛 Troubleshooting

### API connection issues
- Verify `API_BASE_URL` in `.env`
- Check network connectivity to backend
- Review Laravel logs: `storage/logs/laravel.log`

### Asset compilation errors
```bash
npm install
npm run build
```

### Session/token issues
- Clear cache: `php artisan cache:clear`
- Clear sessions: `php artisan session:flush`

## 📝 License

[Specify your license here]

## 👥 Contributors

[List contributors here]

## 🔗 API Endpoints

### Base URL
```
http://127.0.0.1:8003/api
```

### Authentication
- `POST /auth/register` - Register new user
- `POST /auth/login` - User login
- `POST /auth/logout` - User logout
- `GET /auth/me` - Get current user

### Profile Management
- `GET /profile` - Get user profile
- `PUT /profile` - Update profile
- `POST /profile/change-password` - Change password
- `POST /profile/update-organizer-status` - Update organizer status

### Events
- `GET /events` - List all events (homepage)
- `GET /events/{id}` - Get event details
- `POST /events` - Create event (organizer only)
- `PUT /events/{id}` - Update event (organizer only)
- `DELETE /events/{id}` - Delete event (organizer only)
- `GET /events/my-events` - Get user's created events
- `GET /events/participating` - Get user's participating events

### Event Participation
- `POST /participants/join/{event_id}` - Join event
- `POST /participants/cancel/{event_id}` - Cancel participation
- `POST /participants/attendance` - Mark attendance via QR
- `GET /participants/my-participations` - Get user's participations

### Feedback & Certificates
- `POST /feedbacks/{event_id}` - Submit feedback
- `GET /feedbacks/my-feedbacks` - Get user's feedbacks
- `GET /feedbacks/certificate/{event_id}/download` - Download certificate
- `GET /feedbacks/certificate/{event_id}/url` - Get certificate URL

### Notifications
- `GET /notifications` - Get user notifications
- `POST /notifications/{id}/read` - Mark notification as read
- `POST /notifications/mark-all-read` - Mark all as read
- `GET /notifications/unread-count` - Get unread count

### Categories
- `GET /categories` - Get all categories
- `POST /categories` - Create category (admin only)
- `PUT /categories/{id}` - Update category (admin only)
- `DELETE /categories/{id}` - Delete category (admin only)

## 🔐 Authentication

The API uses Laravel Sanctum for authentication. Include the Bearer token in the Authorization header:

```bash
Authorization: Bearer {your-token-here}
```

## 📊 Database Schema

### Tables
- `users` - User accounts and profiles
- `categories` - Event categories
- `events` - Event information
- `event_participants` - User-event relationships
- `feedbacks` - Event feedback and certificates
- `notifications` - System notifications
- `personal_access_tokens` - API authentication tokens

## 🧪 Testing

Test the API using curl or any HTTP client:

```bash
# Register a new user
curl -X POST "http://127.0.0.1:8003/api/auth/register" \
  -H "Content-Type: application/json" \
  -d '{
    "full_name": "Test User",
    "email": "test@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'

# Login
curl -X POST "http://127.0.0.1:8003/api/auth/login" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "password123"
  }'

# Get categories
curl -X GET "http://127.0.0.1:8003/api/categories"
```

## 📝 Sample Data

The seeder creates sample categories:
- Technology
- Business
- Education
- Health & Wellness
- Arts & Culture
- Sports

## 🔧 Configuration

### Environment Variables
```env
APP_NAME="Event Connect"
APP_ENV=local
APP_KEY=base64:your-app-key
APP_DEBUG=true
APP_URL=http://127.0.0.1:8003

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=8889
DB_DATABASE=event_connect
DB_USERNAME=root
DB_PASSWORD=root

SANCTUM_STATEFUL_DOMAINS=127.0.0.1:8003
```

## 📱 Features Overview

### For Regular Users
- Browse and search events
- Join events (free and paid)
- Mark attendance via QR code
- Submit feedback and get certificates
- Manage profile and notifications

### For Event Organizers
- Create and manage events
- Generate QR codes for attendance
- View participant lists
- Process payments
- Manage event categories

### For Administrators
- Manage event categories
- Monitor system activity
- User management

## 🚀 Deployment

1. Set up production environment
2. Configure database and environment variables
3. Run migrations and seeders
4. Set up web server (Apache/Nginx)
5. Configure SSL certificates
6. Set up monitoring and logging

## 📄 License

This project is licensed under the MIT License.

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## 📞 Support

For support and questions, please contact the development team or create an issue in the repository.

---

**Event Connect API** - Built with ❤️ using Laravel# event-connect
