<?php
$achievements = [
    [
        'number' => "100", 
        'price' => "615", 
        'symbol' => "K", 
        'title' => "Completed Property",
    ],
    [
        'number' => "100", 
        'price' => "210", 
        'symbol' => "K", 
        'title' => "Property Sales",
    ],
    [
        'number' => "100", 
        'price' => "916", 
        'symbol' => "+", 
        'title' => "Apartment Rent",
    ],
    [
        'number' => "50", 
        'price' => "150", 
        'symbol' => "K", 
        'title' => "Happy Clients",
    ]
];
?>

<?php foreach ($achievements as $item): ?>
<div class="col-lg-3 col-md-3 col-sm-6 col-6">
    <div class="achievement-wrap">
        <div class="achievement-content">
            <h2 class="fs-1"><span class="ctr counter-value" data-target="<?php echo $item['price']; ?>"><?php echo $item['number']; ?></span><?php echo $item['symbol']; ?></h2>
            <p><?php echo $item['title']; ?></p>
        </div>
    </div>
</div>
<?php endforeach; ?>