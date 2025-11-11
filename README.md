# Portfolio Website

Website portfolio pribadi.

## Requirements

- PHP 7.4+
- MySQL/MariaDB
- Sendmail (untuk fitur contact form)

## Installation

1. Clone repository ini
```bash
git clone <repository-url>
cd <project-folder>
```

2. Buat database MySQL dan import struktur
```sql
CREATE DATABASE your_database;
USE your_database;

-- Tabel admin users
CREATE TABLE admin_users (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel posts
CREATE TABLE posts (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    author VARCHAR(100) NOT NULL,
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel projects
CREATE TABLE projects (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    url VARCHAR(255),
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel chat users
CREATE TABLE chat_users (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    last_seen TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX(last_seen)
);

-- Tabel chat messages
CREATE TABLE chat_messages (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX(created_at)
);

-- Insert default admin
INSERT INTO admin_users (username, password) 
VALUES ('admin', 'hashed_password');
```

3. Buat file `.env` dan sesuaikan konfigurasi:

4. Edit file `.env` dan sesuaikan konfigurasi:
```env
DB_HOST=127.0.0.1
DB_USERNAME=your_database_username
DB_PASSWORD=your_password
DB_DATABASE=your_database
DB_PORT=3306

CONTACT_EMAIL=your_email@gmail.com
SENDMAIL_PATH=/usr/bin/sendmail -t -i

ADMIN_TEST_USERNAME=your_username
ADMIN_TEST_PASSWORD_1=your_password
ADMIN_TEST_PASSWORD_2=your_password
```

## Database Structure

### Tables Overview
- **admin_users** - Admin authentication
- **posts** - Blog posts/articles
- **projects** - Portfolio projects
- **chat_users** - Chat system users
- **chat_messages** - Chat messages

### Detailed Schema

**admin_users**
- `id` - Primary key
- `username` - Unique username
- `password` - Hashed password
- `created_at` - Registration timestamp

**posts**
- `id` - Primary key
- `title` - Post title
- `content` - Post content
- `author` - Author name
- `image` - Image path (optional)
- `created_at` - Creation timestamp
- `updated_at` - Last update timestamp

**projects**
- `id` - Primary key
- `title` - Project title
- `description` - Project description
- `url` - Project URL (optional)
- `image` - Image path (optional)
- `created_at` - Creation timestamp

**chat_users**
- `id` - Primary key
- `username` - Unique username
- `last_seen` - Last activity timestamp

**chat_messages**
- `id` - Primary key
- `username` - Message sender
- `message` - Message content
- `created_at` - Message timestamp


## Security Notes

**PENTING**: File `.env` berisi informasi sensitif. Pastikan:
- Tidak di-commit ke Git
- Ganti semua password default

## License

This project is licensed under the GNU General Public License v3.0 - see the [LICENSE](LICENSE) file for details.

### GPL 3.0 Summary

- Free to use, modify, and distribute
- Source code must be made available
- Modifications must also be GPL 3.0
- Must include copyright and license notices