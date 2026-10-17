<?php

/**
 * Physical closing stock count — 30/09/2026 (from the handwritten count sheet).
 *
 * Applied with:  php artisan stock:apply-count 2026-09-30 [--apply]
 *
 * Every batch dated on or before the count date that is NOT listed here is
 * counted as 0 (sorghum raw material, milling/unpackaged flour, other
 * packaged products, ...).
 *
 * 'batch' is matched against batch numbers ignoring case, spaces and
 * punctuation. If there is no exact match, the digits alone are matched,
 * limited to batches whose item contains 'item' when given.
 */
return [
    // 1. Raw materials and packaging materials (raw_material_stocks)
    'raw' => [
        ['batch' => 'M2602130',   'item' => 'maize',    'qty' => 1758],   // 15 Sept
        ['batch' => 'M2600131',   'item' => 'maize',    'qty' => 10272],  // 26 Sept
        ['batch' => 'SY2600182',  'item' => 'soy',      'qty' => 1561],   // 26 Sept
        ['batch' => 'W260045',    'item' => 'wheat',    'qty' => 116],    // 06 Aug
        ['batch' => 'W260046',    'item' => 'wheat',    'qty' => 144],    // 08 Sept
        ['batch' => 'W260047',    'item' => 'wheat',    'qty' => 602],    // 30 Sept
        ['batch' => 'MI260005',   'item' => 'millet',   'qty' => 115],    // 30 Sept
        ['batch' => 'MIL260402',  'item' => 'milk',     'qty' => 25],     // 30 Sept
        ['batch' => 'SU260408',   'item' => 'sugar',    'qty' => 10500],  // 30 Sept
        ['batch' => 'EMB2605009', 'item' => '1kg',      'qty' => 15318],  // 1kg bags, 26 Sept
        ['batch' => '2605003',    'item' => '5kg',      'qty' => 5010],   // 5kg bags, 30 Sept (prefix unreadable on sheet)
        ['batch' => 'SAC2605004', 'item' => 'sac',      'qty' => 30],     // 30 Sept
        ['batch' => 'BOX2606010', 'item' => 'box',      'qty' => 1665],   // 18 Sept
    ],

    // 2. Sorting — matched by the batch number of the raw material that was sorted
    'sorting' => [
        ['batch' => 'M2602130',  'item' => 'maize',   'qty' => 883],  // 15 Sept
        ['batch' => 'SY2602181', 'item' => 'soy',     'qty' => 270],  // 15 Sept
        ['batch' => 'W260045',   'item' => 'wheat',   'qty' => 220],  // 06 Aug
        ['batch' => '260043',    'item' => 'sorghum', 'qty' => 66],   // 18 Sept (prefix unclear on sheet)
    ],

    // 3. Roasting — matched by roasting batch code
    'roasting' => [
        ['batch' => 'M2602130R',  'item' => 'maize', 'qty' => 540],  // 15 Sept
        ['batch' => 'SY2602181R', 'item' => 'soy',   'qty' => 210],  // 15 Sept
    ],

    // 4. Milling / unpackaged flour — none on hand
    'milling' => [],

    // 5. Packaged flour (emballages) — matched by packaging catalog name or
    //    legacy packaging type. A batch goes to the FIRST line whose 'match'
    //    words appear in its name, so keep 'box' ahead of '1kg'.
    'packaged' => [
        ['match' => ['box', 'carton'], 'units' => 94],
        ['match' => ['1kg'],           'units' => 6],
        ['match' => ['sack', 'sac'],   'units' => 2, 'notes' => '2 sacks, 20 kg in total'],
    ],
];
