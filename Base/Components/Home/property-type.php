<?php
$types = [
    [
        'icon' => 'fa-solid fa-house-laptop', 
        'title' => "Commercial", 
        'name' => "12 Properties", 
    ],
    [
        'icon' => 'fa-solid fa-building', 
        'title' => "Apartment", 
        'name' => "16 Properties", 
    ],
    [
        'icon' => 'fa-solid fa-building-shield', 
        'title' => "Townhouse", 
        'name' => "22 Properties", 
    ],
    [
        'icon' => 'fa-solid fa-synagogue', 
        'title' => "Villa", 
        'name' => "18 Properties", 
    ],
    [
        'icon' => 'fa-solid fa-mosque', 
        'title' => "Offices", 
        'name' => "42 Properties", 
    ],
    [
        'icon' => 'fa-solid fa-tree-city', 
        'title' => "Warehouses", 
        'name' => "63 Properties", 
    ],
    [
        'icon' => 'fa-solid fa-house', 
        'title' => "Private House", 
        'name' => "12 Properties", 
    ],
    [
        'icon' => 'fa-solid fa-landmark-dome', 
        'title' => "Openhouse", 
        'name' => "16 Properties", 
    ],
    [
        'icon' => 'fa-solid fa-city', 
        'title' => "Building", 
        'name' => "19 Properties", 
    ],
    [
        'icon' => 'fa-solid fa-warehouse', 
        'title' => "Shops", 
        'name' => "17 Properties", 
    ],
    [
        'icon' => 'fa-solid fa-shop-lock', 
        'title' => "Studio", 
        'name' => "10 Properties", 
    ],
    [
        'icon' => 'fa-solid fa-building-columns', 
        'title' => "Condos", 
        'name' => "31 Properties", 
    ]
];
?>

<?php foreach ($types as $item): ?>
<div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-6">
    <div class="classical-cats-wrap style_2 shadows">
        <a class="classical-cats-boxs bg-white rounded-4 px-3 py-4">
            <div class="classical-cats-icon circle bg-light-info text-primary fs-2">
                <i class="<?php echo $item['icon']; ?>"></i>
            </div>
            <div class="classical-cats-wrap-content">
                <h4><?php echo $item['title']; ?></h4>
                <p><?php echo $item['name']; ?></p>
            </div>
        </a>
    </div>
</div>
<?php endforeach; ?>