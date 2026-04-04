<?php
$base_dir = __DIR__ . '/Base';
$static_url = '/assets'; // Ensure this is the correct path

// Include the common navlink content
ob_start();
include "$base_dir/navbar.php"; // This file contains the shared navlink content
$navlink_content = ob_get_clean(); // Capture the navlink content
$page= 'nav';
$fpage= 'foot';

// Optionally define the Hero block content
ob_start();

$title = isset($_GET['title']) ? $_GET['title'] : '';

$agents = [
    [
        'id' => 1,
        'img' => '/img/user-6.jpg', 
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
        'title' => 'Tam Nguyen', 
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
        'img' => '/img/user-6.jpg', 
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

$article = null;
if ($title === '') {
    $article = $agents;

} else {
    // Search for the article by slugified title
    foreach ($agents as $item) {
        $slugifiedTitle = str_replace(' ', '-', strtolower($item['title']));
        if ($slugifiedTitle === $title) {
            $article = $item;
            break;
        }
    }
}

if ($article === null) {
    echo "Article not found.";
    exit;
}
?>
			
<!-- ============================ Page Title Start================================== -->
<div class="image-cover page-title">
	<div class="container">
		<div class="row">
			<div class="col-lg-12 col-md-12">
				
				<h2 class="ipt-title">Agent Detail</h2>
				<span class="ipn-subtitle">
					<?php 
						if (!empty($article['title'])) {
								echo $article['title']; 
						} else {
							echo 'Tam Nguyen'; 
						}
					?> - Royal Pacific Realty Corp.
				</span>
			</div>
		</div>
	</div>
</div>
<!-- ============================ Page Title End ================================== -->

<!-- ============================ Agency Name Start================================== -->
<section class="agent-page p-0 gray-simple">
	<div class="container">
		<div class="row">
			<div class="col-lg-12 col-md-12">
				<div class="agency agency-list overlio-40">
				
					<div class="agency-avatar">
						<img src="<?php echo !empty($article['img']) ? $static_url . $article['img'] : $static_url . '/img/user-6.jpg'; ?>" alt="">
					</div>
					
					<div class="agency-content">
						<div class="agency-name">
							<h4><a href="agency-page.php">
								<?php 
									if (!empty($article['title'])) {
											echo $article['title']; 
									} else {
										echo 'Tam Nguyen'; 
									}
								?>
							</a></h4>
							<span><i class="lni-map-marker"></i>604-782-4689</span>
						</div>
						
						<div class="agency-desc">
						<p>Think of a news blog that's filled with content hourly on the day of going live However, reviewers tend to be distracted by comprehensible content. In a professional context it often happens that private or corporate clients corder a publication to be made and presented with the actual content still not being ready.</p>
						</div>
						
						<div class="prt-detios">
							<span class="label text-light bg-success">
								<?php 
									if (!empty($article['title1'])) {
											echo $article['title1']; 
									} else {
										echo 'Real Esate Marketign Powerhouse'; 
									}
								?>
							</span>
						</div>
						
						<ul class="social-icons mt-3">
							<li><a class="facebook" href="#"><i class="fa-brands fa-facebook"></i></a></li>
							<li><a class="website" href="https://tamwynn.ca"><i class="fa-solid fa-globe"></i></a></li>
							<li><a class="linkedin" href="#"><i class="fa-brands fa-linkedin"></i></a></li>
						</ul>
						<div class="clearfix"></div>
					</div>
					
				</div>
			</div>
		</div>
	</div>
</section>
<!-- ============================ Agency Name ================================== -->

<!-- ============================ About Agency ================================== -->
<section class="min gray-simple">
	<div class="container">
		<div class="row">
		
			<!-- property main detail -->
			<div class="col-lg-8 col-md-12 col-sm-12">
				
				<!-- Single Block Wrap -->
				<div class="block-wrap">
					
					<div class="block-header">
						<h4 class="block-title">Agent Info</h4>
					</div>
					
					<div class="block-body">
						<ul class="dw-proprty-info">
							<li><strong>Realtor</strong>Tam Nguyen</li>
							<li><strong>Email</strong>sold@tamwynn.ca</li>
							<li><strong>Address</strong>#100 - 1200 W 73rd Ave, Vancouver, BC V6P 6G5</li>
							<li><strong>Web</strong>www.tamwynn.ca</li>
						</ul>
					</div>
					
				</div>
				
				<!-- Single Block Wrap -->
				<div class="block-wraps">
					<div class="block-wraps-header">
					
						<div class="block-header">
							<ul class="nav nav-pills" id="pills-tab" role="tablist">
								<li class="nav-item" role="presentation">
								<a class="nav-link active px-4" id="rental-tab" data-bs-toggle="pill" href="#rental" role="tab" aria-controls="rental" aria-selected="true">Rental</a>
								</li>
								<li class="nav-item" role="presentation">
								<a class="nav-link px-4" id="sale-tab" data-bs-toggle="pill" href="#sale" role="tab" aria-controls="sale" aria-selected="false">For Sale</a>
								</li>
							</ul>
						</div>
						
						<div class="block-body">
							<div class="tab-content" id="pills-tabContent">
							
								<div class="tab-pane fade show active" id="rental" role="tabpanel" aria-labelledby="rental-tab">
									<!-- row -->
									<div class="row justify-content-center g-4">
			
										<!-- propertys-14 code  -->
										<?php
											include "$base_dir/Components/Features/propertys-14.php";
										?>
										
									</div>
									
									<div class="row align-items-center justify-content-center">
										<div class="col-lg-12 col-md-12 col-sm-12 text-center mt-5">
											<a href="#" class="btn btn-primary px-md-5 rounded">Browse More Properties</a>
										</div>
									</div>
									
								</div>
								
								<div class="tab-pane fade" id="sale" role="tabpanel" aria-labelledby="sale-tab">
									<!-- row -->
									<!-- row -->
									<div class="row justify-content-center g-4">
			
										<!-- Single Property -->
										<div class="col-lg-6 col-md-6 col-sm-12">
											<div class="property-listing card border rounded-3">
												
												<div class="listing-img-wrapper p-3">
													<div class="list-img-slide position-relative">
														<div class="position-absolute top-0 left-0 ms-3 mt-3 z-1">
															<div class="label bg-success text-light d-inline-flex align-items-center justify-content-center">
																<span class="svg-icon text-light svg-icon-2hx me-1">
																	<svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
																		<path opacity="0.3" d="M20.5543 4.37824L12.1798 2.02473C12.0626 1.99176 11.9376 1.99176 11.8203 2.02473L3.44572 4.37824C3.18118 4.45258 3 4.6807 3 4.93945V13.569C3 14.6914 3.48509 15.8404 4.4417 16.984C5.17231 17.8575 6.18314 18.7345 7.446 19.5909C9.56752 21.0295 11.6566 21.912 11.7445 21.9488C11.8258 21.9829 11.9129 22 12.0001 22C12.0872 22 12.1744 21.983 12.2557 21.9488C12.3435 21.912 14.4326 21.0295 16.5541 19.5909C17.8169 18.7345 18.8277 17.8575 19.5584 16.984C20.515 15.8404 21 14.6914 21 13.569V4.93945C21 4.6807 20.8189 4.45258 20.5543 4.37824Z" fill="currentColor"></path>
																		<path d="M14.854 11.321C14.7568 11.2282 14.6388 11.1818 14.4998 11.1818H14.3333V10.2272C14.3333 9.61741 14.1041 9.09378 13.6458 8.65628C13.1875 8.21876 12.639 8 12 8C11.361 8 10.8124 8.21876 10.3541 8.65626C9.89574 9.09378 9.66663 9.61739 9.66663 10.2272V11.1818H9.49999C9.36115 11.1818 9.24306 11.2282 9.14583 11.321C9.0486 11.4138 9 11.5265 9 11.6591V14.5227C9 14.6553 9.04862 14.768 9.14583 14.8609C9.24306 14.9536 9.36115 15 9.49999 15H14.5C14.6389 15 14.7569 14.9536 14.8542 14.8609C14.9513 14.768 15 14.6553 15 14.5227V11.6591C15.0001 11.5265 14.9513 11.4138 14.854 11.321ZM13.3333 11.1818H10.6666V10.2272C10.6666 9.87594 10.7969 9.57597 11.0573 9.32743C11.3177 9.07886 11.6319 8.9546 12 8.9546C12.3681 8.9546 12.6823 9.07884 12.9427 9.32743C13.2031 9.57595 13.3333 9.87594 13.3333 10.2272V11.1818Z" fill="currentColor"></path>
																	</svg>
																</span>Verified
															</div>
														</div>
														<div class="clicks rounded-3 overflow-hidden">
															<div><img src="<?php echo $static_url; ?>/img/p-13.jpg" class="img-fluid mx-auto" alt="" /></div>
														</div>
													</div>
												</div>
												
												<div class="listing-caption-wrapper px-3">
													<div class="listing-detail-wrapper">
														<div class="listing-short-detail-wrap">
															<div class="listing-short-detail">
																<div class="d-flex align-items-center">
																	<span class="label bg-light-success text-success prt-type me-2">For Rent</span><span class="label bg-light-purple text-purple property-cats">Apartment</span>
																</div>
																<h4 class="listing-name fw-semibold fs-5 mb-1"><a href="single-property-1.php">The Green Canton Chrysler</a></h4>
																<div class="prt-location text-muted-2">
																	<span class="svg-icon svg-icon-2hx">
																		<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
																			<path opacity="0.3" d="M18.0624 15.3453L13.1624 20.7453C12.5624 21.4453 11.5624 21.4453 10.9624 20.7453L6.06242 15.3453C4.56242 13.6453 3.76242 11.4453 4.06242 8.94534C4.56242 5.34534 7.46242 2.44534 11.0624 2.04534C15.8624 1.54534 19.9624 5.24534 19.9624 9.94534C20.0624 12.0453 19.2624 13.9453 18.0624 15.3453Z" fill="currentColor"/>
																			<path d="M12.0624 13.0453C13.7193 13.0453 15.0624 11.7022 15.0624 10.0453C15.0624 8.38849 13.7193 7.04535 12.0624 7.04535C10.4056 7.04535 9.06241 8.38849 9.06241 10.0453C9.06241 11.7022 10.4056 13.0453 12.0624 13.0453Z" fill="currentColor"/>
																		</svg>
																	</span>
																	210 Zirak Road, Canada
																</div>
															</div>
														</div>
													</div>
													
													<div class="price-features-wrapper">
														<div class="list-fx-features d-flex align-items-center justify-content-between">
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
													
													<div class="listing-detail-footer d-flex align-items-center justify-content-between py-4">
														<div class="listing-short-detail-flex">
															<h6 class="listing-card-info-price m-0">$80,000</h6>
														</div>
														<div class="footer-flex">
															<a href="property-detail.php" class="prt-view">
																<span class="svg-icon text-primary svg-icon-2hx">
																	<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
																		<path d="M15.43 8.56949L10.744 15.1395C10.6422 15.282 10.5804 15.4492 10.5651 15.6236C10.5498 15.7981 10.5815 15.9734 10.657 16.1315L13.194 21.4425C13.2737 21.6097 13.3991 21.751 13.5557 21.8499C13.7123 21.9488 13.8938 22.0014 14.079 22.0015H14.117C14.3087 21.9941 14.4941 21.9307 14.6502 21.8191C14.8062 21.7075 14.9261 21.5526 14.995 21.3735L21.933 3.33649C22.0011 3.15918 22.0164 2.96594 21.977 2.78013C21.9376 2.59432 21.8452 2.4239 21.711 2.28949L15.43 8.56949Z" fill="currentColor"/>
																		<path opacity="0.3" d="M20.664 2.06648L2.62602 9.00148C2.44768 9.07085 2.29348 9.19082 2.1824 9.34663C2.07131 9.50244 2.00818 9.68731 2.00074 9.87853C1.99331 10.0697 2.04189 10.259 2.14054 10.4229C2.23919 10.5869 2.38359 10.7185 2.55601 10.8015L7.86601 13.3365C8.02383 13.4126 8.19925 13.4448 8.37382 13.4297C8.54839 13.4145 8.71565 13.3526 8.85801 13.2505L15.43 8.56548L21.711 2.28448C21.5762 2.15096 21.4055 2.05932 21.2198 2.02064C21.034 1.98196 20.8409 1.99788 20.664 2.06648Z" fill="currentColor"/>
																	</svg>
																</span>
															</a>
														</div>
													</div>
												
												</div>
												
											</div>
										</div>
										<!-- End Single Property -->
										
										<!-- Single Property -->
										<div class="col-lg-6 col-md-6 col-sm-12">
											<div class="property-listing card border rounded-3">
												
												<div class="listing-img-wrapper p-3">
													<div class="list-img-slide position-relative">
														<div class="position-absolute top-0 left-0 ms-3 mt-3 z-1">
															<div class="label bg-purple text-light d-inline-flex align-items-center justify-content-center">
																<span class="svg-icon text-light svg-icon-2hx me-1">
																	<svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
																		<path opacity="0.3" d="M20.5543 4.37824L12.1798 2.02473C12.0626 1.99176 11.9376 1.99176 11.8203 2.02473L3.44572 4.37824C3.18118 4.45258 3 4.6807 3 4.93945V13.569C3 14.6914 3.48509 15.8404 4.4417 16.984C5.17231 17.8575 6.18314 18.7345 7.446 19.5909C9.56752 21.0295 11.6566 21.912 11.7445 21.9488C11.8258 21.9829 11.9129 22 12.0001 22C12.0872 22 12.1744 21.983 12.2557 21.9488C12.3435 21.912 14.4326 21.0295 16.5541 19.5909C17.8169 18.7345 18.8277 17.8575 19.5584 16.984C20.515 15.8404 21 14.6914 21 13.569V4.93945C21 4.6807 20.8189 4.45258 20.5543 4.37824Z" fill="currentColor"/>
																		<path d="M12.0006 11.1542C13.1434 11.1542 14.0777 10.22 14.0777 9.0771C14.0777 7.93424 13.1434 7 12.0006 7C10.8577 7 9.92348 7.93424 9.92348 9.0771C9.92348 10.22 10.8577 11.1542 12.0006 11.1542Z" fill="currentColor"/>
																		<path d="M15.5652 13.814C15.5108 13.6779 15.4382 13.551 15.3566 13.4331C14.9393 12.8163 14.2954 12.4081 13.5697 12.3083C13.479 12.2993 13.3793 12.3174 13.3067 12.3718C12.9257 12.653 12.4722 12.7981 12.0006 12.7981C11.5289 12.7981 11.0754 12.653 10.6944 12.3718C10.6219 12.3174 10.5221 12.2902 10.4314 12.3083C9.70578 12.4081 9.05272 12.8163 8.64456 13.4331C8.56293 13.551 8.49036 13.687 8.43595 13.814C8.40875 13.8684 8.41781 13.9319 8.44502 13.9864C8.51759 14.1133 8.60828 14.2403 8.68991 14.3492C8.81689 14.5215 8.95295 14.6757 9.10715 14.8208C9.23413 14.9478 9.37925 15.0657 9.52439 15.1836C10.2409 15.7188 11.1026 15.9999 11.9915 15.9999C12.8804 15.9999 13.7421 15.7188 14.4586 15.1836C14.6038 15.0748 14.7489 14.9478 14.8759 14.8208C15.021 14.6757 15.1661 14.5215 15.2931 14.3492C15.3838 14.2312 15.4655 14.1133 15.538 13.9864C15.5833 13.9319 15.5924 13.8684 15.5652 13.814Z" fill="currentColor"/>
																	</svg>
																</span>SuperAgent
															</div>
														</div>
														<div class="clicks rounded-3 overflow-hidden">
															<div><img src="<?php echo $static_url; ?>/img/p-14.jpg" class="img-fluid mx-auto" alt="" /></div>
														</div>
													</div>
												</div>
												
												<div class="listing-caption-wrapper px-3">
													<div class="listing-detail-wrapper">
														<div class="listing-short-detail-wrap">
															<div class="listing-short-detail">
																<div class="d-flex align-items-center">
																	<span class="label bg-light-danger text-danger prt-type me-2">For Sell</span><span class="label bg-light-purple text-purple property-cats">House</span>
																</div>
																<h4 class="listing-name fw-semibold fs-5 mb-1"><a href="single-property-1.php">Purple Flatiron House</a></h4>
																<div class="prt-location text-muted-2">
																	<span class="svg-icon svg-icon-2hx">
																		<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
																			<path opacity="0.3" d="M18.0624 15.3453L13.1624 20.7453C12.5624 21.4453 11.5624 21.4453 10.9624 20.7453L6.06242 15.3453C4.56242 13.6453 3.76242 11.4453 4.06242 8.94534C4.56242 5.34534 7.46242 2.44534 11.0624 2.04534C15.8624 1.54534 19.9624 5.24534 19.9624 9.94534C20.0624 12.0453 19.2624 13.9453 18.0624 15.3453Z" fill="currentColor"/>
																			<path d="M12.0624 13.0453C13.7193 13.0453 15.0624 11.7022 15.0624 10.0453C15.0624 8.38849 13.7193 7.04535 12.0624 7.04535C10.4056 7.04535 9.06241 8.38849 9.06241 10.0453C9.06241 11.7022 10.4056 13.0453 12.0624 13.0453Z" fill="currentColor"/>
																		</svg>
																	</span>
																	210 Zirak Road, Canada
																</div>
															</div>
														</div>
													</div>
													
													<div class="price-features-wrapper">
														<div class="list-fx-features d-flex align-items-center justify-content-between">
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
													
													<div class="listing-detail-footer d-flex align-items-center justify-content-between py-4">
														<div class="listing-short-detail-flex">
															<h6 class="listing-card-info-price m-0">$67,000</h6>
														</div>
														<div class="footer-flex">
															<a href="property-detail.php" class="prt-view">
																<span class="svg-icon text-primary svg-icon-2hx">
																	<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
																		<path d="M15.43 8.56949L10.744 15.1395C10.6422 15.282 10.5804 15.4492 10.5651 15.6236C10.5498 15.7981 10.5815 15.9734 10.657 16.1315L13.194 21.4425C13.2737 21.6097 13.3991 21.751 13.5557 21.8499C13.7123 21.9488 13.8938 22.0014 14.079 22.0015H14.117C14.3087 21.9941 14.4941 21.9307 14.6502 21.8191C14.8062 21.7075 14.9261 21.5526 14.995 21.3735L21.933 3.33649C22.0011 3.15918 22.0164 2.96594 21.977 2.78013C21.9376 2.59432 21.8452 2.4239 21.711 2.28949L15.43 8.56949Z" fill="currentColor"/>
																		<path opacity="0.3" d="M20.664 2.06648L2.62602 9.00148C2.44768 9.07085 2.29348 9.19082 2.1824 9.34663C2.07131 9.50244 2.00818 9.68731 2.00074 9.87853C1.99331 10.0697 2.04189 10.259 2.14054 10.4229C2.23919 10.5869 2.38359 10.7185 2.55601 10.8015L7.86601 13.3365C8.02383 13.4126 8.19925 13.4448 8.37382 13.4297C8.54839 13.4145 8.71565 13.3526 8.85801 13.2505L15.43 8.56548L21.711 2.28448C21.5762 2.15096 21.4055 2.05932 21.2198 2.02064C21.034 1.98196 20.8409 1.99788 20.664 2.06648Z" fill="currentColor"/>
																	</svg>
																</span>
															</a>
														</div>
													</div>
												
												</div>
												
											</div>
										</div>
										<!-- End Single Property -->
										
										<!-- Single Property -->
										<div class="col-lg-6 col-md-6 col-sm-12">
											<div class="property-listing card border rounded-3">
												
												<div class="listing-img-wrapper p-3">
													<div class="list-img-slide position-relative">
														<div class="position-absolute top-0 left-0 ms-3 mt-3 z-1">
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
														</div>
														<div class="clicks rounded-3 overflow-hidden">
															<div><img src="<?php echo $static_url; ?>/img/p-15.jpg" class="img-fluid mx-auto" alt="" /></div>
														</div>
													</div>
												</div>
												
												<div class="listing-caption-wrapper px-3">
													<div class="listing-detail-wrapper">
														<div class="listing-short-detail-wrap">
															<div class="listing-short-detail">
																<div class="d-flex align-items-center">
																	<span class="label bg-light-success text-success prt-type me-2">For Rent</span><span class="label bg-light-purple text-purple property-cats">Building</span>
																</div>
																<h4 class="listing-name fw-semibold fs-5 mb-1"><a href="single-property-1.php">Rustic Reunion Tower</a></h4>
																<div class="prt-location text-muted-2">
																	<span class="svg-icon svg-icon-2hx">
																		<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
																			<path opacity="0.3" d="M18.0624 15.3453L13.1624 20.7453C12.5624 21.4453 11.5624 21.4453 10.9624 20.7453L6.06242 15.3453C4.56242 13.6453 3.76242 11.4453 4.06242 8.94534C4.56242 5.34534 7.46242 2.44534 11.0624 2.04534C15.8624 1.54534 19.9624 5.24534 19.9624 9.94534C20.0624 12.0453 19.2624 13.9453 18.0624 15.3453Z" fill="currentColor"/>
																			<path d="M12.0624 13.0453C13.7193 13.0453 15.0624 11.7022 15.0624 10.0453C15.0624 8.38849 13.7193 7.04535 12.0624 7.04535C10.4056 7.04535 9.06241 8.38849 9.06241 10.0453C9.06241 11.7022 10.4056 13.0453 12.0624 13.0453Z" fill="currentColor"/>
																		</svg>
																	</span>
																	210 Zirak Road, Canada
																</div>
															</div>
														</div>
													</div>
													
													<div class="price-features-wrapper">
														<div class="list-fx-features d-flex align-items-center justify-content-between">
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
													
													<div class="listing-detail-footer d-flex align-items-center justify-content-between py-4">
														<div class="listing-short-detail-flex">
															<h6 class="listing-card-info-price m-0">$92,500</h6>
														</div>
														<div class="footer-flex">
															<a href="property-detail.php" class="prt-view">
																<span class="svg-icon text-primary svg-icon-2hx">
																	<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
																		<path d="M15.43 8.56949L10.744 15.1395C10.6422 15.282 10.5804 15.4492 10.5651 15.6236C10.5498 15.7981 10.5815 15.9734 10.657 16.1315L13.194 21.4425C13.2737 21.6097 13.3991 21.751 13.5557 21.8499C13.7123 21.9488 13.8938 22.0014 14.079 22.0015H14.117C14.3087 21.9941 14.4941 21.9307 14.6502 21.8191C14.8062 21.7075 14.9261 21.5526 14.995 21.3735L21.933 3.33649C22.0011 3.15918 22.0164 2.96594 21.977 2.78013C21.9376 2.59432 21.8452 2.4239 21.711 2.28949L15.43 8.56949Z" fill="currentColor"/>
																		<path opacity="0.3" d="M20.664 2.06648L2.62602 9.00148C2.44768 9.07085 2.29348 9.19082 2.1824 9.34663C2.07131 9.50244 2.00818 9.68731 2.00074 9.87853C1.99331 10.0697 2.04189 10.259 2.14054 10.4229C2.23919 10.5869 2.38359 10.7185 2.55601 10.8015L7.86601 13.3365C8.02383 13.4126 8.19925 13.4448 8.37382 13.4297C8.54839 13.4145 8.71565 13.3526 8.85801 13.2505L15.43 8.56548L21.711 2.28448C21.5762 2.15096 21.4055 2.05932 21.2198 2.02064C21.034 1.98196 20.8409 1.99788 20.664 2.06648Z" fill="currentColor"/>
																	</svg>
																</span>
															</a>
														</div>
													</div>
												
												</div>
												
											</div>
										</div>
										<!-- End Single Property -->
										
										<!-- Single Property -->
										<div class="col-lg-6 col-md-6 col-sm-12">
											<div class="property-listing card border rounded-3">
												
												<div class="listing-img-wrapper p-3">
													<div class="list-img-slide position-relative">
														<div class="position-absolute top-0 left-0 ms-3 mt-3 z-1">
															<div class="label bg-purple text-light d-inline-flex align-items-center justify-content-center">
																<span class="svg-icon text-light svg-icon-2hx me-1">
																	<svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
																		<path opacity="0.3" d="M20.5543 4.37824L12.1798 2.02473C12.0626 1.99176 11.9376 1.99176 11.8203 2.02473L3.44572 4.37824C3.18118 4.45258 3 4.6807 3 4.93945V13.569C3 14.6914 3.48509 15.8404 4.4417 16.984C5.17231 17.8575 6.18314 18.7345 7.446 19.5909C9.56752 21.0295 11.6566 21.912 11.7445 21.9488C11.8258 21.9829 11.9129 22 12.0001 22C12.0872 22 12.1744 21.983 12.2557 21.9488C12.3435 21.912 14.4326 21.0295 16.5541 19.5909C17.8169 18.7345 18.8277 17.8575 19.5584 16.984C20.515 15.8404 21 14.6914 21 13.569V4.93945C21 4.6807 20.8189 4.45258 20.5543 4.37824Z" fill="currentColor"/>
																		<path d="M12.0006 11.1542C13.1434 11.1542 14.0777 10.22 14.0777 9.0771C14.0777 7.93424 13.1434 7 12.0006 7C10.8577 7 9.92348 7.93424 9.92348 9.0771C9.92348 10.22 10.8577 11.1542 12.0006 11.1542Z" fill="currentColor"/>
																		<path d="M15.5652 13.814C15.5108 13.6779 15.4382 13.551 15.3566 13.4331C14.9393 12.8163 14.2954 12.4081 13.5697 12.3083C13.479 12.2993 13.3793 12.3174 13.3067 12.3718C12.9257 12.653 12.4722 12.7981 12.0006 12.7981C11.5289 12.7981 11.0754 12.653 10.6944 12.3718C10.6219 12.3174 10.5221 12.2902 10.4314 12.3083C9.70578 12.4081 9.05272 12.8163 8.64456 13.4331C8.56293 13.551 8.49036 13.687 8.43595 13.814C8.40875 13.8684 8.41781 13.9319 8.44502 13.9864C8.51759 14.1133 8.60828 14.2403 8.68991 14.3492C8.81689 14.5215 8.95295 14.6757 9.10715 14.8208C9.23413 14.9478 9.37925 15.0657 9.52439 15.1836C10.2409 15.7188 11.1026 15.9999 11.9915 15.9999C12.8804 15.9999 13.7421 15.7188 14.4586 15.1836C14.6038 15.0748 14.7489 14.9478 14.8759 14.8208C15.021 14.6757 15.1661 14.5215 15.2931 14.3492C15.3838 14.2312 15.4655 14.1133 15.538 13.9864C15.5833 13.9319 15.5924 13.8684 15.5652 13.814Z" fill="currentColor"/>
																	</svg>
																</span>SuperAgent
															</div>
														</div>
														<div class="clicks rounded-3 overflow-hidden">
															<div><img src="<?php echo $static_url; ?>/img/p-16.jpg" class="img-fluid mx-auto" alt="" /></div>
														</div>
													</div>
												</div>
												
												<div class="listing-caption-wrapper px-3">
													<div class="listing-detail-wrapper">
														<div class="listing-short-detail-wrap">
															<div class="listing-short-detail">
																<div class="d-flex align-items-center">
																	<span class="label bg-light-danger text-danger prt-type me-2">For Sell</span><span class="label bg-light-purple text-purple property-cats">Condos</span>
																</div>
																<h4 class="listing-name fw-semibold fs-5 mb-1"><a href="single-property-1.php">The Red Freedom Tower</a></h4>
																<div class="prt-location text-muted-2">
																	<span class="svg-icon svg-icon-2hx">
																		<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
																			<path opacity="0.3" d="M18.0624 15.3453L13.1624 20.7453C12.5624 21.4453 11.5624 21.4453 10.9624 20.7453L6.06242 15.3453C4.56242 13.6453 3.76242 11.4453 4.06242 8.94534C4.56242 5.34534 7.46242 2.44534 11.0624 2.04534C15.8624 1.54534 19.9624 5.24534 19.9624 9.94534C20.0624 12.0453 19.2624 13.9453 18.0624 15.3453Z" fill="currentColor"/>
																			<path d="M12.0624 13.0453C13.7193 13.0453 15.0624 11.7022 15.0624 10.0453C15.0624 8.38849 13.7193 7.04535 12.0624 7.04535C10.4056 7.04535 9.06241 8.38849 9.06241 10.0453C9.06241 11.7022 10.4056 13.0453 12.0624 13.0453Z" fill="currentColor"/>
																		</svg>
																	</span>
																	210 Zirak Road, Canada
																</div>
															</div>
														</div>
													</div>
													
													<div class="price-features-wrapper">
														<div class="list-fx-features d-flex align-items-center justify-content-between">
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
													
													<div class="listing-detail-footer d-flex align-items-center justify-content-between py-4">
														<div class="listing-short-detail-flex">
															<h6 class="listing-card-info-price m-0">$89,000</h6>
														</div>
														<div class="footer-flex">
															<a href="property-detail.php" class="prt-view">
																<span class="svg-icon text-primary svg-icon-2hx">
																	<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
																		<path d="M15.43 8.56949L10.744 15.1395C10.6422 15.282 10.5804 15.4492 10.5651 15.6236C10.5498 15.7981 10.5815 15.9734 10.657 16.1315L13.194 21.4425C13.2737 21.6097 13.3991 21.751 13.5557 21.8499C13.7123 21.9488 13.8938 22.0014 14.079 22.0015H14.117C14.3087 21.9941 14.4941 21.9307 14.6502 21.8191C14.8062 21.7075 14.9261 21.5526 14.995 21.3735L21.933 3.33649C22.0011 3.15918 22.0164 2.96594 21.977 2.78013C21.9376 2.59432 21.8452 2.4239 21.711 2.28949L15.43 8.56949Z" fill="currentColor"/>
																		<path opacity="0.3" d="M20.664 2.06648L2.62602 9.00148C2.44768 9.07085 2.29348 9.19082 2.1824 9.34663C2.07131 9.50244 2.00818 9.68731 2.00074 9.87853C1.99331 10.0697 2.04189 10.259 2.14054 10.4229C2.23919 10.5869 2.38359 10.7185 2.55601 10.8015L7.86601 13.3365C8.02383 13.4126 8.19925 13.4448 8.37382 13.4297C8.54839 13.4145 8.71565 13.3526 8.85801 13.2505L15.43 8.56548L21.711 2.28448C21.5762 2.15096 21.4055 2.05932 21.2198 2.02064C21.034 1.98196 20.8409 1.99788 20.664 2.06648Z" fill="currentColor"/>
																	</svg>
																</span>
															</a>
														</div>
													</div>
												
												</div>
												
											</div>
										</div>
										<!-- End Single Property -->
										
										<!-- Single Property -->
										<div class="col-lg-6 col-md-6 col-sm-12">
											<div class="property-listing card border rounded-3">
												
												<div class="listing-img-wrapper p-3">
													<div class="list-img-slide position-relative">
														<div class="position-absolute top-0 left-0 ms-3 mt-3 z-1">
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
														</div>
														<div class="clicks rounded-3 overflow-hidden">
															<div><img src="<?php echo $static_url; ?>/img/p-17.jpg" class="img-fluid mx-auto" alt="" /></div>
														</div>
													</div>
												</div>
												
												<div class="listing-caption-wrapper px-3">
													<div class="listing-detail-wrapper">
														<div class="listing-short-detail-wrap">
															<div class="listing-short-detail">
																<div class="d-flex align-items-center">
																	<span class="label bg-light-success text-success prt-type me-2">For Rent</span><span class="label bg-light-purple text-purple property-cats">Villa</span>
																</div>
																<h4 class="listing-name fw-semibold fs-5 mb-1"><a href="single-property-1.php">The Donald Dwelling</a></h4>
																<div class="prt-location text-muted-2">
																	<span class="svg-icon svg-icon-2hx">
																		<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
																			<path opacity="0.3" d="M18.0624 15.3453L13.1624 20.7453C12.5624 21.4453 11.5624 21.4453 10.9624 20.7453L6.06242 15.3453C4.56242 13.6453 3.76242 11.4453 4.06242 8.94534C4.56242 5.34534 7.46242 2.44534 11.0624 2.04534C15.8624 1.54534 19.9624 5.24534 19.9624 9.94534C20.0624 12.0453 19.2624 13.9453 18.0624 15.3453Z" fill="currentColor"/>
																			<path d="M12.0624 13.0453C13.7193 13.0453 15.0624 11.7022 15.0624 10.0453C15.0624 8.38849 13.7193 7.04535 12.0624 7.04535C10.4056 7.04535 9.06241 8.38849 9.06241 10.0453C9.06241 11.7022 10.4056 13.0453 12.0624 13.0453Z" fill="currentColor"/>
																		</svg>
																	</span>
																	210 Zirak Road, Canada
																</div>
															</div>
														</div>
													</div>
													
													<div class="price-features-wrapper">
														<div class="list-fx-features d-flex align-items-center justify-content-between">
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
													
													<div class="listing-detail-footer d-flex align-items-center justify-content-between py-4">
														<div class="listing-short-detail-flex">
															<h6 class="listing-card-info-price m-0">$88,000</h6>
														</div>
														<div class="footer-flex">
															<a href="property-detail.php" class="prt-view">
																<span class="svg-icon text-primary svg-icon-2hx">
																	<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
																		<path d="M15.43 8.56949L10.744 15.1395C10.6422 15.282 10.5804 15.4492 10.5651 15.6236C10.5498 15.7981 10.5815 15.9734 10.657 16.1315L13.194 21.4425C13.2737 21.6097 13.3991 21.751 13.5557 21.8499C13.7123 21.9488 13.8938 22.0014 14.079 22.0015H14.117C14.3087 21.9941 14.4941 21.9307 14.6502 21.8191C14.8062 21.7075 14.9261 21.5526 14.995 21.3735L21.933 3.33649C22.0011 3.15918 22.0164 2.96594 21.977 2.78013C21.9376 2.59432 21.8452 2.4239 21.711 2.28949L15.43 8.56949Z" fill="currentColor"/>
																		<path opacity="0.3" d="M20.664 2.06648L2.62602 9.00148C2.44768 9.07085 2.29348 9.19082 2.1824 9.34663C2.07131 9.50244 2.00818 9.68731 2.00074 9.87853C1.99331 10.0697 2.04189 10.259 2.14054 10.4229C2.23919 10.5869 2.38359 10.7185 2.55601 10.8015L7.86601 13.3365C8.02383 13.4126 8.19925 13.4448 8.37382 13.4297C8.54839 13.4145 8.71565 13.3526 8.85801 13.2505L15.43 8.56548L21.711 2.28448C21.5762 2.15096 21.4055 2.05932 21.2198 2.02064C21.034 1.98196 20.8409 1.99788 20.664 2.06648Z" fill="currentColor"/>
																	</svg>
																</span>
															</a>
														</div>
													</div>
												
												</div>
												
											</div>
										</div>
										<!-- End Single Property -->
										
										<!-- Single Property -->
										<div class="col-lg-6 col-md-6 col-sm-12">
											<div class="property-listing card border rounded-3">
												
												<div class="listing-img-wrapper p-3">
													<div class="list-img-slide position-relative">
														<div class="position-absolute top-0 left-0 ms-3 mt-3 z-1">
															<div class="label bg-purple text-light d-inline-flex align-items-center justify-content-center">
																<span class="svg-icon text-light svg-icon-2hx me-1">
																	<svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
																		<path opacity="0.3" d="M20.5543 4.37824L12.1798 2.02473C12.0626 1.99176 11.9376 1.99176 11.8203 2.02473L3.44572 4.37824C3.18118 4.45258 3 4.6807 3 4.93945V13.569C3 14.6914 3.48509 15.8404 4.4417 16.984C5.17231 17.8575 6.18314 18.7345 7.446 19.5909C9.56752 21.0295 11.6566 21.912 11.7445 21.9488C11.8258 21.9829 11.9129 22 12.0001 22C12.0872 22 12.1744 21.983 12.2557 21.9488C12.3435 21.912 14.4326 21.0295 16.5541 19.5909C17.8169 18.7345 18.8277 17.8575 19.5584 16.984C20.515 15.8404 21 14.6914 21 13.569V4.93945C21 4.6807 20.8189 4.45258 20.5543 4.37824Z" fill="currentColor"/>
																		<path d="M12.0006 11.1542C13.1434 11.1542 14.0777 10.22 14.0777 9.0771C14.0777 7.93424 13.1434 7 12.0006 7C10.8577 7 9.92348 7.93424 9.92348 9.0771C9.92348 10.22 10.8577 11.1542 12.0006 11.1542Z" fill="currentColor"/>
																		<path d="M15.5652 13.814C15.5108 13.6779 15.4382 13.551 15.3566 13.4331C14.9393 12.8163 14.2954 12.4081 13.5697 12.3083C13.479 12.2993 13.3793 12.3174 13.3067 12.3718C12.9257 12.653 12.4722 12.7981 12.0006 12.7981C11.5289 12.7981 11.0754 12.653 10.6944 12.3718C10.6219 12.3174 10.5221 12.2902 10.4314 12.3083C9.70578 12.4081 9.05272 12.8163 8.64456 13.4331C8.56293 13.551 8.49036 13.687 8.43595 13.814C8.40875 13.8684 8.41781 13.9319 8.44502 13.9864C8.51759 14.1133 8.60828 14.2403 8.68991 14.3492C8.81689 14.5215 8.95295 14.6757 9.10715 14.8208C9.23413 14.9478 9.37925 15.0657 9.52439 15.1836C10.2409 15.7188 11.1026 15.9999 11.9915 15.9999C12.8804 15.9999 13.7421 15.7188 14.4586 15.1836C14.6038 15.0748 14.7489 14.9478 14.8759 14.8208C15.021 14.6757 15.1661 14.5215 15.2931 14.3492C15.3838 14.2312 15.4655 14.1133 15.538 13.9864C15.5833 13.9319 15.5924 13.8684 15.5652 13.814Z" fill="currentColor"/>
																	</svg>
																</span>SuperAgent
															</div>
														</div>
														<div class="clicks rounded-3 overflow-hidden">
															<div><img src="<?php echo $static_url; ?>/img/p-18.jpg" class="img-fluid mx-auto" alt="" /></div>
														</div>
													</div>
												</div>
												
												<div class="listing-caption-wrapper px-3">
													<div class="listing-detail-wrapper">
														<div class="listing-short-detail-wrap">
															<div class="listing-short-detail">
																<div class="d-flex align-items-center">
																	<span class="label bg-light-danger text-danger prt-type me-2">For Sell</span><span class="label bg-light-purple text-purple property-cats">Building</span>
																</div>
																<h4 class="listing-name fw-semibold fs-5 mb-1"><a href="single-property-1.php">Red Tiny Hearst Castle</a></h4>
																<div class="prt-location text-muted-2">
																	<span class="svg-icon svg-icon-2hx">
																		<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
																			<path opacity="0.3" d="M18.0624 15.3453L13.1624 20.7453C12.5624 21.4453 11.5624 21.4453 10.9624 20.7453L6.06242 15.3453C4.56242 13.6453 3.76242 11.4453 4.06242 8.94534C4.56242 5.34534 7.46242 2.44534 11.0624 2.04534C15.8624 1.54534 19.9624 5.24534 19.9624 9.94534C20.0624 12.0453 19.2624 13.9453 18.0624 15.3453Z" fill="currentColor"/>
																			<path d="M12.0624 13.0453C13.7193 13.0453 15.0624 11.7022 15.0624 10.0453C15.0624 8.38849 13.7193 7.04535 12.0624 7.04535C10.4056 7.04535 9.06241 8.38849 9.06241 10.0453C9.06241 11.7022 10.4056 13.0453 12.0624 13.0453Z" fill="currentColor"/>
																		</svg>
																	</span>
																	210 Zirak Road, Canada
																</div>
															</div>
														</div>
													</div>
													
													<div class="price-features-wrapper">
														<div class="list-fx-features d-flex align-items-center justify-content-between">
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
													
													<div class="listing-detail-footer d-flex align-items-center justify-content-between py-4">
														<div class="listing-short-detail-flex">
															<h6 class="listing-card-info-price m-0">$10,50000</h6>
														</div>
														<div class="footer-flex">
															<a href="property-detail.php" class="prt-view">
																<span class="svg-icon text-primary svg-icon-2hx">
																	<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
																		<path d="M15.43 8.56949L10.744 15.1395C10.6422 15.282 10.5804 15.4492 10.5651 15.6236C10.5498 15.7981 10.5815 15.9734 10.657 16.1315L13.194 21.4425C13.2737 21.6097 13.3991 21.751 13.5557 21.8499C13.7123 21.9488 13.8938 22.0014 14.079 22.0015H14.117C14.3087 21.9941 14.4941 21.9307 14.6502 21.8191C14.8062 21.7075 14.9261 21.5526 14.995 21.3735L21.933 3.33649C22.0011 3.15918 22.0164 2.96594 21.977 2.78013C21.9376 2.59432 21.8452 2.4239 21.711 2.28949L15.43 8.56949Z" fill="currentColor"/>
																		<path opacity="0.3" d="M20.664 2.06648L2.62602 9.00148C2.44768 9.07085 2.29348 9.19082 2.1824 9.34663C2.07131 9.50244 2.00818 9.68731 2.00074 9.87853C1.99331 10.0697 2.04189 10.259 2.14054 10.4229C2.23919 10.5869 2.38359 10.7185 2.55601 10.8015L7.86601 13.3365C8.02383 13.4126 8.19925 13.4448 8.37382 13.4297C8.54839 13.4145 8.71565 13.3526 8.85801 13.2505L15.43 8.56548L21.711 2.28448C21.5762 2.15096 21.4055 2.05932 21.2198 2.02064C21.034 1.98196 20.8409 1.99788 20.664 2.06648Z" fill="currentColor"/>
																	</svg>
																</span>
															</a>
														</div>
													</div>
												
												</div>
												
											</div>
										</div>
										<!-- End Single Property -->
										
									</div>
									
									<div class="row align-items-center justify-content-center">
										<div class="col-lg-12 col-md-12 col-sm-12 text-center mt-5">
											<a href="#" class="btn btn-primary px-md-5 rounded">Browse More Properties</a>
										</div>
									</div>
									
								</div>
								
							</div>
						</div>
					
					</div>
				</div>
			</div>
			
			<!-- property Sidebar -->
			<div class="col-lg-4 col-md-12 col-sm-12">
					
				<div class="details-sidebar">
			
					<!-- Agent Detail -->
					<div class="sides-widget">
						<div class="sides-widget-header bg-primary">
							<div class="agent-photo"><img src="<?php echo !empty($article['img']) ? $static_url . $article['img'] : $static_url . '/img/user-6.jpg'; ?>" alt=""></div>
							<div class="sides-widget-details">
								<h4><a href="#">
								<?php 
									if (!empty($article['title'])) {
											echo $article['title']; 
									} else {
										echo 'Adam D. Okraar'; 
									}
								?>
								</a></h4>
								<span><i class="lni-phone-handset"></i>(91) 123 456 7895</span>
							</div>
							<div class="clearfix"></div>
						</div>
						
						<div class="sides-widget-body simple-form">
							<div class="form-group">
								<label>Email</label>
								<input type="text" class="form-control" placeholder="Your Email">
							</div>
							<div class="form-group">
								<label>Phone No.</label>
								<input type="text" class="form-control" placeholder="Your Phone">
							</div>
							<div class="form-group">
								<label>Description</label>
								<textarea class="form-control">I'm interested in this property.</textarea>
							</div>
							<button class="btn btn-dark rounded full-width">Send Message</button>
						</div>
					</div>
					
					<!-- Featured Property -->
					<div class="sidebar-widgets">
						
						<h4>Featured Property</h4>
						
						<div class="sidebar_featured_property">
							
							<!-- List Sibar Property -->
							<div class="sides_list_property">
								<div class="sides_list_property_thumb">
									<img src="<?php echo $static_url; ?>/img/p-1.jpg" class="img-fluid" alt="">
								</div>
								<div class="sides_list_property_detail">
									<h4><a href="single-property-1.php">Loss vengel New Apartment</a></h4>
									<span><i class="fa-solid fa-location-dot"></i>Sans Fransico</span>
									<div class="lists_property_price">
										<div class="lists_property_types">
											<div class="property_types_vlix sale">For Sale</div>
										</div>
										<div class="lists_property_price_value">
											<h4>$4,240</h4>
										</div>
									</div>
								</div>
							</div>
							
							<!-- List Sibar Property -->
							<div class="sides_list_property">
								<div class="sides_list_property_thumb">
									<img src="<?php echo $static_url; ?>/img/p-4.jpg" class="img-fluid" alt="">
								</div>
								<div class="sides_list_property_detail">
									<h4><a href="single-property-1.php">Montreal Quriqe Apartment</a></h4>
									<span><i class="fa-solid fa-location-dot"></i>Liverpool, London</span>
									<div class="lists_property_price">
										<div class="lists_property_types">
											<div class="property_types_vlix">For Rent</div>
										</div>
										<div class="lists_property_price_value">
											<h4>$7,380</h4>
										</div>
									</div>
								</div>
							</div>
							
							<!-- List Sibar Property -->
							<div class="sides_list_property">
								<div class="sides_list_property_thumb">
									<img src="<?php echo $static_url; ?>/img/p-7.jpg" class="img-fluid" alt="">
								</div>
								<div class="sides_list_property_detail">
									<h4><a href="single-property-1.php">Curmic Studio For Office</a></h4>
									<span><i class="fa-solid fa-location-dot"></i>Montreal, Canada</span>
									<div class="lists_property_price">
										<div class="lists_property_types">
											<div class="property_types_vlix buy">For Buy</div>
										</div>
										<div class="lists_property_price_value">
											<h4>$8,730</h4>
										</div>
									</div>
								</div>
							</div>
							
							<!-- List Sibar Property -->
							<div class="sides_list_property">
								<div class="sides_list_property_thumb">
									<img src="<?php echo $static_url; ?>/img/p-5.jpg" class="img-fluid" alt="">
								</div>
								<div class="sides_list_property_detail">
									<h4><a href="single-property-1.php">Montreal Quebec City</a></h4>
									<span><i class="fa-solid fa-location-dot"></i>Sreek View, New York</span>
									<div class="lists_property_price">
										<div class="lists_property_types">
											<div class="property_types_vlix">For Rent</div>
										</div>
										<div class="lists_property_price_value">
											<h4>$6,240</h4>
										</div>
									</div>
								</div>
							</div>
							
						</div>
						
					</div>
				
				</div>
					
			</div>
		
		</div>					
	</div>	
</section>
<!-- ============================ About Agency End ================================== -->

<!-- ============================ Call To Action ================================== -->
<section class="bg-primary call-to-act-wrap">
	<div class="container">
		
		<!-- estate-agent code  -->
		<?php
			include "$base_dir/Components/Home/estate-agent.php";
		?>

	</div>
</section>
<!-- ============================ Call To Action End ================================== -->


			

<?php
$hero_content = ob_get_clean(); // Capture the hero content

// Include the base template
include "$base_dir/style/base.php";
?>
