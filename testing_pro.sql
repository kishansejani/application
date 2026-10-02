-- MySQL Database Dump for testing_pro
-- Generated on 2026-10-02 13:51:53
-- Host: 127.0.0.1    Database: testing_pro
-- ------------------------------------------------------

SET FOREIGN_KEY_CHECKS=0;

--
-- Table structure for table `addresses`
--

DROP TABLE IF EXISTS `addresses`;
CREATE TABLE `addresses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Home',
  `recipient_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `recipient_phone` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `house_no` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `street_address` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `landmark` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Ahmedabad',
  `state` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Gujarat',
  `pincode` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `formatted_address` text COLLATE utf8mb4_unicode_ci,
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `addresses_user_id_foreign` (`user_id`),
  CONSTRAINT `addresses_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `addresses`
--

INSERT INTO `addresses` (`id`, `user_id`, `type`, `recipient_name`, `recipient_phone`, `house_no`, `street_address`, `landmark`, `city`, `state`, `pincode`, `latitude`, `longitude`, `formatted_address`, `is_default`, `created_at`, `updated_at`) VALUES ('1', '3', 'Home', 'Jignesh Patel', '9988776655', 'B-402, Shivam Heights', 'Near SG Highway, Bodakdev', 'Opposite Iskcon Temple', 'Ahmedabad', 'Gujarat', '380054', '23.03035700', '72.50754200', 'B-402, Shivam Heights, Opp Iskcon Temple, Bodakdev, SG Highway, Ahmedabad, Gujarat 380054', '1', '2026-10-02 12:57:40', '2026-10-02 12:57:40');
INSERT INTO `addresses` (`id`, `user_id`, `type`, `recipient_name`, `recipient_phone`, `house_no`, `street_address`, `landmark`, `city`, `state`, `pincode`, `latitude`, `longitude`, `formatted_address`, `is_default`, `created_at`, `updated_at`) VALUES ('2', '3', 'Work', 'Jignesh Patel', '9988776655', 'Office 704, Mondeal Square', 'Prahlad Nagar Road', 'Near Prahlad Nagar Garden', 'Ahmedabad', 'Gujarat', '380015', '23.01305400', '72.51268300', '704, Mondeal Square, Prahlad Nagar Road, Ahmedabad, Gujarat 380015', '0', '2026-10-02 12:57:40', '2026-10-02 12:57:40');

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `cart_items`
--

DROP TABLE IF EXISTS `cart_items`;
CREATE TABLE `cart_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `session_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_id` bigint unsigned NOT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cart_items_user_id_foreign` (`user_id`),
  KEY `cart_items_product_id_foreign` (`product_id`),
  KEY `cart_items_session_id_index` (`session_id`),
  CONSTRAINT `cart_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cart_items_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name_en` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_gu` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `icon` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description_en` text COLLATE utf8mb4_unicode_ci,
  `description_gu` text COLLATE utf8mb4_unicode_ci,
  `sort_order` int NOT NULL DEFAULT '0',
  `is_featured` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categories_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name_en`, `name_gu`, `slug`, `image`, `icon`, `description_en`, `description_gu`, `sort_order`, `is_featured`, `is_active`, `created_at`, `updated_at`) VALUES ('1', 'Fruits & Vegetables', 'ફળો અને શાકભાજી', 'fruits-vegetables-305', 'https://images.unsplash.com/photo-1610348725531-843dff563e2c?auto=format&fit=crop&w=600&q=80', 'fa-solid fa-carrot', 'Fresh farm picked vegetables and sweet juicy organic fruits.', 'ખેતરમાંથી સીધા ચૂંટેલા તાજા શાકભાજી અને સ્વાદિષ્ટ ફળો.', '1', '1', '1', '2026-10-02 12:57:40', '2026-10-02 12:57:40');
