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
<section class="image-cover faq-sec text-center" style="background:url(<?php echo $static_url; ?>/img/bg-2.jpg) no-repeat;" data-overlay="6">
	<div class="container">
		<div class="row">
		
			<div class="col-lg-12 col-md-12 position-relative z-1">
				<h1 class="text-light">Frequently Asked Questions</h1>
				<div class="faq-search">
					<form>
						<input name="search" class="form-control" placeholder="Search Your Query...">
						<button type="submit"> <i class="fa-solid fa-magnifying-glass"></i> </button>
					</form>
				</div>
			</div>
			
		</div>
	</div>
</section>
<!-- ============================ Page Title End ================================== -->

<!-- ================= Our Mission ================= -->
<section class="bg-light">
	<div class="container">
	
		<div class="row">
			
			<!-- our-mission code  -->
			<?php
				include "$base_dir/Components/About/our-mission.php";
			?>
			
		</div>
		
		<div class="row">
			
			<div class="col-lg-10 col-md-12 col-sm-12">
				
				<div class="block-header border-0 m-0">
					<ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
						<li class="nav-item" role="presentation">
						<a class="nav-link active" id="general-tab" data-bs-toggle="pill" href="#general" role="tab" aria-controls="general" aria-selected="true">General</a>
						</li>
						
						<li class="nav-item" role="presentation">
						<a class="nav-link" id="payment-tab" data-bs-toggle="pill" href="#payment" role="tab" aria-controls="payment" aria-selected="false">Payment</a>
						</li>
						
						<li class="nav-item" role="presentation">
						<a class="nav-link" id="upgrade-tab" data-bs-toggle="pill" href="#upgrade" role="tab" aria-controls="upgrade" aria-selected="false">Upgrade</a>
						</li>
						
					</ul>
				</div>
				
				<div class="tab-content" id="pills-tabContent">
					
					<!-- general Query -->
					<div class="tab-pane fade show active" id="general" role="tabpanel" aria-labelledby="general-tab">
						
						<div class="accordion border-0" id="generalac">
							<div class="card mb-2">
								<div class="card-header" id="headingOne">
									<h2 class="mb-0">
									<button class="btn btn-link" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
										How To Install Rikada Theme?
									</button>
									</h2>
								</div>

								<div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-bs-parent="#generalac">
									<div class="card-body">
									<p class="ac-para">Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably haven't heard of them accusamus labore sustainable VHS.</p>
									</div>
								</div>
							</div>
							<div class="card mb-2">
								<div class="card-header" id="headingTwo">
									<h2 class="mb-0">
									<button class="btn btn-link collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
										What is main Requirements For Rikada?
									</button>
									</h2>
								</div>
								<div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-bs-parent="#generalac">
									<div class="card-body">
									<p class="ac-para">Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably haven't heard of them accusamus labore sustainable VHS.</p>
									</div>
								</div>
							</div>
							<div class="card mb-2">
								<div class="card-header" id="headingThree">
									<h2 class="mb-0">
									<button class="btn btn-link collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
										Why Choose Rikada Theme?
									</button>
									</h2>
								</div>
								<div id="collapseThree" class="collapse" aria-labelledby="headingThree" data-bs-parent="#generalac">
									<div class="card-body">
									<p class="ac-para">Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably haven't heard of them accusamus labore sustainable VHS.</p>
									</div>
								</div>
							</div>
						</div>
						
					</div>
					
					<!-- general Query -->
					<div class="tab-pane fade" id="payment" role="tabpanel" aria-labelledby="payment-tab">
						
						<div class="accordion border-0" id="paymentac">
							<div class="card mb-2">
								<div class="card-header" id="headingOne1">
									<h2 class="mb-0">
									<button class="btn btn-link" type="button" data-bs-toggle="collapse" data-bs-target="#paymentcollapse" aria-expanded="true" aria-controls="paymentcollapse">
										May I Request For Refund?
									</button>
									</h2>
								</div>

								<div id="paymentcollapse" class="collapse show" aria-labelledby="headingOne1" data-bs-parent="#paymentac">
									<div class="card-body">
									<p class="ac-para">Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably haven't heard of them accusamus labore sustainable VHS.</p>
									</div>
								</div>
							</div>
							<div class="card mb-2">
								<div class="card-header" id="headingTwo1">
									<h2 class="mb-0">
									<button class="btn btn-link collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#paymentcollapseTwo" aria-expanded="false" aria-controls="paymentcollapseTwo">
										May I Get Any Offer in Future?
									</button>
									</h2>
								</div>
								<div id="paymentcollapseTwo" class="collapse" aria-labelledby="headingTwo1" data-bs-parent="#paymentac">
									<div class="card-body">
									<p class="ac-para">Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably haven't heard of them accusamus labore sustainable VHS.</p>
									</div>
								</div>
							</div>
							<div class="card mb-2">
								<div class="card-header" id="headingThree1">
									<h2 class="mb-0">
									<button class="btn btn-link collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#paymentcollapseThree" aria-expanded="false" aria-controls="paymentcollapseThree">
										How Much Time It will Take For refund?
									</button>
									</h2>
								</div>
								<div id="paymentcollapseThree" class="collapse" aria-labelledby="headingThree1" data-bs-parent="#paymentac">
									<div class="card-body">
									<p class="ac-para">Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably haven't heard of them accusamus labore sustainable VHS.</p>
									</div>
								</div>
							</div>
						</div>
						
					</div>
					
					<!-- general Query -->
					<div class="tab-pane fade" id="upgrade" role="tabpanel" aria-labelledby="upgrade-tab">
						
						<div class="accordion border-0" id="upgradeac">
							<div class="card mb-2">
								<div class="card-header" id="headingOne2">
									<h2 class="mb-0">
									<button class="btn btn-link" type="button" data-bs-toggle="collapse" data-bs-target="#upgradecollapseOne" aria-expanded="true" aria-controls="upgradecollapseOne">
										How To Upgrade Rikada Theme?
									</button>
									</h2>
								</div>

								<div id="upgradecollapseOne" class="collapse show" aria-labelledby="headingOne2" data-bs-parent="#upgradeac">
									<div class="card-body">
									<p class="ac-para">Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably haven't heard of them accusamus labore sustainable VHS.</p>
									</div>
								</div>
							</div>
							<div class="card mb-2">
								<div class="card-header" id="headingTwo2">
									<h2 class="mb-0">
									<button class="btn btn-link collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#upgradecollapseTwo" aria-expanded="false" aria-controls="upgradecollapseTwo">
										Rikada available for WordPress Version?
									</button>
									</h2>
								</div>
								<div id="upgradecollapseTwo" class="collapse" aria-labelledby="headingTwo2" data-bs-parent="#upgradeac">
									<div class="card-body">
									<p class="ac-para">Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably haven't heard of them accusamus labore sustainable VHS.</p>
									</div>
								</div>
							</div>
							<div class="card mb-2">
								<div class="card-header" id="headingThree2">
									<h2 class="mb-0">
									<button class="btn btn-link collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#upgradecollapseThree" aria-expanded="false" aria-controls="upgradecollapseThree">
										Why We need to upgrade Rikada?
									</button>
									</h2>
								</div>
								<div id="upgradecollapseThree" class="collapse" aria-labelledby="headingThree2" data-bs-parent="#upgradeac">
									<div class="card-body">
									<p class="ac-para">Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably haven't heard of them accusamus labore sustainable VHS.</p>
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
<!-- ================= Our Mission ================= -->

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