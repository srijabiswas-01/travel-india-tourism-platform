<?php
function db_config(): array
{
    $config = [
        'host' => getenv('TRAVEL_INDIA_DB_HOST') ?: 'localhost',
        'user' => getenv('TRAVEL_INDIA_DB_USER') ?: 'root',
        'password' => getenv('TRAVEL_INDIA_DB_PASSWORD') ?: '',
        'database' => getenv('TRAVEL_INDIA_DB_NAME') ?: 'travel_india',
    ];
    $localConfig = __DIR__ . '/config.local.php';
    if (is_file($localConfig)) {
        $overrides = require $localConfig;
        if (is_array($overrides)) $config = array_replace($config, $overrides);
    }
    return $config;
}

function db_connect(): mysqli
{
    mysqli_report(MYSQLI_REPORT_OFF);
    $config = db_config();
    set_error_handler(static fn() => true);
    try {
        $connection = new mysqli($config['host'], $config['user'], $config['password'], $config['database']);
    } finally {
        restore_error_handler();
    }
    if ($connection->connect_errno) {
        throw new RuntimeException('Database connection failed. Check config.local.php or the TRAVEL_INDIA_DB_* environment variables.');
    }
    $connection->set_charset('utf8mb4');
    return $connection;
}

function table_columns(mysqli $db, string $table): array
{
    $columns = [];
    $result = $db->query("SHOW COLUMNS FROM `$table`");
    while ($result && $column = $result->fetch_assoc()) $columns[] = $column['Field'];
    return $columns;
}

function add_column(mysqli $db, string $table, array &$columns, string $name, string $definition): void
{
    if (in_array($name, $columns, true)) return;
    if (!$db->query("ALTER TABLE `$table` ADD `$name` $definition")) throw new RuntimeException("Could not add $table.$name: " . $db->error);
    $columns[] = $name;
}

function index_exists(mysqli $db, string $table, string $index): bool
{
    $statement = $db->prepare("SHOW INDEX FROM `$table` WHERE Key_name=?");
    $statement->bind_param('s', $index);
    $statement->execute();
    return (bool)$statement->get_result()->fetch_assoc();
}

function ensure_unique_booking_references(mysqli $db): void
{
    $missing = $db->query("SELECT id FROM bookings WHERE booking_reference IS NULL OR booking_reference='' OR booking_reference IN (SELECT duplicate_reference FROM (SELECT booking_reference duplicate_reference FROM bookings WHERE booking_reference IS NOT NULL AND booking_reference<>'' GROUP BY booking_reference HAVING COUNT(*)>1) duplicates)");
    $update = $db->prepare('UPDATE bookings SET booking_reference=? WHERE id=?');
    while ($missing && $booking = $missing->fetch_assoc()) {
        $id = (int)$booking['id'];
        $reference = 'TI-LEGACY-' . $id;
        $update->bind_param('si', $reference, $id);
        $update->execute();
    }
    if (!index_exists($db, 'bookings', 'booking_reference_unique')) {
        if (!$db->query('ALTER TABLE bookings ADD UNIQUE KEY booking_reference_unique(booking_reference)')) {
            throw new RuntimeException('Could not enforce unique booking references: ' . $db->error);
        }
    }
}

