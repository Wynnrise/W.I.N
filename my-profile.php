<?php
$base_dir = __DIR__ . '/Base';
$static_url = '/Resido/assets'; // Ensure this is the correct path

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
				
				<h2 class="ipt-title">Welcome!</h2>
				<span class="ipn-subtitle">Welcome To Your Account</span>
				
			</div>
		</div>
	</div>
</div>
<!-- ============================ Page Title End ================================== -->

<!-- ============================ User Dashboard ================================== -->
<section class="bg-light">
	<div class="container-fluid">
	
		<div class="row">
			<div class="col-lg-12 col-md-12">
				<div class="filter_search_opt">
					<a href="javascript:void(0);" onclick="openFilterSearch()" class="btn btn-dark full-width mb-4">Dashboard Navigation<i class="fa-solid fa-bars ms-2"></i></a>
				</div>
			</div>
		</div>
					
		<div class="row">
			
			<div class="col-lg-3 col-md-12">
				
				<div class="simple-sidebar sm-sidebar" id="filter_search">
					
					<div class="search-sidebar_header">
						<h4 class="ssh_heading">Close Filter</h4>
						<button onclick="closeFilterSearch()" class="w3-bar-item w3-button w3-large"><i class="fa-regular fa-circle-xmark fs-5 text-muted-2"></i></button>
					</div>
					
					<div class="sidebar-widgets">
						<div class="dashboard-navbar">
							
							<div class="d-user-avater">
								<img src="<?php echo $static_url; ?>/img/team-1.jpg" class="img-fluid avater" alt="">
								<h4>Adam Harshvardhan</h4>
								<span>Canada USA</span>
							</div>
							
							<div class="d-navigation">
								<ul>
									<li><a href="dashboard.php"><i class="fa-solid fa-gauge"></i>Dashboard</a></li>
									<li class="active"><a href="my-profile.php"><i class="fa-solid fa-address-card"></i>My Profile</a></li>
									<li><a href="bookmark-list.php"><i class="fa-solid fa-bookmark"></i>Bookmarked Listings</a></li>
									<li><a href="my-property.php"><i class="fa-solid fa-building-circle-check"></i>My Properties</a></li>
									<li><a href="submit-property-dashboard.php"><i class="fa-solid fa-house"></i>Submit New Property</a></li>
									<li><a href="change-password.php"><i class="fa-solid fa-unlock"></i>Change Password</a></li>
									<li><a href="index.php"><i class="fa-solid fa-power-off"></i>Log Out</a></li>
								</ul>
							</div>
							
						</div>
					</div>
					
				</div>
			</div>
			
			<div class="col-lg-9 col-md-12">
				<div class="dashboard-wraper">
				
					<!-- Basic Information -->
					<div class="form-submit">	
						<h4>My Account</h4>
						<div class="submit-section">
							<div class="row">
							
								<div class="form-group col-md-6">
									<label>Your Name</label>
									<input type="text" class="form-control" value="harry Preet">
								</div>
								
								<div class="form-group col-md-6">
									<label>Email</label>
									<input type="email" class="form-control" value="preet77@gmail.com">
								</div>
								
								<div class="form-group col-md-6">
									<label>Your Title</label>
									<input type="text" class="form-control" value="Web Designer">
								</div>
								
								<div class="form-group col-md-6">
									<label>Phone</label>
									<input type="text" class="form-control" value="123 456 5847">
								</div>
								
								<div class="form-group col-md-6">
									<label>Address</label>
									<input type="text" class="form-control" value="522, Arizona, Canada">
								</div>
								
								<div class="form-group col-md-6">
									<label>City</label>
									<input type="text" class="form-control" value="Montquebe">
								</div>
								
								<div class="form-group col-md-6">
									<label>State</label>
									<input type="text" class="form-control" value="Canada">
								</div>
								
								<div class="form-group col-md-6">
									<label>Zip</label>
									<input type="text" class="form-control" value="160052">
								</div>
								
								<div class="form-group col-md-12">
									<label>About</label>
									<textarea class="form-control">Maecenas quis consequat libero, a feugiat eros. Nunc ut lacinia tortor morbi ultricies laoreet ullamcorper phasellus semper</textarea>
								</div>
								
							</div>
						</div>
					</div>
					
					<div class="form-submit">	
						<h4>Social Accounts</h4>
						<div class="submit-section">
							<div class="row">
							
								<div class="form-group col-md-6">
									<label>Facebook</label>
									<input type="text" class="form-control" value="https://facebook.com/">
								</div>
								
								<div class="form-group col-md-6">
									<label>Twitter</label>
									<input type="email" class="form-control" value="https://twitter.com/">
								</div>
								
								<div class="form-group col-md-6">
									<label>Google Plus</label>
									<input type="text" class="form-control" value="https://googleplus.com">
								</div>
								
								<div class="form-group col-md-6">
									<label>LinkedIn</label>
									<input type="text" class="form-control" value="https://linkedin.com/">
								</div>
								
								<div class="form-group col-lg-12 col-md-12">
									<button class="btn btn-primary px-5 rounded" type="submit">Save Changes</button>
								</div>
								
							</div>
						</div>
					</div>
					
				</div>
			</div>
			
		</div>
	</div>
</section>
<!-- ============================ User Dashboard End ================================== -->

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