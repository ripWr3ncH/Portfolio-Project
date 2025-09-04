<?php
// Database setup script
// Run this file once to create the database and tables

$host = 'localhost';
$username = 'root';
$password = '';

try {
    // Connect to MySQL server (without specifying database)
    $pdo = new PDO("mysql:host=$host;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS portfolio_db");
    $pdo->exec("USE portfolio_db");
    
    // Drop existing tables if they exist (for clean setup)
    $pdo->exec("DROP TABLE IF EXISTS admin_logs");
    $pdo->exec("DROP TABLE IF EXISTS education");
    $pdo->exec("DROP TABLE IF EXISTS projects");
    
    // Create projects table
    $projectsTable = "
    CREATE TABLE projects (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        description TEXT NOT NULL,
        technologies JSON,
        image_path VARCHAR(255),
        project_url VARCHAR(255),
        github_url VARCHAR(255),
        is_featured BOOLEAN DEFAULT FALSE,
        display_order INT DEFAULT 0,
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    // Create education table
    $educationTable = "
    CREATE TABLE education (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        institution VARCHAR(255) NOT NULL,
        start_date VARCHAR(50) NOT NULL,
        end_date VARCHAR(50),
        description TEXT,
        highlights JSON,
        display_order INT DEFAULT 0,
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    // Create admin logs table for tracking changes
    $logsTable = "
    CREATE TABLE admin_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        action VARCHAR(100) NOT NULL,
        table_name VARCHAR(50) NOT NULL,
        record_id INT,
        old_data JSON,
        new_data JSON,
        admin_ip VARCHAR(45),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";

    // Create contact messages table
    $contactTable = "
    CREATE TABLE contact_messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        subject VARCHAR(500),
        message TEXT NOT NULL,
        status ENUM('unread', 'read', 'replied', 'archived') DEFAULT 'unread',
        ip_address VARCHAR(45),
        user_agent TEXT,
        is_spam BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    $pdo->exec($projectsTable);
    $pdo->exec($educationTable);
    $pdo->exec($logsTable);
    $pdo->exec($contactTable);
    
    // Insert sample data for projects
    $sampleProjects = [
        [
            'title' => 'Portfolio Website',
            'description' => 'A responsive personal portfolio website built with HTML5, CSS3, and JavaScript featuring modern design and smooth animations.',
            'technologies' => json_encode(['HTML5', 'CSS3', 'JavaScript']),
            'project_url' => 'https://github.com/ripWr3ncH/Portfolio-Project',
            'github_url' => 'https://github.com/ripWr3ncH/Portfolio-Project',
            'is_featured' => false,
            'display_order' => 1
        ],
        [
            'title' => 'Algorithm Visualizer',
            'description' => 'Interactive web application to visualize sorting algorithms and data structures, built as part of Data Structures course project.',
            'technologies' => json_encode(['React', 'JavaScript', 'CSS3']),
            'project_url' => 'https://github.com/ripWr3ncH',
            'github_url' => 'https://github.com/ripWr3ncH',
            'is_featured' => false,
            'display_order' => 2
        ],
        [
            'title' => 'E-Commerce Website',
            'description' => 'Full-stack e-commerce platform with user authentication, product management, and payment integration using modern web technologies.',
            'technologies' => json_encode(['Laravel', 'PHP', 'MySQL', 'Bootstrap']),
            'project_url' => 'https://github.com/ripWr3ncH',
            'github_url' => 'https://github.com/ripWr3ncH',
            'is_featured' => false,
            'display_order' => 3
        ],
        [
            'title' => 'Student Management System',
            'description' => 'Comprehensive web-based student management system developed for university coursework. Features include student registration, course management, grade tracking, and administrative dashboard with role-based access control.',
            'technologies' => json_encode(['Node.js', 'Express', 'MongoDB', 'React', 'JWT']),
            'project_url' => 'https://github.com/ripWr3ncH',
            'github_url' => 'https://github.com/ripWr3ncH',
            'is_featured' => true,
            'display_order' => 4
        ]
    ];
    
    $stmt = $pdo->prepare("INSERT INTO projects (title, description, technologies, project_url, github_url, is_featured, display_order) VALUES (?, ?, ?, ?, ?, ?, ?)");
    foreach ($sampleProjects as $project) {
        $stmt->execute([
            $project['title'],
            $project['description'],
            $project['technologies'],
            $project['project_url'],
            $project['github_url'],
            $project['is_featured'],
            $project['display_order']
        ]);
    }
    
    // Insert sample data for education
    $sampleEducation = [
        [
            'title' => 'Bachelor of Computer Science & Engineering',
            'institution' => 'Khulna University of Engineering & Technology (KUET)',
            'start_date' => '2022',
            'end_date' => 'Present',
            'description' => 'Currently pursuing a comprehensive program in Computer Science & Engineering with focus on software development, algorithms, and modern web technologies.',
            'highlights' => json_encode(['Current CGPA: 3.76/4.00', 'Dean\'s List']),
            'display_order' => 1
        ],
        [
            'title' => 'Higher Secondary Certificate (HSC)',
            'institution' => 'Dhaka City College, Dhaka',
            'start_date' => '2020',
            'end_date' => '2022',
            'description' => 'Completed Higher Secondary education in Science Group with concentration in Mathematics, Physics, Chemistry, and ICT.',
            'highlights' => json_encode(['GPA: 5.00/5.00', 'Golden A+', 'Merit Scholarship']),
            'display_order' => 2
        ],
        [
            'title' => 'Secondary School Certificate (SSC)',
            'institution' => 'Dhaka Residential Model College',
            'start_date' => '2018',
            'end_date' => '2020',
            'description' => 'Completed Secondary education in Science Group with strong foundation in Mathematics, Science, and Technology subjects.',
            'highlights' => json_encode(['GPA: 5.00/5.00', 'Board Scholarship']),
            'display_order' => 3
        ]
    ];
    
    $stmt = $pdo->prepare("INSERT INTO education (title, institution, start_date, end_date, description, highlights, display_order) VALUES (?, ?, ?, ?, ?, ?, ?)");
    foreach ($sampleEducation as $edu) {
        $stmt->execute([
            $edu['title'],
            $edu['institution'],
            $edu['start_date'],
            $edu['end_date'],
            $edu['description'],
            $edu['highlights'],
            $edu['display_order']
        ]);
    }
    
    echo "Database and tables created successfully!<br>";
    echo "Sample data inserted successfully!<br>";
    echo "<a href='admin/login.php'>Go to Admin Panel</a>";
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
