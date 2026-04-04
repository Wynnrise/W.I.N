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

$properties = [
    [
        'id' => 1,
        'img' => '/img/p-1.jpg', 
        'img1' => '/img/p-9.jpg', 
        'img2' => '/img/p-10.jpg', 
        'tag' => 'For Rent', 
        'class' => 'label bg-light-success text-success prt-type me-2', 
        'tag1' => 'Apartment', 
        'title' => 'The Green Canton Chrysler', 
        'location' => '210 Zirak Road, Canada', 
        'price' => '$80,000', 
        'span' => 'Verified', 
    ],
    [
        'id' => 2,
        'img' => '/img/p-2.jpg', 
        'img1' => '/img/p-6.jpg', 
        'img2' => '/img/p-8.jpg', 
        'tag' => 'For Sell', 
        'class' => 'label bg-light-danger text-danger prt-type me-2', 
        'tag1' => 'House', 
        'title' => 'Purple Flatiron House', 
        'location' => '210 Zirak Road, Canada', 
        'price' => '$67,000', 
        'span' => 'SuperAgent', 
    ],
    [
        'id' => 3,
        'img' => '/img/p-3.jpg', 
        'img1' => '/img/p-5.jpg', 
        'img2' => '/img/p-7.jpg', 
        'tag' => 'For Rent', 
        'class' => 'label bg-light-success text-success prt-type me-2', 
        'tag1' => 'Building', 
        'title' => 'Rustic Reunion Tower', 
        'location' => '210 Zirak Road, Canada', 
        'price' => '$92,500', 
        'span' => 'New', 
    ],
    [
        'id' => 4,
        'img' => '/img/p-4.jpg', 
        'img1' => '/img/p-6.jpg', 
        'img2' => '/img/p-9.jpg', 
        'tag' => 'For Sell', 
        'class' => 'label bg-light-danger text-danger prt-type me-2', 
        'tag1' => 'Condos', 
        'title' => 'The Red Freedom Tower', 
        'location' => '210 Zirak Road, Canada', 
        'price' => '$89,000', 
        'span' => 'SuperAgent', 
    ],
    [
        'id' => 5,
        'img' => '/img/p-5.jpg', 
        'img1' => '/img/p-12.jpg', 
        'img2' => '/img/p-13.jpg', 
        'tag' => 'For Rent', 
        'class' => 'label bg-light-success text-success prt-type me-2', 
        'tag1' => 'Villa', 
        'title' => 'The Donald Dwelling', 
        'location' => '210 Zirak Road, Canada', 
        'price' => '$88,000', 
        'span' => 'New', 
    ],
    [
        'id' => 6,
        'img' => '/img/p-6.jpg', 
        'img1' => '/img/p-7.jpg', 
        'img2' => '/img/p-11.jpg', 
        'tag' => 'For Sell', 
        'class' => 'label bg-light-danger text-danger prt-type me-2', 
        'tag1' => 'Building', 
        'title' => 'Red Tiny Hearst Castle', 
        'location' => '210 Zirak Road, Canada', 
        'price' => '$10,50000', 
        'span' => 'SuperAgent', 
	],
	[
        'id' => 7,
        'img' => '/img/p-4.jpg', 
        'img1' => '/img/p-6.jpg', 
        'img2' => '/img/p-9.jpg', 
        'tag' => 'For Sell', 
        'class' => 'label bg-light-danger text-danger prt-type me-2', 
        'tag1' => 'Condos', 
        'title' => 'The Red Freedom Tower1', 
        'location' => '210 Zirak Road, Canada', 
        'price' => '$89,000', 
        'span' => 'SuperAgent', 
    ],
    [
        'id' => 8,
        'img' => '/img/p-5.jpg', 
        'img1' => '/img/p-12.jpg', 
        'img2' => '/img/p-13.jpg', 
        'tag' => 'For Rent', 
        'class' => 'label bg-light-success text-success prt-type me-2', 
        'tag1' => 'Villa', 
        'title' => 'The Donald Dwelling2', 
        'location' => '210 Zirak Road, Canada', 
        'price' => '$88,000', 
        'span' => 'New', 
    ],
    [
        'id' => 9,
        'img' => '/img/p-6.jpg', 
        'img1' => '/img/p-7.jpg', 
        'img2' => '/img/p-11.jpg', 
        'tag' => 'For Sell', 
        'class' => 'label bg-light-danger text-danger prt-type me-2', 
        'tag1' => 'Building', 
        'title' => 'Red Tiny Hearst Castle3', 
        'location' => '210 Zirak Road, Canada', 
        'price' => '$10,50000', 
        'span' => 'SuperAgent', 
	],
	[
        'id' => 10,
        'img' => '/img/p-3.jpg', 
        'tag' => 'For Rent', 
        'class' => 'label bg-light-success text-success prt-type me-2', 
        'tag1' => 'Apartment', 
        'title' => 'The Green Canton Chrysler1', 
        'location' => '210 Zirak Road, Canada', 
        'price' => '$80,000', 
        'span' => 'Verified', 
    ],
    [
        'id' => 11,
        'img' => '/img/p-4.jpg', 
        'tag' => 'For Sell', 
        'class' => 'label bg-light-danger text-danger prt-type me-2', 
        'tag1' => 'House', 
        'title' => 'Purple Flatiron House2', 
        'location' => '210 Zirak Road, Canada', 
        'price' => '$67,000', 
        'span' => 'SuperAgent', 
    ],
    [
        'id' => 12,
        'img' => '/img/p-5.jpg', 
        'tag' => 'For Rent', 
        'class' => 'label bg-light-success text-success prt-type me-2', 
        'tag1' => 'Building', 
        'title' => 'Rustic Reunion Tower1', 
        'location' => '210 Zirak Road, Canada', 
        'price' => '$92,500', 
        'span' => 'New', 
    ],
    [
        'id' => 13,
        'img' => '/img/p-6.jpg', 
        'tag' => 'For Sell', 
        'class' => 'label bg-light-danger text-danger prt-type me-2', 
        'tag1' => 'Condos', 
        'title' => 'The Red Freedom Tower3', 
        'location' => '210 Zirak Road, Canada', 
        'price' => '$89,000', 
        'span' => 'SuperAgent', 
    ],
    [
        'id' => 14,
        'img' => '/img/p-7.jpg', 
        'tag' => 'For Rent', 
        'class' => 'label bg-light-success text-success prt-type me-2', 
        'tag1' => 'Villa', 
        'title' => 'The Donald Dwelling3', 
        'location' => '210 Zirak Road, Canada', 
        'price' => '$88,000', 
        'span' => 'New', 
    ],
    [
        'id' => 15,
        'img' => '/img/p-8.jpg', 
        'tag' => 'For Sell', 
        'class' => 'label bg-light-danger text-danger prt-type me-2', 
        'tag1' => 'Building', 
        'title' => 'Red Tiny Hearst Castle4', 
        'location' => '210 Zirak Road, Canada', 
        'price' => '$10,50000', 
        'span' => 'SuperAgent', 
    ]
];

