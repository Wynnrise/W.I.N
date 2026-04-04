<?php
$base_dir = __DIR__ . '/Base';
$static_url = '/assets'; // Ensure this is the correct path

// Include the common navlink content
ob_start();
include "$base_dir/navbar6.php"; // This file contains the shared navlink content
$navlink_content = ob_get_clean(); // Capture the navlink content
$page= 'nav6';
$fpage= 'foot';

// Optionally define the Hero block content
ob_start();
?>
			
<!-- ============================ Page Title Start================================== -->
<div class="page-title">
	<div class="container">
		<div class="row">
			<div class="col-lg-12 col-md-12">
				
				<h2 class="ipt-title">Update Your Detail</h2>
				<span class="ipn-subtitle">Add Agent Under ABC Agency</span>
				
			</div>
		</div>
	</div>
</div>
<!-- ============================ Page Title End ================================== -->

<!-- ============================ Submit Property Start ================================== -->
<section class="gray-simple">
	<div class="container">
		<div class="row">
			
			<!-- Submit Form -->
			<div class="col-lg-12 col-md-12">
			
				<div class="submit-page">
				
					<!-- Gallery -->
					<div class="form-submit middle-logo">	
						<h3>Profile Logo</h3>
						<div class="submit-section">
							<div class="form-row">
							
								<div class="form-group col-md-12">
									<form action="/upload-target" class="dropzone profile-logo dz-clickable primary-dropzone">
										<div class="dz-default dz-message">
											<i class="fa-solid fa-images"></i>
										</div>
									</form>
								</div>
								
							</div>
						</div>
					</div>
					
					<!-- Basic Information -->
					<div class="form-submit">	
						<h3>Basic Information</h3>
						<div class="submit-section">
							<div class="row">
							
								<div class="form-group col-md-12">
									<label>Full Name<span class="tip-topdata" data-tip="Property Title"><i class="fa-solid fa-info"></i></span></label>
									<input type="text" class="form-control" value="harry Preet">
								</div>
								
								<div class="form-group col-md-6">
									<label>Designation</label>
									<input type="text" class="form-control" value="CEO of Applio">
								</div>
								
								<div class="form-group col-md-6">
									<label>Phone</label>
									<input type="text" class="form-control" value="123 1254 458">
								</div>
								
								<div class="form-group col-md-6">
									<label>Email</label>
									<input type="text" class="form-control" value="support@gmail.com">
								</div>
								
								<div class="form-group col-md-6">
									<label>Landline</label>
									<input type="text" class="form-control" value="123 456">
								</div>
								
								<div class="form-group col-md-12">
									<label>Description</label>
									<textarea class="form-control h-120" value="about text"></textarea>
								</div>
								
							</div>
						</div>
					</div>
					
					<!-- Location -->
					<div class="form-submit">	
						<h3>Location</h3>
						<div class="submit-section">
							<div class="row">
							
								<div class="form-group col-md-6">
									<label>Address</label>
									<input type="text" class="form-control" value="2850, Sector 20 C">
								</div>
								
								<div class="form-group col-md-6">
									<label>Address 2</label>
									<input type="text" class="form-control" value="">
								</div>
								
								<div class="form-group col-md-6">
									<label>Country</label>
									<input type="text" class="form-control" value="India">
								</div>
								
								<div class="form-group col-md-6">
									<label>State</label>
									<input type="text" class="form-control" value="Punjab">
								</div>
								
								<div class="form-group col-md-6">
									<label>City</label>
									<input type="text" class="form-control" value="Chandigarh">
								</div>
								
								<div class="form-group col-md-6">
									<label>Zip Code</label>
									<input type="text" class="form-control" value="160020">
								</div>
								
							</div>
						</div>
					</div>
					
					<!-- Contact Information -->
					<div class="form-submit">	
						<h3>Social Accounts</h3>
						<div class="submit-section">
							<div class="row">
							
								<div class="form-group col-md-4">
									<label>Facebook</label>
									<input type="text" class="form-control" value="https://facebook.com/preet">
								</div>
								
								<div class="form-group col-md-4">
									<label>Twitter</label>
									<input type="text" class="form-control" value="https://twitter.com/preet">
								</div>
								
								<div class="form-group col-md-4">
									<label>Linkedin</label>
									<input type="text" class="form-control" value="https://linkedin.com/preet">
								</div>
								
								<div class="form-group col-md-4">
									<label>Google Plus</label>
									<input type="text" class="form-control">
								</div>
								
								<div class="form-group col-md-4">
									<label>Instagram</label>
									<input type="text" class="form-control">
								</div>
								
								<div class="form-group col-md-4">
									<label>Tumbler</label>
									<input type="text" class="form-control">
								</div>
								
							</div>
						</div>
					</div>
					
					<div class="form-group col-lg-12 col-md-12">
						<label>GDPR Agreement *</label>
						<ul class="no-ul-list">
							<li>
								<input id="aj-1" class="form-check-input" name="aj-1" type="checkbox">
								<label for="aj-1" class="form-check-label">I consent to having this website store my submitted information so they can respond to my inquiry.</label>
							</li>
						</ul>
					</div>
					
					<div class="form-group col-lg-12 col-md-12">
						<button class="btn btn-primary px-5 rounded" type="submit">Submit & Update</button>
					</div>
								
				</div>
			</div>
			
		</div>
	</div>
			
</section>
<!-- ============================ Submit Property End ================================== -->

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