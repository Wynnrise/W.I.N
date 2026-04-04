<?php
$base_dir = __DIR__ . '/Base';
$static_url = '/Resido/assets'; // Ensure this is the correct path

// Include the common navlink content
ob_start();
include "$base_dir/navbar.php"; // This file contains the shared navlink content
$navlink_content = ob_get_clean(); // Capture the navlink content
$page= 'nav';
$fpage= 'foot';

// Optionally define the Hero block content
ob_start();
?>
			
			
<!-- ============================ Page Title Start================================== -->
<section class="bg-primary position-relative">
	<div class="position-absolute start-0 top-0 w-25 h-15 bg-light rounded-end-pill opacity-25 mt-4"></div>
	<div class="position-absolute start-0 bottom-0 w-15 h-20 bg-light rounded-top-pill opacity-25 ms-4"></div>
	<div class="position-absolute end-0 top-0 w-15 h-25 bg-light rounded-bottom-pill opacity-25 me-4"></div>
	<div class="position-absolute end-0 bottom-0 w-25 h-15 bg-light rounded-start-pill opacity-25 mb-4"></div>
	<div class="ht-30"></div>
	<div class="container">
		<div class="row justify-content-center">
			<div class="col-lg-7 col-md-12">
				
				<div class="full-search-2 eclip-search italian-search hero-search-radius shadow-hard">
					<div class="hero-search-content">
						<div class="row">

							<div class="col-lg-10 col-md-9 col-sm-12">
								<div class="form-group">
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
							
							<div class="col-lg-2 col-md-3 col-sm-12">
								<div class="form-group">
									<a href="#" class="btn btn-dark full-width">Search</a>
								</div>
							</div>
									
						</div>
					</div>
				</div>
				
			</div>
		</div>
	</div>
	<div class="ht-30"></div>
</section>
<!-- ============================ Page Title End ================================== -->

<!-- =========================== All Property =============================== -->	
<section class="gray-simple">
	<div class="container">
	
		<div class="row justify-content-center">
			<div class="col-lg-12 col-md-12">
				<div class="item-shorting-box">
					<div class="item-shorting clearfix">
						<div class="left-column pull-left"><h4 class="m-0 fs-6">Found 1-10 of 142 Results</h4></div>
					</div>
					<div class="item-shorting-box-right">
						<div class="shorting-by">
							<select id="shorty" class="form-control">
								<option value="">&nbsp;</option>
								<option value="1">Low Price</option>
								<option value="2">High Price</option>
								<option value="3">Most Popular</option>
							</select>
						</div>
						<ul class="shorting-list">
							<li>
								<a href="grid.php" class="w-12 h-12">
									<span class="svg-icon text-muted-2 svg-icon-2hx">
										<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
											<rect x="2" y="2" width="9" height="9" rx="2" fill="currentColor"/>
											<rect opacity="0.3" x="13" y="2" width="9" height="9" rx="2" fill="currentColor"/>
											<rect opacity="0.3" x="13" y="13" width="9" height="9" rx="2" fill="currentColor"/>
											<rect opacity="0.3" x="2" y="13" width="9" height="9" rx="2" fill="currentColor"/>
										</svg>
									</span>
								</a>
							</li>
							<li>
								<a href="list-layout-full.php" class="active w-12 h-12">
									<span class="svg-icon text-seegreen svg-icon-2hx">
										<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
											<path opacity="0.3" d="M14 10V20C14 20.6 13.6 21 13 21H10C9.4 21 9 20.6 9 20V10C9 9.4 9.4 9 10 9H13C13.6 9 14 9.4 14 10ZM20 9H17C16.4 9 16 9.4 16 10V20C16 20.6 16.4 21 17 21H20C20.6 21 21 20.6 21 20V10C21 9.4 20.6 9 20 9Z" fill="currentColor"/>
											<path d="M7 10V20C7 20.6 6.6 21 6 21H3C2.4 21 2 20.6 2 20V10C2 9.4 2.4 9 3 9H6C6.6 9 7 9.4 7 10ZM21 6V3C21 2.4 20.6 2 20 2H3C2.4 2 2 2.4 2 3V6C2 6.6 2.4 7 3 7H20C20.6 7 21 6.6 21 6Z" fill="currentColor"/>
										</svg>
									</span>
								</a>
							</li>
							<li>
								<a href="#" class="w-12 h-12" data-bs-toggle="modal" data-bs-target="#filter">
									<span class="svg-icon text-primary svg-icon-2hx">
										<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
											<path d="M17.5 11H6.5C4 11 2 9 2 6.5C2 4 4 2 6.5 2H17.5C20 2 22 4 22 6.5C22 9 20 11 17.5 11ZM15 6.5C15 7.9 16.1 9 17.5 9C18.9 9 20 7.9 20 6.5C20 5.1 18.9 4 17.5 4C16.1 4 15 5.1 15 6.5Z" fill="currentColor"/>
											<path opacity="0.3" d="M17.5 22H6.5C4 22 2 20 2 17.5C2 15 4 13 6.5 13H17.5C20 13 22 15 22 17.5C22 20 20 22 17.5 22ZM4 17.5C4 18.9 5.1 20 6.5 20C7.9 20 9 18.9 9 17.5C9 16.1 7.9 15 6.5 15C5.1 15 4 16.1 4 17.5Z" fill="currentColor"/>
										</svg>
									</span>
								</a>
							</li>
						</ul>
					</div>
				</div>
			</div>
		</div>
	
		<div class="row justify-content-center g-4">
			
			<!-- propertys-10 code  -->
			<?php
				include "$base_dir/Components/Listings/propertys-10.php";
			?>
			
		</div>
		
		<!-- Pagination -->
		<div class="row">
			<div class="col-lg-12 col-md-12 col-sm-12">
				<ul class="pagination p-center">
					<li class="page-item">
						<a class="page-link" href="#" aria-label="Previous">
						<i class="fa-solid fa-arrow-left-long"></i>
						<span class="sr-only">Previous</span>
						</a>
					</li>
					<li class="page-item"><a class="page-link" href="#">1</a></li>
					<li class="page-item"><a class="page-link" href="#">2</a></li>
					<li class="page-item active"><a class="page-link" href="#">3</a></li>
					<li class="page-item"><a class="page-link" href="#">...</a></li>
					<li class="page-item"><a class="page-link" href="#">18</a></li>
					<li class="page-item">
						<a class="page-link" href="#" aria-label="Next">
						<i class="fa-solid fa-arrow-right-long"></i>
						<span class="sr-only">Next</span>
						</a>
					</li>
				</ul>
			</div>
		</div>
		
	</div>		
</section>
<!-- =========================== All Property =============================== -->	

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

	<!-- filter-popup code  -->
	<?php
		include "$base_dir/Components/Listings/filter-popup.php";
	?>


			

<?php
$hero_content = ob_get_clean(); // Capture the hero content

// Include the base template
include "$base_dir/style/base.php";
?>