<?php
/*
 * Menu items and prices. To change a price, edit the 'price' value.
 * 'price_large' is optional and only used for drinks that come in two sizes.
 * Allergen codes go in 'allergens' as an array, e.g. ['G', 'D'].
 *
 * Copied from The Coffee Bean's own menu boards (photos, Nov 2026):
 *   - food: the newest board ("Breakfast & Brunch 7:45–15:30", Dubliner €15.95)
 *   - drinks: the Coffee / Iced Coffee / Juices & Smoothies board
 *   - salads: only on an OLDER board, so check those three prices with the café
 *
 * Prices stay hidden until the café confirms them: flip $site['show_prices']
 * in config.php. The boards print allergen numbers but they are too small to
 * read reliably in the photos, so 'allergens' is empty — get them from the
 * café and fill them in. Never guess an allergen code.
 */

$allergenKey = [
    'G'  => 'Gluten (wheat/barley/oats)',
    'D'  => 'Dairy (milk/cheese/cream/butter)',
    'E'  => 'Eggs',
    'F'  => 'Fish',
    'N'  => 'Nuts',
    'Se' => 'Sesame',
    'So' => 'Soy',
    'Mu' => 'Mustard',
    'Ce' => 'Celery',
    'S'  => 'Sulphites',
];

$menu = [
    [
        'id'    => 'breakfast',
        'title' => 'Breakfast & brunch',
        'note'  => '7:45 to 15:30',
        'items' => [
            [
                'name' => 'The Dubliner', 'price' => '15.95', 'allergens' => [],
                'desc' => 'Two sausages, two rashers, fried or scrambled egg, mushrooms, beans, hash brown, tomatoes and toast, with tea, Americano or a can.',
                'extra' => [],
            ],
            [
                'name' => 'The Veggie', 'price' => '14.45', 'allergens' => [],
                'desc' => 'Fried or scrambled egg, mushrooms, beans, hash brown, tomatoes, avocado and toast, with tea, Americano or a can.',
                'extra' => [],
            ],
            [
                'name' => 'Eggs Benedict', 'price' => '13.95', 'allergens' => [],
                'desc' => 'Poached eggs, honey roasted ham, hollandaise and chilli flakes on an English muffin.',
                'extra' => ['Avocado eggs Benedict also available'],
            ],
            [
                'name' => 'Eggs Royale', 'price' => '14.25', 'allergens' => [],
                'desc' => 'Poached eggs, Irish smoked salmon, hollandaise and chilli flakes on an English muffin.',
                'extra' => [],
            ],
            [
                'name' => '3 egg omelette', 'price' => '11.95', 'allergens' => [],
                'desc' => 'Free range eggs with three fillings of your choice (1 meat and 2 veg, or 3 veg), with side salad or toast.',
                'extra' => [],
            ],
            [
                'name' => 'Homemade buttermilk pancakes', 'price' => '13.45', 'allergens' => [],
                'desc' => 'With maple syrup. Choose streaky bacon or seasonal fruit.',
                'extra' => ['Gluten free buttermilk pancakes available €15.45'],
            ],
            [
                'name' => 'Brioche French toast', 'price' => '13.45', 'allergens' => [],
                'desc' => 'With maple syrup. Choose streaky bacon, or berries and walnut.',
                'extra' => [],
            ],
            [
                'name' => 'Croque monsieur', 'price' => '9.25', 'allergens' => [],
                'desc' => 'Honey roasted ham, Monterey Jack and house béchamel.',
                'extra' => [],
            ],
            [
                'name' => 'Breakfast bagel', 'price' => '9.95', 'allergens' => [],
                'desc' => 'Sausages, streaky bacon, fried or scrambled egg and butter.',
                'extra' => ['Add cheese +€2.00'],
            ],
            [
                'name' => 'Veggie bagel', 'price' => '9.25', 'allergens' => [],
                'desc' => 'Avocado, fried or scrambled egg, mixed leaves, Cheddar and butter.',
                'extra' => [],
            ],
            [
                'name' => 'Avocado toast & egg', 'price' => '12.50', 'allergens' => [],
                'desc' => 'Smashed avocado, chilli flakes and house pesto on sourdough, with fried, scrambled or poached eggs.',
                'extra' => ['Add streaky bacon or sausages +€4.00, grilled halloumi +€3.00, Irish smoked salmon +€4.50'],
            ],
            [
                'name' => 'Free range scrambled eggs & toast', 'price' => '7.45', 'allergens' => [],
                'desc' => '',
                'extra' => ['Add streaky bacon or sausages +€4.00, Irish smoked salmon +€4.50'],
            ],
            [
                'name' => 'Fruity porridge', 'price' => '6.95', 'allergens' => [],
                'desc' => 'With banana, blueberries, strawberries, cinnamon and honey.',
                'extra' => ['Made with plant based milk of your choice +€1.00'],
            ],
            [
                'name' => 'Greek yoghurt & granola bowl', 'price' => '7.95', 'allergens' => [],
                'desc' => 'Homemade nutty granola on Greek yoghurt with banana, blueberries, strawberries, house berry compote and honey.',
                'extra' => [],
            ],
            [
                'name' => 'Add to any dish', 'price' => '', 'allergens' => [],
                'desc' => '',
                'extra' => [
                    'Bacon, sausages or eggs +€4.00 · Irish smoked salmon +€4.50',
                    'Halloumi or avocado +€3.00 · Hash brown, beans, mushrooms or cheese +€2.00',
                ],
            ],
        ],
    ],
    [
        'id'    => 'sandwiches',
        'title' => 'Signature sandwiches & wraps',
        'note'  => '7:45 to 16:00',
        'items' => [
            [
                'name' => 'Bacon Brie (ciabatta)', 'price' => '8.95', 'allergens' => [],
                'desc' => 'Streaky bacon, Brie, red onion, tomato and house garlic chilli mayo.',
                'extra' => [],
            ],
            [
                'name' => 'BLTT (white or brown)', 'price' => '8.95', 'allergens' => [],
                'desc' => 'Turkey breast, streaky bacon, red onion, tomato, iceberg and chipotle mayo.',
                'extra' => [],
            ],
            [
                'name' => 'Turkey taco club (sourdough)', 'price' => '9.25', 'allergens' => [],
                'desc' => 'Turkey breast, streaky bacon, Cheddar, red onion, gherkins, tomato, iceberg and taco mayo.',
                'extra' => [],
            ],
            [
                'name' => 'Grilled chicken tango (ciabatta)', 'price' => '9.25', 'allergens' => [],
                'desc' => 'Chicken breast, Cheddar, avocado, tomato, iceberg, jalapeños and house garlic chilli mayo.',
                'extra' => [],
            ],
            [
                'name' => 'Chorizo chicken melt (wrap)', 'price' => '8.95', 'allergens' => [],
                'desc' => 'Roast chicken, spicy chorizo, cheese, scallions, roasted peppers, spinach and house chipotle mayo.',
                'extra' => [],
            ],
            [
                'name' => 'Buffalo chicken (wrap)', 'price' => '8.95', 'allergens' => [],
                'desc' => 'Chicken breast, Cheddar, scallions, crispy onions, iceberg, buffalo sauce and ranch.',
                'extra' => [],
            ],
            [
                'name' => 'Chicken caprese (focaccia)', 'price' => '8.95', 'allergens' => [],
                'desc' => 'Chicken breast, mozzarella, tomato, rocket and house green pesto.',
                'extra' => [],
            ],
            [
                'name' => 'Tuna melt (sourdough)', 'price' => '8.95', 'allergens' => [],
                'desc' => 'Tuna, red onion, mixed peppers, sweetcorn, iceberg, Monterey Jack and mayo.',
                'extra' => [],
            ],
            [
                'name' => 'Salmon delight (bagel)', 'price' => '9.45', 'allergens' => [],
                'desc' => 'Irish smoked salmon, cream cheese, avocado, red onion and rocket.',
                'extra' => [],
            ],
            [
                'name' => 'Grilled halloumi (ciabatta)', 'price' => '9.25', 'allergens' => [],
                'desc' => 'Vegetarian. Halloumi, Cheddar, avocado, jalapeños, mixed leaves and house garlic chilli mayo.',
                'extra' => [],
            ],
            [
                'name' => 'Brie-cado (focaccia)', 'price' => '8.95', 'allergens' => [],
                'desc' => 'Vegetarian. Brie, roasted peppers, avocado, mixed leaves and house green pesto.',
                'extra' => [],
            ],
            [
                'name' => 'Simply vegan (focaccia)', 'price' => '8.95', 'allergens' => [],
                'desc' => 'Vegan cheese, roasted peppers, house vegan green pesto, avocado and mixed leaves.',
                'extra' => ['Seasonal specials too. Ask about gluten free and vegan options.'],
            ],
        ],
    ],
    [
        'id'    => 'salads',
        'title' => 'Salads & soup',
        'note'  => '',
        'items' => [
            [
                'name' => 'Homemade soup of the day', 'price' => '6.50', 'allergens' => [],
                'desc' => 'Ask what is on today.',
                'extra' => [],
            ],
            [
                'name' => 'Tuna salad', 'price' => '10.50', 'allergens' => [],
                'desc' => 'Tuna, cucumber, beetroot, red onion, mixed peppers and cherry tomatoes on mixed leaves, with balsamic and a boiled egg.',
                'extra' => [],
            ],
            [
                'name' => 'Classic chicken salad', 'price' => '11.50', 'allergens' => [],
                'desc' => 'Mixed leaves, cherry tomatoes, grilled chicken breast, bacon bits, Parmesan and a boiled egg, with creamy ranch.',
                'extra' => [],
            ],
            [
                'name' => 'Roasted sweet potato & grilled halloumi', 'price' => '12.50', 'allergens' => [],
                'desc' => 'Sweet potato, grilled halloumi, avocado and dried cranberries on mixed leaves, with honey mustard dressing.',
                'extra' => [],
            ],
        ],
    ],
    [
        'id'    => 'coffee',
        'title' => 'Coffee & tea',
        'note'  => '100% organic Fairtrade Arabica beans',
        'items' => [
            ['name' => 'Espresso',               'price' => '2.70', 'price_large' => '3.10', 'allergens' => [], 'desc' => '', 'extra' => []],
            ['name' => 'Americano',              'price' => '3.30', 'price_large' => '3.60', 'allergens' => [], 'desc' => '', 'extra' => []],
            ['name' => 'Latte',                  'price' => '3.60', 'price_large' => '4.00', 'allergens' => [], 'desc' => '', 'extra' => []],
            ['name' => 'Latte macchiato',        'price' => '3.60', 'price_large' => '4.00', 'allergens' => [], 'desc' => '', 'extra' => []],
            ['name' => 'Cappuccino',             'price' => '3.60', 'price_large' => '4.00', 'allergens' => [], 'desc' => '', 'extra' => []],
            ['name' => 'Flat white / cortado',   'price' => '3.50', 'allergens' => [], 'desc' => '', 'extra' => []],
            ['name' => 'Mocha (dark or white)',  'price' => '4.30', 'price_large' => '4.70', 'allergens' => [], 'desc' => '', 'extra' => []],
            ['name' => 'Hot chocolate',          'price' => '4.00', 'price_large' => '4.30', 'allergens' => [], 'desc' => '', 'extra' => []],
            ['name' => 'Chai latte',             'price' => '4.00', 'price_large' => '4.30', 'allergens' => [], 'desc' => '', 'extra' => []],
            ['name' => 'Matcha latte',           'price' => '4.30', 'price_large' => '4.60', 'allergens' => [], 'desc' => '', 'extra' => []],
            ['name' => 'Organic herbal tea',     'price' => '3.30', 'allergens' => [], 'desc' => '', 'extra' => []],
            ['name' => 'Breakfast tea',          'price' => '3.20', 'allergens' => [], 'desc' => '', 'extra' => []],
            [
                'name' => 'Extras', 'price' => '', 'allergens' => [],
                'desc' => '',
                'extra' => [
                    'Extra shot, cream, marshmallows, or vanilla, caramel, hazelnut, pumpkin spice or gingerbread syrup +€0.50 each',
                    'Plant based milk: oat, almond, coconut or soy +€0.50',
                ],
            ],
        ],
    ],
    [
        'id'    => 'iced',
        'title' => 'Iced coffee',
        'note'  => '',
        'items' => [
            ['name' => 'Iced Americano',         'price' => '3.60', 'allergens' => [], 'desc' => '', 'extra' => []],
            ['name' => 'Iced latte',             'price' => '4.30', 'allergens' => [], 'desc' => '', 'extra' => []],
            ['name' => 'Iced caramel macchiato', 'price' => '4.80', 'allergens' => [], 'desc' => '', 'extra' => []],
            ['name' => 'Iced mocha (dark or white)', 'price' => '4.90', 'allergens' => [], 'desc' => '', 'extra' => []],
            ['name' => 'Iced chai latte',        'price' => '4.60', 'allergens' => [], 'desc' => '', 'extra' => []],
            ['name' => 'Iced matcha latte',      'price' => '4.90', 'allergens' => [], 'desc' => '', 'extra' => []],
            ['name' => 'Iced chocolate',         'price' => '4.50', 'allergens' => [], 'desc' => '', 'extra' => []],
        ],
    ],
    [
        'id'    => 'juices',
        'title' => 'Juices & smoothies',
        'note'  => '',
        'items' => [
            ['name' => 'Freshly squeezed orange juice', 'price' => '4.20', 'allergens' => [], 'desc' => '', 'extra' => []],
            ['name' => 'Green detox',      'price' => '4.99', 'allergens' => [], 'desc' => 'Spinach, banana, lemon, apple and apple juice.', 'extra' => []],
            ['name' => 'Sunset smoothie',  'price' => '4.99', 'allergens' => [], 'desc' => 'Mixed berry, frozen yoghurt and apple juice.', 'extra' => []],
            ['name' => 'Palm Beach smoothie', 'price' => '4.99', 'allergens' => [], 'desc' => 'Mango, banana, frozen yoghurt and apple juice.', 'extra' => []],
            ['name' => 'Fantasy C smoothie', 'price' => '4.99', 'allergens' => [], 'desc' => 'Mixed berry, mango, frozen yoghurt and apple juice.', 'extra' => []],
        ],
    ],
];
