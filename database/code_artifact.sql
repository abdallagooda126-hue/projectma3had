-- ====================================================
-- قاعدة بيانات طلاب معهد القاهرة العالي (Cairo Institute DB)
-- Unified Database Schema & Initial Data
-- ====================================================

-- 1. إنشاء قاعدة البيانات
CREATE DATABASE IF NOT EXISTS CairoInstituteDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE CairoInstituteDB;

-- 2. حذف الجداول القديمة إن وجدت لتجنب التعارض (بالترتيب المناسب)
DROP TABLE IF EXISTS Books;
DROP TABLE IF EXISTS Admins;
DROP TABLE IF EXISTS Students;

-- 3. جدول الطلاب (Students) شامل جميع الحقول (بما فيها المصاريف/الدفع)
CREATE TABLE Students (
    StudentID VARCHAR(10) PRIMARY KEY,
    FullName VARCHAR(100) NOT NULL,
    Username VARCHAR(50) UNIQUE NOT NULL,
    PasswordHash VARCHAR(255) NOT NULL,
    Department VARCHAR(50) DEFAULT 'عام',
    Paid TINYINT(1) NOT NULL DEFAULT 0,
    PaidAt DATETIME NULL,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 4. جدول المسؤولين (Admins)
CREATE TABLE Admins (
    AdminID INT AUTO_INCREMENT PRIMARY KEY,
    FullName VARCHAR(100) NOT NULL,
    Username VARCHAR(50) UNIQUE NOT NULL,
    PasswordHash VARCHAR(255) NOT NULL,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 5. جدول الكتب (Books)
CREATE TABLE Books (
    BookID INT AUTO_INCREMENT PRIMARY KEY,
    Title VARCHAR(200) NOT NULL,
    Author VARCHAR(100) DEFAULT '',
    Category VARCHAR(50) DEFAULT 'عام',
    FileName VARCHAR(100) NULL,
    Url VARCHAR(500) NULL,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- ====================================================
-- إضافة البيانات الأولية (Seed Data)
-- ====================================================

-- إضافة الطلاب وتحديد حالة الدفع للبعض منهم
INSERT INTO Students (StudentID, FullName, Username, PasswordHash, Department, Paid, PaidAt) VALUES
('2024030001', 'عبد الله مصطفى جوده', 'abdalla_g', 'Pass#202401', 'نظم معلومات إدارية', 1, NOW()),
('2024030002', 'أحمد محمود السيد', 'ahmed_m', 'Pass#202402', 'علوم حاسب', 1, NOW()),
('2024030003', 'سارة محمد علي', 'sara_m', 'Pass#202403', 'إدارة أعمال', 1, NOW()),
('2024030004', 'عمر خالد حسن', 'omar_k', 'Pass#202404', 'نظم معلومات إدارية', 0, NULL),
('2024030005', 'مريم يوسف إبراهيم', 'mariam_y', 'Pass#202405', 'محاسبة', 1, NOW()),
('2024030006', 'كريم عبد الرحمن أحمد', 'kareem_a', 'Pass#202406', 'علوم حاسب', 0, NULL),
('2024030007', 'نورهان طارق مصطفى', 'nourhan_t', 'Pass#202407', 'إدارة أعمال', 1, NOW()),
('2024030008', 'محمد شعبان عبد العزيز', 'm_shaban', 'Pass#202408', 'نظم معلومات إدارية', 0, NULL),
('2024030009', 'فاطمة الزهراء أيمن', 'fatma_a', 'Pass#202409', 'محاسبة', 0, NULL),
('2024030010', 'حسام الدين شريف', 'hossam_s', 'Pass#202410', 'علوم حاسب', 0, NULL);

-- إضافة الحساب الأدمن الرئيسي
INSERT INTO Admins (FullName, Username, PasswordHash) VALUES 
('مدير النظام', 'admin', 'Admin#2026');