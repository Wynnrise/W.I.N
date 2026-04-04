<?php
$base_dir = __DIR__ . '/Base';
$static_url = '/Resido/assets'; // Ensure this is the correct path

// Include the common navlink content
ob_start();
include "$base_dir/navbar5.php"; // This file contains the shared navlink content
$navlink_content = ob_get_clean(); // Capture the navlink content
$page= 'nav5';

// Optionally define the Hero block content
ob_start();
?>
			
			
<!-- ============================ Hero Banner  Start================================== -->
<div class="home-map-banner half-map">
	
	<div class="fs-left-map-box">
		<div class="home-map fl-wrap">
			<div class="hm-map-container fw-map">
				<div id="map"></div>
			</div>
		</div>
	</div>
	
	<div class="fs-inner-container">
		<div class="fs-content">
		
			<div class="row">
				<div class="col-lg-12 col-md-12">
					<div class="sty_1523">
						<div class="_mp_filter center mb-3">
							<div class="_mp_filter_first">
								<div class="filter_list_item">
									<div class="selected_item_wrap">
										<div class="slt_single_item"><a href="#" class="remove_pills"><span class="pills_tex">2 Beds</span><span class="remove_cross"></span></a></div>
										<div class="slt_single_item"><a href="#" class="remove_pills"><span class="pills_tex">2 Baths</span><span class="remove_cross"></span></a></div>
										<div class="slt_single_item"><a href="#" class="remove_pills"><span class="pills_tex">Garage</span><span class="remove_cross"></span></a></div>
										<div class="slt_single_item"><a href="#" class="remove_pills"><span class="pills_tex">2 More</span></a></div>
										<div class="slt_single_item"><a href="#" class="clear_pills"><span class="pills_clears">Clear All</span></a></div>
									</div>
								</div>
							</div>
							<div class="_mp_filter_last">
								<a href="#" class="map_filter min" data-bs-toggle="modal" data-bs-target="#filter"><i class="fa fa-sliders-h mr-2"></i>Short Filter</a>
							</div>
						</div>
						<h6>54 Apartment Available</h6>
					</div>
				</div>
			</div>
			
			<!--- All List -->
			<div class="row justify-content-center list-layout g-4">
			
				<!-- propertys-13 code  -->
				<?php
					include "$base_dir/Components/Listings/propertys-13.php";
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
<div class="clearfix"></div>
<!-- ============================ Hero Banner End ================================== -->

<!-- Check Availability -->
<div class="modal fade" id="availability" tabindex="-1" role="dialog" aria-labelledby="sign-up" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered login-pop-form" role="document">
		<div class="modal-content" id="sign-up">
			<span class="mod-close" data-bs-dismiss="modal" aria-hidden="true">
				<span class="svg-icon text-primary svg-icon-2hx">
					<svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
						<rect opacity="0.3" x="2" y="2" width="20" height="20" rx="10" fill="currentColor"/>
						<rect x="7" y="15.3137" width="12" height="2" rx="1" transform="rotate(-45 7 15.3137)" fill="currentColor"/>
						<rect x="8.41422" y="7" width="12" height="2" rx="1" transform="rotate(45 8.41422 7)" fill="currentColor"/>
					</svg>
				</span>
			</span>
			<div class="modal-body">
				<div class="text-center">
					<h2 class="mb-0">CONTACT</h2>
					<h4 class="mb-0">5689 Resot Relly, Canada</h4>
					<a class="_calss_tyui text-primary" href="tel:4048651904">(404) 865-1904</a>
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
						
						<div class="form-group mt-3">
							<button type="submit" class="btn btn-primary full-width">Send Message</button>
						</div>
					
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- End Modal -->

