<?php
$services = [
    [
        'img' => '/img/svg/property.svg', 
        'title' => "See Your Neighbourhood's Real Data", 
        'desc' => "Search our database to see if your upcoming project is already being tracked, or submit your development details directly to us. Whether you are in the DP stage or mid-construction, it’s time to break the silence.", 
        'style' => "middle-icon-features-item", 
        'style1' => "middle-icon-large-features-box f-light-success", 
    ],
    [
        'img' => '/img/svg/agent.svg', 
        'title' => "See Developer Activity Around You", 
        'desc' => "Provide your floorplans, renderings, and project timelines. We transform your public data into a professional research profile, allowing local buyers to discover your vision months before it hits the MLS®.", 
        'style' => "middle-icon-features-item", 
        'style1' => "middle-icon-large-features-box f-light-warning", 
    ],
    [
        'img' => '/img/svg/deal.svg', 
        'title' => "Get Matched to Active Developers", 
        'desc' => "List your project for free to gain basic market awareness, or partner with our Concierge Team for a full marketing package. Get the power of a dedicated project marketing team—renderings, ads, and lead generation—behind your boutique project.", 
        'style' => "middle-icon-features-item remove", 
        'style1' => "middle-icon-large-features-box f-light-purple", 
    ]
];
?>

<?php foreach ($services as $item): ?>
<div class="col-lg-4 col-md-4">
    <div class="<?php echo $item['style']; ?>">
        <div class="icon-features-wrap">
            <div class="<?php echo $item['style1']; ?>">
                <span class="svg-icon text-success svg-icon-2hx">
                    <img src="<?php echo $static_url, $item['img']; ?>" alt="">
                </span>
            </div>
        </div>
        <div class="middle-icon-features-content">
            <h4><?php echo $item['title']; ?></h4>
            <p><?php echo $item['desc']; ?></p>
        </div>
    </div>
</div>
<?php endforeach; ?>