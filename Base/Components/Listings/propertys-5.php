<?php
$propertys = [
    [
        'id' => 12,
        'img' => '/img/p-1.jpg', 
        'img1' => '/img/p-10.jpg', 
        'img2' => '/img/p-15.jpg', 
        'tag' => 'For Sell', 
        'class' => 'label bg-light-danger text-danger me-2', 
        'tag1' => 'Building', 
        'title' => '4789 Resot Relly Market, Montreal Canada, HAQC445', 
        'price' => '$167M', 
        'span' => 'Verified', 
    ],
    [
        'id' => 13,
        'img' => '/img/p-2.jpg', 
        'img1' => '/img/p-24.jpg', 
        'img2' => '/img/p-13.jpg', 
        'tag' => 'For Rent', 
        'class' => 'label bg-light-seegreen text-seegreen me-2', 
        'tag1' => 'Family', 
        'title' => '6898 Resot Relly Market, Montreal Canada, HAQC445', 
        'price' => '$8,800', 
        'span' => 'SuperAgent', 
    ],
    [
        'id' => 14,
        'img' => '/img/p-3.jpg', 
        'img1' => '/img/p-19.jpg', 
        'img2' => '/img/p-18.jpg', 
        'tag' => 'For Sell', 
        'class' => 'label bg-light-danger text-danger me-2', 
        'tag1' => 'Condos', 
        'title' => '6987 Resot Relly Market, Montreal Canada, HAQC445', 
        'price' => '$156M', 
        'span' => 'New', 
    ],
    [
        'id' => 15,
        'img' => '/img/p-4.jpg', 
        'img1' => '/img/p-22.jpg', 
        'img2' => '/img/p-17.jpg', 
        'tag' => 'For Rent', 
        'class' => 'label bg-light-seegreen text-seegreen me-2', 
        'tag1' => 'Villa', 
        'title' => '7654 Resot Relly Market, Montreal Canada, HAQC445', 
        'price' => '$9,700', 
        'span' => 'New1', 
    ],
    [
        'id' => 16,
        'img' => '/img/p-5.jpg', 
        'img1' => '/img/p-14.jpg', 
        'img2' => '/img/p-16.jpg', 
        'tag' => 'For Rent', 
        'class' => 'label bg-light-seegreen text-seegreen me-2', 
        'tag1' => 'Apartment', 
        'title' => '9875 Resot Relly Market, Montreal Canada, HAQC445', 
        'price' => '$7,800', 
        'span' => 'New', 
    ],
    [
        'id' => 17,
        'img' => '/img/p-6.jpg', 
        'img1' => '/img/p-12.jpg', 
        'img2' => '/img/p-23.jpg', 
        'tag' => 'For Sell', 
        'class' => 'label bg-light-danger text-danger me-2', 
        'tag1' => 'Family', 
        'title' => '1648 Resot Relly Market, Montreal Canada, HAQC445', 
        'price' => '$140M', 
        'span' => 'SuperAgent', 
    ],
    [
        'id' => 18,
        'img' => '/img/p-8.jpg', 
        'img1' => '/img/p-9.jpg', 
        'img2' => '/img/p-11.jpg', 
        'tag' => 'For Rent', 
        'class' => 'label bg-light-seegreen text-seegreen me-2', 
        'tag1' => 'Villa', 
        'title' => '5690 Resot Relly Market, Montreal Canada, HAQC445', 
        'price' => '$9,200', 
        'span' => 'New', 
    ],
    [
        'id' => 19,
        'img' => '/img/p-7.jpg', 
        'img1' => '/img/p-16.jpg', 
        'img2' => '/img/p-21.jpg', 
        'tag' => 'For Sell', 
        'class' => 'label bg-light-danger text-danger me-2', 
        'tag1' => 'Family', 
        'title' => '4312 Resot Relly Market, Montreal Canada, HAQC445', 
        'price' => '$150M', 
        'span' => 'SuperAgent', 
    ],
];
?>

