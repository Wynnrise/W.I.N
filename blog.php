<?php
$base_dir = __DIR__ . '/Base';
$static_url = '/assets'; 
include "articles-data.php"; 
require_once "$base_dir/db.php";

ob_start();
include "$base_dir/navbar.php"; 
$navlink_content = ob_get_clean(); 
$page= 'nav'; $fpage= 'foot';
ob_start();
?>
			
<div class="page-title">
	<div class="container">
		<div class="row">
			<div class="col-lg-12 col-md-12">
				<h2 class="ipt-title">Our Articles</h2>
				<span class="ipn-subtitle">See Our Latest Articles & News</span>
			</div>
		</div>
	</div>
</div>

<section class="gray-simple">
	<div class="container">
		<div class="row">
			<div class="col text-center">
				<div class="sec-heading center">
					<h2>Latest News</h2>
					<p>We post regularly the most powerful articles for help and support.</p>
				</div>
			</div>
		</div>
	
		<div class="row justify-content-center g-4">
			<?php foreach ($all_articles as $item): ?>
			<div class="col-lg-4 col-md-6">
				<div class="blog-wrap-grid">
					<div class="blog-thumb">
						<a href="blog-detail.php?id=<?php echo $item['id']; ?>">
							<img src="<?php echo $item['img']; ?>" class="img-fluid" alt="" style="height:250px; width:100%; object-fit:cover;">
						</a>
					</div>
					<div class="blog-info">
						<span class="post-date"><i class="ti-calendar"></i><?php echo $item['date']; ?></span>
					</div>
					<div class="blog-body">
						<h4 class="bl-title"><a href="blog-detail.php?id=<?php echo $item['id']; ?>"><?php echo $item['title']; ?></a></h4>
						<p><?php echo $item['short']; ?></p>
						<a href="blog-detail.php?id=<?php echo $item['id']; ?>" class="bl-continue">Continue</a>
					</div>
				</div>
			</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
<section class="bg-primary call-to-act-wrap">
	<div class="container">
		
		<!-- estate-agent code  -->
		<?php
			include "$base_dir/Components/Home/estate-agent.php";
		?>

	</div>
</section>

<?php
$hero_content = ob_get_clean(); 
include "$base_dir/style/base.php";
?>