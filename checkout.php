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
				
				<h2 class="ipt-title">Checkout</h2>
				<span class="ipn-subtitle">Proceed For Payment</span>
				
			</div>
		</div>
	</div>
</div>
<!-- ============================ Page Title End ================================== -->

<!-- ============================ Our Story Start ================================== -->
<section class="gray-simple">

	<div class="container">
	
		<!-- row Start -->
		<div class="row">
			<div class="col-lg-12 col-md-12">
				<div class="alert bg-success text-light text-center" role="alert">
					Hi Dear, Have you already an account? <a href="#" class="text-warning" data-bs-toggle="collapse" data-bs-target="#login-frm">Please Login</a>
				</div>
			</div>
			
			<div class="col-lg-12 col-md-12">	
				<div id="login-frm" class="collapse mb-5">
					<div class="row">
						
						<div class="col-lg-5 col-md-4 col-sm-6">
							<div class="form-group">
								<div class="input-with-icons">
									<input type="text" class="form-control" placeholder="Username">
								</div>
							</div>
						</div>
						
						<div class="col-lg-5 col-md-4 col-sm-6">
							<div class="form-group">
								<div class="input-with-icons">
									<input type="text" class="form-control" placeholder="*******">
								</div>
							</div>
						</div>
						
						<div class="col-lg-2 col-md-4 col-sm-12">
							<div class="form-group">
								<button type="submit" class="btn btn-primary full-width">Submit</button>
							</div>
						</div>
						
						<div class="col-lg-12 col-md-12 col-sm-12">
							<div class="exclop-wrap d-flex align-items-center justify-content-between">
								<div class="exclop">
									<input id="a-1" class="form-check-input" name="a-1" type="checkbox">
									<label for="a-1" class="form-check-label">Remember Me</label>
								</div>
								<div class="exclop-last">
									<a href="#" class="fw-medium text-primary">Forget Password?</a>
								</div>
							</div>
						</div>
						
					</div>
				</div>
			</div>
		</div>
		<!-- /row -->

		<!-- row Start -->
		<div class="row form-submit">
			<div class="col-lg-8 col-md-8 col-sm-12">
			
				<!-- row -->
				<div class="row m-0">
					<div class="submit-page">
						<div class="row">
						
							<div class="col-lg-12 col-md-12 col-sm-12">
								<h3>Billing Detail</h3>
							</div>
							
							<div class="col-lg-6 col-md-6 col-sm-12">
								<div class="form-group">
									<label>Name<i class="req">*</i></label>
									<input type="text" class="form-control">
								</div>
							</div>
							
							<div class="col-lg-6 col-md-6 col-sm-12">
								<div class="form-group">
									<label>Email<i class="req">*</i></label>
									<input type="text" class="form-control">
								</div>
							</div>
							
							<div class="col-lg-12 col-md-12 col-sm-12">
								<div class="form-group">
									<label>Company Name</label>
									<input type="text" class="form-control">
								</div>
							</div>
							
							<div class="col-lg-12 col-md-12 col-sm-12">
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
							
							<div class="col-lg-12 col-md-12 col-sm-12">
								<div class="form-group">
									<label>Street<i class="req">*</i></label>
									<input type="text" class="form-control">
								</div>
							</div>
							
							<div class="col-lg-6 col-md-6 col-sm-12">
								<div class="form-group">
									<label>Apartment</label>
									<input type="text" class="form-control">
								</div>
							</div>
							
							<div class="col-lg-6 col-md-6 col-sm-12">
								<div class="form-group">
									<label>Town/City<i class="req">*</i></label>
									<select id="town" class="form-control">
										<option value="">&nbsp;</option>
										<option value="1">Punjab</option>
										<option value="2">Chandigarh</option>
										<option value="3">Allahabad</option>
										<option value="4">Lucknow</option>
									</select>
								</div>
							</div>
							
							<div class="col-lg-6 col-md-6 col-sm-12">
								<div class="form-group">
									<label>State<i class="req">*</i></label>
									<input type="text" class="form-control">
								</div>
							</div>
							
							<div class="col-lg-6 col-md-6 col-sm-12">
								<div class="form-group">
									<label>Postcode/Zip<i class="req">*</i></label>
									<input type="text" class="form-control">
								</div>
							</div>
							
							<div class="col-lg-6 col-md-6 col-sm-12">
								<div class="form-group">
									<label>Phone<i class="req">*</i></label>
									<input type="text" class="form-control">
								</div>
							</div>
							
							<div class="col-lg-6 col-md-6 col-sm-12">
								<div class="form-group">
									<label>Landline</label>
									<input type="text" class="form-control">
								</div>
							</div>
							
							<div class="col-lg-12 col-md-12 col-sm-12">
								<div class="form-group">
									<label>Additional Information</label>
									<textarea class="form-control ht-50"></textarea>
								</div>
							</div>
							
							<div class="col-lg-6 col-md-6 col-sm-12">
								<div class="form-group">
									<input id="a-2" class="form-check-input" name="a-2" type="checkbox">
									<label for="a-2" class="form-check-label">Create An Account</label>
								</div>
							</div>
					
						</div>
					</div>
				</div>
				<!--/row -->
			
			</div>
			
			<!-- Col-lg 4 -->
			<div class="col-lg-4 col-md-4 col-sm-12">
				
				<div class="col-lg-12 col-md-12 col-sm-12">
					<h3>Your Order</h3>
				</div>
				
				<div class="col-lg-12 col-md-12 col-sm-12">
					<div class="product-wrap">
						<h5>Platinum</h5>
						<ul>
							<li><strong>Total</strong>$319</li>
							<li><strong>Subtotal</strong>$319</li>
							<li><strong>Tax</strong>$10</li>
							<li><strong>Total</strong>$329</li>
						</ul>
					</div>
				</div>
				
				<div class="col-lg-12 col-md-12 col-sm-12">
					<div class="alert bg-danger text-light text-center" role="alert">
						Have You Coupon? <a href="#" class="text-warning" data-bs-toggle="collapse" data-bs-target="#coupon-frm">Click Here</a>
					</div>
				</div>
				
				<div class="col-lg-12 col-md-12 col-sm-12 mb-2">
					<div id="coupon-frm" class="collapse">
						<input type="text" class="form-control mb-2" placeholder="Coupon Code">
						<button type="submit" class="btn btn-primary full-width mb-2">Apply Coupon</button>
					</div>
				</div>
				
				<div class="col-lg-12 col-md-12 col-sm-12">
					<div class="pay-wrap">
					
						<div class="pay-wrap-header">
							<h4>Platinum</h4>
							<div class="pw-right">
								<h3 class="text-primary">$12<sub>\Month</sub></h3>
							</div>
						</div>
						
						<div class="pay-wrap-content">
							
							<div class="pw-first-content">
								<h4>Your Features</h4>
								<button data-toggle="collapse" data-target="#change-plan">Change Plan</button>
							</div>
							
							<div id="change-plan" class="collapse">
								<ul class="no-ul-list">
									<li>
										<input id="basic" class="form-check-input" name="plan" type="radio">
										<label for="basic" class="form-check-label">Basic Plan</label>
									</li>
									<li>
										<input id="platinum" class="form-check-input" name="plan" type="radio" checked>
										<label for="platinum" class="form-check-label">Platinum</label>
									</li>
									<li>
										<input id="standard" class="form-check-input" name="plan" type="radio">
										<label for="standard" class="form-check-label">Standard</label>
									</li>
								</ul>
							</div>
							
							<div class="pw-content-detail">
								<ul class="pw-features">
									<li>First Features</li>
									<li>Second Features</li>
									<li>Third Features</li>
									<li>Fourth Features</li>
								</ul>
							</div>
							
							<div class="pw-btn-wrap">
								<a href="payment.php" class="btn btn-primary rounded full-width">Proceed Payment</a>
							</div>
							
						</div>
						
					</div>
				</div>
				
				
				
			</div>
			<!-- /col-lg-4 -->
			
		</div>
		<!-- /row -->					
		
	</div>
			
</section>
<!-- ============================ Our Story End ================================== -->

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