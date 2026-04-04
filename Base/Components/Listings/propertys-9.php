<?php
$propertys = [
    [
        'id' => 11,
        'img' => '/img/p-1.jpg', 
        'img1' => '/img/p-9.jpg', 
        'img2' => '/img/p-10.jpg', 
        'tag' => 'For Rent', 
        'tag1' => 'Apartment', 
        'title' => 'Equitable Property Group', 
        'location' => '210 Zirak Road, Canada', 
        'price' => '$78,500', 
        'span' => 'Verified', 
        'reviews' => '47 Reviews', 
    ],
    [
        'id' => 2,
        'img' => '/img/p-2.jpg', 
        'img1' => '/img/p-6.jpg', 
        'img2' => '/img/p-8.jpg', 
        'tag' => 'For Rent', 
        'tag1' => 'Apartment', 
        'title' => 'Purple Flatiron House', 
        'location' => '210 Zirak Road, Canada', 
        'price' => '$67,000', 
        'span' => 'SuperAgent', 
        'reviews' => '102 Reviews',
    ],
    [
        'id' => 3,
        'img' => '/img/p-3.jpg', 
        'img1' => '/img/p-5.jpg', 
        'img2' => '/img/p-7.jpg', 
        'tag' => 'For Rent', 
        'tag1' => 'Apartment', 
        'title' => 'Rustic Reunion Tower', 
        'location' => '210 Zirak Road, Canada', 
        'price' => '$92,500', 
        'span' => 'New', 
        'reviews' => '89 Reviews',
    ],
    [
        'id' => 4,
        'img' => '/img/p-4.jpg', 
        'img1' => '/img/p-6.jpg', 
        'img2' => '/img/p-9.jpg', 
        'tag' => 'For Rent', 
        'tag1' => 'Apartment', 
        'title' => 'The Red Freedom Tower', 
        'location' => '210 Zirak Road, Canada', 
        'price' => '$89,000', 
        'span' => 'SuperAgent', 
        'reviews' => '64 Reviews',
    ],
    [
        'id' => 5,
        'img' => '/img/p-5.jpg', 
        'img1' => '/img/p-12.jpg', 
        'img2' => '/img/p-13.jpg', 
        'tag' => 'For Rent', 
        'tag1' => 'Apartment', 
        'title' => 'The Donald Dwelling', 
        'location' => '210 Zirak Road, Canada', 
        'price' => '$88,000', 
        'span' => 'New', 
        'reviews' => '65 Reviews',
    ],
    [
        'id' => 6,
        'img' => '/img/p-6.jpg', 
        'img1' => '/img/p-7.jpg', 
        'img2' => '/img/p-11.jpg', 
        'tag' => 'For Rent', 
        'tag1' => 'Apartment', 
        'title' => 'Red Tiny Hearst Castle', 
        'location' => '210 Zirak Road, Canada', 
        'price' => '$10,50000', 
        'span' => 'SuperAgent', 
        'reviews' => '47 Reviews',
    ],
    [
        'id' => 20,
        'img' => '/img/p-5.jpg', 
        'img1' => '/img/p-12.jpg', 
        'img2' => '/img/p-13.jpg', 
        'tag' => 'For Rent', 
        'tag1' => 'Apartment', 
        'title' => 'The Donald Dwelling1', 
        'location' => '210 Zirak Road, Canada', 
        'price' => '$78,400', 
        'span' => 'New', 
        'reviews' => '65 Reviews',
    ],
    [
        'id' => 21,
        'img' => '/img/p-6.jpg', 
        'img1' => '/img/p-7.jpg', 
        'img2' => '/img/p-11.jpg', 
        'tag' => 'For Rent', 
        'tag1' => 'Apartment', 
        'title' => 'Red Tiny Hearst Castle2', 
        'location' => '210 Zirak Road, Canada', 
        'price' => '$78,500', 
        'span' => 'SuperAgent', 
        'reviews' => '47 Reviews',
    ]
];
?>

