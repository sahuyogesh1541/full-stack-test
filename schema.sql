CREATE TABLE IF NOT EXISTS tabs (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    title      VARCHAR(255) NOT NULL,
    icon       VARCHAR(500) NOT NULL DEFAULT 'assets/images/DL-learning.svg',
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS slides (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    tab_id     INT NOT NULL,
    eyebrow    VARCHAR(255) NOT NULL DEFAULT '',
    title      VARCHAR(500) NOT NULL,
    image      VARCHAR(500) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tab_id) REFERENCES tabs(id) ON DELETE CASCADE
);

-- Seed data matching the original WPoets design
INSERT INTO tabs (title, icon, sort_order) VALUES
  ('Learning',      'assets/images/DL-learning.svg',      1),
  ('Technology',    'assets/images/DL-technology.svg',    2),
  ('Communication', 'assets/images/DL-communication.svg', 3);

INSERT INTO slides (tab_id, eyebrow, title, image, sort_order) VALUES
  (1, 'Digital Learning Infrastructure', 'Usability enhancement and Training for Transaction Portal for Customers',      'assets/images/DL-Learning-1.jpg', 1),
  (1, 'Digital Learning Infrastructure', 'Improving Engagement Through Interactive Learning Modules',                    'assets/images/DL-Learning-1.jpg', 2),
  (1, 'Digital Learning Infrastructure', 'Scalable E-Learning Platform for Enterprise Teams',                           'assets/images/DL-Learning-1.jpg', 3),
  (2, 'Technology Solutions',            'Cloud Infrastructure Modernisation for Legacy Systems',                        'assets/images/DL-Technology.jpg',  1),
  (2, 'Technology Solutions',            'Secure API Integration Across Distributed Environments',                      'assets/images/DL-Technology.jpg',  2),
  (2, 'Technology Solutions',            'Automated DevOps Pipeline Reducing Deployment Time by 70%',                   'assets/images/DL-Technology.jpg',  3),
  (3, 'Communication Strategy',          'Unified Messaging Platform for Multi-Channel Customer Engagement',            'assets/images/DL-Communication.jpg', 1),
  (3, 'Communication Strategy',          'Real-Time Collaboration Tools for Distributed Teams',                         'assets/images/DL-Communication.jpg', 2),
  (3, 'Communication Strategy',          'Brand Voice Consistency Across All Customer Touchpoints',                     'assets/images/DL-Communication.jpg', 3);
