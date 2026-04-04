<?php
$propertys = [
    [
        'id' => 1,
        'img' => '/img/p-1.jpg', 
        'tag' => 'For Sale', 
        'title' => 'Adobe Property Advisors', 
        'reviews' => '(42 Reviews)', 
        'price' => '$120M', 
        'location' => 'Quice Market, Canada', 
    ],
    [
        'id' => 2,
        'img' => '/img/p-2.jpg', 
        'tag' => 'For Sale', 
        'title' => 'Agile Real Estate Group', 
        'reviews' => '(34 Reviews)', 
        'price' => '$132M', 
        'location' => 'Quice Market, Canada', 
    ],
    [
        'id' => 3,
        'img' => '/img/p-3.jpg', 
        'tag' => 'For Sale', 
        'title' => 'Bluebell Real Estate', 
        'reviews' => '(124 Reviews)', 
        'price' => '$127M', 
        'location' => 'Quice Market, Canada', 
    ],
    [
        'id' => 4,
        'img' => '/img/p-4.jpg', 
        'tag' => 'For Sale', 
        'title' => 'Strive Partners Realty', 
        'reviews' => '(56 Reviews)', 
        'price' => '$132M', 
        'location' => 'Quice Market, Canada', 
    ]
];
?>

<?php foreach ($propertys as $item): ?>
<!-- Single Property Start -->
<div class="col-xl-6 col-lg-6 col-md-12">
    <div class="property-listing property-1 bg-white p-2 rounded">
            
        <div class="listing-img-wrapper">
            <a href="single-property-2.php?title=<?php echo urlencode(str_replace(' ', '-', strtolower($item['title']))); ?>">
                <img src="<?php echo $static_url, $item['img']; ?>" class="img-fluid mx-auto rounded" alt="" />
            </a>
        </div>
        
        <div class="listing-content">
        
            <div class="listing-detail-wrapper-box">
                <div class="listing-detail-wrapper d-flex align-items-center justify-content-between">
                    <div class="listing-short-detail">
                        <span class="label bg-light-danger text-danger d-inline-flex mb-1"><?php echo $item['tag']; ?></span>
                        <h4 class="listing-name mb-1 mt-2"><a href="single-property-2.php?title=<?php echo urlencode(str_replace(' ', '-', strtolower($item['title']))); ?>"><?php echo $item['title']; ?></a></h4>
                        <div class="fr-can-rating">
                            <i class="fas fa-star fs-xs filled"></i>
                            <i class="fas fa-star fs-xs filled"></i>
                            <i class="fas fa-star fs-xs filled"></i>
                            <i class="fas fa-star fs-xs filled"></i>
                            <i class="fas fa-star fs-xs"></i>
                            <span class="reviews_text fs-sm text-muted ms-2"><?php echo $item['reviews']; ?></span>
                        </div>
                        
                    </div>
                    <div class="list-price">
                        <h6 class="listing-card-info-price text-primary"><?php echo $item['price']; ?></h6>
                    </div>
                </div>
            </div>
            
            <div class="price-features-wrapper">
                <div class="list-fx-features d-flex align-items-center justify-content-between mt-3 mb-1">
                    <div class="listing-card d-flex align-items-center">
                        <div class="square--25 text-muted-2 fs-sm circle gray-simple me-1"><i class="fa-solid fa-building-shield fs-xs"></i></div><span class="text-muted-2 fs-sm">3BHK</span>
                    </div>
                    <div class="listing-card d-flex align-items-center">
                        <div class="square--25 text-muted-2 fs-sm circle gray-simple me-1"><i class="fa-solid fa-bed fs-xs"></i></div><span class="text-muted-2 fs-sm">3 Beds</span>
                    </div>
                    <div class="listing-card d-flex align-items-center">
                        <div class="square--25 text-muted-2 fs-sm circle gray-simple me-1"><i class="fa-solid fa-clone fs-xs"></i></div><span class="text-muted-2 fs-sm">1800 SQFT</span>
                    </div>
                </div>
            </div>
        
            <div class="listing-footer-wrapper">
                <div class="listing-locate">
                    <span class="listing-location text-muted-2"><i class="fa-solid fa-location-pin me-1"></i><?php echo $item['location']; ?></span>
                </div>
                <div class="listing-detail-btn">
                    <a href="single-property-2.php?title=<?php echo urlencode(str_replace(' ', '-', strtolower($item['title']))); ?>" class="btn btn-sm px-4 fw-medium btn-primary">View</a>
                </div>
            </div>
            
        </div>
        
    </div>
</div>
<!-- Single Property End -->
<?php endforeach; ?>