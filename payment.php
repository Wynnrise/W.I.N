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
				
				<h2 class="ipt-title">Payment Methode</h2>
				<span class="ipn-subtitle">Pay your payment</span>
				
			</div>
		</div>
	</div>
</div>
<!-- ============================ Page Title End ================================== -->

<!-- =================== Sidebar Search ==================== -->
<section class="gray">
	<div class="container">
		<div class="row">
			<div class="col-lg-9 col-md-12 form-submit">
				
				<!-- 1st Step Checkout -->
				<div class="checkout-wrap">
					
					<div class="checkout-head">
						<ul>
							<li class="active"><span><i class="fa-solid fa-check"></i></span>Customer Detail</li>
							<li><span>2</span>Payment Information</li>
							<li><span>3</span>Confirmation!</li>
						</ul>
					</div>
					
					<div class="checkout-body">
						<div class="row">
					
							<div class="col-lg-12 col-md-12 col-sm-12">
								<h4 class="mb-3">Billing Detail</h4>
							</div>
							
							<div class="col-lg-6 col-md-6 col-sm-12">
								<div class="form-group">
									<label>First Name<i class="req">*</i></label>
									<input type="text" class="form-control" value="harry">
								</div>
							</div>
							
							<div class="col-lg-6 col-md-6 col-sm-12">
								<div class="form-group">
									<label>Last Name<i class="req">*</i></label>
									<input type="text" class="form-control" value="Preet">
								</div>
							</div>
							
							<div class="col-lg-6 col-md-6 col-sm-12">
								<div class="form-group">
									<label>Email<i class="req">*</i></label>
									<input type="email" class="form-control" value="shreethemes@gmail.com">
								</div>
							</div>
							
							<div class="col-lg-6 col-md-6 col-sm-12">
								<div class="form-group">
									<label>Phone</label>
									<input type="text" class="form-control" value="120 053 4444">
								</div>
							</div>
							
							<div class="col-lg-6 col-md-6 col-sm-12">
								<div class="form-group">
									<label>Country<i class="req">*</i></label>
									<select id="country" class="form-control">
										<option value="">&nbsp;</option>
										<option value="1">United State</option>
										<option value="2">United kingdom</option>
										<option value="3">India</option>
										<option value="4">Canada</option>
									</select>
								</div>
							</div>
							
							<div class="col-lg-6 col-md-6 col-sm-12">
								<div class="form-group">
									<label>City<i class="req">*</i></label>
									<select id="cities" class="form-control">
										<option value="">&nbsp;</option>
										<option value="1">Canada, USA</option>
										<option value="2">California</option>
										<option value="3">Newyork</option>
										<option value="4">Liverpool</option>
									</select>
								</div>
							</div>
							
							<div class="col-lg-12 col-md-12 col-sm-12">
								<div class="form-group">
									<label>Special Instruction</label>
									<textarea class="form-control"></textarea>
								</div>
							</div>										
							
							<div class="col-lg-6 col-md-6 col-sm-12">
								<div class="form-group">
									<input id="a-2" class="form-check-input" name="a-2" type="checkbox">
									<label for="a-2" class="form-check-label">Create An Account</label>
								</div>
							</div>
							
							<div class="col-lg-12 col-md-12 col-sm-12">
								<div class="form-group text-center">
									<a href="#" class="btn btn-primary rounded">Next Step</a>
								</div>
							</div>
							
						</div>
					</div>
					
				</div>
				
				<!-- 2st Step Checkout -->
				<div class="checkout-wrap">
					
					<div class="checkout-head">
						<ul>
							<li><span><i class="fa-solid fa-check"></i></span>Customer Detail</li>
							<li class="active"><span>2</span>Payment Information</li>
							<li><span>3</span>Confirmation!</li>
						</ul>
					</div>
					
					<div class="checkout-body">
						<div class="row mb-5">
					
							<div class="col-lg-12 col-md-12 col-sm-12">
								<h4 class="mb-3">Payment Information</h4>
							</div>
							
							<div class="col-lg-6 col-md-6 col-sm-12">
								<div class="switchbtn paying">
									<input id="pay-2" class="switchbtn-checkbox" type="radio" value="2" name="pay-2" checked>
									<label class="switchbtn-label" for="pay-2">
										Pay with Credit card
									</label>
								</div>
							</div>
							
							<div class="col-lg-6 col-md-6 col-sm-12">
								<div class="switchbtn paying">
									<input id="pay-3" class="switchbtn-checkbox" type="radio" value="2" name="pay-2">
									<label class="switchbtn-label" for="pay-3">
										Pay with PayPal
									</label>
								</div>
							</div>
						</div>
						
						<div class="row">
							<div class="col-lg-6 col-md-6 col-sm-12">
								<div class="form-group">
									<label>Card Holder Name</label>
									<input type="text" class="form-control">
								</div>
							</div>
							
							<div class="col-lg-6 col-md-6 col-sm-12">
								<div class="form-group">
									<label>Card Number</label>
									<input type="text" class="form-control">
								</div>
							</div>									
						
							<div class="col-lg-5 col-md-5 col-sm-6">
								<div class="form-group">
									<label>Expire Month</label>
									<input type="text" class="form-control">
								</div>
							</div>
							
							<div class="col-lg-5 col-md-5 col-sm-6">
								<div class="form-group">
									<label>Expire Year</label>
									<input type="text" class="form-control">
								</div>
							</div>
							
							<div class="col-lg-2 col-md-2 col-sm-12">
								<div class="form-group">
									<label>CVC</label>
									<input type="text" class="form-control">
								</div>
							</div>										
							
							<div class="col-lg-6 col-md-6 col-sm-12">
								<div class="form-group">
									<input id="a-2" class="form-check-input" name="a-2" type="checkbox">
									<label for="a-2" class="form-check-label">By Continuing, you ar'e agree to conditions</label>
								</div>
							</div>
							
							<div class="col-lg-12 col-md-12 col-sm-12">
								<div class="form-group text-center">
									<a href="#" class="btn btn-primary rounded full-width">Confirm Booking</a>
								</div>
							</div>
							
						</div>
					</div>
					
				</div>
				
				<!-- 2st Step Checkout -->
				<div class="checkout-wrap">
					
					<div class="checkout-head">
						<div class="success-message">
							<span class="thumb-check"><i class="fa-solid fa-check-double"></i></span>
							<h4>Thank You, Your Booking Order Confirmed!</h4>
							<p>A confirmation mail send to your email, Check your inbox.</p>
						</div>
					</div>
					
					<div class="checkout-body">
						
						
						<div class="row">
							<div class="col-md-12 col-lg-12">
							
								<ul class="booking-detail-list">
									<li>Booking ID/Num.<span>#BK1254872</span></li>
									<li>First Name<span>harry</span></li>
									<li>Last Name<span>Preet</span></li>
									<li>Email<span>shreethemes@gmail.com</span></li>
									<li>Phone<span>91 235 458 7458</span></li>
									<li>City<span>California</span></li>
									<li>Contry<span>United State</span></li>
									<li>Location<span>New Besil, Liverpool</span></li>
									<li>Zip<span>215467</span></li>
								</ul>
								<hr>
								
								<h4>Payment Detail</h4>
								<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit</p>
								
							</div>
						</div>
					</div>
					
				</div>
				
			</div>
			<!-- Sidebar End -->
				
			<div class="col-lg-3 col-md-12">
				<div class="checkout-side">
				
					<div class="booking-short">
						<img src="<?php echo $static_url; ?>/img/p-14.jpg" class="img-fluid" alt="" />
						<h4>Manali To Paris, London</h4>
						<span>Liverpool, London</span>
					</div>
					
					<div class="booking-short-side">
						<div class="accordion" id="accordionExample">
							<div class="card">
								<div class="card-header" id="bookinDet">
									<h2 class="mb-0">
									<button class="btn btn-link py-0" type="button" data-bs-toggle="collapse" data-bs-target="#bookinSer" aria-expanded="true" aria-controls="bookinSer">
										Booking Detail
									</button>
									</h2>
								</div>

								<div id="bookinSer" class="collapse show" aria-labelledby="bookinDet" data-parent="#accordionExample">
									<div class="card-body">
										<ul class="booking-detail-list">
											<li>10 May 2025- 20 May 2025</li>
											<li>Tour Days<span>5 Days</span></li>
											<li>Adults<span>4</span></li>
											<li>Children<span>3</span></li>
										</ul>
									</div>
								</div>
							</div>
							
							<div class="card">
								<div class="card-header" id="extraFeat">
									<h2 class="mb-0">
									<button class="btn btn-link py-0 collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#extraSer" aria-expanded="false" aria-controls="extraSer">
										Extra Features
									</button>
									</h2>
								</div>
								<div id="extraSer" class="collapse" aria-labelledby="extraFeat" data-parent="#accordionExample">
									<div class="card-body">
										<ul class="booking-detail-list">
											<li>
												<div class="form-check">
													<input class="form-check-input" type="checkbox" value="" id="brks">
													<label class="form-check-label" for="brks">
														Breakfast
													</label>
												</div>
											</li>
											<li>
												<div class="form-check">
													<input class="form-check-input" type="checkbox" value="" id="rsv">
													<label class="form-check-label" for="rsv">
														Rooms Service
													</label>
												</div>
											</li>
											<li>
												<div class="form-check">
													<input class="form-check-input" type="checkbox" value="" id="wifis">
													<label class="form-check-label" for="wifis">
														Wifi Free
													</label>
												</div>
											</li>
											<li>
												<div class="form-check">
													<input class="form-check-input" type="checkbox" value="" id="carser">
													<label class="form-check-label" for="carser">
														Car Driving
													</label>
												</div>
											</li>
										</ul>
									</div>
								</div>
								</div>
								
								<div class="card">
								<div class="card-header" id="CouponCode">
									<h2 class="mb-0">
									<button class="btn btn-link py-0 h-auto collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#couponcd" aria-expanded="false" aria-controls="couponcd">
										Coupon Code
									</button>
									</h2>
								</div>
								<div id="couponcd" class="collapse show" aria-labelledby="CouponCode" data-parent="#accordionExample">
									<div class="card-body">
										<div class="form-group">
											<input type="text" class="form-control" placeholder="Code">
											<button type="button" class="btn btn-dark full-width mt-2">Apply</button>
										</div>
									</div>
								</div>
							</div>
							
							<div class="card">
								<div class="card-header" id="PayMents">
									<h2 class="mb-0">
									<button class="btn btn-link collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#payser" aria-expanded="false" aria-controls="payser">
										Payment
									</button>
									</h2>
								</div>
								<div id="payser" class="collapse" aria-labelledby="PayMents" data-parent="#accordionExample">
									<div class="card-body">
										<ul class="booking-detail-list">
											<li>Sub Total<span>$224</span></li>
											<li>Extra Price<span>$70</span></li>
											<li>Tax<span>$20</span></li>
											<li><b>Pay Ammount</b><span>$314</span></li>
										</ul>
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
<!-- =================== Sidebar Search ==================== -->

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