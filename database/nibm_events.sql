-- Table for Admins
CREATE TABLE Admins (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

-- Table for Users
CREATE TABLE Users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

-- Table for Events
CREATE TABLE Events (
    event_id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    img_url VARCHAR(255),
    price DECIMAL(10,2),
    branch VARCHAR(100),
    created_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES Admins(admin_id)
        ON DELETE CASCADE ON UPDATE CASCADE
);

-- Table for Event Likes
CREATE TABLE Event_Likes (
    event_like_id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    user_id INT NOT NULL,
    liked_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES Events(event_id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (user_id) REFERENCES Users(user_id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE KEY unique_like (event_id, user_id)
);

-- Table for Event Attendance
CREATE TABLE Event_Attendance (
    attendance_id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    user_id INT NOT NULL,
    attend_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES Events(event_id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (user_id) REFERENCES Users(user_id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE KEY unique_attendance (event_id, user_id)
);

-- Table for Tickets
CREATE TABLE Tickets (
    ticket_id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    user_id INT NOT NULL,
    ticket_code VARCHAR(100) NOT NULL UNIQUE,
    price DECIMAL(10,2),
    purchase_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES Events(event_id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (user_id) REFERENCES Users(user_id)
        ON DELETE CASCADE ON UPDATE CASCADE
);

-- Insert sample admin
INSERT INTO Admins (name, email, password) VALUES 
('Admin User', 'admin@nibm.lk', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Insert sample events
INSERT INTO Events (admin_id, name, description, img_url, price, branch) VALUES 
(1, 'Tech Innovation Summit 2024', 'Join us for the biggest tech event of the year featuring industry leaders and innovative startups.', 'https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=500', 25.00, 'Colombo'),
(1, 'Cultural Night Extravaganza', 'Experience the rich cultural diversity of Sri Lanka through music, dance, and traditional performances.', 'https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?w=500', 0.00, 'Kandy'),
(1, 'Business Leadership Workshop', 'Learn from successful entrepreneurs and business leaders in this intensive workshop.', 'https://images.unsplash.com/photo-1559136555-9303baea8ebd?w=500', 15.00, 'Galle');

-- Add more sample events
INSERT INTO Events (admin_id, name, description, img_url, price, branch) VALUES 
(1, 'Digital Marketing Workshop', 'Learn the latest digital marketing strategies and tools from industry experts. Perfect for business students.', 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=500', 20.00, 'Matara'),
(1, 'Photography Exhibition', 'Showcase of student photography work from across all NIBM branches. Free entry for all students.', 'https://images.unsplash.com/photo-1452587925148-ce544e77e70d?w=500', 0.00, 'Kurunegala'),
(1, 'Entrepreneurship Summit', 'Meet successful entrepreneurs and learn about starting your own business. Networking opportunities included.', 'https://images.unsplash.com/photo-1556761175-b413da4baf72?w=500', 30.00, 'Ratnapura'),
(1, 'Sports Day 2024', 'Annual inter-branch sports competition. Multiple sports categories and prizes for winners.', 'https://images.unsplash.com/photo-1461896836934-ffe607ba8211?w=500', 0.00, 'Kalutara'),
(1, 'AI & Machine Learning Seminar', 'Explore the future of artificial intelligence and machine learning with leading researchers.', 'https://images.unsplash.com/photo-1485827404703-89b55fcc595e?w=500', 25.00, 'Badulla'),
(1, 'Music Festival', 'Annual music festival featuring local bands and student performances. Food stalls and entertainment.', 'https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?w=500', 15.00, 'Colombo'),
(1, 'Career Fair 2024', 'Meet potential employers and explore career opportunities. Resume review sessions included.', 'https://images.unsplash.com/photo-1521737604893-d14cc237f11d?w=500', 0.00, 'Kandy'),
(1, 'Coding Bootcamp', 'Intensive 3-day coding workshop covering web development, mobile apps, and database design.', 'https://images.unsplash.com/photo-1461749280684-dccba630e2f6?w=500', 50.00, 'Galle');
