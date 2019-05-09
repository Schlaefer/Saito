# Create test database
CREATE DATABASE IF NOT EXISTS `saito_test`;
GRANT ALL PRIVILEGES ON saito_test.* TO 'saito'@'%';
