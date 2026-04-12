<?php

return [

    'resource_label' => 'نوع البحث',
    'resource_plural' => 'أنواع البحث',
    'page_title' => 'القوائم المرجعية',
    'no_types' => 'لا توجد أنواع بحث محددة. قم بتشغيل php artisan lookups:sync لإنشائها من الإعدادات.',

    'tenancy_mode' => [
        'shared' => 'مشترك',
        'tenant' => 'خاص بالمستأجر',
        'both' => 'كلاهما',
    ],

    'form' => [
        'type_details' => 'تفاصيل النوع',
        'name' => 'الاسم',
        'slug' => 'المعرّف',
        'slug_helper' => 'معرّف فريد يُستخدم للإشارة إلى نوع البحث في الكود.',
        'description' => 'الوصف',
        'settings' => 'الإعدادات',
        'is_hierarchical' => 'هرمي',
        'is_hierarchical_helper' => 'تمكين العلاقات بين القيم الأب والابن.',
        'is_active' => 'نشط',
        'sort_order' => 'ترتيب الفرز',
        'tenancy_mode' => 'وضع الإيجار',
    ],

    'table' => [
        'name' => 'الاسم',
        'slug' => 'المعرّف',
        'is_hierarchical' => 'هرمي',
        'is_active' => 'نشط',
        'tenancy_mode' => 'وضع الإيجار',
        'values_count' => 'القيم',
        'created_at' => 'تاريخ الإنشاء',
    ],

    'filters' => [
        'is_active' => 'الحالة',
        'active' => 'نشط',
        'inactive' => 'غير نشط',
    ],

    'relation_manager' => [
        'title' => 'قيم البحث',
    ],

    'value_form' => [
        'name' => 'الاسم',
        'code' => 'الرمز',
        'code_helper' => 'رمز فريد داخل نوع البحث للوصول البرمجي.',
        'parent' => 'القيمة الأب',
        'no_parent' => 'بدون أب (المستوى الجذر)',
        'description' => 'الوصف',
        'is_active' => 'نشط',
        'sort_order' => 'ترتيب الفرز',
        'metadata' => 'البيانات الوصفية',
        'metadata_key' => 'المفتاح',
        'metadata_value' => 'القيمة',
    ],

    'value_table' => [
        'name' => 'الاسم',
        'code' => 'الرمز',
        'parent' => 'الأب',
        'is_active' => 'نشط',
        'sort_order' => 'ترتيب الفرز',
        'children_count' => 'الأبناء',
    ],

];
