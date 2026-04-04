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
<div class="page-title">
	<div class="container">
		<div class="row">
			<div class="col-lg-12 col-md-12">
				
				<h2 class="ipt-title">Grid Layout With Sidebar</h2>
				<span class="ipn-subtitle">Property Grid With Sidebar</span>
				
			</div>
		</div>
	</div>
</div>
<!-- ============================ Page Title End ================================== -->

<!-- ============================ All Property ================================== -->
<section class="gray-simple">

	<div class="container">
	
		<div class="row">
			<div class="col-lg-12 col-md-12">
				<div class="filter_search_opt">
					<a href="javascript:void(0);" class="btn btn-dark full-width mb-4" onclick="openFilterSearch()">
						<span class="svg-icon text-light svg-icon-2hx me-2">
							<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M19.0759 3H4.72777C3.95892 3 3.47768 3.83148 3.86067 4.49814L8.56967 12.6949C9.17923 13.7559 9.5 14.9582 9.5 16.1819V19.5072C9.5 20.2189 10.2223 20.7028 10.8805 20.432L13.8805 19.1977C14.2553 19.0435 14.5 18.6783 14.5 18.273V13.8372C14.5 12.8089 14.8171 11.8056 15.408 10.964L19.8943 4.57465C20.3596 3.912 19.8856 3 19.0759 3Z" fill="currentColor"/>
							</svg>
						</span>Open Filter Option
					</a>
				</div>
			</div>
		</div>
		
		<div class="row">
		
			<!-- property-sidebar code  -->
			<?php
				include "$base_dir/Components/Listings/property-sidebar.php";
			?>
			
			<div class="col-lg-8 col-md-12 col-sm-12">
				
				<div class="row justify-content-center">
					<div class="col-lg-12 col-md-12">
						<div class="item-shorting-box">
							<div class="item-shorting clearfix">
								<div class="left-column pull-left"><h4 class="fs-6 m-0">Found 1-10 of 142 Results</h4></div>
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
										<a href="grid-layout-with-sidebar.php" class="active w-12 h-12">
											<span class="svg-icon text-seegreen svg-icon-2hx">
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
										<a href="list-layout-with-sidebar.php" class="w-12 h-12">
											<span class="svg-icon text-muted-2 svg-icon-2hx">
												<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
													<path opacity="0.3" d="M14 10V20C14 20.6 13.6 21 13 21H10C9.4 21 9 20.6 9 20V10C9 9.4 9.4 9 10 9H13C13.6 9 14 9.4 14 10ZM20 9H17C16.4 9 16 9.4 16 10V20C16 20.6 16.4 21 17 21H20C20.6 21 21 20.6 21 20V10C21 9.4 20.6 9 20 9Z" fill="currentColor"/>
													<path d="M7 10V20C7 20.6 6.6 21 6 21H3C2.4 21 2 20.6 2 20V10C2 9.4 2.4 9 3 9H6C6.6 9 7 9.4 7 10ZM21 6V3C21 2.4 20.6 2 20 2H3C2.4 2 2 2.4 2 3V6C2 6.6 2.4 7 3 7H20C20.6 7 21 6.6 21 6Z" fill="currentColor"/>
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
			
					<!-- propertys-8 code  -->
					<?php
						include "$base_dir/Components/Listings/propertys-8.php";
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
			
		</div>
	</div>	
</section>
<!-- ============================ All Property ================================== -->

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