INSERT INTO `categories` (`id`, `name_en`, `name_gu`, `slug`, `image`, `icon`, `description_en`, `description_gu`, `sort_order`, `is_featured`, `is_active`, `created_at`, `updated_at`) VALUES ('2', 'Dairy, Bread & Eggs', 'ડેરી, બ્રેડ અને ઈંડા', 'dairy-bread-eggs-650', 'https://images.unsplash.com/photo-1550583724-b2692b85b150?auto=format&fit=crop&w=600&q=80', 'fa-solid fa-cheese', 'Fresh milk, butter, cheese, paneer, curd, and bakery bread.', 'તાજું દૂધ, માખણ, પનીર, દહીં અને બેકરી પ્રોડક્ટ્સ.', '2', '1', '1', '2026-10-02 12:57:40', '2026-10-02 12:57:40');
INSERT INTO `categories` (`id`, `name_en`, `name_gu`, `slug`, `image`, `icon`, `description_en`, `description_gu`, `sort_order`, `is_featured`, `is_active`, `created_at`, `updated_at`) VALUES ('3', 'Atta, Rice & Dals', 'લોટ, ચોખા અને કઠોળ', 'atta-rice-dals-628', 'https://images.unsplash.com/photo-1586201375761-83865001e31c?auto=format&fit=crop&w=600&q=80', 'fa-solid fa-wheat-awn', 'Finest quality whole wheat atta, basmati rice, tuver dal, and pulses.', 'ઉચ્ચ ગુણવત્તાવાળો ઘઉંનો લોટ, બાસમતી ચોખા, તુવેર દાળ અને કઠોળ.', '3', '1', '1', '2026-10-02 12:57:40', '2026-10-02 12:57:40');
INSERT INTO `categories` (`id`, `name_en`, `name_gu`, `slug`, `image`, `icon`, `description_en`, `description_gu`, `sort_order`, `is_featured`, `is_active`, `created_at`, `updated_at`) VALUES ('4', 'Oil, Masalas & Spices', 'તેલ, મસાલા અને મરી', 'oil-masalas-spices-991', 'https://images.unsplash.com/photo-1596040033229-a9821ebd058d?auto=format&fit=crop&w=600&q=80', 'fa-solid fa-pepper-hot', 'Pure cold pressed oils, turmeric, chili powder, and whole spices.', 'શુદ્ધ તેલ, હળદર, લાલ મરચું, ધાણાજીરું અને ખડા મસાલા.', '4', '1', '1', '2026-10-02 12:57:40', '2026-10-02 12:57:40');
INSERT INTO `categories` (`id`, `name_en`, `name_gu`, `slug`, `image`, `icon`, `description_en`, `description_gu`, `sort_order`, `is_featured`, `is_active`, `created_at`, `updated_at`) VALUES ('5', 'Snacks & Gujarati Farsan', 'નાસ્તો અને ગુજરાતી ફરસાણ', 'snacks-gujarati-farsan-444', 'https://images.unsplash.com/photo-1599488615731-7e5c2823ff28?auto=format&fit=crop&w=600&q=80', 'fa-solid fa-cookie-bite', 'Crispy khakhra, gathiya, sev, bhakarwadi, biscuits, and namkeen.', 'કરકરા ખાખરા, ગાંઠિયા, સેવ, ભાખરવડી, બિસ્કિટ અને નમકીન.', '5', '1', '1', '2026-10-02 12:57:40', '2026-10-02 12:57:40');
INSERT INTO `categories` (`id`, `name_en`, `name_gu`, `slug`, `image`, `icon`, `description_en`, `description_gu`, `sort_order`, `is_featured`, `is_active`, `created_at`, `updated_at`) VALUES ('6', 'Tea, Coffee & Drinks', 'ચા, કોફી અને પીણાં', 'tea-coffee-drinks-420', 'https://images.unsplash.com/photo-1576092768241-dec231879fc3?auto=format&fit=crop&w=600&q=80', 'fa-solid fa-mug-hot', 'Assam & Darjeeling tea, roasted coffee, juices, and health drinks.', 'આસામ ચા, કોફી પાવડર, જ્યુસ અને હેલ્થ ડ્રિંક્સ.', '6', '1', '1', '2026-10-02 12:57:40', '2026-10-02 12:57:40');

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('1', '0001_01_01_000000_create_users_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('2', '0001_01_01_000001_create_cache_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('3', '0001_01_01_000002_create_jobs_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('4', '2026_10_02_121427_create_personal_access_tokens_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('5', '2026_10_02_121440_create_sliders_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('6', '2026_10_02_121441_01_create_categories_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('7', '2026_10_02_121441_02_create_sub_categories_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('8', '2026_10_02_121441_03_create_products_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('9', '2026_10_02_121441_04_create_product_images_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('10', '2026_10_02_121442_create_addresses_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('11', '2026_10_02_121442_create_offers_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('12', '2026_10_02_121442_create_orders_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('13', '2026_10_02_121443_create_order_items_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('14', '2026_10_02_121443_create_pages_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('15', '2026_10_02_121443_create_wishlists_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('16', '2026_10_02_121444_create_cart_items_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('17', '2026_10_02_130000_create_roles_and_permissions_tables', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('18', '2026_10_02_130001_create_settings_and_password_resets_tables', '1');

--
-- Table structure for table `offers`
--

DROP TABLE IF EXISTS `offers`;
CREATE TABLE `offers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title_en` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title_gu` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `discount_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'percentage',
  `discount_value` decimal(10,2) NOT NULL,
  `min_order_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `max_discount_amount` decimal(10,2) DEFAULT NULL,
  `banner_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description_en` text COLLATE utf8mb4_unicode_ci,
  `description_gu` text COLLATE utf8mb4_unicode_ci,
  `valid_from` datetime DEFAULT NULL,
  `valid_to` datetime DEFAULT NULL,
  `usage_limit` int DEFAULT NULL,
  `used_count` int NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `offers_code_unique` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `offers`
--

INSERT INTO `offers` (`id`, `title_en`, `title_gu`, `code`, `discount_type`, `discount_value`, `min_order_amount`, `max_discount_amount`, `banner_image`, `description_en`, `description_gu`, `valid_from`, `valid_to`, `usage_limit`, `used_count`, `is_active`, `created_at`, `updated_at`) VALUES ('1', 'Flat 20% OFF on Fresh Grocery', 'તાજી કરિયાણા પર ફ્લેટ ૨૦% છૂટ', 'FRESH20', 'percentage', '20.00', '499.00', '150.00', 'https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=600&q=80', 'Get 20% discount up to ₹150 on orders above ₹499', '₹૪૯૯ થી વધુના ઓર્ડર પર ₹૧૫૦ સુધી ૨૦% છૂટ મેળવો', '2026-09-30 12:57:40', '2027-01-02 12:57:40', '500', '14', '1', '2026-10-02 12:57:40', '2026-10-02 12:57:40');
INSERT INTO `offers` (`id`, `title_en`, `title_gu`, `code`, `discount_type`, `discount_value`, `min_order_amount`, `max_discount_amount`, `banner_image`, `description_en`, `description_gu`, `valid_from`, `valid_to`, `usage_limit`, `used_count`, `is_active`, `created_at`, `updated_at`) VALUES ('2', 'Welcome Discount ₹50 Flat', 'સ્વાગત ઓફર ₹૫૦ ફ્લેટ છૂટ', 'WELCOME50', 'flat', '50.00', '299.00', '50.00', 'https://images.unsplash.com/photo-1528735602780-2552fd46c7af?auto=format&fit=crop&w=600&q=80', 'Flat ₹50 OFF on your first purchase above ₹299', '₹૨૯૯ થી વધુના પ્રથમ ઓર્ડર પર ફ્લેટ ₹૫૦ ની છૂટ', '2026-09-22 12:57:40', '2027-04-02 12:57:40', '1000', '38', '1', '2026-10-02 12:57:40', '2026-10-02 12:57:40');
INSERT INTO `offers` (`id`, `title_en`, `title_gu`, `code`, `discount_type`, `discount_value`, `min_order_amount`, `max_discount_amount`, `banner_image`, `description_en`, `description_gu`, `valid_from`, `valid_to`, `usage_limit`, `used_count`, `is_active`, `created_at`, `updated_at`) VALUES ('3', 'Super Saver ₹100 Discount', 'સુપર સેવર ₹૧૦૦ ની છૂટ', 'SUPER100', 'flat', '100.00', '999.00', '100.00', 'https://images.unsplash.com/photo-1596040033229-a9821ebd058d?auto=format&fit=crop&w=600&q=80', 'Flat ₹100 OFF on bulk orders above ₹999', '₹૯૯૯ થી વધુના ઓર્ડર પર સીધા ₹૧૦૦ ની છૂટ', '2026-10-02 12:57:40', '2026-12-02 12:57:40', '200', '5', '1', '2026-10-02 12:57:40', '2026-10-02 12:57:40');

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
CREATE TABLE `order_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned DEFAULT NULL,
  `product_name_en` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_name_gu` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_unit` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `quantity` int NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_items_order_id_foreign` (`order_id`),
  KEY `order_items_product_id_foreign` (`product_id`),
  CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name_en`, `product_name_gu`, `product_unit`, `product_image`, `unit_price`, `quantity`, `total_price`, `created_at`, `updated_at`) VALUES ('1', '1', '1', 'Fresh Hybrid Tomatoes', 'તાજા ટામેટા', '1 kg', 'https://images.unsplash.com/photo-1592924357228-91a4daadcfea?auto=format&fit=crop&w=600&q=80', '38.00', '2', '76.00', '2026-10-02 12:57:40', '2026-10-02 12:57:40');
INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name_en`, `product_name_gu`, `product_unit`, `product_image`, `unit_price`, `quantity`, `total_price`, `created_at`, `updated_at`) VALUES ('2', '1', '7', 'Aashirvaad Shudh Chakki Whole Wheat Atta', 'આશીર્વાદ શુદ્ધ ચક્કી ઘઉંનો લોટ', '5 kg', 'https://images.unsplash.com/photo-1608686207856-001b95cf60ca?auto=format&fit=crop&w=600&q=80', '235.00', '1', '235.00', '2026-10-02 12:57:40', '2026-10-02 12:57:40');
INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name_en`, `product_name_gu`, `product_unit`, `product_image`, `unit_price`, `quantity`, `total_price`, `created_at`, `updated_at`) VALUES ('3', '1', '8', 'Premium Unpolished Tuver (Toor) Dal', 'શુદ્ધ દેશી તુવેર દાળ', '1 kg', 'https://images.unsplash.com/photo-1585994192701-f1a505c8574a?auto=format&fit=crop&w=600&q=80', '155.00', '1', '155.00', '2026-10-02 12:57:40', '2026-10-02 12:57:40');
INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name_en`, `product_name_gu`, `product_unit`, `product_image`, `unit_price`, `quantity`, `total_price`, `created_at`, `updated_at`) VALUES ('4', '1', '13', 'Wagh Bakri Premium CTC Tea', 'વાઘ બકરી પ્રીમિયમ ચા', '1 kg', 'https://images.unsplash.com/photo-1576092768241-dec231879fc3?auto=format&fit=crop&w=600&q=80', '470.00', '1', '470.00', '2026-10-02 12:57:40', '2026-10-02 12:57:40');

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `invoice_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `customer_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_phone` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_id` bigint unsigned DEFAULT NULL,
  `delivery_address` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `delivery_city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `delivery_pincode` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `delivery_lat` decimal(10,8) DEFAULT NULL,
  `delivery_lng` decimal(11,8) DEFAULT NULL,
  `delivery_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'two_hours',
  `delivery_slot` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estimated_delivery_at` timestamp NULL DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `discount_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `coupon_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `delivery_charge` decimal(10,2) NOT NULL DEFAULT '0.00',
  `tax_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total_amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'cod',
  `payment_status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `transaction_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `order_status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `cancellation_reason` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `orders_order_number_unique` (`order_number`),
  UNIQUE KEY `orders_invoice_number_unique` (`invoice_number`),
  KEY `orders_user_id_foreign` (`user_id`),
  KEY `orders_address_id_foreign` (`address_id`),
  CONSTRAINT `orders_address_id_foreign` FOREIGN KEY (`address_id`) REFERENCES `addresses` (`id`) ON DELETE SET NULL,
  CONSTRAINT `orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `order_number`, `invoice_number`, `user_id`, `customer_name`, `customer_phone`, `customer_email`, `address_id`, `delivery_address`, `delivery_city`, `delivery_pincode`, `delivery_lat`, `delivery_lng`, `delivery_type`, `delivery_slot`, `estimated_delivery_at`, `subtotal`, `discount_amount`, `coupon_code`, `delivery_charge`, `tax_amount`, `total_amount`, `payment_method`, `payment_status`, `transaction_id`, `order_status`, `notes`, `cancellation_reason`, `created_at`, `updated_at`) VALUES ('1', 'ORD-6ABFAA44748F5', 'INV-2026-00101', '3', 'Jignesh Patel', '9988776655', 'customer@gmail.com', '1', 'B-402, Shivam Heights, Opp Iskcon Temple, Bodakdev, SG Highway, Ahmedabad, Gujarat 380054', 'Ahmedabad', '380054', '23.03035700', '72.50754200', 'two_hours', 'Today Express Delivery (Within 2 Hours - By 12:30 PM)', '2026-10-02 12:30:00', '610.00', '50.00', 'WELCOME50', '0.00', '0.00', '560.00', 'cod', 'pending', NULL, 'out_for_delivery', 'Please ring the doorbell twice.', NULL, '2026-10-02 12:57:40', '2026-10-02 12:57:40');

--
-- Table structure for table `pages`
--

DROP TABLE IF EXISTS `pages`;
CREATE TABLE `pages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title_en` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title_gu` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content_en` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `content_gu` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `meta_title_en` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_title_gu` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_description_en` text COLLATE utf8mb4_unicode_ci,
  `meta_description_gu` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pages_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pages`
--

INSERT INTO `pages` (`id`, `slug`, `title_en`, `title_gu`, `content_en`, `content_gu`, `meta_title_en`, `meta_title_gu`, `meta_description_en`, `meta_description_gu`, `is_active`, `created_at`, `updated_at`) VALUES ('1', 'about-us', 'About Our Grocery Store', 'અમારા સ્ટોર વિશે (અમારા વિશે)', '<h2>Welcome to Our Fresh Express Grocery</h2><p>We are dedicated to delivering farm fresh fruits, crisp vegetables, authentic Gujarati snacks, organic dairy, and kitchen essentials straight to your doorstep.</p><p>Our unique <strong>Express 2-Hour Delivery</strong> model guarantees that any order placed before 12:00 PM noon is delivered within 2 hours by our dedicated delivery fleet. Orders placed after 12:00 PM are packed in temperature-controlled boxes and delivered the next morning between 9:00 AM and 12:00 PM.</p><h3>Why Choose Us?</h3><ul><li>100% Farm Fresh & Handpicked Quality</li><li>Direct Farm Sourcing with Fair Farmer Pricing</li><li>Bilingual English & Gujarati experience</li><li>Fast Express Delivery</li><li>Easy Returns & Replacement Guarantee</li></ul>', '<h2>અમારા ફ્રેશ એક્સપ્રેસ સ્ટોરમાં આપનું હાર્દિક સ્વાગત છે</h2><p>અમે તમારા ઘર સુધી ખેતરમાંથી સીધા ચૂંટેલા તાજા શાકભાજી, સ્વાદિષ્ટ ફળો, શુદ્ધ દેશી ડેરી, પરંપરાગત ગુજરાતી ફરસાણ અને અનાજ-કઠોળ પહોંચાડવા માટે પ્રતિબદ્ધ છીએ.</p><p>અમારી ખાસ <strong>૨ કલાક એક્સપ્રેસ ડિલિવરી</strong> સિસ્ટમ મુજબ, જો તમે બપોરે ૧૨:૦૦ વાગ્યા પહેલાં ઓર્ડર કરશો તો ૨ કલાકમાં તમારા ઘરે ડિલિવરી મળી જશે. ૧૨:૦૦ વાગ્યા પછીના ઓર્ડર બીજા દિવસે સવારે ૦૯:૦૦ થી ૧૨:૦૦ વચ્ચે પહોંચાડવામાં આવશે.</p><h3>અમારી વિશેષતાઓ:</h3><ul><li>૧૦૦% તાજી અને શ્રેષ્ઠ ગુણવત્તા</li><li>ખેડૂતો પાસેથી સીધી ખરીદી</li><li>સરળ ગુજરાતી અને અંગ્રેજી ભાષા સપોર્ટ</li><li>ઝડપી એક્સપ્રેસ ડિલિવરી</li><li>સરળ રિટર્ન અને રીપ્લેસમેન્ટ ગેરંટી</li></ul>', 'About Us - Fresh Express Grocery Store', 'અમારા વિશે - ફ્રેશ એક્સપ્રેસ કરિયાણા સ્ટોર', 'Learn more about our fresh grocery store, our express 2-hour delivery, and organic products.', 'અમારા ગ્રોસરી સ્ટોર, ૨ કલાક એક્સપ્રેસ ડિલિવરી અને તાજી પ્રોડક્ટ્સ વિશે વધુ જાણો.', '1', '2026-10-02 12:57:40', '2026-10-02 12:57:40');
INSERT INTO `pages` (`id`, `slug`, `title_en`, `title_gu`, `content_en`, `content_gu`, `meta_title_en`, `meta_title_gu`, `meta_description_en`, `meta_description_gu`, `is_active`, `created_at`, `updated_at`) VALUES ('2', 'legal-information', 'Legal Information & Terms of Service', 'કાનૂની માહિતી અને શરતો', '<h2>Legal Information & Company Disclaimer</h2><p>This platform operates in full compliance with the Consumer Protection (E-Commerce) Rules, FSSAI regulations, and Food Safety Standards of India.</p><h3>1. Registration & Licensing</h3><p>All packaged food and organic agricultural items supplied through this website and mobile application adhere to strict FSSAI hygiene guidelines.</p><h3>2. Delivery Terms</h3><p>Orders confirmed before 12:00 PM (noon) local time will be dispatched immediately for 2-hour express delivery. Any external traffic, weather, or force majeure events will be communicated to the customer via SMS / App notification.</p><h3>3. Pricing & Taxes</h3><p>All prices listed on the portal are inclusive of applicable Goods and Services Tax (GST) unless otherwise indicated.</p>', '<h2>કાનૂની માહિતી અને નીતિ-નિયમો</h2><p>આ પ્લેટફોર્મ ભારતના કન્ઝ્યુમર પ્રોટેક્શન (ઈ-કોમર્સ) નિયમો અને FSSAI ફૂડ સેફ્ટી ધોરણોનું સંપૂર્ણ પાલન કરે છે.</p><h3>૧. રજીસ્ટ્રેશન અને લાયસન્સ</h3><p>વેબસાઇટ અને મોબાઇલ એપ દ્વારા ઉપલબ્ધ તમામ ખાદ્ય પદાર્થો FSSAI ના હાઇજીન માપદંડો મુજબ તૈયાર અને સપ્લાય કરવામાં આવે છે.</p><h3>૨. ડિલિવરીની શરતો</h3><p>બપોરે ૧૨:૦૦ વાગ્યા પહેલા નોંધાયેલા ઓર્ડર્સ ૨ કલાકની એક્સપ્રેસ ડિલિવરીમાં પહોંચાડવામાં આવે છે. ટ્રાફિક કે કુદરતી પરિસ્થિતિમાં વિલંબ થાય તો ગ્રાહકને તાત્કાલિક જાણ કરવામાં આવે છે.</p><h3>૩. કિંમત અને ટેક્સ</h3><p>દર્શાવેલ તમામ કિંમતોમાં જીએસટી (GST) શામેલ છે.</p>', 'Legal Information - Fresh Express', 'કાનૂની માહિતી - ફ્રેશ એક્સપ્રેસ', 'Legal compliance and terms of service.', 'કાનૂની નિયમો અને સેવાઓની શરતો.', '1', '2026-10-02 12:57:40', '2026-10-02 12:57:40');
INSERT INTO `pages` (`id`, `slug`, `title_en`, `title_gu`, `content_en`, `content_gu`, `meta_title_en`, `meta_title_gu`, `meta_description_en`, `meta_description_gu`, `is_active`, `created_at`, `updated_at`) VALUES ('3', 'privacy-policy', 'Privacy Policy', 'પ્રાઈવસી પોલિસી (ગોપનીયતા નીતિ)', '<h2>Privacy Policy</h2><p>We respect your privacy and are committed to protecting your personal data, mobile number, delivery addresses, and payment information.</p><h3>1. Information We Collect</h3><ul><li>Mobile phone number for OTP authentication and order updates</li><li>Saved GPS and manual delivery addresses</li><li>Order history and wishlist selections</li></ul><h3>2. How We Protect Your Data</h3><p>We do not sell, rent, or trade your personal information to any third parties. All transactions and customer data are encrypted using secure SSL/TLS protocols.</p><h3>3. Location Services</h3><p>Our interactive map address picker uses your current device coordinates only to assist you in pinning your accurate delivery location.</p>', '<h2>ગોપનીયતા નીતિ (પ્રાઈવસી પોલિસી)</h2><p>અમે તમારી અંગત માહિતી, મોબાઈલ નંબર, ડિલિવરી સરનામું અને ડેટાની સુરક્ષા માટે સંપૂર્ણપણે પ્રતિબદ્ધ છીએ.</p><h3>૧. અમે કઈ માહિતી એકત્ર કરીએ છીએ?</h3><ul><li>ઓટીપી (OTP) લૉગિન અને ઓર્ડર અપડેટ્સ માટે મોબાઇલ નંબર</li><li>ડિલિવરી સરનામાં અને મેપ લોકેશન</li><li>ઓર્ડર હિસ્ટ્રી અને વિશલિસ્ટ પસંદગીઓ</li></ul><h3>૨. માહિતીની સુરક્ષા</h3><p>અમે તમારો ડેટા ક્યારેય કોઈ ત્રીજી કંપનીને વેચતા કે શેર કરતા નથી. તમામ માહિતી સુરક્ષિત એન્ક્રિપ્શન સાથે સાચવવામાં આવે છે.</p><h3>૩. લોકેશન સેવાઓ</h3><p>મેપ દ્વારા એડ્રેસ સિલેક્ટ કરવા માટે જ તમારા ડિવાઇસના લોકેશનનો ઉપયોગ થાય છે.</p>', 'Privacy Policy - Fresh Express', 'પ્રાઈવસી પોલિસી - ફ્રેશ એક્સપ્રેસ', 'Privacy policy and data protection principles.', 'ગ્રાહક ડેટા સુરક્ષા અને ગોપનીયતા નીતિ.', '1', '2026-10-02 12:57:40', '2026-10-02 12:57:40');

--
-- Table structure for table `password_reset_otps`
--

DROP TABLE IF EXISTS `password_reset_otps`;
CREATE TABLE `password_reset_otps` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `email_or_phone` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `otp` varchar(6) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` timestamp NOT NULL,
  `is_used` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `password_reset_otps_email_or_phone_index` (`email_or_phone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
CREATE TABLE `permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `display_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `group` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'general',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `display_name`, `group`, `created_at`, `updated_at`) VALUES ('1', 'manage_categories', 'Manage Categories & Subcategories', 'catalog', '2026-10-02 12:57:39', '2026-10-02 12:57:39');
INSERT INTO `permissions` (`id`, `name`, `display_name`, `group`, `created_at`, `updated_at`) VALUES ('2', 'manage_products', 'Manage Products & Gallery', 'catalog', '2026-10-02 12:57:39', '2026-10-02 12:57:39');
INSERT INTO `permissions` (`id`, `name`, `display_name`, `group`, `created_at`, `updated_at`) VALUES ('3', 'manage_stock', 'Manage Stock & Inventory', 'catalog', '2026-10-02 12:57:39', '2026-10-02 12:57:39');
INSERT INTO `permissions` (`id`, `name`, `display_name`, `group`, `created_at`, `updated_at`) VALUES ('4', 'manage_sliders', 'Manage Sliders & Banners', 'catalog', '2026-10-02 12:57:39', '2026-10-02 12:57:39');
INSERT INTO `permissions` (`id`, `name`, `display_name`, `group`, `created_at`, `updated_at`) VALUES ('5', 'manage_orders', 'Manage Orders & Status', 'sales', '2026-10-02 12:57:39', '2026-10-02 12:57:39');
INSERT INTO `permissions` (`id`, `name`, `display_name`, `group`, `created_at`, `updated_at`) VALUES ('6', 'manage_invoices', 'View & Print Invoices', 'sales', '2026-10-02 12:57:39', '2026-10-02 12:57:39');
INSERT INTO `permissions` (`id`, `name`, `display_name`, `group`, `created_at`, `updated_at`) VALUES ('7', 'manage_offers', 'Manage Offers & Coupons', 'sales', '2026-10-02 12:57:39', '2026-10-02 12:57:39');
INSERT INTO `permissions` (`id`, `name`, `display_name`, `group`, `created_at`, `updated_at`) VALUES ('8', 'manage_pages', 'Manage Static Content Pages', 'content', '2026-10-02 12:57:39', '2026-10-02 12:57:39');
INSERT INTO `permissions` (`id`, `name`, `display_name`, `group`, `created_at`, `updated_at`) VALUES ('9', 'manage_roles', 'Manage Roles & Permissions', 'security', '2026-10-02 12:57:39', '2026-10-02 12:57:39');
INSERT INTO `permissions` (`id`, `name`, `display_name`, `group`, `created_at`, `updated_at`) VALUES ('10', 'manage_users', 'Manage Users & Accounts', 'security', '2026-10-02 12:57:39', '2026-10-02 12:57:39');
INSERT INTO `permissions` (`id`, `name`, `display_name`, `group`, `created_at`, `updated_at`) VALUES ('11', 'manage_settings', 'Manage System & Theme Settings', 'settings', '2026-10-02 12:57:39', '2026-10-02 12:57:39');

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  KEY `personal_access_tokens_expires_at_index` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `product_images`
--

DROP TABLE IF EXISTS `product_images`;
CREATE TABLE `product_images` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint unsigned NOT NULL,
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_images_product_id_foreign` (`product_id`),
  CONSTRAINT `product_images_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_images`
--

INSERT INTO `product_images` (`id`, `product_id`, `image_path`, `sort_order`, `created_at`, `updated_at`) VALUES ('1', '1', 'https://images.unsplash.com/photo-1592924357228-91a4daadcfea?auto=format&fit=crop&w=600&q=80', '1', '2026-10-02 12:57:40', '2026-10-02 12:57:40');
INSERT INTO `product_images` (`id`, `product_id`, `image_path`, `sort_order`, `created_at`, `updated_at`) VALUES ('2', '2', 'https://images.unsplash.com/photo-1518977676601-b53f82aba655?auto=format&fit=crop&w=600&q=80', '1', '2026-10-02 12:57:40', '2026-10-02 12:57:40');
INSERT INTO `product_images` (`id`, `product_id`, `image_path`, `sort_order`, `created_at`, `updated_at`) VALUES ('3', '3', 'https://images.unsplash.com/photo-1611080626919-7cf5a9dbab5b?auto=format&fit=crop&w=600&q=80', '1', '2026-10-02 12:57:40', '2026-10-02 12:57:40');
INSERT INTO `product_images` (`id`, `product_id`, `image_path`, `sort_order`, `created_at`, `updated_at`) VALUES ('4', '4', 'https://images.unsplash.com/photo-1560806887-1e4cd0b6cbd6?auto=format&fit=crop&w=600&q=80', '1', '2026-10-02 12:57:40', '2026-10-02 12:57:40');
INSERT INTO `product_images` (`id`, `product_id`, `image_path`, `sort_order`, `created_at`, `updated_at`) VALUES ('5', '5', 'https://images.unsplash.com/photo-1550583724-b2692b85b150?auto=format&fit=crop&w=600&q=80', '1', '2026-10-02 12:57:40', '2026-10-02 12:57:40');
INSERT INTO `product_images` (`id`, `product_id`, `image_path`, `sort_order`, `created_at`, `updated_at`) VALUES ('6', '6', 'https://images.unsplash.com/photo-1589985270826-4b7bb135bc9d?auto=format&fit=crop&w=600&q=80', '1', '2026-10-02 12:57:40', '2026-10-02 12:57:40');
INSERT INTO `product_images` (`id`, `product_id`, `image_path`, `sort_order`, `created_at`, `updated_at`) VALUES ('7', '7', 'https://images.unsplash.com/photo-1608686207856-001b95cf60ca?auto=format&fit=crop&w=600&q=80', '1', '2026-10-02 12:57:40', '2026-10-02 12:57:40');
INSERT INTO `product_images` (`id`, `product_id`, `image_path`, `sort_order`, `created_at`, `updated_at`) VALUES ('8', '8', 'https://images.unsplash.com/photo-1585994192701-f1a505c8574a?auto=format&fit=crop&w=600&q=80', '1', '2026-10-02 12:57:40', '2026-10-02 12:57:40');
INSERT INTO `product_images` (`id`, `product_id`, `image_path`, `sort_order`, `created_at`, `updated_at`) VALUES ('9', '9', 'https://images.unsplash.com/photo-1631451095765-2c91616fc9e6?auto=format&fit=crop&w=600&q=80', '1', '2026-10-02 12:57:40', '2026-10-02 12:57:40');
INSERT INTO `product_images` (`id`, `product_id`, `image_path`, `sort_order`, `created_at`, `updated_at`) VALUES ('10', '10', 'https://images.unsplash.com/photo-1615485290382-441e4d049cb5?auto=format&fit=crop&w=600&q=80', '1', '2026-10-02 12:57:40', '2026-10-02 12:57:40');
INSERT INTO `product_images` (`id`, `product_id`, `image_path`, `sort_order`, `created_at`, `updated_at`) VALUES ('11', '11', 'https://images.unsplash.com/photo-1601050690597-df0568f70950?auto=format&fit=crop&w=600&q=80', '1', '2026-10-02 12:57:40', '2026-10-02 12:57:40');
INSERT INTO `product_images` (`id`, `product_id`, `image_path`, `sort_order`, `created_at`, `updated_at`) VALUES ('12', '12', 'https://images.unsplash.com/photo-1599488615731-7e5c2823ff28?auto=format&fit=crop&w=600&q=80', '1', '2026-10-02 12:57:40', '2026-10-02 12:57:40');
INSERT INTO `product_images` (`id`, `product_id`, `image_path`, `sort_order`, `created_at`, `updated_at`) VALUES ('13', '13', 'https://images.unsplash.com/photo-1576092768241-dec231879fc3?auto=format&fit=crop&w=600&q=80', '1', '2026-10-02 12:57:40', '2026-10-02 12:57:40');

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `category_id` bigint unsigned NOT NULL,
  `sub_category_id` bigint unsigned DEFAULT NULL,
  `name_en` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_gu` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `unit` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1 item',
  `price` decimal(10,2) NOT NULL,
  `discount_price` decimal(10,2) DEFAULT NULL,
  `stock_quantity` int NOT NULL DEFAULT '0',
  `low_stock_threshold` int NOT NULL DEFAULT '5',
  `thumbnail` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `short_description_en` text COLLATE utf8mb4_unicode_ci,
  `short_description_gu` text COLLATE utf8mb4_unicode_ci,
  `description_en` longtext COLLATE utf8mb4_unicode_ci,
  `description_gu` longtext COLLATE utf8mb4_unicode_ci,
  `is_featured` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `sku` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `products_slug_unique` (`slug`),
  KEY `products_category_id_foreign` (`category_id`),
  KEY `products_sub_category_id_foreign` (`sub_category_id`),
  CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `products_sub_category_id_foreign` FOREIGN KEY (`sub_category_id`) REFERENCES `sub_categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `category_id`, `sub_category_id`, `name_en`, `name_gu`, `slug`, `unit`, `price`, `discount_price`, `stock_quantity`, `low_stock_threshold`, `thumbnail`, `short_description_en`, `short_description_gu`, `description_en`, `description_gu`, `is_featured`, `is_active`, `sku`, `created_at`, `updated_at`) VALUES ('1', '1', '1', 'Fresh Hybrid Tomatoes', 'તાજા ટામેટા', 'fresh-hybrid-tomatoes-6866', '1 kg', '45.00', '38.00', '120', '5', 'https://images.unsplash.com/photo-1592924357228-91a4daadcfea?auto=format&fit=crop&w=600&q=80', 'Farm fresh plump, juicy red hybrid tomatoes.', 'ખેતરમાંથી સીધા તાજા, લાલ અને રસદાર ટામેટા.', 'Handpicked fresh tomatoes perfect for daily cooking, curries, soups, and fresh salads.', 'શાક, દાળ, સૂપ અને સલાડ માટે ઉત્તમ ગુણવત્તાવાળા તાજા લાલ ટામેટા.', '1', '1', 'SKU-D30A1M', '2026-10-02 12:57:40', '2026-10-02 12:57:40');
INSERT INTO `products` (`id`, `category_id`, `sub_category_id`, `name_en`, `name_gu`, `slug`, `unit`, `price`, `discount_price`, `stock_quantity`, `low_stock_threshold`, `thumbnail`, `short_description_en`, `short_description_gu`, `description_en`, `description_gu`, `is_featured`, `is_active`, `sku`, `created_at`, `updated_at`) VALUES ('2', '1', '1', 'Organic Fresh Potatoes (Batata)', 'તાજા બટાકા', 'organic-fresh-potatoes-batata-3442', '1 kg', '35.00', '29.00', '200', '5', 'https://images.unsplash.com/photo-1518977676601-b53f82aba655?auto=format&fit=crop&w=600&q=80', 'Clean, smooth-skinned golden cooking potatoes.', 'સાફ અને સ્વાદિષ્ટ દેશી બટાકા.', 'High quality potatoes suitable for boiling, frying, curries, and snacks.', 'શાક, પરાઠા અને નાસ્તા માટે શ્રેષ્ઠ પસંદગી.', '1', '1', 'SKU-GAXQVE', '2026-10-02 12:57:40', '2026-10-02 12:57:40');
INSERT INTO `products` (`id`, `category_id`, `sub_category_id`, `name_en`, `name_gu`, `slug`, `unit`, `price`, `discount_price`, `stock_quantity`, `low_stock_threshold`, `thumbnail`, `short_description_en`, `short_description_gu`, `description_en`, `description_gu`, `is_featured`, `is_active`, `sku`, `created_at`, `updated_at`) VALUES ('3', '1', '2', 'Sweet Kinnow Oranges (Santra)', 'મીઠા સંતરા', 'sweet-kinnow-oranges-santra-4655', '1 kg (approx 5-6 pcs)', '90.00', '75.00', '45', '5', 'https://images.unsplash.com/photo-1611080626919-7cf5a9dbab5b?auto=format&fit=crop&w=600&q=80', 'Sweet, vitamin C packed juicy oranges.', 'વિટામિન સી થી ભરપૂર મીઠા અને રસદાર સંતરા.', 'Naturally ripened, fresh and juicy oranges ideal for fresh breakfast juice and direct snacking.', 'સ્વાસ્થ્ય માટે ઉત્તમ, તાજા રસદાર સંતરા.', '1', '1', 'SKU-VJZXXL', '2026-10-02 12:57:40', '2026-10-02 12:57:40');
INSERT INTO `products` (`id`, `category_id`, `sub_category_id`, `name_en`, `name_gu`, `slug`, `unit`, `price`, `discount_price`, `stock_quantity`, `low_stock_threshold`, `thumbnail`, `short_description_en`, `short_description_gu`, `description_en`, `description_gu`, `is_featured`, `is_active`, `sku`, `created_at`, `updated_at`) VALUES ('4', '1', '2', 'Kashmiri Royal Delicious Apples', 'કાશ્મીરી સફરજન', 'kashmiri-royal-delicious-apples-7435', '1 kg (4 pcs)', '180.00', '149.00', '30', '5', 'https://images.unsplash.com/photo-1560806887-1e4cd0b6cbd6?auto=format&fit=crop&w=600&q=80', 'Crisp, sweet, and deep red Kashmiri apples.', 'મીઠા, ક્રિસ્પી અને તાજા કાશ્મીરી સફરજન.', 'Directly sourced from Kashmir valleys. Crunchy texture with natural sweetness.', 'કાશ્મીરના બગીચાઓમાંથી લવાયેલા એકદમ તાજા અને સ્વાદિષ્ટ સફરજન.', '1', '1', 'SKU-7L2185', '2026-10-02 12:57:40', '2026-10-02 12:57:40');
INSERT INTO `products` (`id`, `category_id`, `sub_category_id`, `name_en`, `name_gu`, `slug`, `unit`, `price`, `discount_price`, `stock_quantity`, `low_stock_threshold`, `thumbnail`, `short_description_en`, `short_description_gu`, `description_en`, `description_gu`, `is_featured`, `is_active`, `sku`, `created_at`, `updated_at`) VALUES ('5', '2', '4', 'Amul Taaza Homogenised Toned Milk', 'અમૂલ તાઝા ટોન્ડ દૂધ', 'amul-taaza-homogenised-toned-milk-1835', '500 ml', '27.00', NULL, '150', '5', 'https://images.unsplash.com/photo-1550583724-b2692b85b150?auto=format&fit=crop&w=600&q=80', 'Pasteurised toned milk with 3.0% fat, 8.5% SNF.', 'શુદ્ધ પાશ્ચુરાઇઝ્ડ ટોન્ડ દૂધ.', 'Healthy, pure milk for tea, coffee, drinking, and breakfast cereals.', 'રોજિંદા ઉપયોગ, ચા અને કોફી માટે ઉત્તમ અમૂલ દૂધ.', '1', '1', 'SKU-G2TCYO', '2026-10-02 12:57:40', '2026-10-02 12:57:40');
INSERT INTO `products` (`id`, `category_id`, `sub_category_id`, `name_en`, `name_gu`, `slug`, `unit`, `price`, `discount_price`, `stock_quantity`, `low_stock_threshold`, `thumbnail`, `short_description_en`, `short_description_gu`, `description_en`, `description_gu`, `is_featured`, `is_active`, `sku`, `created_at`, `updated_at`) VALUES ('6', '2', '5', 'Fresh Malai Paneer', 'તાજું મલાઈ પનીર', 'fresh-malai-paneer-1537', '200 g', '95.00', '85.00', '40', '5', 'https://images.unsplash.com/photo-1589985270826-4b7bb135bc9d?auto=format&fit=crop&w=600&q=80', 'Soft, melt-in-mouth cottage cheese blocks.', 'નરમ અને સ્વાદિષ્ટ તાજું મલાઈ પનીર.', 'Rich in protein and calcium. Soft textured paneer ideal for Palak Paneer, Paneer Butter Masala and Tikka.', 'પાલક પનીર, પનીર ટીક્કા અને શાક માટે બેસ્ટ મલાઈ પનીર.', '1', '1', 'SKU-PD7AUG', '2026-10-02 12:57:40', '2026-10-02 12:57:40');
INSERT INTO `products` (`id`, `category_id`, `sub_category_id`, `name_en`, `name_gu`, `slug`, `unit`, `price`, `discount_price`, `stock_quantity`, `low_stock_threshold`, `thumbnail`, `short_description_en`, `short_description_gu`, `description_en`, `description_gu`, `is_featured`, `is_active`, `sku`, `created_at`, `updated_at`) VALUES ('7', '3', '7', 'Aashirvaad Shudh Chakki Whole Wheat Atta', 'આશીર્વાદ શુદ્ધ ચક્કી ઘઉંનો લોટ', 'aashirvaad-shudh-chakki-whole-wheat-atta-4187', '5 kg', '260.00', '235.00', '60', '5', 'https://images.unsplash.com/photo-1608686207856-001b95cf60ca?auto=format&fit=crop&w=600&q=80', '100% pure whole wheat grain atta for soft rotis.', '૧૦૦% શુદ્ધ ઘઉંનો લોટ, નરમ રોટલી માટે શ્રેષ્ઠ.', 'Made from heavy sun-kissed golden grains, ensuring rotis remain soft for hours.', 'નરમ અને સ્વાદિષ્ટ રોટલીઓ માટે ઉચ્ચ ગુણવત્તાવાળો ચક્કી આટો.', '1', '1', 'SKU-VAMSA0', '2026-10-02 12:57:40', '2026-10-02 12:57:40');
INSERT INTO `products` (`id`, `category_id`, `sub_category_id`, `name_en`, `name_gu`, `slug`, `unit`, `price`, `discount_price`, `stock_quantity`, `low_stock_threshold`, `thumbnail`, `short_description_en`, `short_description_gu`, `description_en`, `description_gu`, `is_featured`, `is_active`, `sku`, `created_at`, `updated_at`) VALUES ('8', '3', '9', 'Premium Unpolished Tuver (Toor) Dal', 'શુદ્ધ દેશી તુવેર દાળ', 'premium-unpolished-tuver-toor-dal-5125', '1 kg', '175.00', '155.00', '85', '5', 'https://images.unsplash.com/photo-1585994192701-f1a505c8574a?auto=format&fit=crop&w=600&q=80', 'Unpolished, pesticide-free Vasad style Tuver Dal.', 'વાસદની ફેમસ દેશી તુવેર દાળ.', 'Rich in protein, cooks fast and gives the authentic Gujarati Dal aroma and taste.', 'ગુજરાતી દાળ અને ખીચડી માટે ઉત્તમ ગુણવત્તાવાળી તુવેર દાળ.', '1', '1', 'SKU-OLJCIP', '2026-10-02 12:57:40', '2026-10-02 12:57:40');
INSERT INTO `products` (`id`, `category_id`, `sub_category_id`, `name_en`, `name_gu`, `slug`, `unit`, `price`, `discount_price`, `stock_quantity`, `low_stock_threshold`, `thumbnail`, `short_description_en`, `short_description_gu`, `description_en`, `description_gu`, `is_featured`, `is_active`, `sku`, `created_at`, `updated_at`) VALUES ('9', '4', '10', 'Gir Cow A2 Desi Vedic Bilona Ghee', 'ગીર ગાયનું શુદ્ધ વૈદિક બિલોણા ઘી', 'gir-cow-a2-desi-vedic-bilona-ghee-7701', '500 ml Glass Jar', '999.00', '849.00', '25', '5', 'https://images.unsplash.com/photo-1631451095765-2c91616fc9e6?auto=format&fit=crop&w=600&q=80', 'Traditional bilona churned A2 cultured grass-fed ghee.', 'પરંપરાગત માખણ વલોવીને બનાવેલું શુદ્ધ દેશી ગાયનું ઘી.', 'Prepared strictly using ancient Ayurvedic Bilona method with curd from indigenous Gir cows.', 'ઔષધીય ગુણોથી ભરપૂર, સુગંધીદાર દાણાદાર શુદ્ધ દેશી ઘી.', '1', '1', 'SKU-FXAGH7', '2026-10-02 12:57:40', '2026-10-02 12:57:40');
INSERT INTO `products` (`id`, `category_id`, `sub_category_id`, `name_en`, `name_gu`, `slug`, `unit`, `price`, `discount_price`, `stock_quantity`, `low_stock_threshold`, `thumbnail`, `short_description_en`, `short_description_gu`, `description_en`, `description_gu`, `is_featured`, `is_active`, `sku`, `created_at`, `updated_at`) VALUES ('10', '4', '11', 'Resham Patti Special Red Chili Powder', 'રેશમ પટ્ટી સ્પેશિયલ લાલ મરચું પાવડર', 'resham-patti-special-red-chili-powder-1332', '500 g', '210.00', '189.00', '50', '5', 'https://images.unsplash.com/photo-1615485290382-441e4d049cb5?auto=format&fit=crop&w=600&q=80', 'Vibrant natural red color with balanced spice level.', 'કુદરતી લાલ રંગ અને સ્વાદિષ્ટ તીખાશ સાથે.', '100% natural, no artificial color, gives rich color and authentic taste to curries.', 'કોઈ પણ કૃત્રિમ રંગ વગરનું શુદ્ધ મરચું પાવડર.', '0', '1', 'SKU-TDUL3G', '2026-10-02 12:57:40', '2026-10-02 12:57:40');
INSERT INTO `products` (`id`, `category_id`, `sub_category_id`, `name_en`, `name_gu`, `slug`, `unit`, `price`, `discount_price`, `stock_quantity`, `low_stock_threshold`, `thumbnail`, `short_description_en`, `short_description_gu`, `description_en`, `description_gu`, `is_featured`, `is_active`, `sku`, `created_at`, `updated_at`) VALUES ('11', '5', '13', 'Methi Masala Roasted Wheat Khakhra', 'મેથી મસાલા શેકેલા ખાખરા', 'methi-masala-roasted-wheat-khakhra-4658', '500 g (Vacuum Pack)', '140.00', '120.00', '70', '5', 'https://images.unsplash.com/photo-1601050690597-df0568f70950?auto=format&fit=crop&w=600&q=80', 'Crispy 100% whole wheat roasted khakhra with methi and spices.', 'મેથી અને મસાલા સાથે શેકેલા કરકરા ખાખરા.', 'Healthy breakfast snack. Low calorie, vacuum sealed for extra freshness and crunchiness.', 'સવારના નાસ્તા અને ચા માટે બેસ્ટ હેલ્ધી ખાખરા.', '1', '1', 'SKU-IQNVNV', '2026-10-02 12:57:40', '2026-10-02 12:57:40');
INSERT INTO `products` (`id`, `category_id`, `sub_category_id`, `name_en`, `name_gu`, `slug`, `unit`, `price`, `discount_price`, `stock_quantity`, `low_stock_threshold`, `thumbnail`, `short_description_en`, `short_description_gu`, `description_en`, `description_gu`, `is_featured`, `is_active`, `sku`, `created_at`, `updated_at`) VALUES ('12', '5', '14', 'Bhavnagri Fresh Gathiya', 'ભાવનગરી સ્પેશિયલ ગાંઠિયા', 'bhavnagri-fresh-gathiya-2673', '400 g', '130.00', '110.00', '55', '5', 'https://images.unsplash.com/photo-1599488615731-7e5c2823ff28?auto=format&fit=crop&w=600&q=80', 'Famous soft & melt-in-mouth Bhavnagri gathiya made with pure groundnut oil.', 'શુદ્ધ સીંગતેલમાં બનેલા ભાવનગરી સ્વાદિષ્ટ ગાંઠિયા.', 'Authentic taste of Bhavnagar, made fresh with carom seeds (ajwain) and black pepper.', 'સંભારો અને તળેલા મરચા સાથે ખાવાની અસલ મજા.', '1', '1', 'SKU-FTOSHI', '2026-10-02 12:57:40', '2026-10-02 12:57:40');
INSERT INTO `products` (`id`, `category_id`, `sub_category_id`, `name_en`, `name_gu`, `slug`, `unit`, `price`, `discount_price`, `stock_quantity`, `low_stock_threshold`, `thumbnail`, `short_description_en`, `short_description_gu`, `description_en`, `description_gu`, `is_featured`, `is_active`, `sku`, `created_at`, `updated_at`) VALUES ('13', '6', '16', 'Wagh Bakri Premium CTC Tea', 'વાઘ બકરી પ્રીમિયમ ચા', 'wagh-bakri-premium-ctc-tea-3302', '1 kg', '520.00', '470.00', '80', '5', 'https://images.unsplash.com/photo-1576092768241-dec231879fc3?auto=format&fit=crop&w=600&q=80', 'Strong CTC leaf blend with rich aroma and golden color.', 'કડક અને સ્વાદિષ્ટ વાઘ બકરી ચા.', 'Gujarats beloved morning tea, carefully selected from the highest grade Assam tea gardens.', 'સવારને તાજગીભરી બનાવવા માટે દરેક ગુજરાતીની મનપસંદ ચા.', '1', '1', 'SKU-BWTCJ0', '2026-10-02 12:57:40', '2026-10-02 12:57:40');

--
-- Table structure for table `role_has_permissions`
--

DROP TABLE IF EXISTS `role_has_permissions`;
CREATE TABLE `role_has_permissions` (
  `role_id` bigint unsigned NOT NULL,
  `permission_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`permission_id`),
  KEY `role_has_permissions_permission_id_foreign` (`permission_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_has_permissions`
--

INSERT INTO `role_has_permissions` (`role_id`, `permission_id`) VALUES ('1', '1');
INSERT INTO `role_has_permissions` (`role_id`, `permission_id`) VALUES ('2', '1');
INSERT INTO `role_has_permissions` (`role_id`, `permission_id`) VALUES ('1', '2');
INSERT INTO `role_has_permissions` (`role_id`, `permission_id`) VALUES ('2', '2');
INSERT INTO `role_has_permissions` (`role_id`, `permission_id`) VALUES ('1', '3');
INSERT INTO `role_has_permissions` (`role_id`, `permission_id`) VALUES ('2', '3');
INSERT INTO `role_has_permissions` (`role_id`, `permission_id`) VALUES ('1', '4');
INSERT INTO `role_has_permissions` (`role_id`, `permission_id`) VALUES ('2', '4');
INSERT INTO `role_has_permissions` (`role_id`, `permission_id`) VALUES ('1', '5');
INSERT INTO `role_has_permissions` (`role_id`, `permission_id`) VALUES ('2', '5');
INSERT INTO `role_has_permissions` (`role_id`, `permission_id`) VALUES ('1', '6');
INSERT INTO `role_has_permissions` (`role_id`, `permission_id`) VALUES ('2', '6');
INSERT INTO `role_has_permissions` (`role_id`, `permission_id`) VALUES ('1', '7');
INSERT INTO `role_has_permissions` (`role_id`, `permission_id`) VALUES ('2', '7');
INSERT INTO `role_has_permissions` (`role_id`, `permission_id`) VALUES ('1', '8');
INSERT INTO `role_has_permissions` (`role_id`, `permission_id`) VALUES ('2', '8');
INSERT INTO `role_has_permissions` (`role_id`, `permission_id`) VALUES ('1', '9');
INSERT INTO `role_has_permissions` (`role_id`, `permission_id`) VALUES ('1', '10');
INSERT INTO `role_has_permissions` (`role_id`, `permission_id`) VALUES ('1', '11');

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `display_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `display_name`, `description`, `created_at`, `updated_at`) VALUES ('1', 'super_admin', 'Super Admin', 'Full control over all system modules, settings, users, and roles', '2026-10-02 12:57:39', '2026-10-02 12:57:39');
INSERT INTO `roles` (`id`, `name`, `display_name`, `description`, `created_at`, `updated_at`) VALUES ('2', 'admin', 'Store Admin', 'Can manage products, categories, stock, orders, offers, and pages', '2026-10-02 12:57:39', '2026-10-02 12:57:39');
INSERT INTO `roles` (`id`, `name`, `display_name`, `description`, `created_at`, `updated_at`) VALUES ('3', 'user', 'Customer / User', 'Can browse catalog, cart, wishlist, checkout and manage profile', '2026-10-02 12:57:39', '2026-10-02 12:57:39');

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES ('3EelkszJ7VwGthf35sQHsBbWO8JRf9WNMJHy0RR9', NULL, '127.0.0.1', 'Go-http-client/1.1', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiTE5kckpDNEszOUJHbU5nbmNRUTF5MlV1ZG1XMU9HNnFvdkVCNnlvUyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzM6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi9sb2dpbiI7czo1OiJyb3V0ZSI7czoxMToiYWRtaW4ubG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', '1790947547');
INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES ('8Ah7ufnfZy2ourYqzWX5DVMjrmRlFODz0brzJgn4', NULL, '127.0.0.1', 'Go-http-client/1.1', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiSVgxTzdqNUxYbWxLeUQ1ZUs5THF4VlVvNGpCeXE1ODNTeVBqMTZuSyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7czo0OiJob21lIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', '1790947538');
INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES ('f99Y2gYzbnU16Zn5wNQPJh0Y7Z7mhE2JZ7AkRyhU', NULL, '127.0.0.1', 'Go-http-client/1.1', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoieDBSVnlLV3RkSG51RHJZU3VEdzNSSkVycUQ0bFBodU50Tk0xVGZHVyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', '1790947547');
INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES ('mt3Cwmf1ppDvCFqXH42isjv3XTA8QDKqh5zSaPgJ', '2', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', 'YTo2OntzOjY6Il90b2tlbiI7czo0MDoiQ05Ba0V5UDVqS1lab3BKSHpyd2kzcmNWRFhGdHdycmlHUDE4bzc5ZCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzU6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi9zbGlkZXJzIjtzOjU6InJvdXRlIjtzOjE5OiJhZG1pbi5zbGlkZXJzLmluZGV4Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MjtzOjE3OiJwYXNzd29yZF9oYXNoX3dlYiI7czo2NDoiZTE1MWU3MWMyMGVhZTM1M2RlNzYzNjAxOTc4Yzc2NWY1ZDA2YzljYmJhMTYxODcwMTQxZmIxOGNkZDgxNTVlYSI7czo2OiJsb2NhbGUiO3M6MjoiZ3UiO30=', '1790948522');
INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES ('sRQFNftJSX0YYs7YwQPZMSDexoHO2Kfa2PtCXfSy', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiUkRHVmhTc3BiME9zd1RnRW9lQ1NHcFZGWThIUXltMzhzSXNuUTBqViI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjM6Imh0dHA6Ly90ZXN0aW5nX3Byby50ZXN0IjtzOjU6InJvdXRlIjtzOjQ6ImhvbWUiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', '1790946769');
INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES ('uzxXmTzoucVcdNMfxVwxPTawXbiBTdvWOIonaU2W', NULL, '127.0.0.1', 'Go-http-client/1.1', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoiTzQwWHl0SldCOVQ2ZGkzcUNZMGRPZzdlY2RSdFVUOG9BMjRSS1pFUSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', '1790947538');

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci,
  `group` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'general',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `settings_key_unique` (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `key`, `value`, `group`, `created_at`, `updated_at`) VALUES ('1', 'theme_primary_color', '#000000', 'theme', '2026-10-02 12:57:39', '2026-10-02 12:57:39');
INSERT INTO `settings` (`id`, `key`, `value`, `group`, `created_at`, `updated_at`) VALUES ('2', 'theme_hover_color', '#a1a1a1', 'theme', '2026-10-02 12:57:39', '2026-10-02 12:57:39');
INSERT INTO `settings` (`id`, `key`, `value`, `group`, `created_at`, `updated_at`) VALUES ('3', 'sidebar_bg_color', '#000000', 'theme', '2026-10-02 12:57:39', '2026-10-02 12:57:39');
INSERT INTO `settings` (`id`, `key`, `value`, `group`, `created_at`, `updated_at`) VALUES ('4', 'sidebar_active_color', '#add8e6', 'theme', '2026-10-02 12:57:39', '2026-10-02 12:57:39');
INSERT INTO `settings` (`id`, `key`, `value`, `group`, `created_at`, `updated_at`) VALUES ('5', 'footer_copyright_prefix', '© 2026, made with ❤️ by', 'footer', '2026-10-02 12:57:39', '2026-10-02 12:57:39');
INSERT INTO `settings` (`id`, `key`, `value`, `group`, `created_at`, `updated_at`) VALUES ('6', 'footer_creator_name', 'Decent Infoways', 'footer', '2026-10-02 12:57:39', '2026-10-02 12:57:39');
INSERT INTO `settings` (`id`, `key`, `value`, `group`, `created_at`, `updated_at`) VALUES ('7', 'footer_creator_url', 'https://decentinfoways.com', 'footer', '2026-10-02 12:57:39', '2026-10-02 12:57:39');
INSERT INTO `settings` (`id`, `key`, `value`, `group`, `created_at`, `updated_at`) VALUES ('8', 'theme_mode', 'system', 'theme', '2026-10-02 12:57:39', '2026-10-02 12:57:39');

--
-- Table structure for table `sliders`
--

DROP TABLE IF EXISTS `sliders`;
CREATE TABLE `sliders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title_en` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title_gu` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subtitle_en` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subtitle_gu` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `link_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'none',
  `link_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `target_id` bigint unsigned DEFAULT NULL,
  `badge_en` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `badge_gu` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_order` int NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sliders`
--

INSERT INTO `sliders` (`id`, `title_en`, `title_gu`, `subtitle_en`, `subtitle_gu`, `image`, `link_type`, `link_url`, `target_id`, `badge_en`, `badge_gu`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES ('1', 'Farm Fresh Fruits & Vegetables', 'ખેતરમાંથી સીધા તાજા ફળો અને શાકભાજી', 'Express 2-Hour Delivery Before 12 PM', 'બપોરે ૧૨ વાગ્યા પહેલા ઓર્ડર કરો અને ૨ કલાકમાં મેળવો', 'https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=1200&q=80', 'category', NULL, '1', '30% OFF Today', 'આજે ૩૦% સુધી છૂટ', '1', '1', '2026-10-02 12:57:40', '2026-10-02 12:57:40');
INSERT INTO `sliders` (`id`, `title_en`, `title_gu`, `subtitle_en`, `subtitle_gu`, `image`, `link_type`, `link_url`, `target_id`, `badge_en`, `badge_gu`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES ('2', 'Pure Dairy & Organic Essentials', 'શુદ્ધ દેશી ડેરી અને ઓર્ગેનિક વસ્તુઓ', 'Pure A2 Milk, Fresh Paneer & Gir Cow Ghee', 'શુદ્ધ દૂધ, તાજું પનીર અને ગીર ગાયનું શુદ્ધ ઘી', 'https://images.unsplash.com/photo-1528735602780-2552fd46c7af?auto=format&fit=crop&w=1200&q=80', 'category', NULL, '2', 'Fresh Every Morning', 'દરરોજ સવારે તાજું', '2', '1', '2026-10-02 12:57:40', '2026-10-02 12:57:40');
INSERT INTO `sliders` (`id`, `title_en`, `title_gu`, `subtitle_en`, `subtitle_gu`, `image`, `link_type`, `link_url`, `target_id`, `badge_en`, `badge_gu`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES ('3', 'Authentic Gujarati Spices & Grains', 'ગુજરાતી પરંપરાગત મસાલા અને અનાજ', 'Premium Quality Unpolished Dals & Handpicked Spices', 'શ્રેષ્ઠ ગુણવત્તાવાળા કઠોળ અને હાથથી પસંદ કરેલ મસાલા', 'https://images.unsplash.com/photo-1596040033229-a9821ebd058d?auto=format&fit=crop&w=1200&q=80', 'offer', NULL, '1', 'Best Price Guaranteed', 'શ્રેષ્ઠ ભાવની ખાતરી', '3', '1', '2026-10-02 12:57:40', '2026-10-02 12:57:40');

--
-- Table structure for table `sub_categories`
--

DROP TABLE IF EXISTS `sub_categories`;
CREATE TABLE `sub_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `category_id` bigint unsigned NOT NULL,
  `name_en` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_gu` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description_en` text COLLATE utf8mb4_unicode_ci,
  `description_gu` text COLLATE utf8mb4_unicode_ci,
  `sort_order` int NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sub_categories_slug_unique` (`slug`),
  KEY `sub_categories_category_id_foreign` (`category_id`),
  CONSTRAINT `sub_categories_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sub_categories`
--

INSERT INTO `sub_categories` (`id`, `category_id`, `name_en`, `name_gu`, `slug`, `image`, `description_en`, `description_gu`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES ('1', '1', 'Fresh Vegetables', 'તાજા શાકભાજી', 'fresh-vegetables-689', 'https://images.unsplash.com/photo-1566385101042-1a0aa0c1268c?auto=format&fit=crop&w=400&q=80', NULL, NULL, '1', '1', '2026-10-02 12:57:40', '2026-10-02 12:57:40');
INSERT INTO `sub_categories` (`id`, `category_id`, `name_en`, `name_gu`, `slug`, `image`, `description_en`, `description_gu`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES ('2', '1', 'Fresh Fruits', 'તાજા ફળો', 'fresh-fruits-898', 'https://images.unsplash.com/photo-1619566636858-adf3ef46400b?auto=format&fit=crop&w=400&q=80', NULL, NULL, '2', '1', '2026-10-02 12:57:40', '2026-10-02 12:57:40');
INSERT INTO `sub_categories` (`id`, `category_id`, `name_en`, `name_gu`, `slug`, `image`, `description_en`, `description_gu`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES ('3', '1', 'Exotic & Organic', 'ઓર્ગેનિક અને સ્પેશિયલ', 'exotic-organic-225', 'https://images.unsplash.com/photo-1518843875459-f738682238a6?auto=format&fit=crop&w=400&q=80', NULL, NULL, '3', '1', '2026-10-02 12:57:40', '2026-10-02 12:57:40');
INSERT INTO `sub_categories` (`id`, `category_id`, `name_en`, `name_gu`, `slug`, `image`, `description_en`, `description_gu`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES ('4', '2', 'Milk & Curd', 'દૂધ અને દહીં', 'milk-curd-790', 'https://images.unsplash.com/photo-1563636619-e9143da7973b?auto=format&fit=crop&w=400&q=80', NULL, NULL, '1', '1', '2026-10-02 12:57:40', '2026-10-02 12:57:40');
INSERT INTO `sub_categories` (`id`, `category_id`, `name_en`, `name_gu`, `slug`, `image`, `description_en`, `description_gu`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES ('5', '2', 'Paneer & Butter', 'પનીર અને માખણ', 'paneer-butter-621', 'https://images.unsplash.com/photo-1589985270826-4b7bb135bc9d?auto=format&fit=crop&w=400&q=80', NULL, NULL, '2', '1', '2026-10-02 12:57:40', '2026-10-02 12:57:40');
INSERT INTO `sub_categories` (`id`, `category_id`, `name_en`, `name_gu`, `slug`, `image`, `description_en`, `description_gu`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES ('6', '2', 'Bakery & Bread', 'બેકરી અને બ્રેડ', 'bakery-bread-681', 'https://images.unsplash.com/photo-1509440159596-0249088772ff?auto=format&fit=crop&w=400&q=80', NULL, NULL, '3', '1', '2026-10-02 12:57:40', '2026-10-02 12:57:40');
INSERT INTO `sub_categories` (`id`, `category_id`, `name_en`, `name_gu`, `slug`, `image`, `description_en`, `description_gu`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES ('7', '3', 'Atta & Flours', 'લોટ અને બેસન', 'atta-flours-751', 'https://images.unsplash.com/photo-1608686207856-001b95cf60ca?auto=format&fit=crop&w=400&q=80', NULL, NULL, '1', '1', '2026-10-02 12:57:40', '2026-10-02 12:57:40');
INSERT INTO `sub_categories` (`id`, `category_id`, `name_en`, `name_gu`, `slug`, `image`, `description_en`, `description_gu`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES ('8', '3', 'Rice & Poha', 'ચોખા અને પૌંઆ', 'rice-poha-952', 'https://images.unsplash.com/photo-1586201375761-83865001e31c?auto=format&fit=crop&w=400&q=80', NULL, NULL, '2', '1', '2026-10-02 12:57:40', '2026-10-02 12:57:40');
INSERT INTO `sub_categories` (`id`, `category_id`, `name_en`, `name_gu`, `slug`, `image`, `description_en`, `description_gu`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES ('9', '3', 'Dals & Pulses', 'દાળ અને કઠોળ', 'dals-pulses-800', 'https://images.unsplash.com/photo-1585994192701-f1a505c8574a?auto=format&fit=crop&w=400&q=80', NULL, NULL, '3', '1', '2026-10-02 12:57:40', '2026-10-02 12:57:40');
INSERT INTO `sub_categories` (`id`, `category_id`, `name_en`, `name_gu`, `slug`, `image`, `description_en`, `description_gu`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES ('10', '4', 'Cooking Oils & Ghee', 'રસોઈનું તેલ અને ઘી', 'cooking-oils-ghee-762', 'https://images.unsplash.com/photo-1474979266404-7eaacbcd87c5?auto=format&fit=crop&w=400&q=80', NULL, NULL, '1', '1', '2026-10-02 12:57:40', '2026-10-02 12:57:40');
INSERT INTO `sub_categories` (`id`, `category_id`, `name_en`, `name_gu`, `slug`, `image`, `description_en`, `description_gu`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES ('11', '4', 'Powdered Spices', 'દળેલા મસાલા', 'powdered-spices-270', 'https://images.unsplash.com/photo-1615485290382-441e4d049cb5?auto=format&fit=crop&w=400&q=80', NULL, NULL, '2', '1', '2026-10-02 12:57:40', '2026-10-02 12:57:40');
INSERT INTO `sub_categories` (`id`, `category_id`, `name_en`, `name_gu`, `slug`, `image`, `description_en`, `description_gu`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES ('12', '4', 'Whole Spices', 'આખા મસાલા', 'whole-spices-227', 'https://images.unsplash.com/photo-1509358271058-acd22cc93898?auto=format&fit=crop&w=400&q=80', NULL, NULL, '3', '1', '2026-10-02 12:57:40', '2026-10-02 12:57:40');
INSERT INTO `sub_categories` (`id`, `category_id`, `name_en`, `name_gu`, `slug`, `image`, `description_en`, `description_gu`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES ('13', '5', 'Khakhra & Papad', 'ખાખરા અને પાપડ', 'khakhra-papad-944', 'https://images.unsplash.com/photo-1601050690597-df0568f70950?auto=format&fit=crop&w=400&q=80', NULL, NULL, '1', '1', '2026-10-02 12:57:40', '2026-10-02 12:57:40');
INSERT INTO `sub_categories` (`id`, `category_id`, `name_en`, `name_gu`, `slug`, `image`, `description_en`, `description_gu`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES ('14', '5', 'Namkeen & Gathiya', 'નમકીન અને ગાંઠિયા', 'namkeen-gathiya-821', 'https://images.unsplash.com/photo-1599488615731-7e5c2823ff28?auto=format&fit=crop&w=400&q=80', NULL, NULL, '2', '1', '2026-10-02 12:57:40', '2026-10-02 12:57:40');
INSERT INTO `sub_categories` (`id`, `category_id`, `name_en`, `name_gu`, `slug`, `image`, `description_en`, `description_gu`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES ('15', '5', 'Biscuits & Cookies', 'બિસ્કિટ અને કુકીઝ', 'biscuits-cookies-332', 'https://images.unsplash.com/photo-1558961363-fa8fdf82db35?auto=format&fit=crop&w=400&q=80', NULL, NULL, '3', '1', '2026-10-02 12:57:40', '2026-10-02 12:57:40');
INSERT INTO `sub_categories` (`id`, `category_id`, `name_en`, `name_gu`, `slug`, `image`, `description_en`, `description_gu`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES ('16', '6', 'Tea & Chai Masala', 'ચા અને ચા મસાલો', 'tea-chai-masala-524', 'https://images.unsplash.com/photo-1576092768241-dec231879fc3?auto=format&fit=crop&w=400&q=80', NULL, NULL, '1', '1', '2026-10-02 12:57:40', '2026-10-02 12:57:40');
INSERT INTO `sub_categories` (`id`, `category_id`, `name_en`, `name_gu`, `slug`, `image`, `description_en`, `description_gu`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES ('17', '6', 'Juices & Syrups', 'જ્યુસ અને શરબત', 'juices-syrups-461', 'https://images.unsplash.com/photo-1613478223719-2ab802602423?auto=format&fit=crop&w=400&q=80', NULL, NULL, '2', '1', '2026-10-02 12:57:40', '2026-10-02 12:57:40');

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `otp` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `otp_expires_at` timestamp NULL DEFAULT NULL,
  `role` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'customer',
  `role_id` bigint unsigned DEFAULT NULL,
  `language` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'en',
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_phone_unique` (`phone`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_role_id_foreign` (`role_id`),
  CONSTRAINT `users_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `phone`, `email`, `email_verified_at`, `password`, `otp`, `otp_expires_at`, `role`, `role_id`, `language`, `avatar`, `is_active`, `remember_token`, `created_at`, `updated_at`) VALUES ('1', 'Super Administrator', '9876500000', 'superadmin@grocery.com', NULL, '$2y$12$jfLl8pgEOCUnabukbBDZAOZO6LX1Ai.ltqaBvhYqh/ceJXU4cGWie', NULL, NULL, 'super_admin', '1', 'en', NULL, '1', NULL, '2026-10-02 12:57:39', '2026-10-02 12:57:39');
INSERT INTO `users` (`id`, `name`, `phone`, `email`, `email_verified_at`, `password`, `otp`, `otp_expires_at`, `role`, `role_id`, `language`, `avatar`, `is_active`, `remember_token`, `created_at`, `updated_at`) VALUES ('2', 'Store Administrator', '9876543210', 'admin@grocery.com', NULL, '$2y$12$shsJ8N/xGnoxnVOdD437Tu12QAGbm8ip/MCUxtBY0wSzW3exRMGvK', NULL, NULL, 'admin', '2', 'gu', NULL, '1', NULL, '2026-10-02 12:57:40', '2026-10-02 13:14:27');
INSERT INTO `users` (`id`, `name`, `phone`, `email`, `email_verified_at`, `password`, `otp`, `otp_expires_at`, `role`, `role_id`, `language`, `avatar`, `is_active`, `remember_token`, `created_at`, `updated_at`) VALUES ('3', 'Jignesh Patel', '9988776655', 'customer@gmail.com', NULL, '$2y$12$uRsaknL7BwWT/sGerLPbzOPztw/dsyjfJS/.kmP3jIciQOoLSiAEy', NULL, NULL, 'user', '3', 'gu', NULL, '1', NULL, '2026-10-02 12:57:40', '2026-10-02 12:57:40');

--
-- Table structure for table `wishlists`
--

DROP TABLE IF EXISTS `wishlists`;
CREATE TABLE `wishlists` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `wishlists_user_id_product_id_unique` (`user_id`,`product_id`),
  KEY `wishlists_product_id_foreign` (`product_id`),
  CONSTRAINT `wishlists_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `wishlists_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS=1;
