-- شغّله بعد 01_database.sql و 02_admins.sql
USE CairoInstituteDB;

ALTER TABLE Students ADD COLUMN Paid TINYINT(1) NOT NULL DEFAULT 0, ADD COLUMN PaidAt DATETIME NULL;
-- مثال: أول 5 طلاب (بدون الرابع) دافعين، والباقي لا
UPDATE Students SET Paid = 1, PaidAt = NOW() WHERE StudentID IN ('2024030001','2024030002','2024030003','2024030005','2024030007');

CREATE TABLE IF NOT EXISTS Books (
    BookID INT AUTO_INCREMENT PRIMARY KEY,
    Title VARCHAR(200) NOT NULL,
    Author VARCHAR(100) DEFAULT '',
    Category VARCHAR(50) DEFAULT 'عام',
    FileName VARCHAR(100) NULL,
    Url VARCHAR(500) NULL,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
