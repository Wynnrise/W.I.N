<?php
$categories = [
    [
        'icon' => 'fa-solid fa-house', 
        'title' => "Private House", 
        'name' => "12 Properties", 
        'style' => "classical-cats-icon px-4 py-4 rounded bg-light-success text-success fs-2", 
    ],
    [
        'icon' => 'fa-solid fa-building', 
        'title' => "Apartment", 
        'name' => "16 Properties", 
        'style' => "classical-cats-icon px-4 py-4 rounded bg-light-warning text-warning fs-2", 
    ],
    [
        'icon' => 'fa-solid fa-building-shield', 
        'title' => "Townhouse", 
        'name' => "22 Properties", 
        'style' => "classical-cats-icon px-4 py-4 rounded bg-light-danger text-danger fs-2", 
    ],
    [
        'icon' => 'fa-solid fa-synagogue', 
        'title' => "Villa", 
        'name' => "18 Properties", 
        'style' => "classical-cats-icon px-4 py-4 rounded bg-light-info text-primary fs-2", 
    ],
    [
        'icon' => 'fa-solid fa-mosque', 
        'title' => "Offices", 
        'name' => "42 Properties", 
        'style' => "classical-cats-icon px-4 py-4 rounded bg-light-success text-success fs-2", 
    ],
    [
        'icon' => 'fa-solid fa-tree-city', 
        'title' => "Warehouses", 
        'name' => "63 Properties", 
        'style' => "classical-cats-icon px-4 py-4 rounded bg-light-danger text-danger fs-2", 
    ]
];
?>

<?php foreach ($categories as $item): ?>
<div class="col-xl-2 col-lg-3 col-md-3 col-6 mt-5">
    <div class="classical-cats-wrap">
        <a class="classical-cats-boxs">
            <div class="<?php echo $item['style']; ?>">
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