<?php
$blogs = [
    [
        'id' => 1,
        'img' => '/img/p-11.jpg', 
        'date' => '30 july 2025', 
        'title' => 'Why people choose listio for own properties', 
        'desc' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore.', 
        'btn' => 'Continue', 
    ],
    [
        'id' => 2,
        'img' => '/img/p-8.jpg', 
        'date' => '10 August 2025', 
        'title' => 'List of benifits and impressive Resido services', 
        'desc' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore.', 
        'btn' => 'Continue', 
    ],
    [
        'id' => 3,
        'img' => '/img/p-10.jpg', 
        'date' => '30 Sep 2025', 
        'title' => 'What people says about listio properties', 
        'desc' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore.', 
        'btn' => 'Continue', 
    ]
];
?>

<?php foreach ($blogs as $item): ?>
<!-- Single blog Grid -->
<div class="col-xl-4 col-lg-4 col-md-6">
    <div class="blog-wrap-grid h-100 shadow">
        
        <div class="blog-thumb">
            <a href="blog-detail.php?title=<?php echo urlencode(str_replace(' ', '-', strtolower($item['title']))); ?>"><img src="<?php echo $static_url, $item['img']; ?>" class="img-fluid" alt="" /></a>
        </div>
        
        <div class="blog-info">
            <span class="post-date label bg-primary text-light"><i class="ti ti-calendar"></i><?php echo $item['date']; ?></span>
        </div>
        
        <div class="blog-body">
            <h4 class="bl-title"><a href="blog-detail.php?title=<?php echo urlencode(str_replace(' ', '-', strtolower($item['title']))); ?>"><?php echo $item['title']; ?></a></h4>
            <p><?php echo $item['desc']; ?> </p>
            <a href="blog-detail.php?title=<?php echo urlencode(str_replace(' ', '-', strtolower($item['title']))); ?>" class="text-primary fw-medium"><?php echo $item['btn']; ?><i class="fa-solid fa-arrow-right ms-2"></i></a>
        </div>
        
    </div>
</div>
<?php endforeach; ?>