<?php foreach ($propertys as $item): ?>
<!-- Single Property -->
<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
    <div class="property-listing list_view style_new">
        
        <div class="listing-img-wrapper position-relative">
            <div class="like_unlike_prt">
                <label class="toggler toggler-danger"><input type="checkbox"><i class="fa fa-heart"></i></label>
            </div>
            <div class="position-absolute top-0 left-0 ms-3 mt-3 z-1">
                
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
            <?php elseif ($item['span'] === 'New1'): ?>
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
                <div class="clior">
                    <div><a href="single-property-1.php?title=<?php echo urlencode(str_replace(' ', '-', strtolower($item['title']))); ?>"><img src="<?php echo $static_url, $item['img']; ?>" class="img-fluid mx-auto" alt="" /></a></div>
                    <div><a href="single-property-1.php?title=<?php echo urlencode(str_replace(' ', '-', strtolower($item['title']))); ?>"><img src="<?php echo $static_url, $item['img1']; ?>" class="img-fluid mx-auto" alt="" /></a></div>
                    <div><a href="single-property-1.php?title=<?php echo urlencode(str_replace(' ', '-', strtolower($item['title']))); ?>"><img src="<?php echo $static_url, $item['img2']; ?>" class="img-fluid mx-auto" alt="" /></a></div>
                </div>
            </div>
        </div>
        
        <div class="list_view_flex">
        
            <div class="listing-detail-wrapper mt-1">
                <div class="listing-short-detail-wrap">
                    <div class="_card_list_flex mb-2">
                        <div class="_card_flex_01 d-flex align-items-center">
                            <span class="<?php echo $item['class']; ?>"><?php echo $item['tag']; ?></span>
                            <span class="label bg-light-purple text-purple"><?php echo $item['tag1']; ?></span>
                        </div>
                        <div class="_card_flex_last">
                            <h6 class="listing-info-price text-primary fs-4 mb-0"><?php echo $item['price']; ?></h6>
                        </div>
                    </div>
                    <div class="_card_list_flex">
                        <div class="_card_flex_01">
                            <h4 class="listing-name mt-3"><a href="single-property-1.php?title=<?php echo urlencode(str_replace(' ', '-', strtolower($item['title']))); ?>" class="prt-link-detail"><?php echo $item['title']; ?></a></h4>
                        </div>
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
            
            <div class="listing-detail-footer pl-0">
                <div class="footer-first">
                    <a href="#" data-bs-toggle="modal" data-bs-target="#availability" class="btn btn-md btn-primary fw-medium" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Check Availability">
                        <span class="svg-icon text-light svg-icon-2hx me-1">
                            <svg width="19" height="19" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect opacity="0.3" x="2" y="2" width="20" height="20" rx="10" fill="currentColor"/>
                                <path d="M10.4343 12.4343L8.75 10.75C8.33579 10.3358 7.66421 10.3358 7.25 10.75C6.83579 11.1642 6.83579 11.8358 7.25 12.25L10.2929 15.2929C10.6834 15.6834 11.3166 15.6834 11.7071 15.2929L17.25 9.75C17.6642 9.33579 17.6642 8.66421 17.25 8.25C16.8358 7.83579 16.1642 7.83579 15.75 8.25L11.5657 12.4343C11.2533 12.7467 10.7467 12.7467 10.4343 12.4343Z" fill="currentColor"/>
                            </svg>
                        </span>Availability
                    </a>
                    <a href="tel:4048651904" class="btn btn-md btn-light-primary px-3 me-1" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Call Agent">
                        <span class="svg-icon svg-icon-muted svg-icon-2hx">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M6 21C6 21.6 6.4 22 7 22H17C17.6 22 18 21.6 18 21V20H6V21Z" fill="currentColor"/>
                                <path d="M12 4C11.4 4 11 3.6 11 3V2H13V3C13 3.6 12.6 4 12 4Z" fill="currentColor"/>
                                <path opacity="0.3" d="M18 3V20H6V3C6 2.4 6.4 2 7 2H17C17.6 2 18 2.4 18 3ZM16 11C16 8.5 13.7 6.49998 11.1 7.09998C9.60001 7.39998 8.50001 8.6001 8.10001 10.1001C7.80001 11.5001 8.2 12.7 9 13.7L11.2 16.2C11.6 16.6 12.3 16.6 12.7 16.2L14.9 13.7C15.6 13 16 12 16 11Z" fill="currentColor"/>
                                <path d="M12 12.5C12.8284 12.5 13.5 11.8284 13.5 11C13.5 10.1716 12.8284 9.5 12 9.5C11.1716 9.5 10.5 10.1716 10.5 11C10.5 11.8284 11.1716 12.5 12 12.5Z" fill="currentColor"/>
                        </svg>
                        </span>
                    </a>
                    <a href="JavaScript:Void(0);" class="btn btn-md btn-light-primary px-3 me-1" data-bs-toggle="modal" data-bs-target="#autho-message">
                        <span class="svg-icon svg-icon-muted svg-icon-2hx">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path opacity="0.3" d="M21 19H3C2.4 19 2 18.6 2 18V6C2 5.4 2.4 5 3 5H21C21.6 5 22 5.4 22 6V18C22 18.6 21.6 19 21 19Z" fill="currentColor"/>
                                <path d="M21 5H2.99999C2.69999 5 2.49999 5.10005 2.29999 5.30005L11.2 13.3C11.7 13.7 12.4 13.7 12.8 13.3L21.7 5.30005C21.5 5.10005 21.3 5 21 5Z" fill="currentColor"/>
                            </svg>
                        </span>
                    </a>
                    <a href="JavaScript:Void(0);" class="btn btn-md btn-light-primary px-3" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Save property">
                        <span class="svg-icon svg-icon-muted svg-icon-2hx">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M18.3721 4.65439C17.6415 4.23815 16.8052 4 15.9142 4C14.3444 4 12.9339 4.73924 12.003 5.89633C11.0657 4.73913 9.66 4 8.08626 4C7.19611 4 6.35789 4.23746 5.62804 4.65439C4.06148 5.54462 3 7.26056 3 9.24232C3 9.81001 3.08941 10.3491 3.25153 10.8593C4.12155 14.9013 9.69287 20 12.0034 20C14.2502 20 19.875 14.9013 20.7488 10.8593C20.9109 10.3491 21 9.81001 21 9.24232C21.0007 7.26056 19.9383 5.54462 18.3721 4.65439Z" fill="currentColor"/>
                            </svg>
                        </span>
                    </a>
                </div>
            </div>
        </div>
        
    </div>
</div>
<!-- End Single Property -->
<?php endforeach; ?>