# NIBM Events - Campus Event Management System

A comprehensive event management system for NIBM Campus to share events across 8 branches.

## Features

### User Features
- Browse events across all 8 NIBM branches
- Like and attend events
- Purchase digital tickets with QR codes
- View personal ticket collection
- Responsive design for all devices

### Admin Features
- Full CRUD operations for events
- User management
- Event statistics and analytics
- Branch-specific event management

## Technology Stack

- **Frontend**: HTML5, CSS3, JavaScript, Tailwind CSS
- **Backend**: PHP
- **Database**: MySQL
- **Libraries**: 
  - Font Awesome 6 (Icons)
  - QRCode.js (QR Code generation)
  - Inter & Poppins fonts

## Installation

1. **Database Setup**
   \`\`\`sql
   CREATE DATABASE nibm_events;
   USE nibm_events;
   SOURCE database/nibm_events.sql;
   \`\`\`

2. **Web Server Setup**
   - Place all files in your web server directory (htdocs for XAMPP)
   - Ensure PHP and MySQL are running

3. **Configuration**
   - Update database credentials in `backend/db.php` if needed
   - Default credentials: localhost, root, no password

## File Structure

\`\`\`
nibm-events/
├── index.html              # Landing page
├── login.html              # Login page
├── register.html           # Registration page
├── user-dashboard.php      # User dashboard
├── admin-dashboard.php     # Admin dashboard
├── backend/
│   ├── db.php             # Database connection
│   ├── auth.php           # Authentication handler
│   ├── events.php         # Event CRUD operations
│   ├── user-actions.php   # User interactions
│   └── admin-actions.php  # Admin operations
├── assets/
│   ├── css/
│   │   └── style.css      # Custom styles
│   └── js/
│       ├── main.js        # Main JavaScript
│       ├── auth.js        # Authentication JS
│       ├── user-dashboard.js
│       └── admin-dashboard.js
└── database/
    └── nibm_events.sql    # Database schema
\`\`\`

## Default Login Credentials

**Admin Account:**
- Email: admin@nibm.lk
- Password: password

## NIBM Branches

The system supports events across 8 branches:
1. Colombo
2. Kandy
3. Galle
4. Matara
5. Kurunegala
6. Ratnapura
7. Kalutara
8. Badulla

## Design System

The application follows a comprehensive design system with:
- Purple-blue gradient color scheme
- Inter and Poppins typography
- Consistent spacing and shadows
- Responsive breakpoints
- Accessibility features

## Key Features

### QR Code Generation
- Automatic QR code generation for tickets
- Unique ticket codes for each purchase
- Mobile-friendly QR display

### Responsive Design
- Mobile-first approach
- Tablet and desktop optimized
- Touch-friendly interfaces

### Security
- Password hashing with PHP's password_hash()
- SQL injection prevention
- Session management
- CSRF protection ready

## Browser Support

- Chrome 60+
- Firefox 60+
- Safari 12+
- Edge 79+

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## License

This project is licensed under the MIT License.

## Support

For support, please contact the NIBM IT department or create an issue in the repository.
