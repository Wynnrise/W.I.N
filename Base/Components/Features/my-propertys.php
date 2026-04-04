<?php
$propertys = [
    [
        'img' => '/img/p-3.jpg', 
        'title' => 'My List property Name', 
        'price' => 'Price: from $ 154 month', 
        'city' => 'KASIA', 
    ],
    [
        'img' => '/img/p-4.jpg', 
        'title' => 'My List property Name', 
        'price' => 'Price: from $ 154 month', 
        'city' => 'KASIA', 
    ],
    [
        'img' => '/img/p-5.jpg', 
        'title' => 'My List property Name', 
        'price' => 'Price: from $ 154 month', 
        'city' => 'KASIA', 
    ],
    [
        'img' => '/img/p-6.jpg', 
        'title' => 'My List property Name', 
        'price' => 'Price: from $ 154 month', 
        'city' => 'KASIA', 
    ],
    [
        'img' => '/img/p-7.jpg', 
        'title' => 'My List property Name', 
        'price' => 'Price: from $ 154 month', 
        'city' => 'KASIA', 
    ]
];
?>

<?php foreach ($propertys as $item): ?>
<!-- Single Property -->
<div class="col-md-12 col-sm-12 col-md-12">
    <div class="singles-dashboard-list">
        <div class="sd-list-left">
            <img src="<?php echo $static_url, $item['img']; ?>" class="img-fluid" alt="" />
        </div>
        <div class="sd-list-right">
            <h4 class="listing_dashboard_title"><a href="#" class="text-primary"><?php echo $item['title']; ?></a></h4>
            <div class="user_dashboard_listed">
                <?php echo $item['price']; ?>
            </div>
            <div class="user_dashboard_listed">
                Listed in <a href="javascript:void(0);" class="text-primary">Rentals</a> and <a href="javascript:void(0);" class="text-primary">Apartments</a>
            </div>
            <div class="user_dashboard_listed">
                City: <a href="javascript:void(0);" class="text-primary"><?php echo $item['city']; ?></a> , Area:540 sq ft
            </div>
            <div class="action">
                <a href="#" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit"><i class="fa-solid fa-pen-to-square"></i></a>
                <a href="#" data-bs-toggle="tooltip" data-bs-placement="top" title="202 User View"><i class="fa-regular fa-eye"></i></a>
                <a href="#" data-bs-toggle="tooltip" data-bs-placement="top" title="Delete Property" class="delete"><i class="fa-regular fa-circle-xmark"></i></a>
                <a href="#" data-bs-toggle="tooltip" data-bs-placement="top" title="Make Featured" class="delete"><i class="fa-solid fa-star"></i></a>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>