<!-- Filter Popup -->
<div class="modal fade bd-example-modal-lg" id="filter" tabindex="-1" role="dialog" aria-labelledby="sign-up" aria-hidden="true">
	<div class="modal-dialog modal-lg filter_scroll" role="document">
		<div class="modal-content" id="sign-up">
			<span class="mod-close" data-bs-dismiss="modal" aria-hidden="true"><i class="fa-solid fa-xmark"></i></span>
			<div class="modal-body">
				<div class="filter_modal">
					<div class="filter_modal_inner">
						<div class="filter_modal_flex">
						
							<div class="adv_ft_title"><h5>Advance Filter</h5></div>
							<!-- single item -->
							<div class="flt_single_item">
								<div class="flt_item_lablel"><label>Price</label></div>
								<div class="flt_item_content flcl">
									<div class="rg-slider">
										<input type="text" class="js-range-slider" name="my_range" value="" />
									</div>
								</div>
							</div>
							
							<!-- single item -->
							<div class="flt_single_item">
								<div class="flt_item_lablel"><label>Bedrooms</label></div>
								<div class="flt_item_content">
									<div class="switchbtn-wrap">
										<div class="switchbtn">
											<input id="bd-1" class="switchbtn-checkbox" type="checkbox" value="bd1" name="bd-1">
											<label class="switchbtn-label" for="bd-1">Studio</label>
										</div>
									</div>
									<div class="switchbtn-wrap">
										<div class="switchbtn">
											<input id="bd-2" class="switchbtn-checkbox" type="checkbox" value="bd2" name="bd-2">
											<label class="switchbtn-label" for="bd-2">1</label>
										</div>
									</div>
									<div class="switchbtn-wrap">
										<div class="switchbtn">
											<input id="bd-3" class="switchbtn-checkbox" type="checkbox" value="bd3" name="bd-3">
											<label class="switchbtn-label" for="bd-3">2</label>
										</div>
									</div>
									<div class="switchbtn-wrap">
										<div class="switchbtn">
											<input id="bd-4" class="switchbtn-checkbox" type="checkbox" value="bd4" name="bd-4">
											<label class="switchbtn-label" for="bd-4">3</label>
										</div>
									</div>
									<div class="switchbtn-wrap">
										<div class="switchbtn">
											<input id="bd-5" class="switchbtn-checkbox" type="checkbox" value="bd5" name="bd-5">
											<label class="switchbtn-label" for="bd-5">4+</label>
										</div>
									</div>
								</div>
							</div>
							
							<!-- single item -->
							<div class="flt_single_item">
								<div class="flt_item_lablel"><label>Bathrooms</label></div>
								<div class="flt_item_content">
									<div class="switchbtn-wrap">
										<div class="switchbtn">
											<input id="bt-1" class="switchbtn-checkbox" type="checkbox" value="bt1" name="bt-1">
											<label class="switchbtn-label" for="bt-1">1</label>
										</div>
									</div>
									<div class="switchbtn-wrap">
										<div class="switchbtn">
											<input id="bt-2" class="switchbtn-checkbox" type="checkbox" value="bt2" name="bt-2">
											<label class="switchbtn-label" for="bt-2">2</label>
										</div>
									</div>
									<div class="switchbtn-wrap">
										<div class="switchbtn">
											<input id="bt-3" class="switchbtn-checkbox" type="checkbox" value="bt3" name="bt-3">
											<label class="switchbtn-label" for="bt-3">3</label>
										</div>
									</div>
									<div class="switchbtn-wrap">
										<div class="switchbtn">
											<input id="bt-4" class="switchbtn-checkbox" type="checkbox" value="bt4" name="bt-4">
											<label class="switchbtn-label" for="bt-4">4</label>
										</div>
									</div>
									<div class="switchbtn-wrap">
										<div class="switchbtn">
											<input id="bt-5" class="switchbtn-checkbox" type="checkbox" value="bt5" name="bt-5">
											<label class="switchbtn-label" for="bt-5">5+</label>
										</div>
									</div>
								</div>
							</div>
							
							<!-- single item -->
							<div class="flt_single_item">
								<div class="flt_item_lablel"><label>Hot Deals</label></div>
								<div class="flt_item_content">
									<div class="switchbtn-wrap">
										<div class="switchbtn">
											<input id="d-4" class="switchbtn-checkbox" type="checkbox" value="d" name="d-4">
											<label class="switchbtn-label" for="d-4">Hot Deals</label>
										</div>
									</div>
								</div>
							</div>
							
							<!-- single item -->
							<div class="flt_single_item">
								<div class="flt_item_lablel"><label>Pets</label></div>
								<div class="flt_item_content">
									<div class="switchbtn-wrap">
										<div class="switchbtn">
											<input id="pet-4" class="switchbtn-checkbox" type="checkbox" value="pet" name="pet-4">
											<label class="switchbtn-label" for="pet-4">Pet Friendly</label>
										</div>
									</div>
								</div>
							</div>
					
							<!-- single item -->
							<div class="flt_single_item">
								<div class="flt_item_lablel"><label>Laundry</label></div>
								<div class="flt_item_content">
									<div class="switchbtn-wrap">
										<div class="switchbtn">
											<input id="ld-1" class="switchbtn-checkbox" type="checkbox" value="ld1" name="ld-1">
											<label class="switchbtn-label" for="ld-1">Washer/Dryer In Unit</label>
										</div>
									</div>
									<div class="switchbtn-wrap">
										<div class="switchbtn">
											<input id="ld-2" class="switchbtn-checkbox" type="checkbox" value="ld2" name="ld-2">
											<label class="switchbtn-label" for="ld-2">Washer/Dryer Connections</label>
										</div>
									</div>
									<div class="switchbtn-wrap">
										<div class="switchbtn">
											<input id="ld-3" class="switchbtn-checkbox" type="checkbox" value="ld3" name="ld-3">
											<label class="switchbtn-label" for="ld-3">Laundry Facility</label>
										</div>
									</div>
								</div>
							</div>
					
							<!-- single item -->
							<div class="flt_single_item">
								<div class="flt_item_lablel"><label>Amenities</label></div>
								<div class="flt_item_content align_center">
									<div class="switchbtn-wrap">
										<div class="switchbtn">
											<input id="am-1" class="switchbtn-checkbox" type="checkbox" value="am1" name="am-1">
											<label class="switchbtn-label" for="am-1">Air Conditioning</label>
										</div>
									</div>
									<div class="switchbtn-wrap">
										<div class="switchbtn">
											<input id="am-2" class="switchbtn-checkbox" type="checkbox" value="am2" name="am-2">
											<label class="switchbtn-label" for="am-2">Senior Living</label>
										</div>
									</div>
									<div class="switchbtn-wrap">
										<div class="switchbtn">
											<input id="am-3" class="switchbtn-checkbox" type="checkbox" value="am3" name="am-3">
											<label class="switchbtn-label" for="am-3">Waterfront</label>
										</div>
									</div>
									<div class="switchbtn-wrap">
										<div class="switchbtn">
											<input id="am-4" class="switchbtn-checkbox" type="checkbox" value="am4" name="am-4">
											<label class="switchbtn-label" for="am-4">Garage</label>
										</div>
									</div>
									<div class="switchbtn-wrap">
										<div class="switchbtn">
											<input id="am-5" class="switchbtn-checkbox" type="checkbox" value="am5" name="am-5">
											<label class="switchbtn-label" for="am-5">Spa & Massage</label>
										</div>
									</div>
									<div class="switchbtn-wrap">
										<div class="switchbtn">
											<input id="am-6" class="switchbtn-checkbox" type="checkbox" value="am6" name="am-6">
											<label class="switchbtn-label" for="am-6">Car Parking</label>
										</div>
									</div>
									<div class="switchbtn-wrap">
										<div class="switchbtn">
											<input id="am-7" class="switchbtn-checkbox" type="checkbox" value="am7" name="am-7">
											<label class="switchbtn-label" for="am-7">Free WiFi</label>
										</div>
									</div>
									<div class="switchbtn-wrap">
										<div class="switchbtn">
											<input id="am-8" class="switchbtn-checkbox" type="checkbox" value="am8" name="am-8">
											<label class="switchbtn-label" for="am-8">Pets Allow</label>
										</div>
									</div>
									<div class="switchbtn-wrap">
										<div class="switchbtn">
											<input id="am-9" class="switchbtn-checkbox" type="checkbox" value="am9" name="am-9">
											<label class="switchbtn-label" for="am-9">Internet</label>
										</div>
									</div>
									<div class="switchbtn-wrap">
										<div class="switchbtn">
											<input id="am-10" class="switchbtn-checkbox" type="checkbox" value="am10" name="am-10">
											<label class="switchbtn-label" for="am-10">Window Covering</label>
										</div>
									</div>
									<div class="switchbtn-wrap">
										<div class="switchbtn">
											<input id="am-11" class="switchbtn-checkbox" type="checkbox" value="am11" name="am-11">
											<label class="switchbtn-label" for="am-11">Alarm</label>
										</div>
									</div>
									<div class="switchbtn-wrap">
										<div class="switchbtn">
											<input id="am-12" class="switchbtn-checkbox" type="checkbox" value="am12" name="am-12">
											<label class="switchbtn-label" for="am-12">Gym</label>
										</div>
									</div>
									<div class="switchbtn-wrap">
										<div class="switchbtn">
											<input id="am-13" class="switchbtn-checkbox" type="checkbox" value="am13" name="am-13">
											<label class="switchbtn-label" for="am-13">Luxury Community</label>
										</div>
									</div>
									<div class="switchbtn-wrap">
										<div class="switchbtn">
											<input id="am-14" class="switchbtn-checkbox" type="checkbox" value="am14" name="am-14">
											<label class="switchbtn-label" for="am-14">Central Heating</label>
										</div>
									</div>
									<div class="switchbtn-wrap">
										<div class="switchbtn">
											<input id="am-15" class="switchbtn-checkbox" type="checkbox" value="am15" name="am-15">
											<label class="switchbtn-label" for="am-15">Swimming Pool</label>
										</div>
									</div>
								</div>
							</div>
					
							<!-- single item -->
							<div class="flt_single_item">
								<div class="flt_item_lablel"><label>Sort By</label></div>
								<div class="flt_item_content">
									<div class="switchbtn-wrap">
										<div class="switchbtn">
											<input id="st-1" class="switchbtn-checkbox" type="checkbox" value="st1" name="st-1">
											<label class="switchbtn-label" for="st-1">Best Match</label>
										</div>
									</div>
									<div class="switchbtn-wrap">
										<div class="switchbtn">
											<input id="st-2" class="switchbtn-checkbox" type="checkbox" value="st2" name="st-2">
											<label class="switchbtn-label" for="st-2">Price: Low to High</label>
										</div>
									</div>
									<div class="switchbtn-wrap">
										<div class="switchbtn">
											<input id="st-3" class="switchbtn-checkbox" type="checkbox" value="st3" name="st-3">
											<label class="switchbtn-label" for="st-3">Price: High to Low</label>
										</div>
									</div>
									<div class="switchbtn-wrap">
										<div class="switchbtn">
											<input id="st-4" class="switchbtn-checkbox" type="checkbox" value="st4" name="st-4">
											<label class="switchbtn-label" for="st-4">Top Rated</label>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<div class="elgio_filter">
					<div class="elgio_ft_first">
						<button class="btn btn-dark">
							Reset<span class="reset_counter">10</span>
						</button>
					</div>
					<div class="elgio_ft_last">
						<button class="btn btn-gray mr-2">Cancel</button>
						<button class="btn btn-primary mr-2">See 76 Properties</button>
					</div>
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