<?php
$reviews = [
    [
        'name' => "Harman Preet", 
        'title' => "Google Manager", 
        'desc' => "Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.", 
    ],
    [
        'name' => "Bharti Patel", 
        'title' => "BootstrapWire Manager", 
        'desc' => "Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.", 
    ],
    [
        'name' => "Dhananjay Verma", 
        'title' => "Themezhub Manager", 
        'desc' => "Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.", 
    ],
    [
        'name' => "Shreekant Tripathi", 
        'title' => "Megroo Developer", 
        'desc' => "Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.", 
    ],
    [
        'name' => "Rajnish Shukla", 
        'title' => "Airbnb Leader", 
        'desc' => "Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.", 
    ]
];
?>

<?php foreach ($reviews as $item): ?>
<!-- Single Item -->
<div class="single-item-reviews">
    <div class="single-item-reviews-desc">
        <p><?php echo $item['desc']; ?></p>
    </div>
    <div class="single-item-reviews-info">
        <h4><?php echo $item['name']; ?></h4>
        <p class="text-primary"><?php echo $item['title']; ?></p>
    </div>
</div>
<?php endforeach; ?>