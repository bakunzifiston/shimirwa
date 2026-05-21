<?php

return [

    'name' => 'SHIMIRWA COMPANY Ltd',
    'tagline' => 'Empowering local agriculture through high-quality processing, efficient supply chains, and reliable distribution.',
    'logo' => env('ADMIN_LOGO', 'images/shimirwa-logo.jpg'),

    'hero' => [
        'eyebrow' => 'Welcome',
        'headline' => 'Welcome to SHIMIRWA COMPANY Ltd',
        'lead' => 'Empowering local agriculture through high-quality processing, efficient supply chains, and reliable distribution.',
        'description' => 'At SHIMIRWA COMPANY Ltd, we specialize in sourcing, processing, and packaging premium agricultural products such as maize, sorghum, and soy.',
        'card_text' => 'Maize · Sorghum · Soy',
    ],

    'banners' => [
        [
            'image' => 'images/banners/banner-products.png',
            'alt' => 'BINO and KURA flour range — composite, wheat, sorghum, and millet flours made in Rwanda',
        ],
        [
            'image' => 'images/banners/banner-brand-ambassador.png',
            'alt' => 'BINO and KURA composite flour — proudly made in Rwanda',
        ],
    ],

    'mission' => [
        'title' => 'Our Mission',
        'text' => 'To process and deliver high-quality agricultural products while supporting local producers and ensuring food safety.',
    ],

    'vision' => [
        'title' => 'Our Vision',
        'text' => 'To become a leading agro-processing company in East Africa known for quality, innovation, and impact.',
    ],

    'contact' => [
        'email' => env('SITE_CONTACT_EMAIL', 'info@shimirwaltd.rw'),
        'phone' => env('SITE_CONTACT_PHONE', '+250 788 000 000'),
        'address' => 'Kigali, Rwanda',
        'map_embed' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d255948.261439353!2d30.061885!3d-1.970579!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x19dcaefe8c04c0b1%3A0x5fa6f4272e6fae1!2sKigali%2C%20Rwanda!5e0!3m2!1sen!2s!4v1700000000000!5m2!1sen!2s',
    ],

    'social' => [
        ['label' => 'Facebook', 'url' => '#', 'icon' => 'facebook'],
        ['label' => 'Instagram', 'url' => '#', 'icon' => 'instagram'],
        ['label' => 'LinkedIn', 'url' => '#', 'icon' => 'linkedin'],
    ],

    'navigation' => [
        ['label' => 'Home', 'route' => 'home'],
        ['label' => 'About Us', 'route' => 'about'],
        ['label' => 'Shop', 'route' => 'shop.index'],
        ['label' => 'Contact Us', 'route' => 'contact'],
    ],

    'stats' => [
        ['value' => '15+', 'label' => 'Years of excellence'],
        ['value' => '50+', 'label' => 'Partner retailers'],
        ['value' => '12K+', 'label' => 'Kg processed monthly'],
        ['value' => '100%', 'label' => 'Quality assured'],
    ],

    'about' => [
        'hero_eyebrow' => 'About Us',
        'hero_title' => 'SHIMIRWA COMPANY Ltd',
        'who_we_are_title' => 'Who We Are',
        'who_we_are' => 'SHIMIRWA COMPANY Ltd is a proudly Rwandan agri-business dedicated to adding value to local produce and driving agricultural transformation. By working hand-in-hand with smallholder farmers and cooperatives, we create fair market opportunities, promote sustainable practices, and ensure traceability from farm to shelf.',
        'values_title' => 'Our Core Values',
        'values' => [
            [
                'number' => '01',
                'title' => 'Integrity & Trust',
                'text' => 'We build lasting relationships through honesty and transparency.',
            ],
            [
                'number' => '02',
                'title' => 'Quality Assurance',
                'text' => 'Every product undergoes strict control to meet the highest standards.',
            ],
            [
                'number' => '03',
                'title' => 'Sustainability',
                'text' => 'We prioritize eco-friendly practices and support long-term farmer livelihoods.',
            ],
            [
                'number' => '04',
                'title' => 'Innovation',
                'text' => 'We continuously improve processes to stay ahead of market needs.',
            ],
        ],
    ],

    'values' => [
        ['title' => 'Quality', 'text' => 'Rigorous standards from reception through packaging and delivery.'],
        ['title' => 'Integrity', 'text' => 'Transparent sourcing and honest relationships with farmers and clients.'],
        ['title' => 'Innovation', 'text' => 'Modern processing that preserves nutrition and taste.'],
        ['title' => 'Community', 'text' => 'Supporting local agriculture and sustainable livelihoods.'],
    ],

    'team' => [
        ['name' => 'Leadership Team', 'role' => 'Operations & quality', 'bio' => 'Experienced professionals overseeing production and supply chain.'],
        ['name' => 'Production Unit', 'role' => 'Roasting & milling', 'bio' => 'Skilled technicians ensuring consistent output at every batch.'],
        ['name' => 'Client Services', 'role' => 'Sales & support', 'bio' => 'Dedicated to responsive service for retailers and distributors.'],
    ],

    'milestones' => [
        ['year' => '2010', 'title' => 'Founded', 'text' => 'Shimirwa began processing soybean products in Rwanda.'],
        ['year' => '2016', 'title' => 'Expanded facility', 'text' => 'Increased capacity for roasting, milling, and packaging.'],
        ['year' => '2022', 'title' => 'Digital operations', 'text' => 'Integrated inventory management for end-to-end traceability.'],
        ['year' => 'Today', 'title' => 'Growing reach', 'text' => 'Serving clients across Rwanda with premium product lines.'],
    ],

    'testimonials' => [
        ['quote' => 'Consistent quality and reliable delivery. Our customers love the flour products.', 'author' => 'Retail partner, Kigali'],
        ['quote' => 'Professional team and transparent processes. A trusted supplier for our chain.', 'author' => 'Distribution manager'],
        ['quote' => 'The packaging and freshness stand out. Highly recommended for bulk orders.', 'author' => 'Hospitality buyer'],
    ],

    'product_categories' => [
        'all' => 'All products',
        'flour' => 'Flour',
        'packaged' => 'Packaged goods',
        'bulk' => 'Bulk supply',
    ],

    'products' => [
        [
            'slug' => 'premium-soy-flour-1kg',
            'name' => 'Premium Soy Flour — 1kg',
            'category' => 'packaged',
            'price' => 3500,
            'currency' => 'RWF',
            'image' => null,
            'badge' => 'Bestseller',
            'short' => 'Fine-milled soy flour for baking and cooking.',
            'description' => 'Our 1kg premium soy flour is milled from carefully selected soybeans, roasted and processed to preserve protein and flavor. Ideal for households and small retailers.',
            'features' => ['High protein', 'Fine texture', 'Sealed for freshness'],
        ],
        [
            'slug' => 'premium-soy-flour-5kg',
            'name' => 'Premium Soy Flour — 5kg',
            'category' => 'packaged',
            'price' => 15000,
            'currency' => 'RWF',
            'image' => null,
            'badge' => null,
            'short' => 'Economical pack for families and small shops.',
            'description' => 'Five kilograms of our signature soy flour — the same quality as our 1kg line in a value size for regular use.',
            'features' => ['Value size', 'Consistent batch quality', 'Easy storage'],
        ],
        [
            'slug' => 'soy-flour-bulk-25kg',
            'name' => 'Soy Flour — Bulk 25kg',
            'category' => 'bulk',
            'price' => 65000,
            'currency' => 'RWF',
            'image' => null,
            'badge' => 'Wholesale',
            'short' => 'Bulk supply for distributors and food businesses.',
            'description' => 'Twenty-five kilogram sacks for wholesalers, bakeries, and institutional buyers. Traceable batches from our milling facility.',
            'features' => ['Wholesale pricing', 'Batch traceability', 'Delivery available'],
        ],
        [
            'slug' => 'roasted-soy-grits',
            'name' => 'Roasted Soy Grits',
            'category' => 'flour',
            'price' => 4200,
            'currency' => 'RWF',
            'image' => null,
            'badge' => null,
            'short' => 'Versatile roasted grits for traditional and modern recipes.',
            'description' => 'Coarser roasted soy product with rich aroma. Popular for porridge blends and specialty foods.',
            'features' => ['Roasted in-house', 'Rich flavor', 'Multiple uses'],
        ],
        [
            'slug' => 'fortified-blend-1kg',
            'name' => 'Fortified Soy Blend — 1kg',
            'category' => 'packaged',
            'price' => 3800,
            'currency' => 'RWF',
            'image' => null,
            'badge' => 'New',
            'short' => 'Nutrient-focused blend for health-conscious consumers.',
            'description' => 'A carefully balanced soy blend designed for nutritional programs and retail health segments.',
            'features' => ['Fortified formula', 'Retail-ready pack', 'Quality tested'],
        ],
        [
            'slug' => 'mixed-grain-soy-mix',
            'name' => 'Mixed Grain & Soy Mix',
            'category' => 'flour',
            'price' => 4000,
            'currency' => 'RWF',
            'image' => null,
            'badge' => null,
            'short' => 'Blend of milled grains and soy for porridge and baking.',
            'description' => 'Combines our milled soy with sorghum and maize for a hearty, nutritious mix processed at our facility.',
            'features' => ['Multi-grain', 'Balanced nutrition', 'Local ingredients'],
        ],
    ],

];
