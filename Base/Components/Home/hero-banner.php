<?php
$banners = [
    [
        'id' => 8,
        'img' => '/img/slider-1.jpg', 
        'tag' => 'For Sale', 
        'tag1' => 'Featured', 
        'title' => 'Aashirvaad Apartment', 
        'location' => '778 Country St. Panama City, FL', 
        'price' => '$2,580', 
    ],
    [
        'id' => 9,
        'img' => '/img/slider-2.jpg', 
        'tag' => 'For Sale', 
        'tag1' => 'Featured', 
        'title' => 'Aashirvaad Apartment1', 
        'location' => '778 Country St. Panama City, FL', 
        'price' => '$2,580', 
    ],
    [
        'id' => 10,
        'img' => '/img/slider-3.jpg', 
        'tag' => 'For Sale', 
        'tag1' => 'Featured', 
        'title' => 'Aashirvaad Apartment2', 
        'location' => '778 Country St. Panama City, FL', 
        'price' => '$2,580', 
    ]
];
?>

<?php foreach ($banners as $item): ?>
<!-- Slide -->
<div data-background-image="<?php echo $static_url, $item['img']; ?>" class="item">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="home-slider-container">

                    <!-- Slide Title -->
                    <div class="home-slider-desc">
                        <div class="modern-pro-wrap">
                            <span class="property-type bg-light-success text-success"><?php echo $item['tag']; ?></span>
                            <span class="property-featured bg-danger"><?php echo $item['tag1']; ?></span>
                        </div>
                        <div class="home-slider-title">
                            <h3><a href="single-property-1.php?title=<?php echo urlencode(str_replace(' ', '-', strtolower($item['title']))); ?>"><?php echo $item['title']; ?></a></h3>
                            <span><i class="lni-map-marker"></i> <?php echo $item['location']; ?></span>
                        </div>
                        
                        <div class="slide-property-info">
                            <ul>
                                <li>Beds: 4</li>
                                <li>Bath: 2</li>
                                <li>sqft: 5270</li>
                            </ul>
                        </div>
                        
                        <div class="listing-price-with-compare">
                            <h4 class="list-pr fs-3"><?php echo $item['price']; ?></h4>
                            <div class="lpc-right d-flex align-items-center mt-3">
                                <a href="compare-property.php" class="square--50 rounded gray-simple me-2" data-bs-toggle="tooltip" data-bs-placement="top" title="Compare Property"><i class="fa-solid fa-code-compare"></i></a>
                                <a href="#" class="square--50 rounded gray-simple" data-bs-toggle="tooltip" data-bs-placement="top" title="Save Property"><i class="fa-solid fa-heart"></i></a>
                            </div>
                        </div>

                        <a href="single-property-1.php?title=<?php echo urlencode(str_replace(' ', '-', strtolower($item['title']))); ?>" class="read-more bg-primary">View Details <i class="fa fa-angle-right"></i></a>

                    </div>
                    <!-- Slide Title / End -->

                </div>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>