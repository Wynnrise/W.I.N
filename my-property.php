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
									<li><a href="my-profile.php"><i class="fa-solid fa-address-card"></i>My Profile</a></li>
									<li><a href="bookmark-list.php"><i class="fa-solid fa-bookmark"></i>Bookmarked Listings</a></li>
									<li class="active"><a href="my-property.php"><i class="fa-solid fa-building-circle-check"></i>My Properties</a></li>
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
				
					<!-- Bookmark Property -->
					<div class="form-submit mb-4">	
						<h4>My Property</h4>
					</div>
					
					<div class="row">
					
						<!-- my-propertys code  -->
						<?php
							include "$base_dir/Components/Features/my-propertys.php";
						?>
						
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