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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `attendances` */

insert  into `attendances`(`id`,`employee_id`,`date`,`time_in`,`break_out`,`break_in`,`time_out`,`status`,`late_minutes`,`overtime_minutes`,`work_duration_minutes`,`created_at`,`updated_at`) values 
(1,'NRC-2026-002','2026-02-26','08:00:00',NULL,'12:30:00','17:00:00','Hadir',0,60,540,'2026-02-26 22:26:22','2026-02-26 22:30:29');

/*Table structure for table `b2b_customers` */

DROP TABLE IF EXISTS `b2b_customers`;

CREATE TABLE `b2b_customers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_name` varchar(100) NOT NULL,
  `contact_name` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `b2b_customers` */

insert  into `b2b_customers`(`id`,`company_name`,`contact_name`,`phone`,`address`) values 
(1,'Grosir Knalpot Jakarta','Bpk. Hendra','08111222333','Jl. Kebon Jeruk, Jkt'),
(2,'SpeedShop Surabaya','Kang Emil','08999888777','Jl. Pahlawan, Sby');

/*Table structure for table `b2b_sales_orders` */

DROP TABLE IF EXISTS `b2b_sales_orders`;

CREATE TABLE `b2b_sales_orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `so_number` varchar(50) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `order_date` date NOT NULL,
  `due_date` date NOT NULL COMMENT 'Batas Waktu Pembayaran (Tempo)',
  `total_amount` decimal(15,2) NOT NULL,
  `paid_amount` decimal(15,2) DEFAULT 0.00,
  `status` enum('PENDING','PARTIAL','PAID') DEFAULT 'PENDING',
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`),
  CONSTRAINT `b2b_sales_orders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `b2b_customers` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `b2b_sales_orders` */

/*Table structure for table `bom_headers` */

DROP TABLE IF EXISTS `bom_headers`;

CREATE TABLE `bom_headers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fg_sku` varchar(50) NOT NULL COMMENT 'SKU Barang Jadi (Knalpot)',
  `recipe_name` varchar(100) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `bom_headers` */

/*Table structure for table `bom_items` */

DROP TABLE IF EXISTS `bom_items`;

CREATE TABLE `bom_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bom_id` int(11) NOT NULL,
  `rm_sku` varchar(50) NOT NULL COMMENT 'SKU Bahan Baku',
  `qty_required` decimal(10,2) NOT NULL COMMENT 'Jumlah yg dibutuhkan u/ 1 Knalpot',
  PRIMARY KEY (`id`),
  KEY `bom_id` (`bom_id`),
  CONSTRAINT `bom_items_ibfk_1` FOREIGN KEY (`bom_id`) REFERENCES `bom_headers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `bom_items` */

/*Table structure for table `chart_of_accounts` */

DROP TABLE IF EXISTS `chart_of_accounts`;

