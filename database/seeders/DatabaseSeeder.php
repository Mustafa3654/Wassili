<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Driver;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['username' => 'admin'],
            ['name' => 'Wassili Admin', 'email' => null, 'password' => Hash::make('password')]
        );

        // ===================== CATEGORIES =====================
        $restaurants = Category::updateOrCreate(['slug' => 'restaurants'], ['name' => 'Restaurants', 'name_ar' => 'مطاعم', 'icon' => '🍔', 'sort_order' => 1]);
        $market      = Category::updateOrCreate(['slug' => 'supermarket'], ['name' => 'Supermarket', 'name_ar' => 'سوبر ماركت', 'icon' => '🛒', 'sort_order' => 2]);
        $pharmacy    = Category::updateOrCreate(['slug' => 'pharmacy'], ['name' => 'Pharmacy', 'name_ar' => 'صيدلية', 'icon' => '💊', 'sort_order' => 3]);
        $cleaning    = Category::updateOrCreate(['slug' => 'cleaning'], ['name' => 'Cleaning Supplies', 'name_ar' => 'مواد التنظيف', 'icon' => '🧼', 'sort_order' => 4]);
        $electronics = Category::updateOrCreate(['slug' => 'electronics'], ['name' => 'Electronics', 'name_ar' => 'إلكترونيات', 'icon' => '🔌', 'sort_order' => 5]);
        $bakery      = Category::updateOrCreate(['slug' => 'bakery'], ['name' => 'Bakeries', 'name_ar' => 'مخابز', 'icon' => '🥖', 'sort_order' => 6]);
        $flowers     = Category::updateOrCreate(['slug' => 'flowers'], ['name' => 'Flowers & Gifts', 'name_ar' => 'ورود وهدايا', 'icon' => '💐', 'sort_order' => 7]);

        // Sub-categories
        $fastFood  = Category::updateOrCreate(['slug' => 'fast-food'], ['name' => 'Fast Food', 'name_ar' => 'وجبات سريعة', 'icon' => '🍟', 'parent_id' => $restaurants->id, 'sort_order' => 1]);
        $lebanese  = Category::updateOrCreate(['slug' => 'lebanese-cuisine'], ['name' => 'Lebanese Cuisine', 'name_ar' => 'مأكولات لبنانية', 'icon' => '🧆', 'parent_id' => $restaurants->id, 'sort_order' => 2]);
        $dairy     = Category::updateOrCreate(['slug' => 'dairy'], ['name' => 'Dairy & Eggs', 'name_ar' => 'ألبان وبيض', 'icon' => '🥛', 'parent_id' => $market->id, 'sort_order' => 1]);
        $beverages = Category::updateOrCreate(['slug' => 'beverages'], ['name' => 'Beverages', 'name_ar' => 'مشروبات', 'icon' => '🥤', 'parent_id' => $market->id, 'sort_order' => 2]);

        // ===================== VENDORS =====================
        $burger     = Vendor::updateOrCreate(['slug' => 'burger-house'], ['name' => 'Burger House', 'name_ar' => 'بيت البرجر', 'category_id' => $fastFood->id, 'phone' => '71234567', 'address' => 'Hamra Street, Beirut', 'is_open' => true]);
        $pizza      = Vendor::updateOrCreate(['slug' => 'pizza-corner'], ['name' => 'Pizza Corner', 'name_ar' => 'ركن البيتزا', 'category_id' => $fastFood->id, 'phone' => '76123456', 'address' => 'Mar Elias, Beirut', 'is_open' => false]);
        $shawarma   = Vendor::updateOrCreate(['slug' => 'shawarma-king'], ['name' => 'Shawarma King', 'name_ar' => 'شاورما كينغ', 'category_id' => $fastFood->id, 'phone' => '70345678', 'address' => 'Badaro, Beirut', 'is_open' => true]);
        $tawouk     = Vendor::updateOrCreate(['slug' => 'tawouk-express'], ['name' => 'Tawouk Express', 'name_ar' => 'تاووك إكسبرس', 'category_id' => $fastFood->id, 'phone' => '78876543', 'address' => 'Verdun, Beirut', 'is_open' => true]);
        $alBustan   = Vendor::updateOrCreate(['slug' => 'al-bustan'], ['name' => 'Al Bustan', 'name_ar' => 'البستان', 'category_id' => $lebanese->id, 'phone' => '71888888', 'address' => 'Achrafieh, Beirut', 'is_open' => true]);
        $leila      = Vendor::updateOrCreate(['slug' => 'leilas-kitchen'], ['name' => "Leila's Kitchen", 'name_ar' => 'مطبخ ليلى', 'category_id' => $lebanese->id, 'phone' => '76543210', 'address' => 'Gemmayze, Beirut', 'is_open' => true]);
        $saj        = Vendor::updateOrCreate(['slug' => 'saj-village'], ['name' => 'Saj Village', 'name_ar' => 'قرية الصاج', 'category_id' => $lebanese->id, 'phone' => '70123456', 'address' => 'Mazraa, Beirut', 'is_open' => true]);
        $mekhzal    = Vendor::updateOrCreate(['slug' => 'mekhzal'], ['name' => 'Mekhzal Supermarket', 'name_ar' => 'مخزال سوبرماركت', 'category_id' => $market->id, 'phone' => '76111111', 'address' => 'Sin El Fil, Beirut', 'is_open' => true]);
        $fresh      = Vendor::updateOrCreate(['slug' => 'fresh-basket'], ['name' => 'Fresh Basket', 'name_ar' => 'السلة الطازجة', 'category_id' => $market->id, 'phone' => '79000000', 'address' => 'Jnah, Beirut', 'is_open' => true]);
        $diet       = Vendor::updateOrCreate(['slug' => 'diet-boutique'], ['name' => 'Diet Boutique', 'name_ar' => 'بوتيك الحمية', 'category_id' => $market->id, 'phone' => '71818181', 'address' => 'Monot, Beirut', 'is_open' => true]);
        $carefour   = Vendor::updateOrCreate(['slug' => 'carefour-express'], ['name' => 'Carefour Express', 'name_ar' => 'كارفور إكسبرس', 'category_id' => $market->id, 'phone' => '78787878', 'address' => 'Dbayeh, Beirut', 'is_open' => true]);
        $haykal     = Vendor::updateOrCreate(['slug' => 'haykal-pharmacy'], ['name' => 'Haykal Pharmacy', 'name_ar' => 'صيدلية هيكل', 'category_id' => $pharmacy->id, 'phone' => '71222222', 'address' => 'Hamra, Beirut', 'is_open' => true]);
        $sterile    = Vendor::updateOrCreate(['slug' => 'sterile-pharmacy'], ['name' => 'Sterile Pharmacy', 'name_ar' => 'صيدلية سترايل', 'category_id' => $pharmacy->id, 'phone' => '71444444', 'address' => 'Furn El Chebbak, Beirut', 'is_open' => true]);
        $cleanz     = Vendor::updateOrCreate(['slug' => 'cleanz'], ['name' => 'Cleanz', 'name_ar' => 'كلينز', 'category_id' => $cleaning->id, 'phone' => '70998877', 'address' => 'Chiyah, Beirut', 'is_open' => true]);
        $electro    = Vendor::updateOrCreate(['slug' => 'electro-star'], ['name' => 'Electro Star', 'name_ar' => 'إلكترو ستار', 'category_id' => $electronics->id, 'phone' => '76556677', 'address' => 'Mkalles, Beirut', 'is_open' => true]);
        $frencho    = Vendor::updateOrCreate(['slug' => 'furn-abousamra'], ['name' => 'Furn Abousamra', 'name_ar' => 'فرن أبو سمرا', 'category_id' => $bakery->id, 'phone' => '71889988', 'address' => 'Barbour, Beirut', 'is_open' => true]);
        $fleur      = Vendor::updateOrCreate(['slug' => 'fleur-de-vie'], ['name' => 'Fleur De Vie', 'name_ar' => 'فلور دي في', 'category_id' => $flowers->id, 'phone' => '70100200', 'address' => 'Sodeco, Beirut', 'is_open' => true]);

        // ===================== PRODUCTS =====================
        // Burger House
        Product::updateOrCreate(['name' => 'Classic Burger', 'vendor_id' => $burger->id], ['name_ar' => 'برجر كلاسيك', 'price' => 25, 'category_id' => $fastFood->id]);
        Product::updateOrCreate(['name' => 'Cheese Burger', 'vendor_id' => $burger->id], ['name_ar' => 'برجر بالجبن', 'price' => 30, 'category_id' => $fastFood->id]);
        Product::updateOrCreate(['name' => 'Chicken Burger', 'vendor_id' => $burger->id], ['name_ar' => 'برجر دجاج', 'price' => 28, 'category_id' => $fastFood->id]);
        Product::updateOrCreate(['name' => 'Double Cheese', 'vendor_id' => $burger->id], ['name_ar' => 'دبل جبن', 'price' => 38, 'category_id' => $fastFood->id]);
        Product::updateOrCreate(['name' => 'Fries', 'vendor_id' => $burger->id], ['name_ar' => 'بطاطا مقلية', 'price' => 5, 'category_id' => $fastFood->id]);
        Product::updateOrCreate(['name' => 'Onion Rings', 'vendor_id' => $burger->id], ['name_ar' => 'حلقات بصل', 'price' => 6, 'category_id' => $fastFood->id]);

        // Pizza Corner
        Product::updateOrCreate(['name' => 'Margherita Pizza', 'vendor_id' => $pizza->id], ['name_ar' => 'بيتزا مارغريتا', 'price' => 40, 'category_id' => $fastFood->id]);
        Product::updateOrCreate(['name' => 'Pepperoni Pizza', 'vendor_id' => $pizza->id], ['name_ar' => 'بيتزا بيبروني', 'price' => 48, 'category_id' => $fastFood->id]);
        Product::updateOrCreate(['name' => 'Vegetarian Pizza', 'vendor_id' => $pizza->id], ['name_ar' => 'بيتزا نباتية', 'price' => 42, 'category_id' => $fastFood->id]);
        Product::updateOrCreate(['name' => 'Garlic Bread', 'vendor_id' => $pizza->id], ['name_ar' => 'خبز بالثوم', 'price' => 6, 'category_id' => $fastFood->id]);

        // Shawarma King
        Product::updateOrCreate(['name' => 'Chicken Shawarma', 'vendor_id' => $shawarma->id], ['name_ar' => 'شاورما دجاج', 'price' => 18, 'category_id' => $fastFood->id]);
        Product::updateOrCreate(['name' => 'Meat Shawarma', 'vendor_id' => $shawarma->id], ['name_ar' => 'شاورما لحم', 'price' => 22, 'category_id' => $fastFood->id]);
        Product::updateOrCreate(['name' => 'Shawarma Plate', 'vendor_id' => $shawarma->id], ['name_ar' => 'صحن شاورما', 'price' => 35, 'category_id' => $fastFood->id]);
        Product::updateOrCreate(['name' => 'Falafel Wrap', 'vendor_id' => $shawarma->id], ['name_ar' => 'فلافل بلفة', 'price' => 12, 'category_id' => $fastFood->id]);

        // Tawouk Express
        Product::updateOrCreate(['name' => 'Tawouk Wrap', 'vendor_id' => $tawouk->id], ['name_ar' => 'تاووك بلفة', 'price' => 18, 'category_id' => $fastFood->id]);
        Product::updateOrCreate(['name' => 'Tawouk Plate', 'vendor_id' => $tawouk->id], ['name_ar' => 'صحن تاووك', 'price' => 35, 'category_id' => $fastFood->id]);
        Product::updateOrCreate(['name' => 'Garlic Sauce', 'vendor_id' => $tawouk->id], ['name_ar' => 'صوص ثوم', 'price' => 2, 'category_id' => $fastFood->id]);

        // Al Bustan
        Product::updateOrCreate(['name' => 'Hummus', 'vendor_id' => $alBustan->id], ['name_ar' => 'حمص', 'price' => 9, 'category_id' => $lebanese->id]);
        Product::updateOrCreate(['name' => 'Tabbouleh', 'vendor_id' => $alBustan->id], ['name_ar' => 'تبولة', 'price' => 10, 'category_id' => $lebanese->id]);
        Product::updateOrCreate(['name' => 'Grilled Kebbeh', 'vendor_id' => $alBustan->id], ['name_ar' => 'كبة نية', 'price' => 15, 'category_id' => $lebanese->id]);
        Product::updateOrCreate(['name' => 'Stuffed Grape Leaves', 'vendor_id' => $alBustan->id], ['name_ar' => 'ورق عنب', 'price' => 14, 'category_id' => $lebanese->id]);
        Product::updateOrCreate(['name' => 'Fattoush', 'vendor_id' => $alBustan->id], ['name_ar' => 'فتوش', 'price' => 9, 'category_id' => $lebanese->id]);
        Product::updateOrCreate(['name' => 'Baba Ghanouj', 'vendor_id' => $alBustan->id], ['name_ar' => 'بابا غنوج', 'price' => 9, 'category_id' => $lebanese->id]);

        // Leila's Kitchen
        Product::updateOrCreate(['name' => 'Mloukhiyeh', 'vendor_id' => $leila->id], ['name_ar' => 'ملوخية', 'price' => 20, 'category_id' => $lebanese->id]);
        Product::updateOrCreate(['name' => 'Mjadra', 'vendor_id' => $leila->id], ['name_ar' => 'مجدرة', 'price' => 12, 'category_id' => $lebanese->id]);
        Product::updateOrCreate(['name' => 'Freekeh with Chicken', 'vendor_id' => $leila->id], ['name_ar' => 'فريكة بالدجاج', 'price' => 24, 'category_id' => $lebanese->id]);
        Product::updateOrCreate(['name' => 'Koussa Mahshi', 'vendor_id' => $leila->id], ['name_ar' => 'كوسا محشي', 'price' => 18, 'category_id' => $lebanese->id]);

        // Saj Village
        Product::updateOrCreate(['name' => 'Saj Zaatar', 'vendor_id' => $saj->id], ['name_ar' => 'صاج زعتر', 'price' => 8, 'category_id' => $lebanese->id]);
        Product::updateOrCreate(['name' => 'Saj Cheese', 'vendor_id' => $saj->id], ['name_ar' => 'صاج جبن', 'price' => 10, 'category_id' => $lebanese->id]);
        Product::updateOrCreate(['name' => 'Manakeesh', 'vendor_id' => $saj->id], ['name_ar' => 'مناقيش', 'price' => 7, 'category_id' => $lebanese->id]);

        // Mekhzal
        Product::updateOrCreate(['name' => 'Bottled Water 1.5L', 'vendor_id' => $mekhzal->id], ['name_ar' => 'مياه معدنية ١.٥ لتر', 'price' => 3, 'category_id' => $beverages->id]);
        Product::updateOrCreate(['name' => 'Cola Can', 'vendor_id' => $mekhzal->id], ['name_ar' => 'علبة كولا', 'price' => 2, 'category_id' => $beverages->id]);
        Product::updateOrCreate(['name' => 'Orange Juice 1L', 'vendor_id' => $mekhzal->id], ['name_ar' => 'عصير برتقال ١ لتر', 'price' => 5, 'category_id' => $beverages->id]);
        Product::updateOrCreate(['name' => 'Milk 1L', 'vendor_id' => $mekhzal->id], ['name_ar' => 'حليب ١ لتر', 'price' => 4, 'category_id' => $dairy->id]);
        Product::updateOrCreate(['name' => 'Labneh 500g', 'vendor_id' => $mekhzal->id], ['name_ar' => 'لبنة ٥٠٠ غ', 'price' => 6, 'category_id' => $dairy->id]);
        Product::updateOrCreate(['name' => 'White Cheese 250g', 'vendor_id' => $mekhzal->id], ['name_ar' => 'جبنة بيضا ٢٥٠ غ', 'price' => 5, 'category_id' => $dairy->id]);
        Product::updateOrCreate(['name' => 'Dozen Eggs', 'vendor_id' => $mekhzal->id], ['name_ar' => 'بيض (دستة)', 'price' => 12, 'category_id' => $dairy->id]);
        Product::updateOrCreate(['name' => 'White Bread', 'vendor_id' => $mekhzal->id], ['name_ar' => 'خبز أبيض', 'price' => 4, 'category_id' => $bakery->id]);
        Product::updateOrCreate(['name' => 'Pita Bread Pack', 'vendor_id' => $mekhzal->id], ['name_ar' => 'خبز عربي', 'price' => 3, 'category_id' => $bakery->id]);

        // Fresh Basket
        Product::updateOrCreate(['name' => 'Apples 1kg', 'vendor_id' => $fresh->id], ['name_ar' => 'تفاح ١ كغ', 'price' => 7, 'category_id' => $market->id]);
        Product::updateOrCreate(['name' => 'Bananas 1kg', 'vendor_id' => $fresh->id], ['name_ar' => 'موز ١ كغ', 'price' => 6, 'category_id' => $market->id]);
        Product::updateOrCreate(['name' => 'Tomatoes 1kg', 'vendor_id' => $fresh->id], ['name_ar' => 'بندورة ١ كغ', 'price' => 5, 'category_id' => $market->id]);
        Product::updateOrCreate(['name' => 'Cucumbers 1kg', 'vendor_id' => $fresh->id], ['name_ar' => 'خيار ١ كغ', 'price' => 4, 'category_id' => $market->id]);
        Product::updateOrCreate(['name' => 'Onions 1kg', 'vendor_id' => $fresh->id], ['name_ar' => 'بصل ١ كغ', 'price' => 3, 'category_id' => $market->id]);
        Product::updateOrCreate(['name' => 'Potatoes 1kg', 'vendor_id' => $fresh->id], ['name_ar' => 'بطاطا ١ كغ', 'price' => 4, 'category_id' => $market->id]);

        // Diet Boutique
        Product::updateOrCreate(['name' => 'Protein Bar', 'vendor_id' => $diet->id], ['name_ar' => 'بروتين بار', 'price' => 8, 'category_id' => $market->id]);
        Product::updateOrCreate(['name' => 'Almond Milk 1L', 'vendor_id' => $diet->id], ['name_ar' => 'حليب لوز ١ لتر', 'price' => 7, 'category_id' => $dairy->id]);
        Product::updateOrCreate(['name' => 'Granola 500g', 'vendor_id' => $diet->id], ['name_ar' => 'جرانولا ٥٠٠ غ', 'price' => 9, 'category_id' => $market->id]);

        // Carefour Express
        Product::updateOrCreate(['name' => 'Rice 2kg', 'vendor_id' => $carefour->id], ['name_ar' => 'رز ٢ كغ', 'price' => 8, 'category_id' => $market->id]);
        Product::updateOrCreate(['name' => 'Pasta 500g', 'vendor_id' => $carefour->id], ['name_ar' => 'باستا ٥٠٠ غ', 'price' => 3, 'category_id' => $market->id]);
        Product::updateOrCreate(['name' => 'Canned Tuna', 'vendor_id' => $carefour->id], ['name_ar' => 'تونة', 'price' => 4, 'category_id' => $market->id]);
        Product::updateOrCreate(['name' => 'Cooking Oil 1L', 'vendor_id' => $carefour->id], ['name_ar' => 'زيت طبخ ١ لتر', 'price' => 7, 'category_id' => $market->id]);
        Product::updateOrCreate(['name' => 'Sugar 1kg', 'vendor_id' => $carefour->id], ['name_ar' => 'سكر ١ كغ', 'price' => 4, 'category_id' => $market->id]);

        // Haykal Pharmacy
        Product::updateOrCreate(['name' => 'Paracetamol 500mg', 'vendor_id' => $haykal->id], ['name_ar' => 'باراسيتامول ٥٠٠ ملغ', 'price' => 5, 'category_id' => $pharmacy->id]);
        Product::updateOrCreate(['name' => 'Vitamin C 1000mg', 'vendor_id' => $haykal->id], ['name_ar' => 'فيتامين سي ١٠٠٠ ملغ', 'price' => 12, 'category_id' => $pharmacy->id]);
        Product::updateOrCreate(['name' => 'Face Mask Box (50)', 'vendor_id' => $haykal->id], ['name_ar' => 'علبة كمامات (٥٠)', 'price' => 8, 'category_id' => $pharmacy->id]);
        Product::updateOrCreate(['name' => 'Hand Sanitizer 250ml', 'vendor_id' => $haykal->id], ['name_ar' => 'معقم يدين ٢٥٠ مل', 'price' => 6, 'category_id' => $pharmacy->id]);
        Product::updateOrCreate(['name' => 'Multivitamin Jar', 'vendor_id' => $haykal->id], ['name_ar' => 'فيتامينات متعددة', 'price' => 20, 'category_id' => $pharmacy->id]);

        // Sterile Pharmacy
        Product::updateOrCreate(['name' => 'Bandages Box', 'vendor_id' => $sterile->id], ['name_ar' => 'علبة ضمادات', 'price' => 5, 'category_id' => $pharmacy->id]);
        Product::updateOrCreate(['name' => 'Antiseptic Cream', 'vendor_id' => $sterile->id], ['name_ar' => 'كريم مطهر', 'price' => 7, 'category_id' => $pharmacy->id]);
        Product::updateOrCreate(['name' => 'Baby Diapers Pack', 'vendor_id' => $sterile->id], ['name_ar' => 'حفاظات أطفال', 'price' => 25, 'category_id' => $pharmacy->id]);

        // Cleanz
        Product::updateOrCreate(['name' => 'Dish Soap 500ml', 'vendor_id' => $cleanz->id], ['name_ar' => 'سائل غسيل الصحون ٥٠٠ مل', 'price' => 6, 'category_id' => $cleaning->id]);
        Product::updateOrCreate(['name' => 'Bleach 1L', 'vendor_id' => $cleanz->id], ['name_ar' => 'مبيّض ١ لتر', 'price' => 5, 'category_id' => $cleaning->id]);
        Product::updateOrCreate(['name' => 'Floor Cleaner 1L', 'vendor_id' => $cleanz->id], ['name_ar' => 'منظف أرضيات ١ لتر', 'price' => 7, 'category_id' => $cleaning->id]);
        Product::updateOrCreate(['name' => 'Glass Cleaner 500ml', 'vendor_id' => $cleanz->id], ['name_ar' => 'منظف زجاج ٥٠٠ مل', 'price' => 6, 'category_id' => $cleaning->id]);
        Product::updateOrCreate(['name' => 'Laundry Detergent 1kg', 'vendor_id' => $cleanz->id], ['name_ar' => 'مسحوق غسيل ١ كغ', 'price' => 12, 'category_id' => $cleaning->id]);

        // Electro Star
        Product::updateOrCreate(['name' => 'USB Cable', 'vendor_id' => $electro->id], ['name_ar' => 'كابل USB', 'price' => 8, 'category_id' => $electronics->id]);
        Product::updateOrCreate(['name' => 'Phone Charger', 'vendor_id' => $electro->id], ['name_ar' => 'شاحن جوال', 'price' => 15, 'category_id' => $electronics->id]);
        Product::updateOrCreate(['name' => 'Power Bank 10000mAh', 'vendor_id' => $electro->id], ['name_ar' => 'باور بانك ١٠٠٠٠ مللي أمبير', 'price' => 30, 'category_id' => $electronics->id]);
        Product::updateOrCreate(['name' => 'Headphones', 'vendor_id' => $electro->id], ['name_ar' => 'سماعات', 'price' => 22, 'category_id' => $electronics->id]);
        Product::updateOrCreate(['name' => 'Bluetooth Speaker', 'vendor_id' => $electro->id], ['name_ar' => 'مكبر بلوتوث', 'price' => 35, 'category_id' => $electronics->id]);

        // Furn Abousamra
        Product::updateOrCreate(['name' => 'Pita Bread Pack', 'vendor_id' => $frencho->id], ['name_ar' => 'خبز عربي', 'price' => 3, 'category_id' => $bakery->id]);
        Product::updateOrCreate(['name' => 'Baguette', 'vendor_id' => $frencho->id], ['name_ar' => 'باغيت', 'price' => 4, 'category_id' => $bakery->id]);
        Product::updateOrCreate(['name' => 'Croissant', 'vendor_id' => $frencho->id], ['name_ar' => 'كرواسان', 'price' => 5, 'category_id' => $bakery->id]);
        Product::updateOrCreate(['name' => 'Kaak with Sesame', 'vendor_id' => $frencho->id], ['name_ar' => 'كعك بالسمسم', 'price' => 3, 'category_id' => $bakery->id]);

        // Fleur De Vie
        Product::updateOrCreate(['name' => 'Mixed Bouquet', 'vendor_id' => $fleur->id], ['name_ar' => 'باقة مشكلة', 'price' => 40, 'category_id' => $flowers->id]);
        Product::updateOrCreate(['name' => 'Red Roses (Dozen)', 'vendor_id' => $fleur->id], ['name_ar' => 'ورود حمراء (دستة)', 'price' => 55, 'category_id' => $flowers->id]);
        Product::updateOrCreate(['name' => 'Birthday Gift Box', 'vendor_id' => $fleur->id], ['name_ar' => 'علبة هدية عيد ميلاد', 'price' => 35, 'category_id' => $flowers->id]);
        Product::updateOrCreate(['name' => 'Potted Plant', 'vendor_id' => $fleur->id], ['name_ar' => 'نبتة منزلية', 'price' => 25, 'category_id' => $flowers->id]);

        // ===================== UNIVERSAL CATALOG PRODUCTS (vendor_id = NULL) =====================
        Product::updateOrCreate(['name' => 'Bottled Water 500ml', 'vendor_id' => null], ['name_ar' => 'مياه معدنية ٥٠٠ مل', 'price' => 2, 'category_id' => $beverages->id]);
        Product::updateOrCreate(['name' => 'Energy Drink', 'vendor_id' => null], ['name_ar' => 'مشروب طاقة', 'price' => 6, 'category_id' => $beverages->id]);
        Product::updateOrCreate(['name' => 'Candy Bar', 'vendor_id' => null], ['name_ar' => 'شوكولاتة', 'price' => 3, 'category_id' => $market->id]);
        Product::updateOrCreate(['name' => 'Chips Pack', 'vendor_id' => null], ['name_ar' => 'كيس شيبس', 'price' => 2, 'category_id' => $market->id]);
        Product::updateOrCreate(['name' => 'Disposable Razor', 'vendor_id' => null], ['name_ar' => 'موس حلاقة', 'price' => 3, 'category_id' => $market->id]);

        // ===================== DRIVERS =====================
        Driver::updateOrCreate(['phone' => '71111111'], ['name' => 'Ahmed', 'vehicle_type' => 'motorcycle', 'status' => 'available']);
        Driver::updateOrCreate(['phone' => '76111111'], ['name' => 'Khaled', 'vehicle_type' => 'car', 'status' => 'available']);
        Driver::updateOrCreate(['phone' => '70333333'], ['name' => 'Sami', 'vehicle_type' => 'bicycle', 'status' => 'offline']);
        Driver::updateOrCreate(['phone' => '78888888'], ['name' => 'Hassan', 'vehicle_type' => 'motorcycle', 'status' => 'available']);
        Driver::updateOrCreate(['phone' => '79123456'], ['name' => 'Ali', 'vehicle_type' => 'car', 'status' => 'available']);
        Driver::updateOrCreate(['phone' => '76567890'], ['name' => 'Mohammad', 'vehicle_type' => 'motorcycle', 'status' => 'available']);
        Driver::updateOrCreate(['phone' => '71717171'], ['name' => 'Hussein', 'vehicle_type' => 'car', 'status' => 'available']);
        Driver::updateOrCreate(['phone' => '76767676'], ['name' => 'Bilal', 'vehicle_type' => 'motorcycle', 'status' => 'busy']);
        Driver::updateOrCreate(['phone' => '78787878'], ['name' => 'Omar', 'vehicle_type' => 'car', 'status' => 'available']);
        Driver::updateOrCreate(['phone' => '70909090'], ['name' => 'Youssef', 'vehicle_type' => 'motorcycle', 'status' => 'available']);
    }
}
