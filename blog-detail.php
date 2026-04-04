<?php
$base_dir = __DIR__ . '/Base';
$static_url = '/assets';
include "articles-data.php"; 

$article_id = isset($_GET['id']) ? (int)$_GET['id'] : 1;
$article = null;

foreach ($all_articles as $item) {
    if ($item['id'] === $article_id) {
        $article = $item;
        break;
    }
}

if (!$article) { echo "Article not found."; exit; }

// Dynamic URL generation for social sharing
$protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$current_url = urlencode($protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
$article_title = urlencode($article['title']);

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
				<h2 class="ipt-title">Blog Detail</h2>
				<span class="ipn-subtitle"><?php echo $article['title']; ?></span>
			</div>
		</div>
	</div>
</div>

<section class="gray-simple">
	<div class="container">
		<div class="row">
			<div class="col-lg-8 col-md-12 col-sm-12 col-12">
				<div class="blog-details single-post-item format-standard">
					<div class="post-details">
						<div class="post-featured-img">
							<img class="img-fluid" src="<?php echo $article['img']; ?>" alt="">
						</div>
						
						<div class="post-top-meta">
							<ul class="meta-comment-tag">
								<li><i class="fa-solid fa-calendar-day"></i> <?php echo $article['date']; ?></li>
								<li><i class="ti-user"></i> by Vancouver Real Estate Board</li>
							</ul>
						</div>

						<h2 class="post-title"><?php echo $article['title']; ?></h2>
						
						<div class="post-content" style="line-height: 1.8; font-size: 16px; margin-bottom: 30px;">
							<?php echo nl2br($article['full']); ?>
						</div>

						<blockquote style="margin-top:30px;">
							<span class="icon"><i class="fas fa-quote-left"></i></span>
							<p class="text">Market momentum is a slowly evolving force, and these figures represent a market that continues slowly evolving to what may be a new normal.</p>
							<h5 class="name">- Andrew Lis, GVR Chief Economist</h5>
						</blockquote>

						<div class="post-share" style="margin-top: 40px; border-top: 1px solid #eee; padding-top: 20px;">
							<ul class="list" style="display: flex !important; flex-direction: row !important; gap: 20px; list-style: none; padding: 0; margin: 0;">
								<li style="display: inline-block;"><a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo $current_url; ?>" target="_blank" style="font-size: 20px; color: #3b5998;"><i class="fab fa-facebook-f"></i></a></li>
								<li style="display: inline-block;"><a href="https://twitter.com/intent/tweet?url=<?php echo $current_url; ?>&text=<?php echo $article_title; ?>" target="_blank" style="font-size: 20px; color: #1da1f2;"><i class="fab fa-twitter"></i></a></li>
								<li style="display: inline-block;"><a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo $current_url; ?>" target="_blank" style="font-size: 20px; color: #0077b5;"><i class="fab fa-linkedin-in"></i></a></li>
							</ul>
						</div>
					</div>
				</div>
			</div>
			
			<div class="col-lg-4 col-md-12 col-sm-12 col-12">
				<div class="single-widgets widget_search">
					<h4 class="title">Search</h4>
					<form class="sidebar-search-form">
						<input type="search" placeholder="Search..">
						<button type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
					</form>
				</div>

				<div class="single-widgets widget_thumb_post">
					<h4 class="title">Recent News</h4>
					<ul>
						<?php foreach (array_slice($all_articles, 0, 3) as $recent): ?>
						<li>
							<span class="left"><img src="<?php echo $recent['img']; ?>" style="object-fit:cover;"></span>
							<span class="right">
								<a href="blog-detail.php?id=<?php echo $recent['id']; ?>"><?php echo $recent['title']; ?></a>
								<span class="post-date"><?php echo $recent['date']; ?></span>
							</span>
						</li>
						<?php endforeach; ?>
					</ul>
				</div>
				
				<div class="single-widgets widget_tags">
					<h4 class="title">Tags</h4>
					<ul>
						<li><a href="#">Vancouver</a></li>
						<li><a href="#">Market Stats</a></li>
						<li><a href="#">Real Estate</a></li>
					</ul>
				</div>
			</div>
		</div>
	</div>
</section>

<?php
$hero_content = ob_get_clean(); 
include "$base_dir/style/base.php";
?>