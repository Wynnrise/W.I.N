<?php
$posts = [
    [
        'img' => '/img/tour-1.jpg', 
        'title' => 'Alonso Kelina Falao Asiano Pero', 
        'time' => '10 Min ago', 
    ],
    [
        'img' => '/img/tour-2.jpg', 
        'title' => 'It is a long established fact that a reader', 
        'time' => '2 Hours ago', 
    ],
    [
        'img' => '/img/tour-3.jpg', 
        'title' => 'Many desktop publish packages and web', 
        'time' => '4 Hours ago', 
    ],
    [
        'img' => '/img/tour-4.jpg', 
        'title' => 'Various versions have evolved over the years', 
        'time' => '7 Hours ago', 
    ],
    [
        'img' => '/img/tour-5.jpg', 
        'title' => 'Photo booth anim 8-bit PBR 3 wolf moon.', 
        'time' => '3 Days ago', 
    ]
];
?>

<?php foreach ($posts as $item): ?>
<li>
    <span class="left">
        <img src="<?php echo $static_url, $item['img']; ?>" alt="" class="">
    </span>
    <span class="right">
        <a class="feed-title" href="#"><?php echo $item['title']; ?></a> 
        <span class="post-date"><i class="ti ti-calendar"></i><?php echo $item['time']; ?></span>
    </span>
</li>
<?php endforeach; ?>