/*
SQLyog Professional v13.1.1 (64 bit)
MySQL - 10.4.32-MariaDB : Database - noric_management
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`noric_management` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;

USE `noric_management`;

/*Table structure for table `attendances` */

DROP TABLE IF EXISTS `attendances`;

CREATE TABLE `attendances` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` varchar(20) NOT NULL,
  `date` date NOT NULL,
  `time_in` time DEFAULT NULL,
  `break_out` time DEFAULT NULL,
  `break_in` time DEFAULT NULL,
  `time_out` time DEFAULT NULL,
  `status` enum('Hadir','Terlambat','Cuti','Alpa','Izin') DEFAULT 'Alpa',
  `late_minutes` int(4) DEFAULT 0,
  `overtime_minutes` int(11) DEFAULT 0,
  `work_duration_minutes` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_attendance` (`employee_id`,`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `attendances` */

/*Table structure for table `employees` */

DROP TABLE IF EXISTS `employees`;

CREATE TABLE `employees` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pin` int(11) DEFAULT NULL,
  `employee_id` varchar(20) NOT NULL,
  `rfid` varchar(50) DEFAULT NULL,
  `finger_count` int(2) DEFAULT 0,
  `face_count` int(2) DEFAULT 0,
  `machine_privilege` int(1) DEFAULT 1 COMMENT '1:user, 2:admin, 3:subadmin',
  `name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `marital_status` varchar(15) DEFAULT 'TK/0' COMMENT 'TK/0=Tidak Kawin, K/1=Kawin Anak 1',
  `department` varchar(50) NOT NULL,
  `position` varchar(50) NOT NULL,
  `shift_id` int(11) unsigned DEFAULT NULL,
  `status` enum('Tetap','Kontrak','Magang') DEFAULT 'Kontrak',
  `is_active` tinyint(1) DEFAULT 1,
  `join_date` date NOT NULL,
  `resign_date` date DEFAULT NULL,
  `bank_name` varchar(50) DEFAULT NULL,
  `bank_account` varchar(50) DEFAULT NULL,
  `basic_salary` decimal(15,2) DEFAULT 0.00,
  `salary_type` enum('Harian','Mingguan','Bulanan') NOT NULL DEFAULT 'Bulanan',
  `position_allowance` decimal(15,2) DEFAULT 0.00,
  `meal_allowance` decimal(15,2) DEFAULT 0.00,
  `transport_allowance` decimal(15,2) DEFAULT 0.00,
  `overtime_rate` decimal(15,2) DEFAULT 0.00,
  `bpjs_kesehatan` tinyint(1) DEFAULT 0,
  `bpjs_ketenagakerjaan` tinyint(1) DEFAULT 0,
  `leave_balance` int(3) DEFAULT 12,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `employee_id` (`employee_id`),
  UNIQUE KEY `pin` (`pin`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `employees` */

insert  into `employees`(`id`,`pin`,`employee_id`,`rfid`,`finger_count`,`face_count`,`machine_privilege`,`name`,`phone`,`address`,`marital_status`,`department`,`position`,`shift_id`,`status`,`is_active`,`join_date`,`resign_date`,`bank_name`,`bank_account`,`basic_salary`,`salary_type`,`position_allowance`,`meal_allowance`,`transport_allowance`,`overtime_rate`,`bpjs_kesehatan`,`bpjs_ketenagakerjaan`,`leave_balance`,`created_at`,`updated_at`) values 
(6,1,'NRC-2026-002',NULL,0,0,1,'Aldo','085854685623','Purbalingga','TK/0','Produksi & Manufaktur','Plant Manager',1,'Tetap',1,'2026-02-26',NULL,'BSI','123456',100000.00,'Bulanan',0.00,0.00,0.00,0.00,0,0,12,'2026-02-26 05:10:11','2026-02-26 05:10:11');

/*Table structure for table `fingerspot_logs` */

DROP TABLE IF EXISTS `fingerspot_logs`;

CREATE TABLE `fingerspot_logs` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `cloud_id` varchar(50) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `original_data` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `fingerspot_logs` */

/*Table structure for table `leave_requests` */

DROP TABLE IF EXISTS `leave_requests`;

CREATE TABLE `leave_requests` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` varchar(20) NOT NULL,
  `leave_type` varchar(50) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `duration` int(3) NOT NULL,
  `reason` text NOT NULL,
  `attachment` varchar(255) DEFAULT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `reviewed_by` varchar(50) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `leave_requests` */

/*Table structure for table `operational_cash` */

DROP TABLE IF EXISTS `operational_cash`;

CREATE TABLE `operational_cash` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `transaction_code` varchar(50) NOT NULL,
  `transaction_date` date NOT NULL,
  `type` enum('Cash In','Cash Out') NOT NULL,
  `metode` enum('Cash','ATM') NOT NULL DEFAULT 'Cash',
  `category` varchar(100) NOT NULL,
  `amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `description` text NOT NULL,
  `pic_name` varchar(100) NOT NULL,
  `receipt_file` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `operational_cash` */

/*Table structure for table `payroll_details` */

DROP TABLE IF EXISTS `payroll_details`;

CREATE TABLE `payroll_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `payroll_id` int(11) NOT NULL,
  `employee_id` varchar(50) NOT NULL,
  `total_present` int(11) DEFAULT 0,
  `total_late_minutes` int(11) DEFAULT 0,
  `total_overtime_minutes` int(11) DEFAULT 0,
  `basic_salary` decimal(15,2) DEFAULT 0.00,
  `position_allowance` decimal(15,2) DEFAULT 0.00,
  `meal_allowance` decimal(15,2) DEFAULT 0.00,
  `transport_allowance` decimal(15,2) DEFAULT 0.00,
  `overtime_pay` decimal(15,2) DEFAULT 0.00,
  `late_penalty` decimal(15,2) DEFAULT 0.00,
  `bpjs_deduction` decimal(15,2) DEFAULT 0.00,
  `net_salary` decimal(15,2) DEFAULT 0.00,
  PRIMARY KEY (`id`),
  KEY `payroll_id` (`payroll_id`),
  CONSTRAINT `payroll_details_ibfk_1` FOREIGN KEY (`payroll_id`) REFERENCES `payrolls` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `payroll_details` */

/*Table structure for table `payrolls` */

DROP TABLE IF EXISTS `payrolls`;

CREATE TABLE `payrolls` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `payroll_code` varchar(50) NOT NULL,
  `salary_type` enum('Harian','Mingguan','Bulanan') NOT NULL,
  `period_start` date NOT NULL,
  `period_end` date NOT NULL,
  `total_employees` int(11) DEFAULT 0,
  `total_amount` decimal(15,2) DEFAULT 0.00,
  `status` enum('Draft','Approved','Paid') DEFAULT 'Draft',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `payrolls` */

/*Table structure for table `users` */

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` varchar(20) DEFAULT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(100) NOT NULL,
  `role` enum('admin','karyawan') DEFAULT 'karyawan',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `users` */

insert  into `users`(`id`,`employee_id`,`username`,`password`,`name`,`role`,`created_at`,`updated_at`) values 
(1,'NRC-2026-000','admin','$2y$10$EDTW.EgLnjsn28qMwmQ88uQyKbB3rVhBgMnU2XoeOpSxzPGQ2QCPm','Administrator Noric','admin','2026-02-24 17:29:59','2026-02-25 10:30:22'),
(6,'NRC-2026-001','sumanto','$2y$10$4SV8qTj/NvkiENoRB1f4kOGuzp92a22eYECvpazpr2ODuoFIwg0ZW','Sumanto','karyawan','2026-02-25 03:08:34','2026-02-25 10:30:31'),
(7,'NRC-2026-002','aldo','$2y$10$opD7NNmHxwup5c8GANJA3Oj/xsIPfb2RNpkHLy0CCpGmRW071Q6.m','Aldo','karyawan','2026-02-26 05:10:11','2026-02-26 05:10:11');

/*Table structure for table `work_shifts` */

DROP TABLE IF EXISTS `work_shifts`;

CREATE TABLE `work_shifts` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `shift_name` varchar(100) NOT NULL,
  `shift_type` enum('Reguler','Fleksibel') DEFAULT 'Reguler',
  `time_in` time DEFAULT NULL,
  `scan_in_before` int(11) DEFAULT 60,
  `scan_in_after` int(11) DEFAULT 120,
  `time_out` time DEFAULT NULL,
  `scan_out_before` int(11) DEFAULT 30,
  `scan_out_after` int(11) DEFAULT 240,
  `break_out` time DEFAULT NULL,
  `break_in` time DEFAULT NULL,
  `late_tolerance` int(11) DEFAULT 15,
  `late_penalty_rate` decimal(15,2) DEFAULT 0.00,
  `early_leave_tolerance` int(11) DEFAULT 0,
  `half_day_duration` int(11) DEFAULT 240,
  `full_day_duration` int(11) DEFAULT 480,
  `min_overtime` int(11) DEFAULT 60,
  `max_overtime` int(11) DEFAULT 240,
  `overtime_deduction` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `work_shifts` */

insert  into `work_shifts`(`id`,`shift_name`,`shift_type`,`time_in`,`scan_in_before`,`scan_in_after`,`time_out`,`scan_out_before`,`scan_out_after`,`break_out`,`break_in`,`late_tolerance`,`late_penalty_rate`,`early_leave_tolerance`,`half_day_duration`,`full_day_duration`,`min_overtime`,`max_overtime`,`overtime_deduction`,`created_at`,`updated_at`) values 
(1,'Jam Kerja Normal','Reguler','08:00:00',60,120,'16:00:00',30,240,'11:30:00','12:30:00',15,0.00,0,210,420,30,750,0,'2026-02-25 03:07:55','2026-02-25 03:07:55');

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
