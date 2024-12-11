# Bug Tracker Project - Implementation Details

## Overview
This document outlines the key architectural enhancements, and deviations from the initial design diagrams and stubbed code implementation. The final solution includes several additional features and architectural improvements that went beyond the original requirements to create a more robust and secure bug tracking system.

## Major Enhancements

### 1. Security Enhancements

#### Session Management
- Added comprehensive session timeout handling
- Implemented session refresh functionality with AJAX
- Added visual countdown banner for session expiration
- Enhanced session security with proper initialization and destruction

#### Authentication & Authorization
- Added password complexity requirements and validation

### 2. Data Management

#### Export Functionality
- Added data export capabilities (CSV format)
- Implemented ZIP archive creation for exported files
- Role-based export permissions
- Support for selective data export (Users, Projects, Bugs)

#### User Management
- Protected against deleting last admin/manager

### 3. UI/UX Improvements

#### Modal System
- Implemented dynamic modal forms
- Added real-time form validation
- Enhanced password visibility toggle
- Added confirmation dialogs for critical actions

### 4. Code Architecture

#### Database Layer
- Implemented singleton pattern for database connections
- Enhanced error handling and logging
- Added object mapping capabilities

#### Model Layer
- Seperated some functions like save() into multiple function for data sanitization, updating esxisting data, and inserting new data

#### Controller Layer
- Added AJAX support for asynchronous operations
- Enhanced error handling and feedback

### 5. Additional Features Beyond Requirements

1. **User Experience**
   - Password complexity checker
   - Real-time form validation
   - Session timeout notifications
   - Confirmation dialogs
   - Event Listeners to change <select> depending on what was changed (For Example: Changing the project for a bug by an admin or manager will change the bug status to unassigned, user to unaasigned, and replaces the names in the drop down with the names of the users in that project)

2. **Data Management**
   - Data Export Feature

## Deviations from Original Design

### Architectural Changes
1. **Session Management**
   - Added more robust session handling than initially planned
   - Implemented session refresh mechanism
   - Added timeout visualization

2. **Routing System**
   - Enhanced routing with better error handling
   - Added support for AJAX routes
   - Implemented more granular access control

3. **Controller Structure**
   - Added more specialized controller methods
   - Implemented better separation of concerns
   - Enhanced error handling and feedback

## Conclusion
The final implementation significantly expanded upon the initial design while maintaining its core MVC architecture. The additions focused on security, usability, and data integrity, resulting in a more robust and user-friendly bug tracking system. These enhancements make the system more suitable for production use while maintaining good coding practices and security standards.

## Submission Error Fix Additions
I had issues with the routing urls so with the following changes I got it working.

### Environment Configuration (config/config.php)
The application uses environment-specific configuration to handle different paths and settings between local development and production

### Apache Configuration (.htaccess)
The application uses Apache's mod_rewrite to handle clean URLs and routing:

### Router Changes (Router.php)
The Router class was enhanced to handle both local and production environments:

1. Base Path Handling:
   - Removes the base path from URLs before routing
   - Handles both local and production paths correctly
   - Removes any 'public/' references from the URL

2. URL Normalization:
   - Removes leading and trailing slashes
   - Normalizes empty URLs to root path ('/')
   - Preserves query strings with QSA flag

3. Error Handling:
   - Better exception handling for 404s
   - Improved error messages
   - Logging of routing errors
