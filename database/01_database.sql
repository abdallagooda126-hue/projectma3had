-- ====================================================
-- قاعدة بيانات طلاب معهد القاهرة العالي (Cairo Institute DB)
-- Generated on: ٢‏/١١‏/٢٠٢٦
-- ====================================================

CREATE DATABASE IF NOT EXISTS CairoInstituteDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE CairoInstituteDB;

DROP TABLE IF EXISTS Students;

CREATE TABLE Students (
    StudentID VARCHAR(10) PRIMARY KEY,
    FullName VARCHAR(100) NOT NULL,
    Username VARCHAR(50) UNIQUE NOT NULL,
    PasswordHash VARCHAR(255) NOT NULL,
    Department VARCHAR(50) DEFAULT 'عام',
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO Students (StudentID, FullName, Username, PasswordHash, Department) VALUES
('2024030001', 'عبد الله مصطفى جوده', 'abdalla_g', 'Pass#202401', 'نظم معلومات إدارية'),
('2024030002', 'أحمد محمود السيد', 'ahmed_m', 'Pass#202402', 'علوم حاسب'),
('2024030003', 'سارة محمد علي', 'sara_m', 'Pass#202403', 'إدارة أعمال'),
('2024030004', 'عمر خالد حسن', 'omar_k', 'Pass#202404', 'نظم معلومات إدارية'),
('2024030005', 'مريم يوسف إبراهيم', 'mariam_y', 'Pass#202405', 'محاسبة'),
('2024030006', 'كريم عبد الرحمن أحمد', 'kareem_a', 'Pass#202406', 'علوم حاسب'),
('2024030007', 'نورهان طارق مصطفى', 'nourhan_t', 'Pass#202407', 'إدارة أعمال'),
('2024030008', 'محمد شعبان عبد العزيز', 'm_shaban', 'Pass#202408', 'نظم معلومات إدارية'),
('2024030009', 'فاطمة الزهراء أيمن', 'fatma_a', 'Pass#202409', 'محاسبة'),
('2024030010', 'حسام الدين شريف', 'hossam_s', 'Pass#202410', 'علوم حاسب');