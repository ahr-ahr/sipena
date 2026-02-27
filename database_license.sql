CREATE TABLE clients (
  id INT AUTO_INCREMENT PRIMARY KEY,
  client_code VARCHAR(50) UNIQUE NOT NULL,
  school_name VARCHAR(150) NOT NULL,
  email VARCHAR(100),
  phone VARCHAR(30),
  address TEXT,
  status ENUM('active','inactive') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE licenses (
  id INT AUTO_INCREMENT PRIMARY KEY,
  client_id INT NOT NULL,
  license_key VARCHAR(100) UNIQUE NOT NULL,
  license_type ENUM('trial','annual','lifetime') NOT NULL,
  max_users INT DEFAULT 0,
  expires_at DATE NULL,
  status ENUM('active','expired','revoked') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE RESTRICT ON UPDATE CASCADE
);

CREATE TABLE license_activations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  license_id INT NOT NULL,
  server_fingerprint VARCHAR(255) NOT NULL,
  activated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  last_check_at TIMESTAMP NULL,
  status ENUM('active','disabled') DEFAULT 'active',
  FOREIGN KEY (license_id) REFERENCES licenses(id)
);

CREATE TABLE license_activation_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  license_activation_id INT,
  action VARCHAR(50),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (license_activation_id) REFERENCES license_activations(id) ON DELETE CASCADE
);

-- CLIENTS
CREATE UNIQUE INDEX idx_clients_client_code 
ON clients (client_code);

CREATE INDEX idx_clients_status 
ON clients (status);

-- LICENSES
CREATE UNIQUE INDEX idx_licenses_license_key
ON licenses (license_key);

CREATE INDEX idx_licenses_client_id
ON licenses (client_id);

CREATE INDEX idx_licenses_status
ON licenses (status);

CREATE INDEX idx_licenses_expires_at
ON licenses (expires_at);

CREATE INDEX idx_licenses_client_status
ON licenses (client_id, status);

-- LICENSE ACTIVATIONS
CREATE INDEX idx_activations_license_id
ON license_activations (license_id);

CREATE INDEX idx_activations_fingerprint
ON license_activations (server_fingerprint);

CREATE INDEX idx_activations_lookup
ON license_activations (license_id, server_fingerprint, status);

CREATE UNIQUE INDEX uniq_activation_per_server
ON license_activations (license_id, server_fingerprint);

CREATE INDEX idx_activations_last_check
ON license_activations (last_check_at);

-- LICENSE ACTIVATION LOGS
CREATE INDEX idx_activation_logs_activation_id
ON license_activation_logs (license_activation_id);