<?php foreach ($propertys as $item): ?>
<!-- Single Property -->
<div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
    <div class="property-listing card border-0 rounded-3">
        
        <div class="listing-img-wrapper p-3">
            <div class="position-relative">
                <div class=" position-absolute top-0 left-0 ms-3 mt-3 z-1">
                    
                <?php if ($item['span'] === 'Verified'): ?>
                    <div class="label bg-success text-light d-inline-flex align-items-center justify-content-center">
                        <span class="svg-icon text-light svg-icon-2hx me-1">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path opacity="0.3" d="M20.5543 4.37824L12.1798 2.02473C12.0626 1.99176 11.9376 1.99176 11.8203 2.02473L3.44572 4.37824C3.18118 4.45258 3 4.6807 3 4.93945V13.569C3 14.6914 3.48509 15.8404 4.4417 16.984C5.17231 17.8575 6.18314 18.7345 7.446 19.5909C9.56752 21.0295 11.6566 21.912 11.7445 21.9488C11.8258 21.9829 11.9129 22 12.0001 22C12.0872 22 12.1744 21.983 12.2557 21.9488C12.3435 21.912 14.4326 21.0295 16.5541 19.5909C17.8169 18.7345 18.8277 17.8575 19.5584 16.984C20.515 15.8404 21 14.6914 21 13.569V4.93945C21 4.6807 20.8189 4.45258 20.5543 4.37824Z" fill="currentColor"></path>
                                <path d="M14.854 11.321C14.7568 11.2282 14.6388 11.1818 14.4998 11.1818H14.3333V10.2272C14.3333 9.61741 14.1041 9.09378 13.6458 8.65628C13.1875 8.21876 12.639 8 12 8C11.361 8 10.8124 8.21876 10.3541 8.65626C9.89574 9.09378 9.66663 9.61739 9.66663 10.2272V11.1818H9.49999C9.36115 11.1818 9.24306 11.2282 9.14583 11.321C9.0486 11.4138 9 11.5265 9 11.6591V14.5227C9 14.6553 9.04862 14.768 9.14583 14.8609C9.24306 14.9536 9.36115 15 9.49999 15H14.5C14.6389 15 14.7569 14.9536 14.8542 14.8609C14.9513 14.768 15 14.6553 15 14.5227V11.6591C15.0001 11.5265 14.9513 11.4138 14.854 11.321ZM13.3333 11.1818H10.6666V10.2272C10.6666 9.87594 10.7969 9.57597 11.0573 9.32743C11.3177 9.07886 11.6319 8.9546 12 8.9546C12.3681 8.9546 12.6823 9.07884 12.9427 9.32743C13.2031 9.57595 13.3333 9.87594 13.3333 10.2272V11.1818Z" fill="currentColor"></path>
                            </svg>
                        </span>Verified
                    </div>
                <?php elseif ($item['span'] === 'SuperAgent'): ?>
                    <div class="label bg-purple text-light d-inline-flex align-items-center justify-content-center">
                        <span class="svg-icon text-light svg-icon-2hx me-1">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path opacity="0.3" d="M20.5543 4.37824L12.1798 2.02473C12.0626 1.99176 11.9376 1.99176 11.8203 2.02473L3.44572 4.37824C3.18118 4.45258 3 4.6807 3 4.93945V13.569C3 14.6914 3.48509 15.8404 4.4417 16.984C5.17231 17.8575 6.18314 18.7345 7.446 19.5909C9.56752 21.0295 11.6566 21.912 11.7445 21.9488C11.8258 21.9829 11.9129 22 12.0001 22C12.0872 22 12.1744 21.983 12.2557 21.9488C12.3435 21.912 14.4326 21.0295 16.5541 19.5909C17.8169 18.7345 18.8277 17.8575 19.5584 16.984C20.515 15.8404 21 14.6914 21 13.569V4.93945C21 4.6807 20.8189 4.45258 20.5543 4.37824Z" fill="currentColor"/>
                                <path d="M12.0006 11.1542C13.1434 11.1542 14.0777 10.22 14.0777 9.0771C14.0777 7.93424 13.1434 7 12.0006 7C10.8577 7 9.92348 7.93424 9.92348 9.0771C9.92348 10.22 10.8577 11.1542 12.0006 11.1542Z" fill="currentColor"/>
                                <path d="M15.5652 13.814C15.5108 13.6779 15.4382 13.551 15.3566 13.4331C14.9393 12.8163 14.2954 12.4081 13.5697 12.3083C13.479 12.2993 13.3793 12.3174 13.3067 12.3718C12.9257 12.653 12.4722 12.7981 12.0006 12.7981C11.5289 12.7981 11.0754 12.653 10.6944 12.3718C10.6219 12.3174 10.5221 12.2902 10.4314 12.3083C9.70578 12.4081 9.05272 12.8163 8.64456 13.4331C8.56293 13.551 8.49036 13.687 8.43595 13.814C8.40875 13.8684 8.41781 13.9319 8.44502 13.9864C8.51759 14.1133 8.60828 14.2403 8.68991 14.3492C8.81689 14.5215 8.95295 14.6757 9.10715 14.8208C9.23413 14.9478 9.37925 15.0657 9.52439 15.1836C10.2409 15.7188 11.1026 15.9999 11.9915 15.9999C12.8804 15.9999 13.7421 15.7188 14.4586 15.1836C14.6038 15.0748 14.7489 14.9478 14.8759 14.8208C15.021 14.6757 15.1661 14.5215 15.2931 14.3492C15.3838 14.2312 15.4655 14.1133 15.538 13.9864C15.5833 13.9319 15.5924 13.8684 15.5652 13.814Z" fill="currentColor"/>
                            </svg>
                        </span>SuperAgent
                    </div>
                <?php elseif ($item['span'] === 'New'): ?>
                    <div class="label bg-success text-light d-inline-flex align-items-center justify-content-center">
                        <span class="svg-icon text-light svg-icon-2hx me-1">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path opacity="0.3" d="M20.5543 4.37824L12.1798 2.02473C12.0626 1.99176 11.9376 1.99176 11.8203 2.02473L3.44572 4.37824C3.18118 4.45258 3 4.6807 3 4.93945V13.569C3 14.6914 3.48509 15.8404 4.4417 16.984C5.17231 17.8575 6.18314 18.7345 7.446 19.5909C9.56752 21.0295 11.6566 21.912 11.7445 21.9488C11.8258 21.9829 11.9129 22 12.0001 22C12.0872 22 12.1744 21.983 12.2557 21.9488C12.3435 21.912 14.4326 21.0295 16.5541 19.5909C17.8169 18.7345 18.8277 17.8575 19.5584 16.984C20.515 15.8404 21 14.6914 21 13.569V4.93945C21 4.6807 20.8189 4.45258 20.5543 4.37824Z" fill="currentColor"></path>
                                <path d="M14.854 11.321C14.7568 11.2282 14.6388 11.1818 14.4998 11.1818H14.3333V10.2272C14.3333 9.61741 14.1041 9.09378 13.6458 8.65628C13.1875 8.21876 12.639 8 12 8C11.361 8 10.8124 8.21876 10.3541 8.65626C9.89574 9.09378 9.66663 9.61739 9.66663 10.2272V11.1818H9.49999C9.36115 11.1818 9.24306 11.2282 9.14583 11.321C9.0486 11.4138 9 11.5265 9 11.6591V14.5227C9 14.6553 9.04862 14.768 9.14583 14.8609C9.24306 14.9536 9.36115 15 9.49999 15H14.5C14.6389 15 14.7569 14.9536 14.8542 14.8609C14.9513 14.768 15 14.6553 15 14.5227V11.6591C15.0001 11.5265 14.9513 11.4138 14.854 11.321ZM13.3333 11.1818H10.6666V10.2272C10.6666 9.87594 10.7969 9.57597 11.0573 9.32743C11.3177 9.07886 11.6319 8.9546 12 8.9546C12.3681 8.9546 12.6823 9.07884 12.9427 9.32743C13.2031 9.57595 13.3333 9.87594 13.3333 10.2272V11.1818Z" fill="currentColor"></path>
                            </svg>
                        </span>Verified
                    </div>
                    <div class="label bg-danger text-light d-inline-flex align-items-center justify-content-center ms-1">
                        <span class="svg-icon text-light svg-icon-2hx me-1">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M19.0647 5.43757C19.3421 5.43757 19.567 5.21271 19.567 4.93534C19.567 4.65796 19.3421 4.43311 19.0647 4.43311C18.7874 4.43311 18.5625 4.65796 18.5625 4.93534C18.5625 5.21271 18.7874 5.43757 19.0647 5.43757Z" fill="currentColor"/>
                                <path d="M20.0692 9.48884C20.3466 9.48884 20.5714 9.26398 20.5714 8.98661C20.5714 8.70923 20.3466 8.48438 20.0692 8.48438C19.7918 8.48438 19.567 8.70923 19.567 8.98661C19.567 9.26398 19.7918 9.48884 20.0692 9.48884Z" fill="currentColor"/>
                                <path d="M12.0335 20.5714C15.6943 20.5714 18.9426 18.2053 20.1168 14.7338C20.1884 14.5225 20.1114 14.289 19.9284 14.161C19.746 14.034 19.5003 14.0418 19.3257 14.1821C18.2432 15.0546 16.9371 15.5156 15.5491 15.5156C12.2257 15.5156 9.48884 12.8122 9.48884 9.48886C9.48884 7.41079 10.5773 5.47137 12.3449 4.35752C12.5342 4.23832 12.6 4.00733 12.5377 3.79251C12.4759 3.57768 12.2571 3.42859 12.0335 3.42859C7.32556 3.42859 3.42857 7.29209 3.42857 12C3.42857 16.7079 7.32556 20.5714 12.0335 20.5714Z" fill="currentColor"/>
                                <path d="M13.0379 7.47998C13.8688 7.47998 14.5446 8.15585 14.5446 8.98668C14.5446 9.26428 14.7693 9.48891 15.0469 9.48891C15.3245 9.48891 15.5491 9.26428 15.5491 8.98668C15.5491 8.15585 16.225 7.47998 17.0558 7.47998C17.3334 7.47998 17.558 7.25535 17.558 6.97775C17.558 6.70015 17.3334 6.47552 17.0558 6.47552C16.225 6.47552 15.5491 5.76616 15.5491 4.93534C15.5491 4.65774 15.3245 4.43311 15.0469 4.43311C14.7693 4.43311 14.5446 4.65774 14.5446 4.93534C14.5446 5.76616 13.8688 6.47552 13.0379 6.47552C12.7603 6.47552 12.5357 6.70015 12.5357 6.97775C12.5357 7.25535 12.7603 7.47998 13.0379 7.47998Z" fill="currentColor"/>
                            </svg>
                        </span>New
                    </div>
                <?php endif; ?>

                </div>
                <div class="list-img-slide">
                    <div class="click mb-0 rounded-3 overflow-hidden">
                        <div><a href="single-property-1.php?title=<?php echo urlencode(str_replace(' ', '-', strtolower($item['title']))); ?>"><img src="<?php echo $static_url, $item['img']; ?>" class="img-fluid" alt="" /></a></div>
                        <div><a href="single-property-1.php?title=<?php echo urlencode(str_replace(' ', '-', strtolower($item['title']))); ?>"><img src="<?php echo $static_url, $item['img1']; ?>" class="img-fluid" alt="" /></a></div>
                        <div><a href="single-property-1.php?title=<?php echo urlencode(str_replace(' ', '-', strtolower($item['title']))); ?>"><img src="<?php echo $static_url, $item['img2']; ?>" class="img-fluid" alt="" /></a></div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="listing-caption-wrapper px-3">
            <div class="listing-detail-wrapper">
                <div class="listing-short-detail-wrap">
                    <div class="listing-short-detail">
                        <div class="d-flex align-items-center mb-1">
                            <span class="label bg-light-success text-success prt-type me-2"><?php echo $item['tag']; ?></span><span class="label bg-light-purple text-purple property-cats"><?php echo $item['tag1']; ?></span>
                        </div>
                        <h4 class="listing-name fw-semibold fs-6 mb-2 mt-3"><a href="single-property-1.php?title=<?php echo urlencode(str_replace(' ', '-', strtolower($item['title']))); ?>" class="prt-link-detail"><?php echo $item['title']; ?></a></h4>
                        <div class="prt-location text-muted-2">
                            <span class="svg-icon svg-icon-2hx">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path opacity="0.3" d="M18.0624 15.3453L13.1624 20.7453C12.5624 21.4453 11.5624 21.4453 10.9624 20.7453L6.06242 15.3453C4.56242 13.6453 3.76242 11.4453 4.06242 8.94534C4.56242 5.34534 7.46242 2.44534 11.0624 2.04534C15.8624 1.54534 19.9624 5.24534 19.9624 9.94534C20.0624 12.0453 19.2624 13.9453 18.0624 15.3453Z" fill="currentColor"></path>
                                    <path d="M12.0624 13.0453C13.7193 13.0453 15.0624 11.7022 15.0624 10.0453C15.0624 8.38849 13.7193 7.04535 12.0624 7.04535C10.4056 7.04535 9.06241 8.38849 9.06241 10.0453C9.06241 11.7022 10.4056 13.0453 12.0624 13.0453Z" fill="currentColor"></path>
                                </svg>
                            </span>
                            <?php echo $item['location']; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="price-features-wrapper d-flex align-items-center justify-content-between my-4">
                <div class="listing-short-detail-flex">
                    <h6 class="listing-card-info-price text-primary ps-0 m-0"><?php echo $item['price']; ?></h6>
                </div>
                <div class="lst-short-btns-groups d-flex align-items-center">
                    <a href="JavaScript:Void(0);" class="square--50 circle text-primary bg-light-primary me-2" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Compare Property"><i class="fa-solid fa-code-compare"></i></a>
                    <a href="JavaScript:Void(0);" class="square--50 circle text-success bg-light-success me-2" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Send message"><i class="fa-solid fa-envelope-open-text"></i></a>
                    <a href="JavaScript:Void(0);" class="square--50 circle text-danger bg-light-danger" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Save Property"><i class="fa-solid fa-heart-circle-check"></i></a>
                </div>
            </div>
        </div>
        <div class="lst-detail-footer d-flex align-items-center justify-content-between border-top py-2 px-3">
            <div class="footer-first">
                <div class="foot-reviews-wrap">
                    <div class="foot-reviews-stars">
                        <span><i class="fa-solid fa-star text-warning fs-sm"></i></span>
                        <span><i class="fa-solid fa-star text-warning fs-sm"></i></span>
                        <span><i class="fa-solid fa-star text-warning fs-sm"></i></span>
                        <span><i class="fa-solid fa-star text-warning fs-sm"></i></span>
                        <span><i class="fa-solid fa-star text-warning fs-sm"></i></span>
                    </div>
                    <div class="foot-reviews-subtitle">
                        <span class="text-muted"><?php echo $item['reviews']; ?></span>
                    </div>
                </div>
            </div>
            <div class="footer-flex">
                <div class="list-fx-features d-flex align-items-center justify-content-between m-0">
                    <div class="listing-card d-flex align-items-center me-3">
                        <div class="square--30 text-muted-2 fs-sm circle gray-simple me-2"><i class="fa-solid fa-building-shield fs-sm"></i></div><span class="text-muted-2">3BHK</span>
                    </div>
                    <div class="listing-card d-flex align-items-center">
                        <div class="square--30 text-muted-2 fs-sm circle gray-simple me-2"><i class="fa-solid fa-clone fs-sm"></i></div><span class="text-muted-2">1800 SQFT</span>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
</div>
<!-- End Single Property -->
<?php endforeach; ?>