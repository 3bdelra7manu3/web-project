# تطبيق رعايتي الصحية - توثيق التطبيق

## جدول المحتويات
1. [نظرة عامة](#overview)
2. [التثبيت](#installation)
3. [المميزات](#features)
4. [هيكل الملفات](#file-structure)
5. [هيكل قاعدة البيانات](#database-structure)
6. [شرح فئات Bootstrap](#bootstrap-classes-explained)
7. [وظائف وطرق PHP](#php-functions--methods)
8. [اعتبارات الأمان](#security-considerations)
9. [تحسينات مستقبلية](#future-enhancements)
10. [دعم اللغة العربية](#arabic-support)
11. [الوضع المظلم](#dark-mode)

## نظرة عامة <a name="overview"></a>

"رعايتي" هو تطبيق ويب مبسط لإدارة الصحة الشخصية تم بناؤه باستخدام PHP وMySQL وJavaScript وBootstrap. يساعد المستخدمين على تتبع مختلف جوانب صحتهم، مع التركيز على المواعيد والأدوية. تم تصميم التطبيق بهيكل كود سهل الفهم للمبتدئين لسهولة الفهم والصيانة. يدعم التطبيق اللغة العربية بشكل كامل ويوفر وضع مظلم لتحسين تجربة المستخدم.

## Installation

### Requirements
- XAMPP (or any server with PHP 7.0+ and MySQL)
- Web browser

### Setup Instructions
1. Install XAMPP on your computer
2. Clone or place the application files in the `c:\xampp\htdocs\health\` directory
3. Start the Apache and MySQL services in XAMPP control panel
4. Access the application through your browser: `http://localhost/health/`
5. The database will be automatically created on first access
6. Register for a new account and start using the application

## المميزات <a name="features"></a>

### مصادقة المستخدم
- تسجيل المستخدم مع التحقق من البريد الإلكتروني
- نظام تسجيل دخول آمن مع تشفير كلمات المرور
- إدارة الجلسات للمستخدمين المصادق عليهم
- نظام مصادقة موحد في ملف واحد لإدارة مبسطة

### لوحة التحكم
- نظرة عامة على المواعيد القادمة
- إحصائيات سريعة عن الأدوية النشطة والمواعيد القادمة
- تتبع حالة الدواء والجرعات
- تنبيهات إعادة التعبئة للأدوية التي توشك على النفاد

### المواعيد
- جدولة المواعيد مع مقدمي الرعاية الصحية
- تسجيل تفاصيل الموعد بما في ذلك الطبيب والموقع والملاحظات
- عرض المواعيد القادمة بسهولة
- الاحتفاظ بسجل المواعيد السابقة

### الأدوية
- تتبع الأدوية مع الجرعة والتكرار والتعليمات
- تسجيل وقت تناول كل جرعة دواء
- مراقبة المخزون المتبقي من الحبوب
- الحصول على تنبيهات عند الحاجة إلى إعادة التعبئة
- عرض سجل الأدوية
- تسجيل سريع للجرعات من لوحة التحكم

### دعم اللغة العربية
- واجهة مستخدم كاملة باللغة العربية
- دعم RTL للتنسيق العربي
- عرض التواريخ والأوقات بالتنسيق العربي

### الوضع المظلم
- تصميم مظلم كامل لراحة العين
- ألوان متناسقة للنص والخلفية
- تحسين تجربة المستخدم في الإضاءة المنخفضة

## هيكل الملفات <a name="file-structure"></a>

### الملفات الرئيسية
- `index.php`: صفحة تسجيل الدخول والتسجيل
- `config.php`: اتصال قاعدة البيانات وإعداد الجداول
- `dashboard.php`: لوحة التحكم الرئيسية مع نظرة عامة على بيانات الصحة
- `appointments.php`: لإدارة مواعيد الطبيب (مع معالجة متكاملة)
- `medications.php`: لإدارة الأدوية وتسجيل الجرعات (مع معالجة متكاملة)
- `logout.php`: للخروج من التطبيق وإنهاء الجلسة

### دليل الأصول
- `assets/css/`: يحتوي على أوراق الأنماط للتطبيق
- `assets/js/app.js`: يحتوي على وظائف JavaScript للتطبيق

### ملفات اللغة
- `lang/`: دليل ملفات اللغة
- `lang/ar.php`: ملف ترجمات اللغة العربية
- `lang/config.php`: ملف إعدادات اللغة

## Database Structure

The application uses a simple database structure with three main tables. The database is automatically created and tables are set up when you first access the application through the `config.php` file.

### Users Table
```sql
CREATE TABLE users (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,  -- Securely hashed passwords
    email VARCHAR(100) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)
```

### Appointments Table
```sql
CREATE TABLE appointments (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,        -- Links to the user who owns this appointment
    title VARCHAR(100) NOT NULL,     -- Appointment title
    doctor VARCHAR(100),             -- Doctor name
    location VARCHAR(100),           -- Where the appointment is
    notes TEXT,                      -- Any additional information
    appointment_date DATETIME NOT NULL, -- When the appointment is scheduled
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP, -- When this record was created
    FOREIGN KEY (user_id) REFERENCES users(id)
)
```

### Medications Table
```sql
CREATE TABLE medications (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,           -- Links to the user who owns this medication
    name VARCHAR(100) NOT NULL,         -- Medication name
    dosage VARCHAR(50) NOT NULL,        -- How much to take (e.g., "100mg")
    frequency VARCHAR(100) NOT NULL,    -- How often to take it
    start_date DATE NOT NULL,           -- When to start taking this
    end_date DATE,                      -- When to stop taking this (optional)
    instructions TEXT,                  -- Any additional instructions
    remaining INT(11),                  -- Pills remaining (optional)
    refill_reminder BOOLEAN DEFAULT 0,  -- Whether to remind about refills
    refill_reminder_threshold INT(5) DEFAULT 5, -- At what count to show reminder
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP, -- When this record was created
    FOREIGN KEY (user_id) REFERENCES users(id)
)
```

### Medication Logs Table
```sql
CREATE TABLE medication_logs (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    medication_id INT(11) NOT NULL,      -- Links to the medication taken
    user_id INT(11) NOT NULL,            -- Links to the user who took it
    taken_at DATETIME NOT NULL,          -- When it was taken
    notes TEXT,                          -- Any notes about taking it
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP, -- When this log was created
    FOREIGN KEY (medication_id) REFERENCES medications(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
)
```

## Bootstrap Classes Explained

### Layout Classes
- `container`: Container for responsive layout with margins
- `row`: Creates a horizontal group of columns
- `col-*`: Column within a row (responsive grid)
- `col-md-*`: Column that adjusts based on medium screen sizes
- `col-sm-*`: Column that adjusts based on small screen sizes
- `col-lg-*`: Column that adjusts based on large screen sizes

### Spacing Classes
- `mt-*`: Margin top
- `mb-*`: Margin bottom
- `me-*`: Margin right (end)
- `ms-*`: Margin left (start)
- `my-*`: Margin top and bottom
- `mx-*`: Margin left and right
- `pt-*`: Padding top
- `pb-*`: Padding bottom
- `pe-*`: Padding right (end)
- `ps-*`: Padding left (start)
- `py-*`: Padding top and bottom
- `px-*`: Padding left and right
- `gap-*`: Gap between elements in flex/grid layouts

### Component Classes
- `card`: Creates a bordered box with padding
- `card-header`: Header part of a card
- `card-body`: Body part of a card
- `card-footer`: Footer part of a card
- `form-control`: Styling for form inputs
- `form-label`: Styling for form labels
- `form-check`: Styling for checkboxes and radio buttons
- `btn`: Basic button 
- `btn-primary`: Primary action button (blue)
- `btn-secondary`: Secondary action button (gray)
- `btn-success`: Success action button (green)
- `btn-danger`: Danger/delete button (red)
- `btn-warning`: Warning button (yellow)
- `btn-info`: Information button (light blue)
- `btn-outline-*`: Outlined version of buttons
- `alert`: Alert box
- `alert-success`: Success alert (green)
- `alert-danger`: Danger alert (red)
- `alert-warning`: Warning alert (yellow)
- `alert-info`: Information alert (blue)
- `table`: Styling for tables
- `table-hover`: Adds hover effect to table rows
- `badge`: Badge/label for highlighting
- `modal`: Modal dialog box
- `navbar`: Navigation bar
- `nav-tabs`: Tabbed navigation interface

### Utility Classes
- `text-center`: Center-aligns text
- `text-primary`, `text-secondary`, etc.: Text colors
- `bg-primary`, `bg-secondary`, etc.: Background colors
- `d-flex`: Display as flexbox
- `justify-content-*`: Justify content in flexbox
- `align-items-*`: Align items in flexbox
- `fw-bold`: Bold text
- `fs-*`: Font size
- `shadow`: Box shadow effect
- `rounded`: Rounded corners
- `d-grid`: Grid layout for buttons, etc.

## دعم اللغة العربية <a name="arabic-support"></a>

تم تصميم التطبيق ليدعم اللغة العربية بشكل كامل، مع مراعاة الاتجاه من اليمين إلى اليسار (RTL) واستخدام تنسيقات التاريخ والوقت المناسبة للمستخدمين العرب.

### نظام الترجمة

يستخدم التطبيق نظامًا بسيطًا للترجمة يعتمد على الملفات التالية:

- `lang/config.php`: يقوم بتعيين اللغة الافتراضية (العربية) وتحميل ملف الترجمات المناسب
- `lang/ar.php`: يحتوي على كافة النصوص المترجمة للغة العربية كمصفوفة

### طريقة استخدام الترجمات

لعرض نص مترجم في التطبيق، يتم استخدام دالة `__()` كما يلي:

```php
<?php echo __('key_name'); ?>
```

حيث `key_name` هو مفتاح النص المراد ترجمته في ملف اللغة.

### دعم RTL

تم تطبيق دعم الاتجاه من اليمين إلى اليسار (RTL) باستخدام:

- تعديل اتجاه HTML باستخدام `dir="rtl"`
- استخدام دوال مساعدة مثل `get_align()` و `get_opposite_align()` لضمان التنسيق الصحيح للعناصر
- تعديل هوامش العناصر لتتناسب مع الاتجاه العربي

### تنسيقات التاريخ والوقت

تمت ترجمة كافة تنسيقات التاريخ والوقت في التطبيق من الإنجليزية إلى العربية، بما في ذلك:

- تحويل أسماء الأشهر الإنجليزية إلى أسماء الأشهر العربية
- تحويل علامات AM/PM إلى ص/م بالعربية

## الوضع المظلم <a name="dark-mode"></a>

تم تنفيذ وضع مظلم كامل لتطبيق "رعايتي" لتحسين تجربة المستخدم وراحة العين، خاصة في بيئات الإضاءة المنخفضة.

### التنفيذ التقني

تم تنفيذ الوضع المظلم عن طريق:

1. **متغيرات CSS**: تم تعريف متغيرات CSS لألوان النص والخلفية بحيث يمكن تغييرها بسهولة
2. **تعديل النمط**: تم تطبيق ألوان الخلفية الداكنة وألوان النص الفاتحة على كافة العناصر
3. **تناقض كافٍ**: تم الحرص على وجود تباين كافٍ بين النص والخلفية لتحسين القراءة

### عناصر الواجهة المظلمة

تم تطبيق الوضع المظلم على العناصر التالية:

- خلفية الصفحات الرئيسية
- البطاقات والجداول
- النماذج وحقول الإدخال
- أزرار التنقل وشريط التنقل
- مربعات الحوار والتنبيهات

### ألوان التصميم

تستخدم الواجهة المظلمة مجموعة ألوان متناسقة تتضمن:

- خلفيات داكنة مع تدرجات لطيفة لتقليل إجهاد العين
- نصوص فاتحة مع تباين جيد للقراءة
- ألوان أساسية زاهية (الأزرق، الأخضر، إلخ) للعناصر التفاعلية
- ألوان ثانوية متناسقة للعناصر الأقل أهمية

### تحسينات إضافية

- تم تحسين تأثيرات التحويم (hover) والتركيز (focus) لتكون أكثر وضوحًا في الوضع المظلم
- تم ضبط ظلال البطاقات والعناصر لتكون أكثر ملاءمة للوضع المظلم
- تم تعديل ألوان الشارات والأزرار لضمان وضوحها على الخلفيات الداكنة

## PHP Functions & Methods

### Core Files
- `config.php`: Sets up database connection and creates tables if needed
- `auth.php`: Handles all authentication functions (login, register, logout)

### Main Processing Functions

#### Authentication (auth.php)
- Login processing: Validates credentials and starts user session
- Registration: Validates and creates new user accounts
- Logout: Destroys user session

#### Appointments (appointments.php)
- Add appointment: Adds a new appointment record
- Update appointment: Modifies an existing appointment
- Delete appointment: Removes an appointment
- Verify ownership: Security check to ensure users only access their own data

#### Medications (medications.php)
- Add medication: Adds a new medication record
- Update medication: Modifies an existing medication
- Delete medication: Removes a medication and related logs
- Log dose: Records when medication is taken
- Update remaining: Updates pill count
- Verify ownership: Reusable function to check medication belongs to current user

### Helper Functions
- `verifyMedicationOwnership()`: Ensures users can only access their own medications
- `formatDate()`: Formats date for display (app.js)
- `setActiveNavLink()`: Highlights active navigation link (app.js)
- `count()`: Counts elements in an array
- `empty()`: Determines whether a variable is empty
- `isset()`: Determines if a variable is set and is not NULL

## Security Considerations

### Input Validation
- All user inputs are validated server-side before processing
- Required fields are checked to prevent empty submissions
- Email format validation for registration

### SQL Injection Prevention
- Prepared statements used for all database queries
- Parameter binding to separate SQL code from user input
- No direct inclusion of user input in SQL queries

### Authentication & Authorization
- Passwords are securely hashed using PHP's `password_hash()` function
- Password verification using `password_verify()`
- Session management to maintain login state
- Ownership verification to ensure users only access their own data

### Output Sanitization
- Data is sanitized before display with `htmlspecialchars()`
- Prevents XSS (Cross-Site Scripting) attacks

## Future Enhancements

### Possible Features
- Email notifications for upcoming appointments
- Improved medication schedule visualization
- Data export functionality (PDF, CSV)
- Medication interaction warnings
- Doctor/pharmacy contact management
- Health metrics tracking (weight, blood pressure, etc.)
- Calendar integration with Google/Apple calendars
- Prescription refill tracking and reminders

### Technical Improvements
- AJAX for smoother user experience without page reloads
- Responsive design improvements for mobile devices
- Improved data visualization with charts
- Accessibility enhancements
- More detailed error handling and user feedback
- Dark mode option for the UI
- Multi-language support