function ensure_app_schema(mysqli $db): void
{
    if (!defined('TRAVEL_INDIA_ALLOW_SCHEMA_MIGRATION') || TRAVEL_INDIA_ALLOW_SCHEMA_MIGRATION !== true) return;
    $db->query("CREATE TABLE IF NOT EXISTS users (email varchar(500) NOT NULL PRIMARY KEY,name varchar(500) NOT NULL,password varchar(500) NOT NULL,address varchar(500),phone varchar(20)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $columns = table_columns($db, 'users');
    add_column($db, 'users', $columns, 'address', 'varchar(500) DEFAULT NULL');
    add_column($db, 'users', $columns, 'phone', 'varchar(20) DEFAULT NULL');
    add_column($db, 'users', $columns, 'is_active', 'tinyint(1) NOT NULL DEFAULT 1');
    add_column($db, 'users', $columns, 'created_at', 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP');
    add_column($db, 'users', $columns, 'profile_image', 'varchar(255) DEFAULT NULL');
    add_column($db, 'users', $columns, 'alt_phone', 'varchar(20) DEFAULT NULL');
    add_column($db, 'users', $columns, 'city', 'varchar(120) DEFAULT NULL');
    add_column($db, 'users', $columns, 'state', 'varchar(120) DEFAULT NULL');
    add_column($db, 'users', $columns, 'postal_code', 'varchar(12) DEFAULT NULL');
    add_column($db, 'users', $columns, 'country', "varchar(80) NOT NULL DEFAULT 'India'");
    add_column($db, 'users', $columns, 'medical_issues', 'text DEFAULT NULL');
    add_column($db, 'users', $columns, 'date_of_birth', 'date DEFAULT NULL');
    add_column($db, 'users', $columns, 'gender', 'varchar(30) DEFAULT NULL');
    add_column($db, 'users', $columns, 'is_pwd', 'tinyint(1) NOT NULL DEFAULT 0');
    add_column($db, 'users', $columns, 'pwd_details', 'varchar(500) DEFAULT NULL');
    $db->query("CREATE TABLE IF NOT EXISTS family_members (id int NOT NULL AUTO_INCREMENT PRIMARY KEY,user_email varchar(500) NOT NULL,name varchar(150) NOT NULL,relationship varchar(80) NOT NULL,date_of_birth date DEFAULT NULL,gender varchar(30) DEFAULT NULL,phone varchar(20) DEFAULT NULL,medical_issues text DEFAULT NULL,is_active tinyint(1) NOT NULL DEFAULT 1,created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,INDEX(user_email)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $columns = table_columns($db, 'family_members');
    add_column($db, 'family_members', $columns, 'is_pwd', 'tinyint(1) NOT NULL DEFAULT 0');
    add_column($db, 'family_members', $columns, 'pwd_details', 'varchar(500) DEFAULT NULL');

    $db->query("CREATE TABLE IF NOT EXISTS countries (id int NOT NULL AUTO_INCREMENT PRIMARY KEY,name varchar(120) NOT NULL UNIQUE,iso_code varchar(3) NOT NULL UNIQUE,currency_code char(3) NOT NULL DEFAULT 'INR',image varchar(255) DEFAULT NULL,status varchar(30) NOT NULL DEFAULT 'active',created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $db->query("INSERT IGNORE INTO countries(name,iso_code,currency_code,status) VALUES('India','IND','INR','active')");
    $db->query("CREATE TABLE IF NOT EXISTS states (id int NOT NULL AUTO_INCREMENT PRIMARY KEY,name varchar(120) NOT NULL UNIQUE,slug varchar(130) NOT NULL UNIQUE,type varchar(30) NOT NULL DEFAULT 'state',image varchar(255) NOT NULL,status varchar(30) NOT NULL DEFAULT 'active',created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $columns = table_columns($db, 'states');
    add_column($db, 'states', $columns, 'country_id', 'int DEFAULT NULL AFTER id');
    $db->query("UPDATE states SET country_id=(SELECT id FROM countries WHERE iso_code='IND' LIMIT 1) WHERE country_id IS NULL");
    $db->query("CREATE TABLE IF NOT EXISTS destinations (id int NOT NULL AUTO_INCREMENT PRIMARY KEY,state_id int NOT NULL,name varchar(150) NOT NULL,slug varchar(160) NOT NULL UNIQUE,description text,status varchar(30) NOT NULL DEFAULT 'active',created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,INDEX(state_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $db->query("CREATE TABLE IF NOT EXISTS hotels (id int NOT NULL AUTO_INCREMENT PRIMARY KEY,destination_id int DEFAULT NULL,name varchar(180) NOT NULL,category varchar(60) NOT NULL DEFAULT 'Standard',address varchar(500) DEFAULT NULL,description varchar(1000) DEFAULT NULL,status varchar(30) NOT NULL DEFAULT 'active',created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,updated_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,UNIQUE KEY hotel_destination_name(destination_id,name),INDEX(destination_id),INDEX(status)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $columns = table_columns($db, 'hotels');
    if (!$db->query('ALTER TABLE hotels MODIFY destination_id int DEFAULT NULL')) throw new RuntimeException('Could not enable global accommodation options: '.$db->error);
    add_column($db, 'hotels', $columns, 'upgrade_surcharge', 'decimal(10,2) NOT NULL DEFAULT 0');
    $db->query("UPDATE hotels SET name=CONCAT(category,' accommodation #',id),address=NULL WHERE name NOT LIKE CONCAT(category,' accommodation%') OR address IS NOT NULL");
    if (!$db->query("CREATE TABLE IF NOT EXISTS meal_plans (id int NOT NULL AUTO_INCREMENT PRIMARY KEY,destination_id int DEFAULT NULL,name varchar(80) NOT NULL,plan_type varchar(20) NOT NULL DEFAULT 'premium',included_meals varchar(255) NOT NULL DEFAULT 'Breakfast, lunch and dinner',description text DEFAULT NULL,surcharge_per_traveller decimal(10,2) NOT NULL DEFAULT 0,supports_veg tinyint(1) NOT NULL DEFAULT 1,supports_nonveg tinyint(1) NOT NULL DEFAULT 1,status varchar(30) NOT NULL DEFAULT 'active',created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,updated_at timestamp NULL DEFAULT NULL,INDEX(destination_id),INDEX(status)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4")) throw new RuntimeException('Could not create meal_plans: '.$db->error);
    $columns = table_columns($db, 'meal_plans');
    add_column($db, 'meal_plans', $columns, 'destination_id', 'int DEFAULT NULL');
    add_column($db, 'meal_plans', $columns, 'name', "varchar(80) NOT NULL DEFAULT 'Meal plan'");
    add_column($db, 'meal_plans', $columns, 'plan_type', "varchar(20) NOT NULL DEFAULT 'premium'");
    add_column($db, 'meal_plans', $columns, 'included_meals', "varchar(255) NOT NULL DEFAULT 'Breakfast, lunch and dinner'");
    add_column($db, 'meal_plans', $columns, 'description', 'text DEFAULT NULL');
    add_column($db, 'meal_plans', $columns, 'surcharge_per_traveller', 'decimal(10,2) NOT NULL DEFAULT 0');
    add_column($db, 'meal_plans', $columns, 'supports_veg', 'tinyint(1) NOT NULL DEFAULT 1');
    add_column($db, 'meal_plans', $columns, 'supports_nonveg', 'tinyint(1) NOT NULL DEFAULT 1');
    add_column($db, 'meal_plans', $columns, 'status', "varchar(30) NOT NULL DEFAULT 'active'");
    add_column($db, 'meal_plans', $columns, 'created_at', 'datetime NOT NULL DEFAULT CURRENT_TIMESTAMP');
    add_column($db, 'meal_plans', $columns, 'updated_at', 'datetime NULL DEFAULT NULL');
    if (!$db->query("INSERT INTO meal_plans(destination_id,name,plan_type,surcharge_per_traveller,description) SELECT NULL,'Standard meals','standard',0,'Standard vegetarian or non-vegetarian meals included with the package' WHERE NOT EXISTS(SELECT 1 FROM meal_plans WHERE plan_type='standard' AND destination_id IS NULL)")) throw new RuntimeException('Could not initialize Standard meals: '.$db->error);
    $db->query("UPDATE meal_plans SET surcharge_per_traveller=0,status='active' WHERE plan_type='standard' AND destination_id IS NULL");
    $db->query("UPDATE meal_plans SET name=CASE WHEN plan_type='standard' THEN 'Standard meals' ELSE 'Premium meals' END");
    $db->query("UPDATE meal_plans duplicate_plan JOIN meal_plans keeper ON keeper.plan_type=duplicate_plan.plan_type AND keeper.destination_id <=> duplicate_plan.destination_id AND keeper.id<duplicate_plan.id SET duplicate_plan.status='disabled' WHERE duplicate_plan.status='active'");
    $db->query("CREATE TABLE IF NOT EXISTS offers (id int NOT NULL AUTO_INCREMENT PRIMARY KEY,name varchar(160) NOT NULL,slug varchar(180) NOT NULL UNIQUE,public_heading varchar(200) NOT NULL,description text DEFAULT NULL,terms text DEFAULT NULL,image varchar(255) DEFAULT NULL,code varchar(50) DEFAULT NULL UNIQUE,discount_type varchar(20) NOT NULL DEFAULT 'percentage',discount_value decimal(10,2) NOT NULL,calculation_scope varchar(30) NOT NULL DEFAULT 'booking_total',minimum_travellers int NOT NULL DEFAULT 1,minimum_booking_amount decimal(10,2) NOT NULL DEFAULT 0,maximum_discount decimal(10,2) DEFAULT NULL,booking_start_at datetime NOT NULL,booking_end_at datetime NOT NULL,usage_limit int DEFAULT NULL,per_customer_limit int DEFAULT NULL,is_automatic tinyint(1) NOT NULL DEFAULT 1,is_featured tinyint(1) NOT NULL DEFAULT 0,priority int NOT NULL DEFAULT 0,status varchar(30) NOT NULL DEFAULT 'draft',created_by int DEFAULT NULL,created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,updated_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,INDEX(status),INDEX(booking_start_at),INDEX(booking_end_at)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $db->query("CREATE TABLE IF NOT EXISTS offer_packages (offer_id int NOT NULL,package_id int NOT NULL,PRIMARY KEY(offer_id,package_id),INDEX(package_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $db->query("CREATE TABLE IF NOT EXISTS offer_redemptions (id bigint NOT NULL AUTO_INCREMENT PRIMARY KEY,offer_id int NOT NULL,booking_id int NOT NULL,user_email varchar(500) NOT NULL,base_amount decimal(10,2) NOT NULL,discount_amount decimal(10,2) NOT NULL,final_amount decimal(10,2) NOT NULL,redeemed_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,UNIQUE KEY booking_offer(booking_id,offer_id),INDEX(offer_id),INDEX(user_email)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $db->query("CREATE TABLE IF NOT EXISTS transport_categories (id int NOT NULL AUTO_INCREMENT PRIMARY KEY,name varchar(100) NOT NULL UNIQUE,description varchar(500) DEFAULT NULL,status varchar(30) NOT NULL DEFAULT 'active',created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $db->query("INSERT IGNORE INTO transport_categories(name,description) VALUES ('Air-conditioned transport','Comfortable air-conditioned road transport'),('Private car','Private point-to-point car service'),('Train and coach','Rail journey with local coach transfers'),('Flight and transfers','Flight with airport and local transfers')");
    $db->query("CREATE TABLE IF NOT EXISTS trip_themes (id int NOT NULL AUTO_INCREMENT PRIMARY KEY,name varchar(100) NOT NULL UNIQUE,slug varchar(110) NOT NULL UNIQUE,description varchar(500) DEFAULT NULL,status varchar(30) NOT NULL DEFAULT 'active',display_order int NOT NULL DEFAULT 0,created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,updated_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,INDEX(status),INDEX(display_order)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $db->query("INSERT IGNORE INTO trip_themes(name,slug,description,display_order) VALUES ('Leisure','leisure','Relaxed sightseeing and recreation',10),('Family holiday','family_holiday','Journeys planned for families',20),('Couple','couple','Travel for couples',30),('Friends','friends','Group travel with friends',40),('Solo','solo','Individual travel',50),('Business','business','Work-related travel',60),('Pilgrimage','pilgrimage','Religious and spiritual journeys',70),('Adventure','adventure','Activity and adventure travel',80),('Wellness','wellness','Health, relaxation, and wellness travel',90),('Other','other','Other travel requirements',100)");
    $db->query("CREATE TABLE IF NOT EXISTS packages (id int NOT NULL AUTO_INCREMENT PRIMARY KEY,name varchar(150) NOT NULL,slug varchar(160) NOT NULL UNIQUE,destination varchar(150) NOT NULL,description text NOT NULL,image varchar(255) NOT NULL,price decimal(10,2) NOT NULL,duration_days int NOT NULL DEFAULT 3,hotel_rating tinyint NOT NULL DEFAULT 3,transport varchar(255) NOT NULL DEFAULT 'Air-conditioned transport',status varchar(30) NOT NULL DEFAULT 'published',created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,updated_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $columns = table_columns($db, 'packages');
    add_column($db, 'packages', $columns, 'state_id', 'int DEFAULT NULL AFTER id');
    add_column($db, 'packages', $columns, 'destination_id', 'int DEFAULT NULL AFTER state_id');
    add_column($db, 'packages', $columns, 'transport_category_id', 'int DEFAULT NULL AFTER destination_id');
    $db->query("UPDATE packages SET transport_category_id=(SELECT id FROM transport_categories WHERE name='Air-conditioned transport' LIMIT 1) WHERE transport_category_id IS NULL");
    add_column($db, 'packages', $columns, 'currency', "char(3) NOT NULL DEFAULT 'INR' AFTER price");
    add_column($db, 'packages', $columns, 'visit_places', 'text DEFAULT NULL AFTER description');
    $db->query("CREATE TABLE IF NOT EXISTS bookings (id int NOT NULL AUTO_INCREMENT PRIMARY KEY,from_date date NOT NULL,to_date date NOT NULL,destination varchar(255) NOT NULL,hotel varchar(255) NOT NULL,people int NOT NULL,customer_name varchar(500) NOT NULL,created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $columns = table_columns($db, 'bookings');
    add_column($db, 'bookings', $columns, 'booking_reference', 'varchar(24) DEFAULT NULL');
    add_column($db, 'bookings', $columns, 'user_email', 'varchar(500) DEFAULT NULL');
    add_column($db, 'bookings', $columns, 'package_id', 'int DEFAULT NULL');
    add_column($db, 'bookings', $columns, 'hotel_id', 'int DEFAULT NULL AFTER package_id');
    add_column($db, 'bookings', $columns, 'hotel_surcharge_per_traveller', 'decimal(10,2) NOT NULL DEFAULT 0');
    add_column($db, 'bookings', $columns, 'hotel_surcharge_total', 'decimal(10,2) NOT NULL DEFAULT 0');
    add_column($db, 'bookings', $columns, 'meal_plan_id', 'int DEFAULT NULL');
    add_column($db, 'bookings', $columns, 'meal_plan_name', "varchar(80) NOT NULL DEFAULT 'Standard meals (included)'");
    add_column($db, 'bookings', $columns, 'meal_surcharge_per_traveller', 'decimal(10,2) NOT NULL DEFAULT 0');
    add_column($db, 'bookings', $columns, 'meal_surcharge_total', 'decimal(10,2) NOT NULL DEFAULT 0');
    add_column($db, 'bookings', $columns, 'status', "varchar(30) NOT NULL DEFAULT 'pending'");
    add_column($db, 'bookings', $columns, 'total_amount', 'decimal(10,2) NOT NULL DEFAULT 0');
    add_column($db, 'bookings', $columns, 'payment_status', "varchar(30) NOT NULL DEFAULT 'unpaid'");
    add_column($db, 'bookings', $columns, 'customer_note', 'text DEFAULT NULL');
    add_column($db, 'bookings', $columns, 'trip_category', "varchar(30) NOT NULL DEFAULT 'family'");
    add_column($db, 'bookings', $columns, 'trip_theme', "varchar(30) NOT NULL DEFAULT 'leisure'");
    add_column($db, 'bookings', $columns, 'package_unit_price', 'decimal(10,2) NOT NULL DEFAULT 0');
    add_column($db, 'bookings', $columns, 'base_amount', 'decimal(10,2) NOT NULL DEFAULT 0');
    add_column($db, 'bookings', $columns, 'offer_id', 'int DEFAULT NULL');
    add_column($db, 'bookings', $columns, 'offer_name', 'varchar(160) DEFAULT NULL');
    add_column($db, 'bookings', $columns, 'offer_code', 'varchar(50) DEFAULT NULL');
    add_column($db, 'bookings', $columns, 'discount_type', 'varchar(20) DEFAULT NULL');
    add_column($db, 'bookings', $columns, 'discount_value', 'decimal(10,2) NOT NULL DEFAULT 0');
    add_column($db, 'bookings', $columns, 'discount_amount', 'decimal(10,2) NOT NULL DEFAULT 0');
    add_column($db, 'bookings', $columns, 'admin_note', 'text DEFAULT NULL');
    add_column($db, 'bookings', $columns, 'updated_at', 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');
    add_column($db, 'bookings', $columns, 'customer_hidden_at', 'timestamp NULL DEFAULT NULL');
    $db->query("UPDATE bookings b JOIN meal_plans m ON m.id=b.meal_plan_id SET b.meal_plan_name=CASE WHEN m.plan_type='standard' THEN 'Standard meals (included)' ELSE 'Premium meals' END");
    $db->query("UPDATE bookings b LEFT JOIN hotels h ON h.id=b.hotel_id SET b.hotel=CASE WHEN h.id IS NULL OR h.category='Standard' THEN 'Standard accommodation (included)' ELSE CONCAT(h.category,' accommodation') END");
    $db->query("UPDATE bookings SET admin_note='Payment received in full.' WHERE payment_status='paid' AND LOWER(TRIM(admin_note)) IN ('pay the amount','please pay the amount','pay amount','please pay amount')");
    ensure_unique_booking_references($db);
    $db->query("CREATE TABLE IF NOT EXISTS booking_travellers (id int NOT NULL AUTO_INCREMENT PRIMARY KEY,booking_id int NOT NULL,family_member_id int DEFAULT NULL,name varchar(150) NOT NULL,relationship varchar(80) DEFAULT NULL,date_of_birth date DEFAULT NULL,gender varchar(30) DEFAULT NULL,medical_issues text DEFAULT NULL,traveller_type varchar(30) NOT NULL DEFAULT 'other',created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,INDEX(booking_id),INDEX(family_member_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $columns = table_columns($db, 'booking_travellers');
    add_column($db, 'booking_travellers', $columns, 'phone', 'varchar(20) DEFAULT NULL AFTER gender');
    add_column($db, 'booking_travellers', $columns, 'age_category', "varchar(20) NOT NULL DEFAULT 'adult' AFTER phone");
    add_column($db, 'booking_travellers', $columns, 'is_pwd', 'tinyint(1) NOT NULL DEFAULT 0');
    add_column($db, 'booking_travellers', $columns, 'pwd_details', 'varchar(500) DEFAULT NULL');
    add_column($db, 'booking_travellers', $columns, 'meal_plan_name', "varchar(80) NOT NULL DEFAULT 'Standard meals (included)'");
    add_column($db, 'booking_travellers', $columns, 'food_preference', "varchar(30) NOT NULL DEFAULT 'Vegetarian'");
    add_column($db, 'booking_travellers', $columns, 'dietary_notes', 'varchar(500) DEFAULT NULL');
    add_column($db, 'booking_travellers', $columns, 'meal_surcharge', 'decimal(10,2) NOT NULL DEFAULT 0');
    $db->query("UPDATE booking_travellers bt JOIN bookings b ON b.id=bt.booking_id SET bt.meal_plan_name=b.meal_plan_name");

    $db->query("CREATE TABLE IF NOT EXISTS message (email varchar(500) NOT NULL,fname varchar(500) NOT NULL,lname varchar(500) NOT NULL,sub varchar(500) NOT NULL,message text NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $columns = table_columns($db, 'message');
    add_column($db, 'message', $columns, 'id', 'int NOT NULL AUTO_INCREMENT UNIQUE FIRST');
    add_column($db, 'message', $columns, 'status', "varchar(30) NOT NULL DEFAULT 'open'");
    add_column($db, 'message', $columns, 'admin_response', 'text DEFAULT NULL');
    add_column($db, 'message', $columns, 'created_at', 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP');
    add_column($db, 'message', $columns, 'resolved_at', 'timestamp NULL DEFAULT NULL');

    $db->query("CREATE TABLE IF NOT EXISTS admins (id int NOT NULL AUTO_INCREMENT PRIMARY KEY,name varchar(150) NOT NULL,email varchar(255) NOT NULL UNIQUE,password varchar(255) NOT NULL,role varchar(30) NOT NULL DEFAULT 'manager',is_active tinyint(1) NOT NULL DEFAULT 1,created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,last_login_at timestamp NULL DEFAULT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $db->query("CREATE TABLE IF NOT EXISTS booking_status_history (id int NOT NULL AUTO_INCREMENT PRIMARY KEY,booking_id int NOT NULL,old_status varchar(30),new_status varchar(30) NOT NULL,note text,changed_by varchar(255) NOT NULL,created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,INDEX (booking_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $db->query("CREATE TABLE IF NOT EXISTS payments (id int NOT NULL AUTO_INCREMENT PRIMARY KEY,booking_id int NOT NULL,amount decimal(10,2) NOT NULL,method varchar(50) NOT NULL DEFAULT 'manual',transaction_reference varchar(100),status varchar(30) NOT NULL DEFAULT 'pending',created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,INDEX (booking_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $db->query("CREATE TABLE IF NOT EXISTS booking_amendments (id bigint NOT NULL AUTO_INCREMENT PRIMARY KEY,booking_id int NOT NULL,old_from_date date NOT NULL,old_to_date date NOT NULL,requested_from_date date NOT NULL,requested_to_date date NOT NULL,customer_reason varchar(1000) NOT NULL,admin_response varchar(1000) DEFAULT NULL,status varchar(30) NOT NULL DEFAULT 'requested',requested_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,reviewed_at timestamp NULL DEFAULT NULL,reviewed_by int DEFAULT NULL,INDEX(booking_id),INDEX(status)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $db->query("CREATE TABLE IF NOT EXISTS cancellation_policies (id int NOT NULL AUTO_INCREMENT PRIMARY KEY,name varchar(150) NOT NULL,minimum_days_before int NOT NULL,refund_percentage decimal(5,2) NOT NULL,administrative_fee decimal(10,2) NOT NULL DEFAULT 0,status varchar(30) NOT NULL DEFAULT 'active',effective_from date NOT NULL,effective_to date DEFAULT NULL,created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,INDEX(status),INDEX(minimum_days_before)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $db->query("INSERT INTO cancellation_policies(name,minimum_days_before,refund_percentage,administrative_fee,status,effective_from) SELECT '15+ days before departure',15,90,0,'active','2026-01-01' WHERE NOT EXISTS(SELECT 1 FROM cancellation_policies)");
    $db->query("INSERT INTO cancellation_policies(name,minimum_days_before,refund_percentage,administrative_fee,status,effective_from) SELECT '8-14 days before departure',8,75,0,'active','2026-01-01' WHERE NOT EXISTS(SELECT 1 FROM cancellation_policies WHERE minimum_days_before=8)");
    $db->query("INSERT INTO cancellation_policies(name,minimum_days_before,refund_percentage,administrative_fee,status,effective_from) SELECT '3-7 days before departure',3,50,0,'active','2026-01-01' WHERE NOT EXISTS(SELECT 1 FROM cancellation_policies WHERE minimum_days_before=3)");
    $db->query("INSERT INTO cancellation_policies(name,minimum_days_before,refund_percentage,administrative_fee,status,effective_from) SELECT 'Less than 72 hours',0,0,0,'active','2026-01-01' WHERE NOT EXISTS(SELECT 1 FROM cancellation_policies WHERE minimum_days_before=0)");
    $db->query("CREATE TABLE IF NOT EXISTS cancellation_requests (id bigint NOT NULL AUTO_INCREMENT PRIMARY KEY,booking_id int NOT NULL,reason varchar(1000) NOT NULL,paid_amount decimal(10,2) NOT NULL DEFAULT 0,policy_id int DEFAULT NULL,policy_name varchar(150) NOT NULL,refund_percentage decimal(5,2) NOT NULL DEFAULT 0,administrative_fee decimal(10,2) NOT NULL DEFAULT 0,estimated_refund decimal(10,2) NOT NULL DEFAULT 0,approved_refund decimal(10,2) DEFAULT NULL,admin_response varchar(1000) DEFAULT NULL,status varchar(30) NOT NULL DEFAULT 'requested',requested_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,reviewed_at timestamp NULL DEFAULT NULL,reviewed_by int DEFAULT NULL,INDEX(booking_id),INDEX(status)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $db->query("CREATE TABLE IF NOT EXISTS booking_reviews (id bigint NOT NULL AUTO_INCREMENT PRIMARY KEY,booking_id int NOT NULL,user_email varchar(500) NOT NULL,package_id int NOT NULL,hotel_id int DEFAULT NULL,package_rating tinyint NOT NULL,hotel_rating tinyint NOT NULL,review_text varchar(1000) DEFAULT NULL,status varchar(30) NOT NULL DEFAULT 'published',created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,updated_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,UNIQUE KEY booking_review_unique(booking_id),INDEX(package_id),INDEX(hotel_id),INDEX(user_email),INDEX(status)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $db->query("CREATE TABLE IF NOT EXISTS audit_logs (id bigint NOT NULL AUTO_INCREMENT PRIMARY KEY,admin_id int,action varchar(100) NOT NULL,entity_type varchar(50) NOT NULL,entity_id varchar(50),details text,created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $db->query("CREATE TABLE IF NOT EXISTS notifications (id bigint NOT NULL AUTO_INCREMENT PRIMARY KEY,audience varchar(20) NOT NULL DEFAULT 'public',user_email varchar(500) DEFAULT NULL,title varchar(160) NOT NULL,message text NOT NULL,link varchar(255) DEFAULT NULL,type varchar(30) NOT NULL DEFAULT 'info',is_active tinyint(1) NOT NULL DEFAULT 1,starts_at datetime DEFAULT NULL,expires_at datetime DEFAULT NULL,created_by int DEFAULT NULL,created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,INDEX(audience),INDEX(user_email),INDEX(is_active),INDEX(created_at)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $db->query("CREATE TABLE IF NOT EXISTS notification_reads (notification_id bigint NOT NULL,user_email varchar(500) NOT NULL,read_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,PRIMARY KEY(notification_id,user_email),INDEX(user_email)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $db->query("CREATE TABLE IF NOT EXISTS login_attempts (id bigint NOT NULL AUTO_INCREMENT PRIMARY KEY,identifier varchar(255) NOT NULL,portal varchar(20) NOT NULL,ip_address varchar(45) NOT NULL,attempted_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,INDEX(identifier,portal,attempted_at),INDEX(ip_address,attempted_at)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    seed_states($db);
    seed_destinations($db);
    seed_packages($db);
    migrate_india_catalog($db);
    seed_state_packages($db);
    seed_package_visit_places($db);
}

function seed_package_visit_places(mysqli $db): void
{
    $places = [
        'Port Blair'=>'Cellular Jail, Corbyn Cove Beach, Ross Island, North Bay Island, Samudrika Museum, Chidiya Tapu',
        'Visakhapatnam'=>'RK Beach, Kailasagiri, INS Kursura Museum, Simhachalam Temple, Rushikonda Beach, Borra Caves',
        'Tawang'=>'Tawang Monastery, Sela Pass, Madhuri Lake, Tawang War Memorial, Nuranang Falls, Bum La Pass',
        'Kaziranga'=>'Kaziranga National Park, Kohora Range, Bagori Range, Orchid Park, Kakochang Waterfalls, Tea Gardens',
        'Bodh Gaya'=>'Mahabodhi Temple, Bodhi Tree, Great Buddha Statue, Thai Monastery, Royal Bhutan Monastery, Archaeological Museum',
        'Chandigarh City'=>'Rock Garden, Sukhna Lake, Rose Garden, Capitol Complex, Government Museum, Japanese Garden',
        'Jagdalpur'=>'Chitrakote Falls, Tirathgarh Falls, Kanger Valley National Park, Kutumsar Caves, Danteshwari Temple, Anthropological Museum',
        'Daman'=>'Devka Beach, Jampore Beach, Moti Daman Fort, Nani Daman Fort, Bom Jesus Church, Daman Lighthouse',
        'New Delhi'=>'India Gate, Red Fort, Qutub Minar, Humayun Tomb, Lotus Temple, Raj Ghat, Akshardham Temple',
        'Panaji'=>'Fontainhas, Our Lady of Immaculate Conception Church, Miramar Beach, Dona Paula, Mandovi River, Reis Magos Fort',
        'Ahmedabad'=>'Sabarmati Ashram, Adalaj Stepwell, Sidi Saiyyed Mosque, Kankaria Lake, Calico Museum, Science City',
        'Kurukshetra'=>'Brahma Sarovar, Jyotisar, Krishna Museum, Sannihit Sarovar, Bhadrakali Temple, Sheikh Chilli Tomb',
        'Manali'=>'Hadimba Temple, Solang Valley, Rohtang Pass, Manu Temple, Vashisht Hot Springs, Mall Road',
        'Srinagar'=>'Dal Lake, Mughal Gardens, Shankaracharya Temple, Hazratbal Shrine, Pari Mahal, Old City',
        'Ranchi'=>'Dassam Falls, Hundru Falls, Tagore Hill, Jagannath Temple, Rock Garden, Birsa Zoological Park',
        'Mysuru'=>'Mysore Palace, Chamundi Hills, Brindavan Gardens, St Philomena Cathedral, Mysore Zoo, Jaganmohan Palace',
        'Munnar'=>'Eravikulam National Park, Mattupetty Dam, Tea Museum, Top Station, Echo Point, Kundala Lake',
        'Leh'=>'Leh Palace, Shanti Stupa, Thiksey Monastery, Magnetic Hill, Hall of Fame, Pangong Lake',
        'Agatti'=>'Agatti Beach, Agatti Lagoon, Bangaram Island, Thinnakara Island, Kalpitti Island, Marine Museum',
        'Khajuraho'=>'Western Group of Temples, Kandariya Mahadeva Temple, Lakshmana Temple, Eastern Group of Temples, Archaeological Museum, Raneh Falls',
        'Mumbai'=>'Gateway of India, Marine Drive, Chhatrapati Shivaji Terminus, Elephanta Caves, Colaba Causeway, Siddhivinayak Temple',
        'Imphal'=>'Kangla Fort, Loktak Lake, Ima Keithel, Shri Govindajee Temple, INA Museum, Keibul Lamjao National Park',
        'Shillong'=>'Umiam Lake, Elephant Falls, Shillong Peak, Ward Lake, Don Bosco Museum, Laitlum Canyons',
        'Aizawl'=>'Solomon Temple, Mizoram State Museum, Durtlang Hills, Reiek Tlang, KV Paradise, Falkawn Village',
        'Kohima'=>'Kohima War Cemetery, Kisama Heritage Village, Dzukou Valley, Nagaland State Museum, Japfu Peak, Cathedral of Mary Help of Christians',
        'Puri'=>'Jagannath Temple, Puri Beach, Gundicha Temple, Narendra Tank, Raghurajpur Village, Chilika Lake',
        'Puducherry City'=>'Promenade Beach, Sri Aurobindo Ashram, Auroville, French Quarter, Paradise Beach, Basilica of the Sacred Heart',
        'Amritsar'=>'Golden Temple, Jallianwala Bagh, Wagah Border, Partition Museum, Durgiana Temple, Gobindgarh Fort',
        'Jaipur'=>'Amber Fort, City Palace, Hawa Mahal, Jantar Mantar, Jal Mahal, Nahargarh Fort',
        'Gangtok'=>'MG Marg, Rumtek Monastery, Tsomgo Lake, Nathula Pass, Tashi View Point, Namgyal Institute of Tibetology',
        'Madurai'=>'Meenakshi Amman Temple, Thirumalai Nayakkar Palace, Gandhi Memorial Museum, Vandiyur Mariamman Teppakulam, Alagar Kovil, Pazhamudhir Solai',
        'Hyderabad'=>'Charminar, Golconda Fort, Salar Jung Museum, Hussain Sagar, Chowmahalla Palace, Qutb Shahi Tombs',
        'Agartala'=>'Ujjayanta Palace, Neermahal, Tripura Sundari Temple, Heritage Park, Sepahijala Wildlife Sanctuary, Chaturdasha Temple',
        'Agra'=>'Taj Mahal, Agra Fort, Mehtab Bagh, Itmad-ud-Daulah Tomb, Akbar Tomb, Fatehpur Sikri',
        'Haridwar'=>'Har Ki Pauri, Mansa Devi Temple, Chandi Devi Temple, Daksh Mahadev Temple, Bharat Mata Mandir, Shantikunj',
        'Darjeeling'=>'Tiger Hill, Batasia Loop, Himalayan Railway, Padmaja Naidu Zoo, Peace Pagoda, Tea Gardens',
        'Dalhousie'=>'Khajjiar, Dainkund Peak, Panchpula, Kalatop Wildlife Sanctuary, St John Church, Subhash Baoli',
        'Dharamshala'=>'McLeod Ganj, Dalai Lama Temple, Bhagsu Waterfall, Namgyal Monastery, Dal Lake, Triund',
        'Kasauli'=>'Monkey Point, Gilbert Trail, Sunset Point, Christ Church, Mall Road, Kasauli Brewery',
        'Mathura'=>'Krishna Janmabhoomi, Dwarkadhish Temple, Vishram Ghat, Govardhan Hill, Prem Mandir, Banke Bihari Temple'
    ];
    $statement=$db->prepare("UPDATE packages SET visit_places=? WHERE LOWER(destination)=LOWER(?) AND (visit_places IS NULL OR TRIM(visit_places)='')");
    foreach($places as $destination=>$list){$statement->bind_param('ss',$list,$destination);$statement->execute();}
}

function seed_destinations(mysqli $db): void
{
    $items = [
        'Andaman and Nicobar Islands'=>'Port Blair','Andhra Pradesh'=>'Visakhapatnam','Arunachal Pradesh'=>'Tawang','Assam'=>'Kaziranga','Bihar'=>'Bodh Gaya','Chandigarh'=>'Chandigarh City','Chhattisgarh'=>'Jagdalpur','Dadra and Nagar Haveli and Daman and Diu'=>'Daman','Delhi'=>'New Delhi','Goa'=>'Panaji','Gujarat'=>'Ahmedabad','Haryana'=>'Kurukshetra','Himachal Pradesh'=>'Manali','Jammu and Kashmir'=>'Srinagar','Jharkhand'=>'Ranchi','Karnataka'=>'Mysuru','Kerala'=>'Munnar','Ladakh'=>'Leh','Lakshadweep'=>'Agatti','Madhya Pradesh'=>'Khajuraho','Maharashtra'=>'Mumbai','Manipur'=>'Imphal','Meghalaya'=>'Shillong','Mizoram'=>'Aizawl','Nagaland'=>'Kohima','Odisha'=>'Puri','Puducherry'=>'Puducherry City','Punjab'=>'Amritsar','Rajasthan'=>'Jaipur','Sikkim'=>'Gangtok','Tamil Nadu'=>'Madurai','Telangana'=>'Hyderabad','Tripura'=>'Agartala','Uttar Pradesh'=>'Agra','Uttarakhand'=>'Haridwar','West Bengal'=>'Darjeeling'
    ];
    $statement=$db->prepare("INSERT IGNORE INTO destinations(state_id,name,slug,description) SELECT id,?,?,? FROM states WHERE name=?");
    foreach($items as $state=>$name){$slug=strtolower(trim(preg_replace('/[^a-z0-9]+/i','-',$state.'-'.$name),'-'));$description='Popular tourism destination in '.$state.'.';$statement->bind_param('ssss',$name,$slug,$description,$state);$statement->execute();}
}

function seed_states(mysqli $db): void
{
    $states = [
        ['Andaman and Nicobar Islands','andaman-nicobar','union_territory','img/1.jpg'],['Andhra Pradesh','andhra-pradesh','state','img/2.jpg'],['Arunachal Pradesh','arunachal-pradesh','state','img/3.jpg'],['Assam','assam','state','img/4.jpg'],['Bihar','bihar','state','img/5.jpg'],['Chandigarh','chandigarh','union_territory','img/6.jpg'],['Chhattisgarh','chhattisgarh','state','img/7.jpg'],['Dadra and Nagar Haveli and Daman and Diu','dadra-nagar-haveli-daman-diu','union_territory','img/9.jpg'],['Delhi','delhi','union_territory','img/10.jpg'],['Goa','goa','state','img/11.jpg'],['Gujarat','gujarat','state','img/12.jpg'],['Haryana','haryana','state','img/13.jpg'],['Himachal Pradesh','himachal-pradesh','state','img/14.jpg'],['Jammu and Kashmir','jammu-kashmir','union_territory','img/15.jpg'],['Jharkhand','jharkhand','state','img/16.jpg'],['Karnataka','karnataka','state','img/17.jpg'],['Kerala','kerala','state','img/18.jpg'],['Ladakh','ladakh','union_territory','img/30.jpg'],['Lakshadweep','lakshadweep','union_territory','img/19.jpg'],['Madhya Pradesh','madhya-pradesh','state','img/20.jpg'],['Maharashtra','maharashtra','state','img/21.jpg'],['Manipur','manipur','state','img/22.jpg'],['Meghalaya','meghalaya','state','img/23.jpg'],['Mizoram','mizoram','state','img/24.jpg'],['Nagaland','nagaland','state','img/25.png'],['Odisha','odisha','state','img/26.jpg'],['Puducherry','puducherry','union_territory','img/27.jpg'],['Punjab','punjab','state','img/28.jpg'],['Rajasthan','rajasthan','state','img/29.jpg'],['Sikkim','sikkim','state','img/30.jpg'],['Tamil Nadu','tamil-nadu','state','img/31.jpg'],['Telangana','telangana','state','img/32.jpg'],['Tripura','tripura','state','img/33.jpg'],['Uttar Pradesh','uttar-pradesh','state','img/34.jpg'],['Uttarakhand','uttarakhand','state','img/35.jpg'],['West Bengal','west-bengal','state','img/36.jpg']
    ];
    $statement = $db->prepare('INSERT IGNORE INTO states (name,slug,type,image) VALUES (?,?,?,?)');
    foreach ($states as [$name,$slug,$type,$image]) { $statement->bind_param('ssss',$name,$slug,$type,$image); $statement->execute(); }
}

function seed_packages(mysqli $db): void
{
    $result = $db->query('SELECT COUNT(*) AS total FROM packages');
    if ($result && (int)$result->fetch_assoc()['total'] > 0) return;
    $packages = [
        ['Agra','agra','Agra','Explore the Taj Mahal, Agra Fort, and the timeless heritage along the Yamuna River.','img/Agra.jpg',8999,3,4,'Private car and local transfers'],
        ['Amritsar','amritsar','Amritsar','Experience the Golden Temple, historic old city, and the moving Wagah border ceremony.','img/Amritsar.jpg',10999,4,4,'Train and air-conditioned coach'],
        ['Dalhousie','dalhousie','Dalhousie','Relax among pine-covered hills, colonial architecture, and panoramic Dhauladhar views.','img/Dalhousie.jpg',7499,3,3,'Air-conditioned coach'],
        ['Dharamshala','dharamshala','Dharamshala','Discover cedar forests, Tibetan culture, monasteries, and Himalayan mountain scenery.','img/Dharamshala.jpg',9999,4,4,'Private car and local transfers'],
        ['Haridwar','haridwar','Haridwar','Visit sacred ghats, witness the evening Ganga Aarti, and explore the Himalayan foothills.','img/Haridwar.jpg',6999,3,3,'Train and local transfers'],
        ['Kasauli','kasauli','Kasauli','Enjoy quiet colonial lanes, orchards, forest walks, and sweeping hill views.','img/Kasauli.jpg',7999,3,3,'Private car'],
        ['Manali','manali','Manali','A Himalayan escape with valleys, adventure activities, temples, and mountain passes.','img/Manali.jpg',14999,5,4,'Volvo coach and local transfers'],
        ['Mathura','mathura','Mathura','Explore the birthplace of Lord Krishna, ancient temples, ghats, and nearby Vrindavan.','img/Mathura.jpg',6499,3,3,'Private car and local transfers'],
        ['Mumbai','mumbai','Mumbai','See the Gateway of India, Marine Drive, heritage districts, and vibrant city life.','img/Mumbai.jpg',12999,4,4,'Airport and city transfers']
    ];
    $statement = $db->prepare('INSERT INTO packages (name,slug,destination,description,image,price,duration_days,hotel_rating,transport) VALUES (?,?,?,?,?,?,?,?,?)');
    foreach ($packages as $p) {
        [$name,$slug,$destination,$description,$image,$price,$days,$rating,$transport] = $p;
        $statement->bind_param('sssssdiss', $name,$slug,$destination,$description,$image,$price,$days,$rating,$transport);
        $statement->execute();
    }
}

function migrate_india_catalog(mysqli $db): void
{
    $mapping = ['Agra'=>'Uttar Pradesh','Mathura'=>'Uttar Pradesh','Amritsar'=>'Punjab','Dalhousie'=>'Himachal Pradesh','Dharamshala'=>'Himachal Pradesh','Kasauli'=>'Himachal Pradesh','Manali'=>'Himachal Pradesh','Haridwar'=>'Uttarakhand','Mumbai'=>'Maharashtra'];
    foreach ($mapping as $package => $state) {
        $statement = $db->prepare('UPDATE packages p JOIN states s ON s.name=? SET p.state_id=s.id,p.currency=\'INR\' WHERE p.name=?');
        $statement->bind_param('ss',$state,$package); $statement->execute();
    }
    $db->query("UPDATE packages p JOIN destinations d ON LOWER(d.name)=LOWER(p.destination) SET p.destination_id=d.id,p.state_id=d.state_id WHERE p.destination_id IS NULL");
    $prices = ['Agra'=>8999,'Amritsar'=>10999,'Dalhousie'=>7499,'Dharamshala'=>9999,'Haridwar'=>6999,'Kasauli'=>7999,'Manali'=>14999,'Mathura'=>6499,'Mumbai'=>12999];
    $statement = $db->prepare('UPDATE packages SET price=?,currency=\'INR\' WHERE name=? AND price<1000');
    foreach ($prices as $name=>$price) { $statement->bind_param('ds',$price,$name); $statement->execute(); }
    $db->query("UPDATE packages SET status='archived' WHERE name='Sydney'");
}

function seed_state_packages(mysqli $db): void
{
    $result=$db->query("SELECT d.id destination_id,d.name destination,s.id state_id,s.name state_name,s.slug,s.image FROM destinations d JOIN states s ON s.id=d.state_id WHERE d.status='active' AND s.status='active' AND NOT EXISTS(SELECT 1 FROM packages p WHERE p.state_id=s.id AND p.status<>'archived')");
    $statement=$db->prepare("INSERT INTO packages(state_id,destination_id,name,slug,destination,description,image,price,currency,duration_days,hotel_rating,transport,status) VALUES(?,?,?,?,?,?,?,?,'INR',4,3,'Air-conditioned local transport','published')");
    while($row=$result->fetch_assoc()){
        $name=$row['destination'].' Discovery'; $slug=$row['slug'].'-discovery'; $description='Discover the culture, heritage, landscapes, and local experiences of '.$row['destination'].', '.$row['state_name'].'. This curated India tour includes comfortable stays and guided sightseeing.'; $price=8999.0; $stateId=(int)$row['state_id']; $destinationId=(int)$row['destination_id']; $destination=$row['destination']; $image=$row['image'];
        $statement->bind_param('iisssssd',$stateId,$destinationId,$name,$slug,$destination,$description,$image,$price); $statement->execute();
    }
}

function redirect_with_message(string $location, string $message): never
{
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    $_SESSION['flash'] = $message;
    header('Location: ' . $location);
    exit;
}
