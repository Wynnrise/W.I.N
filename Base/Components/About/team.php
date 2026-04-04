<?php
$teams = [
    [
        'img' => '/img/team-1.jpg', 
        'name' => 'harry Preet', 
        'title' => 'Co-Founder', 
    ],
    [
        'img' => '/img/team-2.jpg', 
        'name' => 'Shivangi Preet', 
        'title' => 'Content Writer', 
    ],
    [
        'img' => '/img/team-3.jpg', 
        'name' => 'Yash Preet', 
        'title' => 'Content Writer', 
    ],
    [
        'img' => '/img/team-4.jpg', 
        'name' => 'Dhananjay Preet', 
        'title' => 'CEO & Manager', 
    ],
    [
        'img' => '/img/team-5.jpg', 
        'name' => 'Rahul Gilkrist', 
        'title' => 'App Designer', 
    ],
    [
        'img' => '/img/team-6.jpg', 
        'name' => 'Adam Wilcard', 
        'title' => 'Web Developer', 
    ]
];
?>

<?php foreach ($teams as $item): ?>
<!-- Single Teamm -->
<div class="single-team">
    <div class="team-grid">

        <div class="teamgrid-user">
            <img src="<?php echo $static_url, $item['img']; ?>" alt="" class="img-fluid" />
        </div>
        
        <div class="teamgrid-content">
            <h4><?php echo $item['name']; ?></h4>
            <span><?php echo $item['title']; ?></span>
        </div>
        
        <div class="teamgrid-social">
            <ul>
                <li><a href="#" class="f-cl"><i class="fa-brands fa-facebook"></i></a></li>
                <li><a href="#" class="t-cl"><i class="fa-brands fa-twitter"></i></a></li>
                <li><a href="#" class="i-cl"><i class="fa-brands fa-instagram"></i></a></li>
                <li><a href="#" class="l-cl"><i class="fa-brands fa-linkedin"></i></a></li>
            </ul>
        </div>

    </div>
</div>
<?php endforeach; ?>