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
			
<!-- ============================ Page Title Start================================== -->
<div class="page-title">
	<div class="container">
		<div class="row">
			<div class="col-lg-12 col-md-12">
				
				<h2 class="ipt-title">Contact Us</h2>
				<span class="ipn-subtitle">Lists of our all Popular agencies</span>
				
			</div>
		</div>
	</div>
</div>
<!-- ============================ Page Title End ================================== -->

<!-- ============================ Agency List Start ================================== -->
<section>

	<div class="container">
	
		<!-- row Start -->
		<div class="row">
		
			<div class="col-lg-7 col-md-7">
				<form method="POST" name="myForm" id="myForm" onsubmit="return validateForm();">
					<p class="mb-0" id="error-msg"></p>
					<div id="simple-msg"></div>
						
						<div class="row">
							<div class="col-lg-6 col-md-6">
								<div class="form-group">
									<label class="mb-2" for="name">Name</label>
									<input name="name" id="name" type="text" class="form-control simple" placeholder="Enter your full name">
								</div>
							</div>
							<div class="col-lg-6 col-md-6">
								<div class="form-group">
									<label class="mb-2" for="email">Email</label>
									<input name="email" id="email" type="email" class="form-control simple" placeholder="Enter your email address">
								</div>
							</div>
						</div>
						
						<div class="form-group">
							<label class="mb-2" for="subject">Subject</label>
							<input name="subject" id="subject" type="text" class="form-control simple" placeholder="Enter your subject">
						</div>
						
						<div class="form-group">
							<label class="mb-2" for="Message">Message</label>
							<textarea name="Message" id="Message" class="form-control simple" placeholder="Enter your message"></textarea>
						</div>
						
						<div class="form-group">
							<button class="btn btn-primary px-5 rounded" type="submit" id="submit" name="send">
								Submit Request
							</button>
						</div>
										
				</form>
			</div>
			
			<div class="col-lg-5 col-md-5">
				<div class="contact-info">
					
					<h2>Get In Touch</h2>
					<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. </p>
					
						<!-- get-in-touch code  -->
						<?php
							include "$base_dir/Components/Pages/get-in-touch.php";
						?>
					
				</div>
			</div>
			
		</div>
		<!-- /row -->		
		
	</div>
			
</section>
<!-- ============================ Agency List End ================================== -->

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