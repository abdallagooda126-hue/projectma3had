-- شغّل 01_database.sql الأول، وبعدها الملف ده
USE CairoInstituteDB;

CREATE TABLE IF NOT EXISTS Admins (
    AdminID INT AUTO_INCREMENT PRIMARY KEY,
    FullName VARCHAR(100) NOT NULL,
    Username VARCHAR(50) UNIQUE NOT NULL,
    PasswordHash VARCHAR(255) NOT NULL
);

-- أول تسجيل دخول بيحوّل الباسورد لـ bcrypt تلقائيًا. غيّره بعد أول دخول.
INSERT IGNORE INTO Admins (FullName, Username, PasswordHash) VALUES ('مدير النظام', 'admin', 'Admin#2026');
