/*
SQLyog Professional v13.1.1 (64 bit)
MySQL - 10.4.32-MariaDB 
*********************************************************************
*/
/*!40101 SET NAMES utf8 */;

create table `company_settings` (
	`id` int (11),
	`app_name` varchar (300),
	`company_name` varchar (450),
	`address` text ,
	`phone` varchar (150),
	`logo_path` varchar (765),
	`updated_at` datetime 
); 
insert into `company_settings` (`id`, `app_name`, `company_name`, `address`, `phone`, `logo_path`, `updated_at`) values('1','NORIC MANAGEMENT','Noric Racing Exhaust','Purbalingga','081234567890','1774638990_951de9a862cd31e73798.png','2026-03-28 02:17:04');
