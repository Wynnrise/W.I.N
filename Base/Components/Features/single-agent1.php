<?php
$agents = [
    [
        'id' => 1,
        'img' => '/img/user-1.jpg', 
        'title' => 'James N. Green', 
        'title1' => '117 Properties', 
        'number' => '1234567859', 
        'reviews' => '(42 Reviews)', 
    ],
    [
        'id' => 2,
        'img' => '/img/user-2.jpg', 
        'title' => 'Seema Gauranki', 
        'title1' => '46 Properties', 
        'number' => '1234567859', 
        'reviews' => '(33 Reviews)', 
    ],
    [
        'id' => 3,
        'img' => '/img/user-3.jpg', 
        'title' => 'Adam Walcorn', 
        'title1' => '38 Properties', 
        'number' => '1234567859', 
        'reviews' => '(16 Reviews)', 
    ],
    [
        'id' => 4,
        'img' => '/img/user-4.jpg', 
        'title' => 'Jasmin Khatri', 
        'title1' => '51 Properties', 
        'number' => '1234567859', 
        'reviews' => '(28 Reviews)', 
    ],
    [
        'id' => 5,
        'img' => '/img/user-5.jpg', 
        'title' => 'Rudra K. Mathan', 
        'title1' => '75 Properties', 
        'number' => '1234567859', 
        'reviews' => '(52 Reviews)', 
    ],
    [
        'id' => 6,
        'img' => '/img/user-6.jpg', 
        'title' => 'Niharika Muthurk', 
        'title1' => '15 Properties', 
        'number' => '1234567859', 
        'reviews' => '(46 Reviews)', 
    ],
    [
        'id' => 7,
        'img' => '/img/user-7.jpg', 
        'title' => 'Grack Chappel', 
        'title1' => '17 Properties', 
        'number' => '1234567859', 
        'reviews' => '(102 Reviews)', 
    ],
    [
        'id' => 8,
        'img' => '/img/user-8.jpg', 
        'title' => 'Nikita Rajaswi', 
        'title1' => '62 Properties', 
        'number' => '1234567859', 
        'reviews' => '(18 Reviews)', 
    ],
    [
        'id' => 9,
        'img' => '/img/user-5.jpg', 
        'title' => 'Rudra K. Mathan1', 
        'title1' => '75 Properties', 
        'number' => '1234567859', 
        'reviews' => '(52 Reviews)', 
    ],
    [
        'id' => 10,
        'img' => '/img/user-6.jpg', 
        'title' => 'Niharika Muthurk1', 
        'title1' => '15 Properties', 
        'number' => '1234567859', 
        'reviews' => '(46 Reviews)', 
    ],
    [
        'id' => 11,
        'img' => '/img/user-7.jpg', 
        'title' => 'Grack Chappel1', 
        'title1' => '17 Properties', 
        'number' => '1234567859', 
        'reviews' => '(102 Reviews)', 
    ],
    [
        'id' => 12,
        'img' => '/img/user-8.jpg', 
        'title' => 'Nikita Rajaswi1', 
        'title1' => '62 Properties', 
        'number' => '1234567859', 
        'reviews' => '(18 Reviews)', 
    ]
];
?>

<?php foreach ($agents as $item): ?>
<!-- Single Agent -->
<div class="col-lg-6 col-md-6 col-sm-12">
    <div class="agents-grid card rounded-3 border">
        
        <div class="agents-grid-wrap">
            <div class="fr-grid-thumb mx-auto text-center mt-5 mb-3">
                <a href="agent-page.php?title=<?php echo urlencode(str_replace(' ', '-', strtolower($item['title']))); ?>" class="d-inline-flex p-1 circle border">
                    <img src="<?php echo $static_url, $item['img']; ?>" class="img-fluid circle" width="130" alt="" />
                </a>
            </div>
            <div class="fr-grid-deatil text-center">
                <div class="fr-grid-deatil-flex">
                    <h5 class="fr-can-name mb-0"><a href="agent-page.php?title=<?php echo urlencode(str_replace(' ', '-', strtolower($item['title']))); ?>"><?php echo $item['title']; ?></a></h5>
                    <span class="agent-property text-muted-2"><?php echo $item['title1']; ?></span>
                </div>
            </div>
        </div>
        
        <div class="fr-grid-info d-flex align-items-center justify-content-between px-4 py-4">
            <div class="fr-grid-sder">
                <ul class="p-0">
                    <li><strong>Call:</strong><span class="fw-medium text-primary ms-2"><?php echo $item['number']; ?></span></li>
                    <li>
                        <div class="fr-can-rating">
                            <i class="fas fa-star fs-xs text-warning"></i>
                            <i class="fas fa-star fs-xs text-warning"></i>
                            <i class="fas fa-star fs-xs text-warning"></i>
                            <i class="fas fa-star fs-xs text-warning"></i>
                            <i class="fas fa-star fs-xs text-muted"></i>
                            <span class="reviews_text fs-sm text-muted-2"><?php echo $item['reviews']; ?></span>
                        </div>
                    </li>
                </ul>
            </div>
            <div class="fr-grid-deatil-flex-right">
                <div class="agent-email"><a href="#" class="square--50 rounded text-danger bg-light-danger"><i class="fa-solid fa-envelope-circle-check"></i></a></div>
            </div>
        </div>
        
    </div>
</div>
<?php endforeach; ?>