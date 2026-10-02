<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Slider;
use App\Models\Offer;
use App\Models\Page;
use App\Models\Address;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 0. Seed Settings (matching screenshot theme & footer)
        \App\Models\Setting::set('theme_primary_color', '#000000', 'theme');
        \App\Models\Setting::set('theme_hover_color', '#a1a1a1', 'theme');
        \App\Models\Setting::set('sidebar_bg_color', '#000000', 'theme');
        \App\Models\Setting::set('sidebar_active_color', '#add8e6', 'theme');
        \App\Models\Setting::set('footer_copyright_prefix', '© 2026, made with ❤️ by', 'footer');
        \App\Models\Setting::set('footer_creator_name', 'Decent Infoways', 'footer');
        \App\Models\Setting::set('footer_creator_url', 'https://decentinfoways.com', 'footer');
        \App\Models\Setting::set('theme_mode', 'system', 'theme');

        // 1. Create Roles & Permissions
        $superAdminRole = \App\Models\Role::firstOrCreate(
            ['name' => 'super_admin'],
            ['display_name' => 'Super Admin', 'description' => 'Full control over all system modules, settings, users, and roles']
        );

        $adminRole = \App\Models\Role::firstOrCreate(
            ['name' => 'admin'],
            ['display_name' => 'Store Admin', 'description' => 'Can manage products, categories, stock, orders, offers, and pages']
        );

        $customerRole = \App\Models\Role::firstOrCreate(
            ['name' => 'user'],
            ['display_name' => 'Customer / User', 'description' => 'Can browse catalog, cart, wishlist, checkout and manage profile']
        );

        $permissionsList = [
            // Catalog
            ['name' => 'manage_categories', 'display_name' => 'Manage Categories & Subcategories', 'group' => 'catalog'],
            ['name' => 'manage_products', 'display_name' => 'Manage Products & Gallery', 'group' => 'catalog'],
            ['name' => 'manage_stock', 'display_name' => 'Manage Stock & Inventory', 'group' => 'catalog'],
            ['name' => 'manage_sliders', 'display_name' => 'Manage Sliders & Banners', 'group' => 'catalog'],
            // Sales
            ['name' => 'manage_orders', 'display_name' => 'Manage Orders & Status', 'group' => 'sales'],
            ['name' => 'manage_invoices', 'display_name' => 'View & Print Invoices', 'group' => 'sales'],
            ['name' => 'manage_offers', 'display_name' => 'Manage Offers & Coupons', 'group' => 'sales'],
            // Content & Security
            ['name' => 'manage_pages', 'display_name' => 'Manage Static Content Pages', 'group' => 'content'],
            ['name' => 'manage_roles', 'display_name' => 'Manage Roles & Permissions', 'group' => 'security'],
            ['name' => 'manage_users', 'display_name' => 'Manage Users & Accounts', 'group' => 'security'],
            ['name' => 'manage_settings', 'display_name' => 'Manage System & Theme Settings', 'group' => 'settings'],
        ];

        $allPermissionIds = [];
        $adminPermissionIds = [];

        foreach ($permissionsList as $p) {
            $perm = \App\Models\Permission::firstOrCreate(['name' => $p['name']], $p);
            $allPermissionIds[] = $perm->id;
            if (!in_array($p['group'], ['security', 'settings'])) {
                $adminPermissionIds[] = $perm->id;
            }
        }

        $superAdminRole->permissions()->sync($allPermissionIds);
        $adminRole->permissions()->sync($adminPermissionIds);

        // 2. Create Admin, Super Admin & Customer Users
        $superAdmin = User::create([
            'name' => 'Super Administrator',
            'email' => 'superadmin@grocery.com',
            'phone' => '9876500000',
            'password' => Hash::make('admin123'),
            'role' => 'super_admin',
            'role_id' => $superAdminRole->id,
            'language' => 'en',
            'is_active' => true,
        ]);

        $admin = User::create([
            'name' => 'Store Administrator',
            'email' => 'admin@grocery.com',
            'phone' => '9876543210',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'role_id' => $adminRole->id,
            'language' => 'en',
            'is_active' => true,
        ]);

        $customer = User::create([
            'name' => 'Jignesh Patel',
            'email' => 'customer@gmail.com',
            'phone' => '9988776655',
            'password' => Hash::make('customer123'),
            'role' => 'user',
            'role_id' => $customerRole->id,
            'language' => 'gu',
            'is_active' => true,
        ]);

        // 2. Sliders / Banners
        Slider::create([
            'title_en' => 'Farm Fresh Fruits & Vegetables',
            'title_gu' => 'ખેતરમાંથી સીધા તાજા ફળો અને શાકભાજી',
            'subtitle_en' => 'Express 2-Hour Delivery Before 12 PM',
            'subtitle_gu' => 'બપોરે ૧૨ વાગ્યા પહેલા ઓર્ડર કરો અને ૨ કલાકમાં મેળવો',
            'badge_en' => '30% OFF Today',
            'badge_gu' => 'આજે ૩૦% સુધી છૂટ',
            'image' => 'https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=1200&q=80',
            'link_type' => 'category',
            'target_id' => 1,
            'sort_order' => 1,
            'is_active' => true,
        ]);

        Slider::create([
            'title_en' => 'Pure Dairy & Organic Essentials',
            'title_gu' => 'શુદ્ધ દેશી ડેરી અને ઓર્ગેનિક વસ્તુઓ',
            'subtitle_en' => 'Pure A2 Milk, Fresh Paneer & Gir Cow Ghee',
            'subtitle_gu' => 'શુદ્ધ દૂધ, તાજું પનીર અને ગીર ગાયનું શુદ્ધ ઘી',
            'badge_en' => 'Fresh Every Morning',
            'badge_gu' => 'દરરોજ સવારે તાજું',
            'image' => 'https://images.unsplash.com/photo-1528735602780-2552fd46c7af?auto=format&fit=crop&w=1200&q=80',
            'link_type' => 'category',
            'target_id' => 2,
            'sort_order' => 2,
            'is_active' => true,
        ]);

        Slider::create([
            'title_en' => 'Authentic Gujarati Spices & Grains',
            'title_gu' => 'ગુજરાતી પરંપરાગત મસાલા અને અનાજ',
            'subtitle_en' => 'Premium Quality Unpolished Dals & Handpicked Spices',
            'subtitle_gu' => 'શ્રેષ્ઠ ગુણવત્તાવાળા કઠોળ અને હાથથી પસંદ કરેલ મસાલા',
            'badge_en' => 'Best Price Guaranteed',
            'badge_gu' => 'શ્રેષ્ઠ ભાવની ખાતરી',
            'image' => 'https://images.unsplash.com/photo-1596040033229-a9821ebd058d?auto=format&fit=crop&w=1200&q=80',
            'link_type' => 'offer',
            'target_id' => 1,
            'sort_order' => 3,
            'is_active' => true,
        ]);

        // 3. Categories & Subcategories
        $catData = [
            [
                'name_en' => 'Fruits & Vegetables',
                'name_gu' => 'ફળો અને શાકભાજી',
                'icon' => 'fa-solid fa-carrot',
                'image' => 'https://images.unsplash.com/photo-1610348725531-843dff563e2c?auto=format&fit=crop&w=600&q=80',
                'description_en' => 'Fresh farm picked vegetables and sweet juicy organic fruits.',
                'description_gu' => 'ખેતરમાંથી સીધા ચૂંટેલા તાજા શાકભાજી અને સ્વાદિષ્ટ ફળો.',
                'subs' => [
                    ['name_en' => 'Fresh Vegetables', 'name_gu' => 'તાજા શાકભાજી', 'image' => 'https://images.unsplash.com/photo-1566385101042-1a0aa0c1268c?auto=format&fit=crop&w=400&q=80'],
                    ['name_en' => 'Fresh Fruits', 'name_gu' => 'તાજા ફળો', 'image' => 'https://images.unsplash.com/photo-1619566636858-adf3ef46400b?auto=format&fit=crop&w=400&q=80'],
                    ['name_en' => 'Exotic & Organic', 'name_gu' => 'ઓર્ગેનિક અને સ્પેશિયલ', 'image' => 'https://images.unsplash.com/photo-1518843875459-f738682238a6?auto=format&fit=crop&w=400&q=80'],
                ],
            ],
            [
                'name_en' => 'Dairy, Bread & Eggs',
                'name_gu' => 'ડેરી, બ્રેડ અને ઈંડા',
                'icon' => 'fa-solid fa-cheese',
                'image' => 'https://images.unsplash.com/photo-1550583724-b2692b85b150?auto=format&fit=crop&w=600&q=80',
                'description_en' => 'Fresh milk, butter, cheese, paneer, curd, and bakery bread.',
                'description_gu' => 'તાજું દૂધ, માખણ, પનીર, દહીં અને બેકરી પ્રોડક્ટ્સ.',
                'subs' => [
                    ['name_en' => 'Milk & Curd', 'name_gu' => 'દૂધ અને દહીં', 'image' => 'https://images.unsplash.com/photo-1563636619-e9143da7973b?auto=format&fit=crop&w=400&q=80'],
                    ['name_en' => 'Paneer & Butter', 'name_gu' => 'પનીર અને માખણ', 'image' => 'https://images.unsplash.com/photo-1589985270826-4b7bb135bc9d?auto=format&fit=crop&w=400&q=80'],
                    ['name_en' => 'Bakery & Bread', 'name_gu' => 'બેકરી અને બ્રેડ', 'image' => 'https://images.unsplash.com/photo-1509440159596-0249088772ff?auto=format&fit=crop&w=400&q=80'],
                ],
            ],
            [
                'name_en' => 'Atta, Rice & Dals',
                'name_gu' => 'લોટ, ચોખા અને કઠોળ',
                'icon' => 'fa-solid fa-wheat-awn',
                'image' => 'https://images.unsplash.com/photo-1586201375761-83865001e31c?auto=format&fit=crop&w=600&q=80',
                'description_en' => 'Finest quality whole wheat atta, basmati rice, tuver dal, and pulses.',
                'description_gu' => 'ઉચ્ચ ગુણવત્તાવાળો ઘઉંનો લોટ, બાસમતી ચોખા, તુવેર દાળ અને કઠોળ.',
                'subs' => [
                    ['name_en' => 'Atta & Flours', 'name_gu' => 'લોટ અને બેસન', 'image' => 'https://images.unsplash.com/photo-1608686207856-001b95cf60ca?auto=format&fit=crop&w=400&q=80'],
                    ['name_en' => 'Rice & Poha', 'name_gu' => 'ચોખા અને પૌંઆ', 'image' => 'https://images.unsplash.com/photo-1586201375761-83865001e31c?auto=format&fit=crop&w=400&q=80'],
                    ['name_en' => 'Dals & Pulses', 'name_gu' => 'દાળ અને કઠોળ', 'image' => 'https://images.unsplash.com/photo-1585994192701-f1a505c8574a?auto=format&fit=crop&w=400&q=80'],
                ],
            ],
            [
                'name_en' => 'Oil, Masalas & Spices',
                'name_gu' => 'તેલ, મસાલા અને મરી',
                'icon' => 'fa-solid fa-pepper-hot',
                'image' => 'https://images.unsplash.com/photo-1596040033229-a9821ebd058d?auto=format&fit=crop&w=600&q=80',
                'description_en' => 'Pure cold pressed oils, turmeric, chili powder, and whole spices.',
                'description_gu' => 'શુદ્ધ તેલ, હળદર, લાલ મરચું, ધાણાજીરું અને ખડા મસાલા.',
                'subs' => [
                    ['name_en' => 'Cooking Oils & Ghee', 'name_gu' => 'રસોઈનું તેલ અને ઘી', 'image' => 'https://images.unsplash.com/photo-1474979266404-7eaacbcd87c5?auto=format&fit=crop&w=400&q=80'],
                    ['name_en' => 'Powdered Spices', 'name_gu' => 'દળેલા મસાલા', 'image' => 'https://images.unsplash.com/photo-1615485290382-441e4d049cb5?auto=format&fit=crop&w=400&q=80'],
                    ['name_en' => 'Whole Spices', 'name_gu' => 'આખા મસાલા', 'image' => 'https://images.unsplash.com/photo-1509358271058-acd22cc93898?auto=format&fit=crop&w=400&q=80'],
                ],
            ],
            [
                'name_en' => 'Snacks & Gujarati Farsan',
                'name_gu' => 'નાસ્તો અને ગુજરાતી ફરસાણ',
                'icon' => 'fa-solid fa-cookie-bite',
                'image' => 'https://images.unsplash.com/photo-1599488615731-7e5c2823ff28?auto=format&fit=crop&w=600&q=80',
                'description_en' => 'Crispy khakhra, gathiya, sev, bhakarwadi, biscuits, and namkeen.',
                'description_gu' => 'કરકરા ખાખરા, ગાંઠિયા, સેવ, ભાખરવડી, બિસ્કિટ અને નમકીન.',
                'subs' => [
                    ['name_en' => 'Khakhra & Papad', 'name_gu' => 'ખાખરા અને પાપડ', 'image' => 'https://images.unsplash.com/photo-1601050690597-df0568f70950?auto=format&fit=crop&w=400&q=80'],
                    ['name_en' => 'Namkeen & Gathiya', 'name_gu' => 'નમકીન અને ગાંઠિયા', 'image' => 'https://images.unsplash.com/photo-1599488615731-7e5c2823ff28?auto=format&fit=crop&w=400&q=80'],
                    ['name_en' => 'Biscuits & Cookies', 'name_gu' => 'બિસ્કિટ અને કુકીઝ', 'image' => 'https://images.unsplash.com/photo-1558961363-fa8fdf82db35?auto=format&fit=crop&w=400&q=80'],
                ],
            ],
            [
                'name_en' => 'Tea, Coffee & Drinks',
                'name_gu' => 'ચા, કોફી અને પીણાં',
                'icon' => 'fa-solid fa-mug-hot',
                'image' => 'https://images.unsplash.com/photo-1576092768241-dec231879fc3?auto=format&fit=crop&w=600&q=80',
                'description_en' => 'Assam & Darjeeling tea, roasted coffee, juices, and health drinks.',
                'description_gu' => 'આસામ ચા, કોફી પાવડર, જ્યુસ અને હેલ્થ ડ્રિંક્સ.',
                'subs' => [
                    ['name_en' => 'Tea & Chai Masala', 'name_gu' => 'ચા અને ચા મસાલો', 'image' => 'https://images.unsplash.com/photo-1576092768241-dec231879fc3?auto=format&fit=crop&w=400&q=80'],
                    ['name_en' => 'Juices & Syrups', 'name_gu' => 'જ્યુસ અને શરબત', 'image' => 'https://images.unsplash.com/photo-1613478223719-2ab802602423?auto=format&fit=crop&w=400&q=80'],
                ],
            ],
        ];

        $createdSubs = [];

        foreach ($catData as $i => $cd) {
            $cat = Category::create([
                'name_en' => $cd['name_en'],
                'name_gu' => $cd['name_gu'],
                'icon' => $cd['icon'],
                'image' => $cd['image'],
                'description_en' => $cd['description_en'],
                'description_gu' => $cd['description_gu'],
                'sort_order' => $i + 1,
                'is_featured' => true,
                'is_active' => true,
            ]);

            foreach ($cd['subs'] as $j => $sub) {
                $subModel = SubCategory::create([
                    'category_id' => $cat->id,
                    'name_en' => $sub['name_en'],
                    'name_gu' => $sub['name_gu'],
                    'image' => $sub['image'],
                    'sort_order' => $j + 1,
                    'is_active' => true,
                ]);
                $createdSubs[$sub['name_en']] = $subModel->id;
            }
        }

        // 4. Products with Rich Data
        $products = [
            // Fruits & Veg
            [
                'category_id' => 1,
                'sub_category_id' => $createdSubs['Fresh Vegetables'] ?? 1,
                'name_en' => 'Fresh Hybrid Tomatoes',
                'name_gu' => 'તાજા ટામેટા',
                'unit' => '1 kg',
                'price' => 45.00,
                'discount_price' => 38.00,
                'stock_quantity' => 120,
                'thumbnail' => 'https://images.unsplash.com/photo-1592924357228-91a4daadcfea?auto=format&fit=crop&w=600&q=80',
                'short_description_en' => 'Farm fresh plump, juicy red hybrid tomatoes.',
                'short_description_gu' => 'ખેતરમાંથી સીધા તાજા, લાલ અને રસદાર ટામેટા.',
                'description_en' => 'Handpicked fresh tomatoes perfect for daily cooking, curries, soups, and fresh salads.',
                'description_gu' => 'શાક, દાળ, સૂપ અને સલાડ માટે ઉત્તમ ગુણવત્તાવાળા તાજા લાલ ટામેટા.',
                'is_featured' => true,
            ],
            [
                'category_id' => 1,
                'sub_category_id' => $createdSubs['Fresh Vegetables'] ?? 1,
                'name_en' => 'Organic Fresh Potatoes (Batata)',
                'name_gu' => 'તાજા બટાકા',
                'unit' => '1 kg',
                'price' => 35.00,
                'discount_price' => 29.00,
                'stock_quantity' => 200,
                'thumbnail' => 'https://images.unsplash.com/photo-1518977676601-b53f82aba655?auto=format&fit=crop&w=600&q=80',
                'short_description_en' => 'Clean, smooth-skinned golden cooking potatoes.',
                'short_description_gu' => 'સાફ અને સ્વાદિષ્ટ દેશી બટાકા.',
                'description_en' => 'High quality potatoes suitable for boiling, frying, curries, and snacks.',
                'description_gu' => 'શાક, પરાઠા અને નાસ્તા માટે શ્રેષ્ઠ પસંદગી.',
                'is_featured' => true,
            ],
            [
                'category_id' => 1,
                'sub_category_id' => $createdSubs['Fresh Fruits'] ?? 2,
                'name_en' => 'Sweet Kinnow Oranges (Santra)',
                'name_gu' => 'મીઠા સંતરા',
                'unit' => '1 kg (approx 5-6 pcs)',
                'price' => 90.00,
                'discount_price' => 75.00,
                'stock_quantity' => 45,
                'thumbnail' => 'https://images.unsplash.com/photo-1611080626919-7cf5a9dbab5b?auto=format&fit=crop&w=600&q=80',
                'short_description_en' => 'Sweet, vitamin C packed juicy oranges.',
                'short_description_gu' => 'વિટામિન સી થી ભરપૂર મીઠા અને રસદાર સંતરા.',
                'description_en' => 'Naturally ripened, fresh and juicy oranges ideal for fresh breakfast juice and direct snacking.',
                'description_gu' => 'સ્વાસ્થ્ય માટે ઉત્તમ, તાજા રસદાર સંતરા.',
                'is_featured' => true,
            ],
            [
                'category_id' => 1,
                'sub_category_id' => $createdSubs['Fresh Fruits'] ?? 2,
                'name_en' => 'Kashmiri Royal Delicious Apples',
                'name_gu' => 'કાશ્મીરી સફરજન',
                'unit' => '1 kg (4 pcs)',
                'price' => 180.00,
                'discount_price' => 149.00,
                'stock_quantity' => 30,
                'thumbnail' => 'https://images.unsplash.com/photo-1560806887-1e4cd0b6cbd6?auto=format&fit=crop&w=600&q=80',
                'short_description_en' => 'Crisp, sweet, and deep red Kashmiri apples.',
                'short_description_gu' => 'મીઠા, ક્રિસ્પી અને તાજા કાશ્મીરી સફરજન.',
                'description_en' => 'Directly sourced from Kashmir valleys. Crunchy texture with natural sweetness.',
                'description_gu' => 'કાશ્મીરના બગીચાઓમાંથી લવાયેલા એકદમ તાજા અને સ્વાદિષ્ટ સફરજન.',
                'is_featured' => true,
            ],

            // Dairy
            [
                'category_id' => 2,
                'sub_category_id' => $createdSubs['Milk & Curd'] ?? 4,
                'name_en' => 'Amul Taaza Homogenised Toned Milk',
                'name_gu' => 'અમૂલ તાઝા ટોન્ડ દૂધ',
                'unit' => '500 ml',
                'price' => 27.00,
                'discount_price' => null,
                'stock_quantity' => 150,
                'thumbnail' => 'https://images.unsplash.com/photo-1550583724-b2692b85b150?auto=format&fit=crop&w=600&q=80',
                'short_description_en' => 'Pasteurised toned milk with 3.0% fat, 8.5% SNF.',
                'short_description_gu' => 'શુદ્ધ પાશ્ચુરાઇઝ્ડ ટોન્ડ દૂધ.',
                'description_en' => 'Healthy, pure milk for tea, coffee, drinking, and breakfast cereals.',
                'description_gu' => 'રોજિંદા ઉપયોગ, ચા અને કોફી માટે ઉત્તમ અમૂલ દૂધ.',
                'is_featured' => true,
            ],
            [
                'category_id' => 2,
                'sub_category_id' => $createdSubs['Paneer & Butter'] ?? 5,
                'name_en' => 'Fresh Malai Paneer',
                'name_gu' => 'તાજું મલાઈ પનીર',
                'unit' => '200 g',
                'price' => 95.00,
                'discount_price' => 85.00,
                'stock_quantity' => 40,
                'thumbnail' => 'https://images.unsplash.com/photo-1589985270826-4b7bb135bc9d?auto=format&fit=crop&w=600&q=80',
                'short_description_en' => 'Soft, melt-in-mouth cottage cheese blocks.',
                'short_description_gu' => 'નરમ અને સ્વાદિષ્ટ તાજું મલાઈ પનીર.',
                'description_en' => 'Rich in protein and calcium. Soft textured paneer ideal for Palak Paneer, Paneer Butter Masala and Tikka.',
                'description_gu' => 'પાલક પનીર, પનીર ટીક્કા અને શાક માટે બેસ્ટ મલાઈ પનીર.',
                'is_featured' => true,
            ],

            // Atta & Dals
            [
                'category_id' => 3,
                'sub_category_id' => $createdSubs['Atta & Flours'] ?? 7,
                'name_en' => 'Aashirvaad Shudh Chakki Whole Wheat Atta',
                'name_gu' => 'આશીર્વાદ શુદ્ધ ચક્કી ઘઉંનો લોટ',
                'unit' => '5 kg',
                'price' => 260.00,
                'discount_price' => 235.00,
                'stock_quantity' => 60,
                'thumbnail' => 'https://images.unsplash.com/photo-1608686207856-001b95cf60ca?auto=format&fit=crop&w=600&q=80',
                'short_description_en' => '100% pure whole wheat grain atta for soft rotis.',
                'short_description_gu' => '૧૦૦% શુદ્ધ ઘઉંનો લોટ, નરમ રોટલી માટે શ્રેષ્ઠ.',
                'description_en' => 'Made from heavy sun-kissed golden grains, ensuring rotis remain soft for hours.',
                'description_gu' => 'નરમ અને સ્વાદિષ્ટ રોટલીઓ માટે ઉચ્ચ ગુણવત્તાવાળો ચક્કી આટો.',
                'is_featured' => true,
            ],
            [
                'category_id' => 3,
                'sub_category_id' => $createdSubs['Dals & Pulses'] ?? 9,
                'name_en' => 'Premium Unpolished Tuver (Toor) Dal',
                'name_gu' => 'શુદ્ધ દેશી તુવેર દાળ',
                'unit' => '1 kg',
                'price' => 175.00,
                'discount_price' => 155.00,
                'stock_quantity' => 85,
                'thumbnail' => 'https://images.unsplash.com/photo-1585994192701-f1a505c8574a?auto=format&fit=crop&w=600&q=80',
                'short_description_en' => 'Unpolished, pesticide-free Vasad style Tuver Dal.',
                'short_description_gu' => 'વાસદની ફેમસ દેશી તુવેર દાળ.',
                'description_en' => 'Rich in protein, cooks fast and gives the authentic Gujarati Dal aroma and taste.',
                'description_gu' => 'ગુજરાતી દાળ અને ખીચડી માટે ઉત્તમ ગુણવત્તાવાળી તુવેર દાળ.',
                'is_featured' => true,
            ],

            // Oils & Spices
            [
                'category_id' => 4,
                'sub_category_id' => $createdSubs['Cooking Oils & Ghee'] ?? 10,
                'name_en' => 'Gir Cow A2 Desi Vedic Bilona Ghee',
                'name_gu' => 'ગીર ગાયનું શુદ્ધ વૈદિક બિલોણા ઘી',
                'unit' => '500 ml Glass Jar',
                'price' => 999.00,
                'discount_price' => 849.00,
                'stock_quantity' => 25,
                'thumbnail' => 'https://images.unsplash.com/photo-1631451095765-2c91616fc9e6?auto=format&fit=crop&w=600&q=80',
                'short_description_en' => 'Traditional bilona churned A2 cultured grass-fed ghee.',
                'short_description_gu' => 'પરંપરાગત માખણ વલોવીને બનાવેલું શુદ્ધ દેશી ગાયનું ઘી.',
                'description_en' => 'Prepared strictly using ancient Ayurvedic Bilona method with curd from indigenous Gir cows.',
                'description_gu' => 'ઔષધીય ગુણોથી ભરપૂર, સુગંધીદાર દાણાદાર શુદ્ધ દેશી ઘી.',
                'is_featured' => true,
            ],
            [
                'category_id' => 4,
                'sub_category_id' => $createdSubs['Powdered Spices'] ?? 11,
                'name_en' => 'Resham Patti Special Red Chili Powder',
                'name_gu' => 'રેશમ પટ્ટી સ્પેશિયલ લાલ મરચું પાવડર',
                'unit' => '500 g',
                'price' => 210.00,
                'discount_price' => 189.00,
                'stock_quantity' => 50,
                'thumbnail' => 'https://images.unsplash.com/photo-1615485290382-441e4d049cb5?auto=format&fit=crop&w=600&q=80',
                'short_description_en' => 'Vibrant natural red color with balanced spice level.',
                'short_description_gu' => 'કુદરતી લાલ રંગ અને સ્વાદિષ્ટ તીખાશ સાથે.',
                'description_en' => '100% natural, no artificial color, gives rich color and authentic taste to curries.',
                'description_gu' => 'કોઈ પણ કૃત્રિમ રંગ વગરનું શુદ્ધ મરચું પાવડર.',
                'is_featured' => false,
            ],

            // Gujarati Snacks
            [
                'category_id' => 5,
                'sub_category_id' => $createdSubs['Khakhra & Papad'] ?? 13,
                'name_en' => 'Methi Masala Roasted Wheat Khakhra',
                'name_gu' => 'મેથી મસાલા શેકેલા ખાખરા',
                'unit' => '500 g (Vacuum Pack)',
                'price' => 140.00,
                'discount_price' => 120.00,
                'stock_quantity' => 70,
                'thumbnail' => 'https://images.unsplash.com/photo-1601050690597-df0568f70950?auto=format&fit=crop&w=600&q=80',
                'short_description_en' => 'Crispy 100% whole wheat roasted khakhra with methi and spices.',
                'short_description_gu' => 'મેથી અને મસાલા સાથે શેકેલા કરકરા ખાખરા.',
                'description_en' => 'Healthy breakfast snack. Low calorie, vacuum sealed for extra freshness and crunchiness.',
                'description_gu' => 'સવારના નાસ્તા અને ચા માટે બેસ્ટ હેલ્ધી ખાખરા.',
                'is_featured' => true,
            ],
            [
                'category_id' => 5,
                'sub_category_id' => $createdSubs['Namkeen & Gathiya'] ?? 14,
                'name_en' => 'Bhavnagri Fresh Gathiya',
                'name_gu' => 'ભાવનગરી સ્પેશિયલ ગાંઠિયા',
                'unit' => '400 g',
                'price' => 130.00,
                'discount_price' => 110.00,
                'stock_quantity' => 55,
                'thumbnail' => 'https://images.unsplash.com/photo-1599488615731-7e5c2823ff28?auto=format&fit=crop&w=600&q=80',
                'short_description_en' => 'Famous soft & melt-in-mouth Bhavnagri gathiya made with pure groundnut oil.',
                'short_description_gu' => 'શુદ્ધ સીંગતેલમાં બનેલા ભાવનગરી સ્વાદિષ્ટ ગાંઠિયા.',
                'description_en' => 'Authentic taste of Bhavnagar, made fresh with carom seeds (ajwain) and black pepper.',
                'description_gu' => 'સંભારો અને તળેલા મરચા સાથે ખાવાની અસલ મજા.',
                'is_featured' => true,
            ],

            // Tea
            [
                'category_id' => 6,
                'sub_category_id' => $createdSubs['Tea & Chai Masala'] ?? 16,
                'name_en' => 'Wagh Bakri Premium CTC Tea',
                'name_gu' => 'વાઘ બકરી પ્રીમિયમ ચા',
                'unit' => '1 kg',
                'price' => 520.00,
                'discount_price' => 470.00,
                'stock_quantity' => 80,
                'thumbnail' => 'https://images.unsplash.com/photo-1576092768241-dec231879fc3?auto=format&fit=crop&w=600&q=80',
                'short_description_en' => 'Strong CTC leaf blend with rich aroma and golden color.',
                'short_description_gu' => 'કડક અને સ્વાદિષ્ટ વાઘ બકરી ચા.',
                'description_en' => 'Gujarats beloved morning tea, carefully selected from the highest grade Assam tea gardens.',
                'description_gu' => 'સવારને તાજગીભરી બનાવવા માટે દરેક ગુજરાતીની મનપસંદ ચા.',
                'is_featured' => true,
            ],
        ];

        foreach ($products as $p) {
            $prod = Product::create($p);

            // Add secondary image
            ProductImage::create([
                'product_id' => $prod->id,
                'image_path' => $prod->thumbnail,
                'sort_order' => 1,
            ]);
        }

        // 5. Offers / Promo Codes
        Offer::create([
            'title_en' => 'Flat 20% OFF on Fresh Grocery',
            'title_gu' => 'તાજી કરિયાણા પર ફ્લેટ ૨૦% છૂટ',
            'code' => 'FRESH20',
            'discount_type' => 'percentage',
            'discount_value' => 20,
            'min_order_amount' => 499,
            'max_discount_amount' => 150,
            'banner_image' => 'https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=600&q=80',
            'description_en' => 'Get 20% discount up to ₹150 on orders above ₹499',
            'description_gu' => '₹૪૯૯ થી વધુના ઓર્ડર પર ₹૧૫૦ સુધી ૨૦% છૂટ મેળવો',
            'valid_from' => Carbon::now()->subDays(2),
            'valid_to' => Carbon::now()->addMonths(3),
            'usage_limit' => 500,
            'used_count' => 14,
            'is_active' => true,
        ]);

        Offer::create([
            'title_en' => 'Welcome Discount ₹50 Flat',
            'title_gu' => 'સ્વાગત ઓફર ₹૫૦ ફ્લેટ છૂટ',
            'code' => 'WELCOME50',
            'discount_type' => 'flat',
            'discount_value' => 50,
            'min_order_amount' => 299,
            'max_discount_amount' => 50,
            'banner_image' => 'https://images.unsplash.com/photo-1528735602780-2552fd46c7af?auto=format&fit=crop&w=600&q=80',
            'description_en' => 'Flat ₹50 OFF on your first purchase above ₹299',
            'description_gu' => '₹૨૯૯ થી વધુના પ્રથમ ઓર્ડર પર ફ્લેટ ₹૫૦ ની છૂટ',
            'valid_from' => Carbon::now()->subDays(10),
            'valid_to' => Carbon::now()->addMonths(6),
            'usage_limit' => 1000,
            'used_count' => 38,
            'is_active' => true,
        ]);

        Offer::create([
            'title_en' => 'Super Saver ₹100 Discount',
            'title_gu' => 'સુપર સેવર ₹૧૦૦ ની છૂટ',
            'code' => 'SUPER100',
            'discount_type' => 'flat',
            'discount_value' => 100,
            'min_order_amount' => 999,
            'max_discount_amount' => 100,
            'banner_image' => 'https://images.unsplash.com/photo-1596040033229-a9821ebd058d?auto=format&fit=crop&w=600&q=80',
            'description_en' => 'Flat ₹100 OFF on bulk orders above ₹999',
            'description_gu' => '₹૯૯૯ થી વધુના ઓર્ડર પર સીધા ₹૧૦૦ ની છૂટ',
            'valid_from' => Carbon::now(),
            'valid_to' => Carbon::now()->addMonths(2),
            'usage_limit' => 200,
            'used_count' => 5,
            'is_active' => true,
        ]);

        // 6. User Address
        $address = Address::create([
            'user_id' => $customer->id,
            'type' => 'Home',
            'recipient_name' => 'Jignesh Patel',
            'recipient_phone' => '9988776655',
            'house_no' => 'B-402, Shivam Heights',
            'street_address' => 'Near SG Highway, Bodakdev',
            'landmark' => 'Opposite Iskcon Temple',
            'city' => 'Ahmedabad',
            'state' => 'Gujarat',
            'pincode' => '380054',
            'latitude' => 23.030357,
            'longitude' => 72.507542,
            'formatted_address' => 'B-402, Shivam Heights, Opp Iskcon Temple, Bodakdev, SG Highway, Ahmedabad, Gujarat 380054',
            'is_default' => true,
        ]);

        Address::create([
            'user_id' => $customer->id,
            'type' => 'Work',
            'recipient_name' => 'Jignesh Patel',
            'recipient_phone' => '9988776655',
            'house_no' => 'Office 704, Mondeal Square',
            'street_address' => 'Prahlad Nagar Road',
            'landmark' => 'Near Prahlad Nagar Garden',
            'city' => 'Ahmedabad',
            'state' => 'Gujarat',
            'pincode' => '380015',
            'latitude' => 23.013054,
            'longitude' => 72.512683,
            'formatted_address' => '704, Mondeal Square, Prahlad Nagar Road, Ahmedabad, Gujarat 380015',
            'is_default' => false,
        ]);

        // 7. Demo Orders (showing 2 hours delivery vs next day)
        $slotInfo1 = Order::determineDeliverySlot(Carbon::now()->setTime(10, 30));
        $order1 = Order::create([
            'order_number' => 'ORD-' . strtoupper(uniqid()),
            'invoice_number' => 'INV-' . date('Y') . '-00101',
            'user_id' => $customer->id,
            'customer_name' => 'Jignesh Patel',
            'customer_phone' => '9988776655',
            'customer_email' => 'customer@gmail.com',
            'address_id' => $address->id,
            'delivery_address' => $address->full_address,
            'delivery_city' => 'Ahmedabad',
            'delivery_pincode' => '380054',
            'delivery_lat' => 23.030357,
            'delivery_lng' => 72.507542,
            'delivery_type' => $slotInfo1['type'],
            'delivery_slot' => $slotInfo1['slot_en'],
            'estimated_delivery_at' => $slotInfo1['estimated_at'],
            'subtotal' => 610.00,
            'discount_amount' => 50.00,
            'coupon_code' => 'WELCOME50',
            'delivery_charge' => 0.00,
            'tax_amount' => 0.00,
            'total_amount' => 560.00,
            'payment_method' => 'cod',
            'payment_status' => 'pending',
            'order_status' => 'out_for_delivery',
            'notes' => 'Please ring the doorbell twice.',
        ]);

        OrderItem::create([
            'order_id' => $order1->id,
            'product_id' => 1,
            'product_name_en' => 'Fresh Hybrid Tomatoes',
            'product_name_gu' => 'તાજા ટામેટા',
            'product_unit' => '1 kg',
            'product_image' => 'https://images.unsplash.com/photo-1592924357228-91a4daadcfea?auto=format&fit=crop&w=600&q=80',
            'unit_price' => 38.00,
            'quantity' => 2,
            'total_price' => 76.00,
        ]);

        OrderItem::create([
            'order_id' => $order1->id,
            'product_id' => 7,
            'product_name_en' => 'Aashirvaad Shudh Chakki Whole Wheat Atta',
            'product_name_gu' => 'આશીર્વાદ શુદ્ધ ચક્કી ઘઉંનો લોટ',
            'product_unit' => '5 kg',
            'product_image' => 'https://images.unsplash.com/photo-1608686207856-001b95cf60ca?auto=format&fit=crop&w=600&q=80',
            'unit_price' => 235.00,
            'quantity' => 1,
            'total_price' => 235.00,
        ]);

        OrderItem::create([
            'order_id' => $order1->id,
            'product_id' => 8,
            'product_name_en' => 'Premium Unpolished Tuver (Toor) Dal',
            'product_name_gu' => 'શુદ્ધ દેશી તુવેર દાળ',
            'product_unit' => '1 kg',
            'product_image' => 'https://images.unsplash.com/photo-1585994192701-f1a505c8574a?auto=format&fit=crop&w=600&q=80',
            'unit_price' => 155.00,
            'quantity' => 1,
            'total_price' => 155.00,
        ]);

        OrderItem::create([
            'order_id' => $order1->id,
            'product_id' => 13,
            'product_name_en' => 'Wagh Bakri Premium CTC Tea',
            'product_name_gu' => 'વાઘ બકરી પ્રીમિયમ ચા',
            'product_unit' => '1 kg',
            'product_image' => 'https://images.unsplash.com/photo-1576092768241-dec231879fc3?auto=format&fit=crop&w=600&q=80',
            'unit_price' => 470.00,
            'quantity' => 1,
            'total_price' => 470.00,
        ]);

        // 8. Pages (About Us, Legal Information, Privacy Policy)
        Page::create([
            'slug' => 'about-us',
            'title_en' => 'About Our Grocery Store',
            'title_gu' => 'અમારા સ્ટોર વિશે (અમારા વિશે)',
            'content_en' => '<h2>Welcome to Our Fresh Express Grocery</h2><p>We are dedicated to delivering farm fresh fruits, crisp vegetables, authentic Gujarati snacks, organic dairy, and kitchen essentials straight to your doorstep.</p><p>Our unique <strong>Express 2-Hour Delivery</strong> model guarantees that any order placed before 12:00 PM noon is delivered within 2 hours by our dedicated delivery fleet. Orders placed after 12:00 PM are packed in temperature-controlled boxes and delivered the next morning between 9:00 AM and 12:00 PM.</p><h3>Why Choose Us?</h3><ul><li>100% Farm Fresh & Handpicked Quality</li><li>Direct Farm Sourcing with Fair Farmer Pricing</li><li>Bilingual English & Gujarati experience</li><li>Fast Express Delivery</li><li>Easy Returns & Replacement Guarantee</li></ul>',
            'content_gu' => '<h2>અમારા ફ્રેશ એક્સપ્રેસ સ્ટોરમાં આપનું હાર્દિક સ્વાગત છે</h2><p>અમે તમારા ઘર સુધી ખેતરમાંથી સીધા ચૂંટેલા તાજા શાકભાજી, સ્વાદિષ્ટ ફળો, શુદ્ધ દેશી ડેરી, પરંપરાગત ગુજરાતી ફરસાણ અને અનાજ-કઠોળ પહોંચાડવા માટે પ્રતિબદ્ધ છીએ.</p><p>અમારી ખાસ <strong>૨ કલાક એક્સપ્રેસ ડિલિવરી</strong> સિસ્ટમ મુજબ, જો તમે બપોરે ૧૨:૦૦ વાગ્યા પહેલાં ઓર્ડર કરશો તો ૨ કલાકમાં તમારા ઘરે ડિલિવરી મળી જશે. ૧૨:૦૦ વાગ્યા પછીના ઓર્ડર બીજા દિવસે સવારે ૦૯:૦૦ થી ૧૨:૦૦ વચ્ચે પહોંચાડવામાં આવશે.</p><h3>અમારી વિશેષતાઓ:</h3><ul><li>૧૦૦% તાજી અને શ્રેષ્ઠ ગુણવત્તા</li><li>ખેડૂતો પાસેથી સીધી ખરીદી</li><li>સરળ ગુજરાતી અને અંગ્રેજી ભાષા સપોર્ટ</li><li>ઝડપી એક્સપ્રેસ ડિલિવરી</li><li>સરળ રિટર્ન અને રીપ્લેસમેન્ટ ગેરંટી</li></ul>',
            'meta_title_en' => 'About Us - Fresh Express Grocery Store',
            'meta_title_gu' => 'અમારા વિશે - ફ્રેશ એક્સપ્રેસ કરિયાણા સ્ટોર',
            'meta_description_en' => 'Learn more about our fresh grocery store, our express 2-hour delivery, and organic products.',
            'meta_description_gu' => 'અમારા ગ્રોસરી સ્ટોર, ૨ કલાક એક્સપ્રેસ ડિલિવરી અને તાજી પ્રોડક્ટ્સ વિશે વધુ જાણો.',
            'is_active' => true,
        ]);

        Page::create([
            'slug' => 'legal-information',
            'title_en' => 'Legal Information & Terms of Service',
            'title_gu' => 'કાનૂની માહિતી અને શરતો',
            'content_en' => '<h2>Legal Information & Company Disclaimer</h2><p>This platform operates in full compliance with the Consumer Protection (E-Commerce) Rules, FSSAI regulations, and Food Safety Standards of India.</p><h3>1. Registration & Licensing</h3><p>All packaged food and organic agricultural items supplied through this website and mobile application adhere to strict FSSAI hygiene guidelines.</p><h3>2. Delivery Terms</h3><p>Orders confirmed before 12:00 PM (noon) local time will be dispatched immediately for 2-hour express delivery. Any external traffic, weather, or force majeure events will be communicated to the customer via SMS / App notification.</p><h3>3. Pricing & Taxes</h3><p>All prices listed on the portal are inclusive of applicable Goods and Services Tax (GST) unless otherwise indicated.</p>',
            'content_gu' => '<h2>કાનૂની માહિતી અને નીતિ-નિયમો</h2><p>આ પ્લેટફોર્મ ભારતના કન્ઝ્યુમર પ્રોટેક્શન (ઈ-કોમર્સ) નિયમો અને FSSAI ફૂડ સેફ્ટી ધોરણોનું સંપૂર્ણ પાલન કરે છે.</p><h3>૧. રજીસ્ટ્રેશન અને લાયસન્સ</h3><p>વેબસાઇટ અને મોબાઇલ એપ દ્વારા ઉપલબ્ધ તમામ ખાદ્ય પદાર્થો FSSAI ના હાઇજીન માપદંડો મુજબ તૈયાર અને સપ્લાય કરવામાં આવે છે.</p><h3>૨. ડિલિવરીની શરતો</h3><p>બપોરે ૧૨:૦૦ વાગ્યા પહેલા નોંધાયેલા ઓર્ડર્સ ૨ કલાકની એક્સપ્રેસ ડિલિવરીમાં પહોંચાડવામાં આવે છે. ટ્રાફિક કે કુદરતી પરિસ્થિતિમાં વિલંબ થાય તો ગ્રાહકને તાત્કાલિક જાણ કરવામાં આવે છે.</p><h3>૩. કિંમત અને ટેક્સ</h3><p>દર્શાવેલ તમામ કિંમતોમાં જીએસટી (GST) શામેલ છે.</p>',
            'meta_title_en' => 'Legal Information - Fresh Express',
            'meta_title_gu' => 'કાનૂની માહિતી - ફ્રેશ એક્સપ્રેસ',
            'meta_description_en' => 'Legal compliance and terms of service.',
            'meta_description_gu' => 'કાનૂની નિયમો અને સેવાઓની શરતો.',
            'is_active' => true,
        ]);

        Page::create([
            'slug' => 'privacy-policy',
            'title_en' => 'Privacy Policy',
            'title_gu' => 'પ્રાઈવસી પોલિસી (ગોપનીયતા નીતિ)',
            'content_en' => '<h2>Privacy Policy</h2><p>We respect your privacy and are committed to protecting your personal data, mobile number, delivery addresses, and payment information.</p><h3>1. Information We Collect</h3><ul><li>Mobile phone number for OTP authentication and order updates</li><li>Saved GPS and manual delivery addresses</li><li>Order history and wishlist selections</li></ul><h3>2. How We Protect Your Data</h3><p>We do not sell, rent, or trade your personal information to any third parties. All transactions and customer data are encrypted using secure SSL/TLS protocols.</p><h3>3. Location Services</h3><p>Our interactive map address picker uses your current device coordinates only to assist you in pinning your accurate delivery location.</p>',
            'content_gu' => '<h2>ગોપનીયતા નીતિ (પ્રાઈવસી પોલિસી)</h2><p>અમે તમારી અંગત માહિતી, મોબાઈલ નંબર, ડિલિવરી સરનામું અને ડેટાની સુરક્ષા માટે સંપૂર્ણપણે પ્રતિબદ્ધ છીએ.</p><h3>૧. અમે કઈ માહિતી એકત્ર કરીએ છીએ?</h3><ul><li>ઓટીપી (OTP) લૉગિન અને ઓર્ડર અપડેટ્સ માટે મોબાઇલ નંબર</li><li>ડિલિવરી સરનામાં અને મેપ લોકેશન</li><li>ઓર્ડર હિસ્ટ્રી અને વિશલિસ્ટ પસંદગીઓ</li></ul><h3>૨. માહિતીની સુરક્ષા</h3><p>અમે તમારો ડેટા ક્યારેય કોઈ ત્રીજી કંપનીને વેચતા કે શેર કરતા નથી. તમામ માહિતી સુરક્ષિત એન્ક્રિપ્શન સાથે સાચવવામાં આવે છે.</p><h3>૩. લોકેશન સેવાઓ</h3><p>મેપ દ્વારા એડ્રેસ સિલેક્ટ કરવા માટે જ તમારા ડિવાઇસના લોકેશનનો ઉપયોગ થાય છે.</p>',
            'meta_title_en' => 'Privacy Policy - Fresh Express',
            'meta_title_gu' => 'પ્રાઈવસી પોલિસી - ફ્રેશ એક્સપ્રેસ',
            'meta_description_en' => 'Privacy policy and data protection principles.',
            'meta_description_gu' => 'ગ્રાહક ડેટા સુરક્ષા અને ગોપનીયતા નીતિ.',
            'is_active' => true,
        ]);
    }
}
