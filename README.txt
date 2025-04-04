Queue Management System 
Project Overview
The Queue Management System is a web-based application designed to efficiently manage customer queues in various service environments such as banks, hospitals, offices, and customer service centers. The system allows customers to join a queue and enables administrators to call the next customer in line while keeping track of their details.

Problem Statement
Many businesses struggle with managing long queues effectively, leading to:

Customer frustration due to unclear waiting times

Service providers losing track of who should be attended to next

Lack of notifications for customers when it's their turn

Manual queue management causing delays and inefficiency

To address these issues, we developed this Queue Management System, which provides an automated, structured, and real-time queue management solution.

Features of the System
✅ Customers Can Join the Queue

Customers input their name and phone number through the index.html page.

Their details are stored in the database and displayed in the admin panel.

✅ Admin Panel to View and Call Customers

The admin sees a real-time queue list of customers.

They can call the next customer and remove them from the queue.

Customers receive notifications when it’s their turn.

✅ Database-Driven Queue Management

Customers are stored in a MySQL database.

The queue auto-refreshes every 5 seconds to show the latest data.

Technologies Used
🔹 Frontend: HTML, CSS, JavaScript
🔹 Backend: PHP
🔹 Database: MySQL
🔹 Server Requests: Fetch API for AJAX calls

How the System Works
1️⃣ Customer Registration (index.html)
Customers fill out a form with:

Name

Phone Number

Once submitted, their details are stored in the database using join_queue.php, and a success message is displayed.

2️⃣ Admin Dashboard (admin.html)
The admin page:

Displays the queue list retrieved from get_queue.php.

Allows the admin to call the next customer using next_customer.php.

Automatically refreshes every 5 seconds to update the queue.

3️⃣ Customer Notification
When a customer is called:

Their name appears in an alert message.

Their phone number is displayed as a notification on the admin page.

4️⃣ Real-time Queue Management
The queue auto-updates using JavaScript's fetch() method.

Called customers are removed from the database, ensuring no duplicates.

Installation & Setup
Step 1: Set Up the Database
Open phpMyAdmin (or MySQL CLI).

Create a database:

sql
Copy
Edit
CREATE DATABASE queue_system;
Select the database and create the queue table:

sql
Copy
Edit
CREATE TABLE queue (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
Step 2: Configure the Backend
Place all PHP files (db_connect.php, join_queue.php, get_queue.php, next_customer.php) in the php folder.

Ensure db_connect.php contains the correct database credentials:

php
Copy
Edit
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "queue_system";
Start XAMPP or WAMP, then run Apache and MySQL.

Step 3: Run the System
Open a web browser.

Go to http://localhost/Queue%20Management%20system/index.html to join the queue.

Go to http://localhost/Queue%20Management%20system/admin.html to manage the queue.

Troubleshooting & Common Errors
Issue	Cause	Solution
Queue is not loading in the admin panel	get_queue.php is not fetching data	Check database connection in db_connect.php
Clicking "Call Next" does nothing	next_customer.php might not be deleting the called customer	Check if the DELETE SQL query is executed successfully
No success message after joining queue	JavaScript fetch() request failed	Open browser console (F12 > Console) for errors
Phone number is not displayed when calling	notifyCustomer() function not executing	Ensure callNextCustomer() properly calls notifyCustomer()
Future Improvements
🚀 Add SMS notifications so customers receive alerts on their phones.
🚀 Implement QR codes for customers to scan and join the queue.
🚀 Add Admin Login Authentication for security.

Contributors
💡 Developed by Beth Njuguna
📧 Contact: graciousglorybetty@gmail.com
