-- Database: internship_db (Create this database first)
-- USE internship_db;

CREATE TABLE IF NOT EXISTS `users` (
  `user_id` INT AUTO_INCREMENT PRIMARY KEY,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` ENUM('student', 'admin') NOT NULL DEFAULT 'student',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `students` (
  `student_id` INT PRIMARY KEY, -- References users.user_id
  `first_name` VARCHAR(100) NULL,
  `last_name` VARCHAR(100) NULL,
  `major` VARCHAR(100) NULL,
  `academic_year` ENUM('Freshman', 'Sophomore', 'Junior', 'Senior', 'Graduate') NULL,
  `gpa` FLOAT NULL,
  `profile_summary` TEXT NULL,
  `linkedin_url` VARCHAR(255) NULL,
  `github_url` VARCHAR(255) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`student_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE,
  INDEX `idx_major` (`major`),
  INDEX `idx_academic_year` (`academic_year`),
  INDEX `idx_gpa` (`gpa`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `skills` (
  `skill_id` INT AUTO_INCREMENT PRIMARY KEY,
  `skill_name` VARCHAR(100) NOT NULL UNIQUE,
  INDEX `idx_skill_name` (`skill_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `courses` (
  `course_id` INT AUTO_INCREMENT PRIMARY KEY,
  `course_code` VARCHAR(20) NOT NULL UNIQUE,
  `course_name` VARCHAR(255) NOT NULL,
  INDEX `idx_course_code` (`course_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `student_skills` (
  `student_id` INT NOT NULL,
  `skill_id` INT NOT NULL,
  PRIMARY KEY (`student_id`, `skill_id`),
  FOREIGN KEY (`student_id`) REFERENCES `students`(`student_id`) ON DELETE CASCADE,
  FOREIGN KEY (`skill_id`) REFERENCES `skills`(`skill_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `student_courses` (
  `student_id` INT NOT NULL,
  `course_id` INT NOT NULL,
  PRIMARY KEY (`student_id`, `course_id`),
  FOREIGN KEY (`student_id`) REFERENCES `students`(`student_id`) ON DELETE CASCADE,
  FOREIGN KEY (`course_id`) REFERENCES `courses`(`course_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `student_preferences` (
  `preference_id` INT AUTO_INCREMENT PRIMARY KEY,
  `student_id` INT NOT NULL,
  `preference_type` ENUM('Industry', 'Location') NOT NULL,
  `preference_value` VARCHAR(255) NOT NULL,
  `priority` INT DEFAULT 1,
  FOREIGN KEY (`student_id`) REFERENCES `students`(`student_id`) ON DELETE CASCADE,
  INDEX `idx_student_pref_type` (`student_id`, `preference_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `companies` (
  `company_id` INT AUTO_INCREMENT PRIMARY KEY,
  `company_name` VARCHAR(255) NOT NULL UNIQUE,
  `website` VARCHAR(255) NULL,
  `description` TEXT NULL,
  `logo_url` VARCHAR(255) NULL,
  INDEX `idx_company_name` (`company_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `internships` (
  `internship_id` INT AUTO_INCREMENT PRIMARY KEY,
  `company_id` INT NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT NULL,
  `location` VARCHAR(150) NULL,
  `industry` VARCHAR(100) NULL,
  `required_gpa` FLOAT NULL,
  `required_major` VARCHAR(255) NULL, -- Consider a separate majors table for normalization
  `required_year` ENUM('Any', 'Sophomore', 'Junior', 'Senior', 'Graduate') NULL DEFAULT 'Any',
  `application_deadline` DATE NULL,
  `start_date` DATE NULL,
  `duration` VARCHAR(50) NULL,
  `is_paid` BOOLEAN NULL,
  `url` VARCHAR(255) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`company_id`) REFERENCES `companies`(`company_id`) ON DELETE CASCADE,
  INDEX `idx_company_id` (`company_id`),
  INDEX `idx_location` (`location`),
  INDEX `idx_industry` (`industry`),
  INDEX `idx_required_gpa` (`required_gpa`),
  INDEX `idx_required_year` (`required_year`),
  INDEX `idx_application_deadline` (`application_deadline`),
  FULLTEXT KEY `idx_ft_description` (`description`, `title`) -- For basic keyword search if needed
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE IF NOT EXISTS `internship_required_skills` (
  `internship_id` INT NOT NULL,
  `skill_id` INT NOT NULL,
  PRIMARY KEY (`internship_id`, `skill_id`),
  FOREIGN KEY (`internship_id`) REFERENCES `internships`(`internship_id`) ON DELETE CASCADE,
  FOREIGN KEY (`skill_id`) REFERENCES `skills`(`skill_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `internship_preferred_skills` (
  `internship_id` INT NOT NULL,
  `skill_id` INT NOT NULL,
  PRIMARY KEY (`internship_id`, `skill_id`),
  FOREIGN KEY (`internship_id`) REFERENCES `internships`(`internship_id`) ON DELETE CASCADE,
  FOREIGN KEY (`skill_id`) REFERENCES `skills`(`skill_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `internship_required_courses` (
  `internship_id` INT NOT NULL,
  `course_id` INT NOT NULL,
  PRIMARY KEY (`internship_id`, `course_id`),
  FOREIGN KEY (`internship_id`) REFERENCES `internships`(`internship_id`) ON DELETE CASCADE,
  FOREIGN KEY (`course_id`) REFERENCES `courses`(`course_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `internship_preferred_courses` (
  `internship_id` INT NOT NULL,
  `course_id` INT NOT NULL,
  PRIMARY KEY (`internship_id`, `course_id`),
  FOREIGN KEY (`internship_id`) REFERENCES `internships`(`internship_id`) ON DELETE CASCADE,
  FOREIGN KEY (`course_id`) REFERENCES `courses`(`course_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add sample skills and courses needed by the generator script
-- INSERT IGNORE INTO skills (skill_name) VALUES ('Python'), ('Java'), ('C++'), ('JavaScript'), ('React'), ('Node.js'), ('SQL'), ('NoSQL'), ('AWS'), ('Azure'), ('Docker'), ('Kubernetes'), ('Machine Learning'), ('Data Analysis'), ('Project Management'), ('Communication'), ('Teamwork'), ('Git'), ('Agile'), ('Excel'), ('CAD'), ('Circuit Design'), ('Marketing Analytics'), ('SEO');
-- INSERT IGNORE INTO courses (course_code, course_name) VALUES ('CS101', 'Intro to Programming'), ('CS201', 'Data Structures'), ('EE101', 'Circuit Theory'), ('BA101', 'Intro to Business');
-- Add more as needed by your generated data
