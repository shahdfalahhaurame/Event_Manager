# Event Manager

A simple event management web application built with PHP and MySQL. Users can register, create events, manage event needs, and track the status of events.

## 📁 Project Structure

- `index.php` – Main entry point.
- `includes/db.php` – Database connection.
- `auth/` – User registration and login.
- `events/` – Event creation and management.
- `needs/` – Manage event-related needs.

## ⚙️ Features

- User registration and login
- Create and manage events
- Track event needs and fulfillment status
- Event state transitions (draft → active → full/completed)

## 🛠️ Setup Instructions

### 1. Clone the repository

```bash
git clone https://github.com/shahdfalahhaurame/Event_Manager.git
cd Event_Manager



CREATE DATABASE eventmanager;

USE eventmanager;

-- Users table
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100),
  email VARCHAR(100) UNIQUE,
  password_hash VARCHAR(255),
  role VARCHAR(20),
  created_at DATETIME
);

-- Events table
CREATE TABLE events (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255),
  description TEXT,
  date DATE,
  time TIME,
  location VARCHAR(255),
  state ENUM('draft', 'active', 'full', 'cancelled', 'completed') DEFAULT 'draft',
  created_by INT,
  created_at DATETIME,
  updated_at DATETIME,
  FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Needs table
CREATE TABLE needs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  event_id INT,
  title VARCHAR(100),
  description TEXT,
  priority ENUM('Low', 'Medium', 'High') DEFAULT 'Medium',
  is_fulfilled BOOLEAN DEFAULT FALSE,
  created_at DATETIME,
  FOREIGN KEY (event_id) REFERENCES events(id)
);