$article = null;
if ($title === '') {
    $article = $properties;

} else {
    // Search for the article by slugified title
    foreach ($properties as $item) {
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
			
			
<!-- ============================ Hero Banner  Start================================== -->
<div class="featured_slick_gallery gray">
	<div class="featured_slick_gallery-slide">
	<div class="featured_slick_padd"><a href="<?php echo !empty($article['img1']) ? $static_url . $article['img1'] : $static_url . '/img/p-1.jpg'; ?>" class="mfp-gallery"><img src="<?php echo !empty($article['img1']) ? $static_url . $article['img1'] : $static_url . '/img/p-1.jpg'; ?>" class="img-fluid mx-auto" alt="" /></a></div>
		<div class="featured_slick_padd"><a href="<?php echo !empty($article['img2']) ? $static_url . $article['img2'] : $static_url . '/img/p-2.jpg'; ?>" class="mfp-gallery"><img src="<?php echo !empty($article['img2']) ? $static_url . $article['img2'] : $static_url . '/img/p-2.jpg'; ?>" class="img-fluid mx-auto" alt="" /></a></div>
		<div class="featured_slick_padd"><a href="<?php echo $static_url; ?>/img/p-3.jpg" class="mfp-gallery"><img src="<?php echo $static_url; ?>/img/p-3.jpg" class="img-fluid mx-auto" alt="" /></a></div>
		<div class="featured_slick_padd"><a href="<?php echo !empty($article['img']) ? $static_url . $article['img'] : $static_url . '/img/p-4.jpg'; ?>" class="mfp-gallery"><img src="<?php echo !empty($article['img']) ? $static_url . $article['img'] : $static_url . '/img/p-4.jpg'; ?>" class="img-fluid mx-auto" alt="" /></a></div>
	</div>
	<a href="JavaScript:Void(0);" class="btn-view-pic">View photos</a>
</div>
<!-- ============================ Hero Banner End ================================== -->

<!-- ============================ Property Detail Start ================================== -->
<section class="gray-simple">
	<div class="container">
		<div class="row">
			
			<!-- property main detail -->
			<div class="col-lg-8 col-md-12 col-sm-12">
			
				<div class="property_block_wrap style-2 p-4">
					<div class="prt-detail-title-desc">
						<span class="label text-light bg-success">
							<?php 
								if (!empty($article['tag'])) {
										echo $article['tag']; 
								} else {
									echo 'For Sale'; 
								}
							?>
						</span>
						<h3 class="mt-3">
							<?php 
								if (!empty($article['title'])) {
										echo $article['title']; 
								} else {
									echo 'Jannat Graynight Mood In Siver Colony, London'; 
								}
							?>
						</h3>
						<span><i class="lni-map-marker"></i> 
							<?php 
								if (!empty($article['location'])) {
										echo $article['location']; 
								} else {
									echo '778 Country St. Panama City, FL'; 
								}
							?>
						</span>
						<h3 class="prt-price-fix text-primary mt-2">
							<?php 
								if (!empty($article['price'])) {
										echo $article['price']; 
								} else {
									echo '$7,600'; 
								}
							?>
						<sub>/month</sub></h3>
						<div class="list-fx-features">
							<div class="listing-card-info-icon">
								<div class="inc-fleat-icon me-1"><img src="<?php echo $static_url; ?>/img/bed.svg" width="13" alt=""></div>3 Beds
							</div>
							<div class="listing-card-info-icon">
								<div class="inc-fleat-icon me-1"><img src="<?php echo $static_url; ?>/img/bathtub.svg" width="13" alt=""></div>1 Bath
							</div>
							<div class="listing-card-info-icon">
								<div class="inc-fleat-icon me-1"><img src="<?php echo $static_url; ?>/img/move.svg" width="13" alt=""></div>800 sqft
							</div>
						</div>
					</div>
				</div>
				
				<!-- single-propertys-1 code  -->
				<?php
					include "$base_dir/Components/Features/single-propertys-1.php";
				?>
				
			</div>
			
			<!-- property Sidebar -->
			<div class="col-lg-4 col-md-12 col-sm-12">
				
				<!-- Like And Share -->
				<div class="like_share_wrap b-0">
					<ul class="like_share_list">
						<li><a href="JavaScript:Void(0);" class="btn btn-likes" data-toggle="tooltip" data-original-title="Share"><i class="fas fa-share"></i>Share</a></li>
						<li><a href="JavaScript:Void(0);" class="btn btn-likes" data-toggle="tooltip" data-original-title="Save"><i class="fas fa-heart"></i>Save</a></li>
					</ul>
				</div>
				
				<div class="property-sidebar side_stiky">
													
					<div class="sider_blocks_wrap">
						<div class="side-booking-header">
							<ul class="nav nav-pills sider_tab" id="pills-tab" role="tablist">
								<li class="nav-item">
								<a class="nav-link active" id="pills-book-tab" data-bs-toggle="pill" href="#pills-book" role="tab" aria-controls="pills-home" aria-selected="true">Book Now</a>
								</li>
								<li class="nav-item">
								<a class="nav-link" id="pills-appointment-tab" data-bs-toggle="pill" href="#pills-appointment" role="tab" aria-controls="pills-appointment" aria-selected="false">Appointment</a>
								</li>
							</ul>
						</div>
						<div class="sidetab-content">
							<div class="tab-content" id="pills-tabContent">
								<!-- Book Now Tab -->
								<div class="tab-pane fade show active" id="pills-book" role="tabpanel" aria-labelledby="pills-book-tab">
									<div class="side-booking-body">
										<div class="row">
											<div class="col-lg-6 col-md-6 col-sm-6 col-6">
												<div class="form-group">
													<label for="guests">Check In</label>
													<div class="cld-box">
														<i class="fa-solid fa-calendar-week"></i>
														<input type="text" name="checkin" class="form-control" value="10/24/2025" />
													</div>
												</div>
											</div>
											<div class="col-lg-6 col-md-6 col-sm-6 col-6">
												<div class="form-group">
													<label for="guests">Check Out</label>
													<div class="cld-box">
														<i class="fa-solid fa-calendar-week"></i>
														<input type="text" name="checkout" class="form-control" value="10/24/2025" />
													</div>
												</div>
											</div>
											<div class="col-lg-6 col-md-6 col-sm-6 col-6">
												<div class="form-group">
													<div class="guests">
														<label for="guests">Adults</label>
														<div class="guests-box">
															<button class="counter-btn" type="button" id="cnt-down"><i class="fa-solid fa-minus"></i></button>
															<input type="text" id="guestNo" name="guests" value="2"/>
															<button class="counter-btn" type="button" id="cnt-up"><i class="fa-solid fa-plus"></i></button>
														</div>
													</div>
												</div>
											</div>
											<div class="col-lg-6 col-md-6 col-sm-6 col-6">
												<div class="form-group">
													<div class="guests">
														<label for="guests">Kids</label>
														<div class="guests-box">
															<button class="counter-btn" type="button" id="kcnt-down"><i class="fa-solid fa-minus"></i></button>
															<input type="text" id="kidsNo" name="kids" value="0"/>
															<button class="counter-btn" type="button" id="kcnt-up"><i class="fa-solid fa-plus"></i></button>
														</div>
													</div>
												</div>
											</div>
											
											<div class="col-lg-12 col-md-12 col-sm-12 mt-3">
												<label for="guests">Advance features</label>
												<div class="_adv_features_list">
													<ul class="no-ul-list">
														<li>
															<input id="a-1" class="form-check-input" name="a-1" type="checkbox">
															<label for="a-1" class="form-check-label">Air Condition<i>$10</i></label>
														</li>
														<li>
															<input id="a-2" class="form-check-input" name="a-2" type="checkbox" checked>
															<label for="a-2" class="form-check-label">Bedding<i>$07</i></label>
														</li>
														<li>
															<input id="a-3" class="form-check-input" name="a-3" type="checkbox" checked>
															<label for="a-3" class="form-check-label">Heating<i>$20</i></label>
														</li>
														<li>
															<input id="a-4" class="form-check-input" name="a-4" type="checkbox">
															<label for="a-4" class="form-check-label">Internet<i>$10</i></label>
														</li>
														<li>
															<input id="a-5" class="form-check-input" name="a-5" type="checkbox">
															<label for="a-5" class="form-check-label">Microwave<i>$05</i></label>
														</li>
														</li>
													</ul>
												</div>
											</div>
											
											<div class="col-lg-12 col-md-12 col-sm-12 mt-3">
												<label for="guests">Price & Tax</label>
												<div class="_adv_features">
													<ul>
														<li>I Night<span>$310</span></li>
														<li>Discount 25$<span>-$250</span></li>
														<li>Service Fee<span>$17</span></li>
														<li>Breakfast Per Adult<span>$35</span></li>
													</ul>
												</div>
											</div>
											
											<div class="col-lg-12 col-md-12 col-sm-12">
												<div class="side-booking-foot">
													<span class="sb-header-left">Total Payment</span>
													<h3 class="price text-primary">$170</h3>
												</div>
											</div>
											<div class="col-lg-12 col-md-12 col-sm-12">
												<div class="stbooking-footer mt-1">
													<div class="form-group mb-0 pb-0">
														<a href="#" class="btn btn-primary fw-medium full-width">Book It Now</a>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
								
								<!-- Appointment Now Tab -->
								<div class="tab-pane fade" id="pills-appointment" role="tabpanel" aria-labelledby="pills-appointment-tab">
									<div class="sider-block-body p-3">
										<div class="row">
											<div class="col-lg-12 col-md-12 col-sm-12">
												<div class="form-group">
													<label>Full Name</label>
													<input type="text" class="form-control light" placeholder="Enter Name">
												</div>
											</div>
											<div class="col-lg-12 col-md-12 col-sm-12">
												<div class="form-group">
													<label>Email ID</label>
													<input type="text" class="form-control light" placeholder="Enter eMail ID">
												</div>
											</div>
											<div class="col-lg-12 col-md-12 col-sm-12">
												<div class="form-group">
													<label>Contact Number</label>
													<input type="text" class="form-control light" placeholder="Enter Phone No.">
												</div>
											</div>
											<div class="col-lg-12 col-md-12 col-sm-12">
												<div class="form-group">
													<label>Message</label>
													<textarea class="form-control light" placeholder="Explain Query"></textarea>
												</div>
											</div>
											<div class="col-lg-12 col-md-12 col-sm-12">
												<div class="form-group">
													<button class="btn btn-primary fw-medium full-width">Make Appointment</button>
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
			
		</div>
	</div>
</section>
<!-- ============================ Property Detail End ================================== -->

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



<!-- Display on mobile -->
<div class="dismob_block d-block d-sm-none">
	<div class="dismob_block_left">
		<a href="#" class="edlio_btn_block" data-bs-toggle="modal" data-bs-target="#availability"><i class="fa-solid fa-envelope-circle-check me-1"></i>Contact</a>
	</div>
	<div class="dismob_block_left">
		<a href="#" class="edlio_btn_block light" data-bs-toggle="modal" data-bs-target="#autho-message"><i class="fa-solid fa-calendar-days me-1"></i>Book A Tour</a>
	</div>
</div>

<!-- Contact -->
<div class="modal fade" id="availability" tabindex="-1" role="dialog" aria-labelledby="sign-up" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered login-pop-form" role="document">
		<div class="modal-content" id="sign-up">
			<span class="mod-close" data-bs-dismiss="modal" aria-hidden="true"><i class="fa-solid fa-circle-xmark"></i></span>
			<div class="modal-body">
				<div class="text-center">
					<h2 class="mb-0">CONTACT</h2>
					<h4 class="mb-0">5689 Resot Relly, Canada</h4>
					<a class="_calss_tyui text-primary fw-medium" href="tel:4048651904">(404) 865-1904</a>
				</div>
				<div class="login-form">
					<form>
						
						<div class="row">
							
							<div class="col-lg-12 col-md-12">
								<div class="form-group">
									<label>Message</label>
									<textarea class="form-control ht-120">I'm interested in 5689 Resot Relly, Canada. Please send me current availability and additional details.</textarea>
								</div>
							</div>
							
							<div class="col-lg-12 col-md-12">
								<div class="form-group">
									<label>Name*</label>
									<input type="text" class="form-control">
								</div>
							</div>
							
							<div class="col-lg-12 col-md-12">
								<div class="form-group">
									<label>Email*</label>
									<input type="email" class="form-control">
								</div>
							</div>
							
							<div class="col-lg-12 col-md-12">
								<div class="form-group">
									<label>Phone</label>
									<input type="text" class="form-control">
								</div>
							</div>
							
						</div>
						
						<div class="default-terms_wrap">
							<div class="default-terms_flex">
								<input id="tm" class="form-check-input" name="tm" type="checkbox">
								<label for="tm" class="form-check-label"></label>
							</div>
							<div class="default-terms">By submitting this form, you agree to our <a href="#" title="Terms of Service">Terms of Service</a> and <a href="#" title="Privacy Policy">Privacy Policy</a>.</div>
						</div>
						
						<div class="form-group">
							<button type="submit" class="btn full-width btn-primary">Send Message</button>
						</div>
					
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- End Modal -->

<!-- Send Message -->
<div class="modal fade" id="autho-message" tabindex="-1" role="dialog" aria-labelledby="authomessage" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered login-pop-form" role="document">
		<div class="modal-content" id="authomessage">
			<span class="mod-close" data-bs-dismiss="modal" aria-hidden="true"><i class="fa-solid fa-circle-xmark"></i></span>
			<div class="modal-body">
				<h2 class="text-center">Drop Message</h2>
				<div class="login-form">
					<form>
					
						<div class="form-group">
							<label>Subject</label>
							<div class="input-with-icons">
								<input type="text" class="form-control" placeholder="Message Title">
							</div>
						</div>
						
						<div class="form-group">
							<label>Messages</label>
							<div class="input-with-icons">
								<textarea class="form-control ht-80"></textarea>
							</div>
						</div>
						
						<div class="form-group">
							<button type="submit" class="btn full-width btn-primary">Send Message</button>
						</div>
					
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- End Modal -->
			

<?php
$hero_content = ob_get_clean(); // Capture the hero content

// Include the base template
include "$base_dir/style/base.php";
?>