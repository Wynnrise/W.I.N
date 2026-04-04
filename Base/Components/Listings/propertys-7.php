<?php
$propertys = [
    [
        'id' => 8,
        'img' => '/img/p-1.jpg', 
        'tag' => 'For Rent', 
        'class' => 'label bg-light-success text-success d-inline-flex mb-1', 
        'title' => 'Resort Valley Ocs', 
        'reviews' => '(36 Reviews)', 
        'price' => '$8,700', 
        'location' => 'Quice Market, Canada', 
    ],
    [
        'id' => 9,
        'img' => '/img/p-2.jpg', 
        'tag' => 'For Sale', 
        'class' => 'label bg-light-danger text-danger d-inline-flex mb-1', 
        'title' => 'Adobe Property Advisors1', 
        'reviews' => '(42 Reviews)', 
        'price' => '$120M', 
        'location' => 'Quice Market, Canada', 
    ],
    [
        'id' => 3,
        'img' => '/img/p-3.jpg', 
        'tag' => 'For Rent', 
        'class' => 'label bg-light-success text-success d-inline-flex mb-1', 
        'title' => 'Bluebell Real Estate', 
        'reviews' => '(1125 Reviews)', 
        'price' => '$7,800', 
        'location' => 'Quice Market, Canada', 
    ],
    [
        'id' => 10,
        'img' => '/img/p-4.jpg', 
        'tag' => 'For Rent', 
        'class' => 'label bg-light-success text-success d-inline-flex mb-1', 
        'title' => 'Agile Real Estate Group2', 
        'reviews' => '(48 Reviews)', 
        'price' => '$8,000', 
        'location' => 'Quice Market, Canada', 
    ],
    [
        'id' => 11,
        'img' => '/img/p-5.jpg', 
        'tag' => 'For Sale', 
        'class' => 'label bg-light-danger text-danger d-inline-flex mb-1', 
        'title' => 'Nestled Real Estate1', 
        'reviews' => '(15 Reviews)', 
        'price' => '$153M', 
        'location' => 'Quice Market, Canada', 
    ],
    [
        'id' => 12,
        'img' => '/img/p-6.jpg', 
        'tag' => 'For Sale', 
        'class' => 'label bg-light-danger text-danger d-inline-flex mb-1', 
        'title' => 'Flow Group Real Estate1', 
        'reviews' => '(23 Reviews)', 
        'price' => '$140M', 
        'location' => 'Quice Market, Canada', 
    ],
    [
        'id' => 13,
        'img' => '/img/p-7.jpg', 
        'tag' => 'For Rent', 
        'class' => 'label bg-light-success text-success d-inline-flex mb-1', 
        'title' => 'Strive Partners Realty1', 
        'reviews' => '(72 Reviews)', 
        'price' => '$9,500', 
        'location' => 'Quice Market, Canada', 
    ],
    [
        'id' => 14,
        'img' => '/img/p-10.jpg', 
        'tag' => 'For Sale', 
        'class' => 'label bg-light-danger text-danger d-inline-flex mb-1', 
        'title' => 'Black Oak Realty', 
        'reviews' => '(42 Reviews)', 
        'price' => '$147M', 
        'location' => 'Quice Market, Canada', 
    ],
    [
        'id' => 15,
        'img' => '/img/p-17.jpg', 
        'tag' => 'For Rent', 
        'class' => 'label bg-light-success text-success d-inline-flex mb-1', 
        'title' => 'Purple Flatiron House', 
        'reviews' => '(72 Reviews)', 
        'price' => '$9,500', 
        'location' => 'Quice Market, Canada', 
    ],
    [
        'id' => 16,
        'img' => '/img/p-10.jpg', 
        'tag' => 'For Sale', 
        'class' => 'label bg-light-danger text-danger d-inline-flex mb-1', 
        'title' => 'Black Oak Realty1', 
        'reviews' => '(42 Reviews)', 
        'price' => '$147M', 
        'location' => 'Quice Market, Canada', 
    ],
    [
        'id' => 17,
        'img' => '/img/p-13.jpg', 
        'tag' => 'For Rent', 
        'class' => 'label bg-light-success text-success d-inline-flex mb-1', 
        'title' => 'Tiny Hearst Castle', 
        'reviews' => '(65 Reviews)', 
        'price' => '$6,900', 
        'location' => 'Quice Market, Canada', 
    ],
    [
        'id' => 18,
        'img' => '/img/p-22.jpg', 
        'tag' => 'For Sale', 
        'class' => 'label bg-light-danger text-danger d-inline-flex mb-1', 
        'title' => 'The Canton Chrysler', 
        'reviews' => '(15 Reviews)', 
        'price' => '$135M', 
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
                        <span class="<?php echo $item['class']; ?>"><?php echo $item['tag']; ?></span>
                        <h4 class="listing-name mb-0 mt-2"><a href="single-property-2.php?title=<?php echo urlencode(str_replace(' ', '-', strtolower($item['title']))); ?>"><?php echo $item['title']; ?></a></h4>
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
                <div class="list-fx-features d-flex align-items-center justify-content-between">
                    <div class="listing-card d-flex align-items-center">
                        <div class="square--30 text-muted-2 fs-sm circle gray-simple me-2"><i class="fa-solid fa-building-shield fs-sm"></i></div><span class="text-muted-2">3BHK</span>
                    </div>
                    <div class="listing-card d-flex align-items-center">
                        <div class="square--30 text-muted-2 fs-sm circle gray-simple me-2"><i class="fa-solid fa-bed fs-sm"></i></div><span class="text-muted-2">3 Beds</span>
                    </div>
                    <div class="listing-card d-flex align-items-center">
                        <div class="square--30 text-muted-2 fs-sm circle gray-simple me-2"><i class="fa-solid fa-clone fs-sm"></i></div><span class="text-muted-2">1800 SQFT</span>
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