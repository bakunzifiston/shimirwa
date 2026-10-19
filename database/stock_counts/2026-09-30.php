<?php

/**
 * Physical closing stock count — 30/09/2026 (from the handwritten count sheet).
 *
 * Applied with:  php artisan stock:apply-count 2026-09-30 [--apply]
 *
 * Every batch dated on or before the count date that is NOT listed here is
 * counted as 0 — all types, including "Other".
 *
 * 'batch' matches a batch number ending with it, ignoring case, spaces and
 * punctuation (so "M2602130" matches "15SeptM2602130"). If nothing matches,
 * the digits alone are tried, limited to batches whose item contains 'item'.
 *
 * 'create': the sheet lists this batch but it was never entered, so it is
 * created on that date holding the counted quantity.
 */
return [
    // 1. Raw materials and packaging materials (raw_material_stocks)
    'raw' => [
        ['batch' => '15SeptM2602130', 'item' => 'maize', 'qty' => 1758],
        ['batch' => 'M2600131',       'item' => 'maize', 'qty' => 10272,
            'create' => ['date' => '2026-09-26', 'item' => 'Maize', 'batch_number' => '26SeptM2600131']],
        ['batch' => 'SY2600182',      'item' => 'soy',   'qty' => 1561,
            'create' => ['date' => '2026-09-26', 'item' => 'Soy', 'batch_number' => '26SeptSy2600182']],
        ['batch' => '06AugW260045',   'item' => 'wheat', 'qty' => 116],
        ['batch' => '08Sept1W260046', 'item' => 'wheat', 'qty' => 144],
        ['batch' => '30SeptW260047',  'item' => 'wheat', 'qty' => 602],
        ['batch' => 'MI260005',       'item' => 'millet', 'qty' => 115,
            'create' => ['date' => '2026-09-30', 'item' => 'Millet', 'batch_number' => '30SeptMi260005']],
        ['batch' => 'MIL260402',      'item' => 'milk',  'qty' => 25,
            'create' => ['date' => '2026-09-30', 'item' => 'Milk', 'batch_number' => '30SeptMil260402']],
        ['batch' => 'SU260408',       'item' => 'sugar', 'qty' => 10500,
            'create' => ['date' => '2026-09-30', 'item' => 'Sugar', 'batch_number' => '30SeptSu260408']],
        ['batch' => 'EMB2605009',     'item' => '1kg',   'qty' => 15318,
            'create' => ['date' => '2026-09-26', 'item' => '1kg', 'batch_number' => '26SeptEmb2605009', 'type' => 'Packaging Material']],
        ['batch' => '2605003',        'item' => '5kg',   'qty' => 5010,   // prefix unreadable on the sheet
            'create' => ['date' => '2026-09-30', 'item' => '5kg', 'batch_number' => '30Sept2605003', 'type' => 'Packaging Material']],
        ['batch' => 'SAC2605004',     'item' => 'sac',   'qty' => 30,
            'create' => ['date' => '2026-09-30', 'item' => 'Sac', 'batch_number' => '30SeptSac2605004', 'type' => 'Packaging Material']],
        ['batch' => 'BOX2606010',     'item' => 'box',   'qty' => 1665,
            'create' => ['date' => '2026-09-18', 'item' => 'Box', 'batch_number' => '18SeptBox2606010', 'type' => 'Packaging Material']],
    ],

    // 2. Sorting — matched by the batch number of the raw material that was sorted
    'sorting' => [
        ['batch' => 'M2602130',  'item' => 'maize',   'qty' => 883],  // 15 Sept
        ['batch' => 'SY2602181', 'item' => 'soy',     'qty' => 270],  // 15 Sept
        ['batch' => 'W260045',   'item' => 'wheat',   'qty' => 220],  // 06 Aug
        ['batch' => '260043',    'item' => 'sorghum', 'qty' => 66],   // 18 Sept
    ],

    // 3. Roasting — matched by roasting batch code
    'roasting' => [
        ['batch' => 'M2602130R',  'item' => 'maize', 'qty' => 540],  // 15 Sept
        ['batch' => 'SY2602181R', 'item' => 'soy',   'qty' => 210,   // 15 Sept
            'create' => ['date' => '2026-09-15', 'batch' => '15SeptSy2602181R', 'source_batch' => '15SeptSy2602181']],
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
