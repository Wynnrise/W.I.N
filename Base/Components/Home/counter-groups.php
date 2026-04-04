<?php
$groups = [
    [
        'number' => "100", 
        'price' => "310", 
        'symbol' => "M", 
        'title' => "Owned from",
        'title1' => "properties transaction",
        'symbol1' => "$",
    ],
    [
        'number' => "10", 
        'price' => "40", 
        'symbol' => "K", 
        'title' => "Properties for",
        'title1' => "Buy & Sell",
        'symbol1' => "",
    ],
    [
        'number' => "100", 
        'price' => "500", 
        'symbol' => "", 
        'title' => "Daily Completed",
        'title1' => "transaction",
        'symbol1' => "",
    ],
    [
        'number' => "10", 
        'price' => "114", 
        'symbol' => "M", 
        'title' => "Happy Customers",
        'title1' => "from Resido",
        'symbol1' => "",
    ]
];
?>

<?php foreach ($groups as $item): ?>
<div class="single-counter-wrap">
    <div class="single-counter-title">
        <h2><?php echo $item['symbol1']; ?><span class="ctr counter-value" data-target="<?php echo $item['price']; ?>"><?php echo $item['number']; ?></span><?php echo $item['symbol']; ?></h2>
    </div>
    <div class="single-counter-subtitle">
        <p><?php echo $item['title']; ?><br><?php echo $item['title1']; ?></p>
    </div>
</div>
<?php endforeach; ?>