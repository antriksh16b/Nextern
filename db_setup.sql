CREATE DATABASE IF NOT EXISTS nextern_php;
USE nextern_php;

CREATE TABLE IF NOT EXISTS bookings (
  id VARCHAR(100) PRIMARY KEY,
  full_name VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL,
  phone VARCHAR(50) DEFAULT '',
  business_name VARCHAR(255) DEFAULT '',
  meeting_at VARCHAR(50) NOT NULL,
  meeting_type VARCHAR(100) DEFAULT 'Strategy Call',
  notes TEXT,
  created_at VARCHAR(50) NOT NULL,
  UNIQUE KEY uq_bookings_meeting_at (meeting_at),
  KEY idx_bookings_meeting_at (meeting_at),
  KEY idx_bookings_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS app_settings (
  setting_key VARCHAR(100) PRIMARY KEY,
  setting_value TEXT NOT NULL,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
