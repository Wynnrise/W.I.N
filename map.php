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
?>
			
			
<!-- ============================ Hero Banner  Start================================== -->
<div class="home-map-banner full-wrapious">
	<div class="hm-map-container fw-map">
		<div id="map"></div>
	</div>
	
	<!-- Advance Search -->
	<div class="advance-search-container">
		<div class="container">
			<div class="row">
				<div class="col-md-12">
					<button data-bs-toggle="collapse" data-bs-target="#ad-search" class="btn btn-primary">Advance Search</button>

					<div id="ad-search" class="collapse">
						<div class="map-search-box">
				
							<div class="full-search-2 eclip-search italian-search hero-search-radius rounded-4 border">
								<div class="hero-search-content">
									<div class="row">
									
										<div class="col-xl-3 col-lg-3 col-md-4 col-sm-12 b-r">
											<div class="form-group">
												<div class="choose-propert-type">
													<ul>
														<li>
															<div class="form-check">
																<input class="form-check-input" type="radio" id="typbuy" name="typeprt">
																<label class="form-check-label" for="typbuy">
																	For Buy
																</label>
															</div>
														</li>
														<li>
															<div class="form-check">
																<input class="form-check-input" type="radio" id="typrent" name="typeprt" checked>
																<label class="form-check-label" for="typrent">
																	For Rent
																</label>
															</div>
														</li>
													</ul>
												</div>
											</div>
										</div>
										
										<div class="col-xl-7 col-lg-7 col-md-5 col-sm-12 p-md-0 elio">
											<div class="form-group border-start borders">
												<div class="position-relative">
													<input type="text" class="form-control border-0 ps-5" placeholder="Search for a location">
													<div class="position-absolute top-50 start-0 translate-middle-y ms-2">
														<span class="svg-icon text-primary svg-icon-2hx">
															<svg width="25" height="25" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
																<path opacity="0.3" d="M18.0624 15.3453L13.1624 20.7453C12.5624 21.4453 11.5624 21.4453 10.9624 20.7453L6.06242 15.3453C4.56242 13.6453 3.76242 11.4453 4.06242 8.94534C4.56242 5.34534 7.46242 2.44534 11.0624 2.04534C15.8624 1.54534 19.9624 5.24534 19.9624 9.94534C20.0624 12.0453 19.2624 13.9453 18.0624 15.3453Z" fill="currentColor"/>
																<path d="M12.0624 13.0453C13.7193 13.0453 15.0624 11.7022 15.0624 10.0453C15.0624 8.38849 13.7193 7.04535 12.0624 7.04535C10.4056 7.04535 9.06241 8.38849 9.06241 10.0453C9.06241 11.7022 10.4056 13.0453 12.0624 13.0453Z" fill="currentColor"/>
															</svg>
														</span>
													</div>
												</div>
											</div>
										</div>
										
										<div class="col-xl-2 col-lg-2 col-md-3 col-sm-12">
											<div class="form-group">
												<button type="button" class="btn btn-dark full-width">Search</button>
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
<!-- ============================ Hero Banner End ================================== -->

<!-- ============================ Step How To Use Start ================================== -->
<section>
	<div class="container">
		
		<div class="row justify-content-center">
			<div class="col-xl-6 col-lg-7 col-md-10 text-center">
				<div class="sec-heading center">
					<h2>How It Works?</h2>
					<p>At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores</p>
				</div>
			</div>
		</div>
		
		<div class="row justify-content-center g-4">
			
			<!-- services code  -->
			<?php
				include "$base_dir/Components/Home/services.php";
			?>

		</div>
		
	</div>
</section>
<div class="clearfix"></div>
<!-- ============================ Step How To Use End ====================== -->


<!-- ========================= Explore Property ========================== -->
<section class="bg-light">
	<div class="container">
		
		<div class="row justify-content-center">
			<div class="col-xl-6 col-lg-7 col-md-10 text-center">
				<div class="sec-heading center">
					<h2>Explore Recent properties</h2>
					<p>At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores</p>
				</div>
			</div>
		</div>
		
		<div class="row justify-content-center g-4">
			
			<!-- properties code  -->
			<?php
				include "$base_dir/Components/Home/properties.php";
			?>
			
		</div>
		
		<div class="row align-items-center justify-content-center">
			<div class="col-lg-12 col-md-12 col-sm-12 text-center mt-5">
				<a href="listings-list-with-sidebar.php" class="btn btn-primary px-md-5 rounded">Browse More Properties</a>
			</div>
		</div>
		
	</div>	
</section>
<!-- ================================= Explore Property =============================== -->


<!-- ============================ Property Location Start ================================== -->
<section>
	<div class="container">
		
		<div class="row justify-content-center">
			<div class="col-xl-6 col-lg-7 col-md-10 text-center">
				<div class="sec-heading center">
					<h2>Find Best Locations</h2>
					<p>At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores</p>
				</div>
			</div>
		</div>
		
		<div class="row justify-content-center g-xl-4 g-md-3 g-4">
		
			<!-- best-locations code  -->
			<?php
				include "$base_dir/Components/Home/best-locations.php";
			?>
			
		</div>
		
		<div class="row">
			<div class="col-lg-12 col-md-12 col-sm-12 text-center mt-5">
				<a href="listings-list-with-sidebar.php" class="btn btn-primary px-md-5 rounded">Browse More Locations</a>
			</div>
		</div>
		
	</div>
</section>
<!-- ============================ Property Location End ================================== -->


<!-- ============================ Smart Testimonials ================================== -->
<section class="gray">
	<div class="container">
	
		<div class="row justify-content-center">
			<div class="col-xl-6 col-lg-7 col-md-10 text-center">
				<div class="sec-heading center">
					<h2>Good Reviews by Customers</h2>
					<p>At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores</p>
				</div>
			</div>
		</div>
		
		<div class="row justify-content-center">
			
			<div class="col-lg-12 col-md-12">
				
				<div class="smart-textimonials smart-center" id="smart-textimonials">
					
					<!-- reviews code  -->
					<?php
						include "$base_dir/Components/Home/reviews.php";
					?>
					
				</div>
			</div>
			
		</div>
	</div>
</section>
<!-- ============================ Smart Testimonials End ================================== -->

<!-- ============================ Price Table Start ================================== -->
<section>
	<div class="container">
	
		<div class="row justify-content-center">
			<div class="col-xl-6 col-lg-7 col-md-10 text-center">
				<div class="sec-heading center">
					<h2>See our packages</h2>
					<p>At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores</p>
				</div>
			</div>
		</div>
		
		<div class="row align-items-center justify-content-center g-lg-4 g-md-3 g-4">
		
			<!-- packages code  -->
			<?php
				include "$base_dir/Components/Home/packages.php";
			?>
			
		</div>
		
	</div>	
</section>
<!-- ============================ Price Table End ================================== -->

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