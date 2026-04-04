<?php
$places = [
    [
        'img' => '/img/ct-1.png', 
        'name' => "Dubai", 
        'title' => "112 New Properties", 
    ],
    [
        'img' => '/img/ct-2.png', 
        'name' => "Abu Dhabi", 
        'title' => "76 New Properties", 
    ],
    [
        'img' => '/img/ct-3.png', 
        'name' => "Sharjah", 
        'title' => "36 New Properties", 
    ],
    [
        'img' => '/img/ct-4.png', 
        'name' => "Khor Fakkan", 
        'title' => "42 New Properties", 
    ],
    [
        'img' => '/img/ct-5.png', 
        'name' => "Dibba AI-Hisn", 
        'title' => "25 New Properties", 
    ],
    [
        'img' => '/img/ct-6.png', 
        'name' => "AI Madam", 
        'title' => "43 New Properties", 
    ],
    [
        'img' => '/img/ct-7.png', 
        'name' => "Algaaz", 
        'title' => "17 New Properties", 
    ],
    [
        'img' => '/img/ct-8.png', 
        'name' => "AI Milaah", 
        'title' => "32 New Properties", 
    ]
];
?>

<?php foreach ($places as $item): ?>
<!-- Single Property -->
<div class="col-xl-3 col-lg-4 col-md-6 col-sm-12">
    <div class="card border-0 rounded-4 h-100 position-relative">
        <a href="place-search.php">
            <div class="abx-thumb" data-overlay="3">
                <img src="<?php echo $static_url, $item['img']; ?>" class="img-fluid" alt="">
            </div>
        </a>
        <div class="position-absolute top-0 left-0 mt-3 ms-3 z-1">
            <div class="d-inline-flex align-items-center justify-content-start">
                <div class="flex-shrink-0">
                    <div class="square--50 circle"><img src="<?php echo $static_url, $item['img']; ?>" class="img-fluid h-100 object-fit circle" alt=""></div>
                </div>
                <div class="explo-caption ps-3">
                    <div class="label d-inline-flex bg-primary text-light mb-1"><?php echo $item['name']; ?></div>
                    <div class="text-light fw-medium"><?php echo $item['title']; ?></div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End Single Property -->
<?php endforeach; ?>