CREATE TABLE `chart_of_accounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_code` varchar(20) NOT NULL,
  `account_name` varchar(100) NOT NULL,
  `account_type` enum('ASET','LIABILITI','EKUITI','PENDAPATAN','PERBELANJAAN') NOT NULL,
  `balance` decimal(15,2) DEFAULT 0.00,
  PRIMARY KEY (`id`),
  UNIQUE KEY `account_code` (`account_code`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `chart_of_accounts` */

insert  into `chart_of_accounts`(`id`,`account_code`,`account_name`,`account_type`,`balance`) values 
(1,'1-1000','Tunai di Tangan','ASET',0.00),
(2,'1-2000','Akaun Bank BCA','ASET',0.00),
(3,'1-3000','Inventori Bahan Mentah','ASET',0.00),
(4,'2-1000','Akaun Pemiutang (Hutang)','LIABILITI',0.00),
(5,'3-1000','Modal Pemilik','EKUITI',0.00),
(6,'4-1000','Pendapatan Jualan','PENDAPATAN',0.00),
(7,'5-1000','Kos Barang Dijual (HPP)','PERBELANJAAN',0.00),
(8,'5-2000','Perbelanjaan Gaji','PERBELANJAAN',0.00);

/*Table structure for table `company_settings` */

DROP TABLE IF EXISTS `company_settings`;

CREATE TABLE `company_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `app_name` varchar(100) NOT NULL DEFAULT 'My ERP System',
  `company_name` varchar(150) NOT NULL DEFAULT 'PT. Nama Perusahaan',
  `address` text DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `logo_path` varchar(255) DEFAULT 'default-logo.png',
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `company_settings` */

insert  into `company_settings`(`id`,`app_name`,`company_name`,`address`,`phone`,`logo_path`,`updated_at`) values 
(1,'My ERP System','PT. Nama Perusahaan','Purbalingga','081234567890','1772145565_b243919220df1b46516d.png','2026-02-27 05:39:25');

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
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `employees` */

insert  into `employees`(`id`,`pin`,`employee_id`,`rfid`,`finger_count`,`face_count`,`machine_privilege`,`name`,`phone`,`address`,`marital_status`,`department`,`position`,`shift_id`,`status`,`is_active`,`join_date`,`resign_date`,`bank_name`,`bank_account`,`basic_salary`,`salary_type`,`position_allowance`,`meal_allowance`,`transport_allowance`,`overtime_rate`,`bpjs_kesehatan`,`bpjs_ketenagakerjaan`,`leave_balance`,`created_at`,`updated_at`) values 
(7,1,'NRC-2026-001',NULL,0,0,0,'IRFAAN SHOODIQ','08123123123','Purbalingga','Lajang','Manajemen & HRD','HRD',1,'Tetap',1,'2026-02-27',NULL,'BCA','12312312312',100000.00,'Mingguan',0.00,0.00,0.00,0.00,0,0,12,'2026-02-27 12:00:32','2026-02-27 12:00:32');

/*Table structure for table `factory_assets` */

DROP TABLE IF EXISTS `factory_assets`;

CREATE TABLE `factory_assets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `asset_code` varchar(50) NOT NULL,
  `asset_name` varchar(100) NOT NULL,
  `purchase_date` date DEFAULT NULL,
  `status` enum('ACTIVE','MAINTENANCE','BROKEN') DEFAULT 'ACTIVE',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `factory_assets` */

insert  into `factory_assets`(`id`,`asset_code`,`asset_name`,`purchase_date`,`status`) values 
(1,'AST-001','Mesin Las Argon TIG','2023-01-15','ACTIVE'),
(2,'AST-002','Mesin Bending Pipa Hidrolik','2022-11-10','ACTIVE');

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

/*Table structure for table `journal_items` */

DROP TABLE IF EXISTS `journal_items`;

CREATE TABLE `journal_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `journal_id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `debit` decimal(15,2) DEFAULT 0.00,
  `credit` decimal(15,2) DEFAULT 0.00,
  PRIMARY KEY (`id`),
  KEY `journal_id` (`journal_id`),
  KEY `account_id` (`account_id`),
  CONSTRAINT `journal_items_ibfk_1` FOREIGN KEY (`journal_id`) REFERENCES `journals` (`id`) ON DELETE CASCADE,
  CONSTRAINT `journal_items_ibfk_2` FOREIGN KEY (`account_id`) REFERENCES `chart_of_accounts` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `journal_items` */

/*Table structure for table `journals` */

DROP TABLE IF EXISTS `journals`;

CREATE TABLE `journals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `journal_number` varchar(50) NOT NULL,
  `transaction_date` date NOT NULL,
  `description` text DEFAULT NULL,
  `total_amount` decimal(15,2) NOT NULL,
  `created_by` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `journals` */

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

/*Table structure for table `offline_sale_items` */

DROP TABLE IF EXISTS `offline_sale_items`;

CREATE TABLE `offline_sale_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_no` varchar(50) NOT NULL,
  `sku` varchar(100) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `qty` int(11) NOT NULL,
  `price` decimal(15,2) NOT NULL,
  `subtotal` decimal(15,2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `offline_sale_items` */

insert  into `offline_sale_items`(`id`,`invoice_no`,`sku`,`item_name`,`qty`,`price`,`subtotal`) values 
(1,'INV-20260228-0001','FG-0001','WR155',1,100000.00,100000.00);

/*Table structure for table `offline_sales` */

DROP TABLE IF EXISTS `offline_sales`;

CREATE TABLE `offline_sales` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_no` varchar(50) NOT NULL,
  `customer_name` varchar(150) DEFAULT 'Pelanggan Umum',
  `total_amount` decimal(15,2) NOT NULL,
  `payment_method` varchar(50) DEFAULT 'Cash',
  `sale_date` datetime DEFAULT current_timestamp(),
  `cashier_name` varchar(100) DEFAULT 'Admin',
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoice_no` (`invoice_no`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `offline_sales` */

insert  into `offline_sales`(`id`,`invoice_no`,`customer_name`,`total_amount`,`payment_method`,`sale_date`,`cashier_name`) values 
(1,'INV-20260228-0001','Umum',100000.00,'Cash','2026-02-28 23:44:52','Administrator Noric');

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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `payrolls` */

/*Table structure for table `production_logs` */

DROP TABLE IF EXISTS `production_logs`;

CREATE TABLE `production_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sku` varchar(100) NOT NULL,
  `qty_produced` int(11) NOT NULL,
  `production_date` datetime DEFAULT current_timestamp(),
  `operator_name` varchar(100) DEFAULT 'Admin',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `production_logs` */

/*Table structure for table `purchase_order_items` */

DROP TABLE IF EXISTS `purchase_order_items`;

CREATE TABLE `purchase_order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `po_id` int(11) NOT NULL,
  `rm_sku` varchar(50) NOT NULL COMMENT 'SKU Bahan Baku',
  `qty` decimal(10,2) NOT NULL,
  `unit_price` decimal(15,2) NOT NULL,
  `subtotal` decimal(15,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `po_id` (`po_id`),
  CONSTRAINT `purchase_order_items_ibfk_1` FOREIGN KEY (`po_id`) REFERENCES `purchase_orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `purchase_order_items` */

/*Table structure for table `purchase_orders` */

DROP TABLE IF EXISTS `purchase_orders`;

CREATE TABLE `purchase_orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `po_number` varchar(50) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `po_date` date NOT NULL,
  `status` enum('DRAFT','ORDERED','RECEIVED') DEFAULT 'ORDERED',
  `total_amount` decimal(15,2) DEFAULT 0.00,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `supplier_id` (`supplier_id`),
  CONSTRAINT `purchase_orders_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `purchase_orders` */

/*Table structure for table `raw_materials` */

DROP TABLE IF EXISTS `raw_materials`;

CREATE TABLE `raw_materials` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sku_material` varchar(100) NOT NULL,
  `material_name` varchar(255) NOT NULL,
  `unit` varchar(50) DEFAULT 'Pcs' COMMENT 'Satuan: Batang, Kg, Liter, Lembar',
  `hpp` decimal(15,2) DEFAULT 0.00 COMMENT 'Harga Beli Modal',
  `physical_stock` decimal(15,2) DEFAULT 0.00,
  `min_stock` decimal(15,2) DEFAULT 10.00,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `sku_material` (`sku_material`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `raw_materials` */

/*Table structure for table `sales_order_items` */

DROP TABLE IF EXISTS `sales_order_items`;

CREATE TABLE `sales_order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_sn` varchar(50) NOT NULL,
  `item_id` bigint(20) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `variation_name` varchar(255) DEFAULT NULL,
  `model_qty` int(11) NOT NULL,
  `model_discounted_price` decimal(15,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_sn` (`order_sn`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `sales_order_items` */

/*Table structure for table `sales_orders` */

DROP TABLE IF EXISTS `sales_orders`;

CREATE TABLE `sales_orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_sn` varchar(50) NOT NULL,
  `shop_id` varchar(50) NOT NULL,
  `buyer_username` varchar(100) DEFAULT NULL,
  `total_amount` decimal(15,2) DEFAULT 0.00,
  `payment_method` varchar(50) DEFAULT NULL,
  `order_status` varchar(50) DEFAULT NULL,
  `shipping_carrier` varchar(100) DEFAULT NULL,
  `tracking_number` varchar(100) DEFAULT NULL,
  `order_date` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_sn` (`order_sn`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `sales_orders` */

/*Table structure for table `shopee_finances` */

DROP TABLE IF EXISTS `shopee_finances`;

CREATE TABLE `shopee_finances` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_sn` varchar(50) NOT NULL,
  `shop_id` varchar(50) NOT NULL,
  `buyer_username` varchar(100) DEFAULT NULL,
  `original_price` decimal(15,2) DEFAULT 0.00,
  `admin_fee` decimal(15,2) DEFAULT 0.00,
  `service_fee` decimal(15,2) DEFAULT 0.00,
  `shipping_fee_paid_by_seller` decimal(15,2) DEFAULT 0.00,
  `escrow_amount` decimal(15,2) DEFAULT 0.00 COMMENT 'Uang BERSIH yang cair ke rekening',
  `payout_time` datetime DEFAULT NULL,
  `status` varchar(50) DEFAULT 'COMPLETED',
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_sn` (`order_sn`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `shopee_finances` */

/*Table structure for table `shopee_integrations` */

DROP TABLE IF EXISTS `shopee_integrations`;

CREATE TABLE `shopee_integrations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shop_id` varchar(50) NOT NULL,
  `shop_name` varchar(100) DEFAULT NULL,
  `access_token` text NOT NULL,
  `refresh_token` text NOT NULL,
  `expire_at` int(11) NOT NULL,
  `status` enum('Active','Expired','Disconnected') DEFAULT 'Active',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `shop_id` (`shop_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `shopee_integrations` */

/*Table structure for table `shopee_products` */

DROP TABLE IF EXISTS `shopee_products`;

CREATE TABLE `shopee_products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shop_id` varchar(50) NOT NULL,
  `item_id` bigint(20) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `item_sku` varchar(100) DEFAULT NULL,
  `price` decimal(15,2) DEFAULT 0.00,
  `stock` int(11) DEFAULT 0,
  `status` varchar(50) DEFAULT 'NORMAL',
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `item_id` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `shopee_products` */

/*Table structure for table `shopee_rma` */

DROP TABLE IF EXISTS `shopee_rma`;

CREATE TABLE `shopee_rma` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `return_sn` varchar(50) NOT NULL,
  `order_sn` varchar(50) NOT NULL,
  `shop_id` varchar(50) NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `refund_amount` decimal(15,2) DEFAULT 0.00,
  `is_stock_returned` tinyint(1) DEFAULT 0 COMMENT '1 Jika knalpot sudah dimasukkan ke gudang lagi',
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `return_sn` (`return_sn`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `shopee_rma` */

/*Table structure for table `suppliers` */

DROP TABLE IF EXISTS `suppliers`;

CREATE TABLE `suppliers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `supplier_name` varchar(100) NOT NULL,
  `contact_person` varchar(50) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `suppliers` */

insert  into `suppliers`(`id`,`supplier_name`,`contact_person`,`phone`,`address`) values 
(1,'PT. Baja Stainless Indo','Bpk. Budi','08123456789','Kawasan Industri Cikarang'),
(2,'CV. Glaswool Abadi','Ibu Siska','08987654321','Bandung');

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
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `users` */

insert  into `users`(`id`,`employee_id`,`username`,`password`,`name`,`role`,`created_at`,`updated_at`) values 
(1,'NRC-2026-000','admin','$2y$10$EDTW.EgLnjsn28qMwmQ88uQyKbB3rVhBgMnU2XoeOpSxzPGQ2QCPm','Administrator Noric','admin','2026-02-24 17:29:59','2026-02-25 10:30:22'),
(6,'NRC-2026-001','sumanto','$2y$10$4SV8qTj/NvkiENoRB1f4kOGuzp92a22eYECvpazpr2ODuoFIwg0ZW','Sumanto','karyawan','2026-02-25 03:08:34','2026-02-25 10:30:31'),
(8,'NRC-2026-001','aliando','$2y$10$9H8gLluhztFgvOySvHx4LuY8Uk2WQHZSzqtkvtyDDGQVMv41iEraW','IRFAAN SHOODIQ','karyawan','2026-02-27 12:00:32','2026-02-27 12:00:32');

/*Table structure for table `warehouse_inventory` */

DROP TABLE IF EXISTS `warehouse_inventory`;

CREATE TABLE `warehouse_inventory` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sku` varchar(100) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `item_type` varchar(50) DEFAULT 'Barang Jadi (Knalpot)',
  `hpp` decimal(15,2) DEFAULT 0.00 COMMENT 'Harga Pokok Produksi',
  `physical_stock` int(11) DEFAULT 0,
  `min_stock` int(11) DEFAULT 10 COMMENT 'Batas peringatan stok menipis',
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `sku` (`sku`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `warehouse_inventory` */

insert  into `warehouse_inventory`(`id`,`sku`,`item_name`,`item_type`,`hpp`,`physical_stock`,`min_stock`,`updated_at`) values 
(1,'FG-0001','WR155','Barang Jadi',100000.00,11,5,'2026-02-28 23:44:52');

/*Table structure for table `work_orders` */

DROP TABLE IF EXISTS `work_orders`;

CREATE TABLE `work_orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `spk_number` varchar(50) NOT NULL,
  `bom_id` int(11) NOT NULL,
  `planned_qty` int(11) NOT NULL COMMENT 'Target Produksi',
  `status` enum('DRAFT','IN_PROGRESS','COMPLETED') DEFAULT 'DRAFT',
  `start_date` date DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `work_orders` */

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
