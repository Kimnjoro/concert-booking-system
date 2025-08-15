A full-featured event booking system using PHP and MySQL with role-based access for Admins and Customers.

Core Functionalities
User Management: Secure registration and login for different user roles.

Event Handling: Admins can create, update, and delete concert events.

Booking & Payments: Users can book available concert tickets, which are initially "Pending". They can then "Pay" to confirm the booking and generate a QR code.

Ticket Management: Users can cancel unpaid bookings or request refunds for paid ones, which correctly adjusts the available ticket count.

User Roles & Features
Admin
Dashboard: Centralized view to manage all events.

CRUD Operations: Full control to Create, Read, Update, and Delete concert listings.

Usage Tracking: View total tickets, tickets sold, and remaining availability for each event.

Customer (User)
Dashboard: View available concerts and manage personal bookings.

Booking: Book tickets for any concert with available seats.

Payment & QR Code: Finalize a booking by making a mock payment, which generates a unique QR code for entry.

Cancellation/Refund: Remove pending bookings or initiate a refund process for paid tickets.

Quick Setup Guide
Database Setup:

Create a MySQL database named concert_db.

Execute the provided SQL script to create the users, events, and bookings tables.

Configuration:

Verify the database credentials in db.php match your local environment.

Run:

Place all project files in your web server's root directory (e.g., htdocs or www).

Navigate to the project folder in your browser (e.g., http://localhost/your-project-folder/).

File Structure
File	Description
index.php	Main landing page displaying upcoming concerts.
db.php	Database connection and session start.
register.php / login.php	Handles user registration and authentication.
admin_dashboard.php	Admin interface for managing events.
user_dashboard.php	User interface for booking tickets and viewing bookings.
edit_event.php	Form to update an existing event's details.
logout.php	Terminates the user session.
refund.php	Confirmation page for refund requests.
