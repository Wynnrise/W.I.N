<?php
$partners = [
    [
        'img' => '/img/partners/booking-light.png', 
    ],
    [
        'img' => '/img/partners/columbia-light.png', 
    ],
    [
        'img' => '/img/partners/Payoneer-light.png', 
    ],
    [
        'img' => '/img/partners/Paypal-light.png', 
    ],
    [
        'img' => '/img/partners/razorpay-light.png', 
    ],
    [
        'img' => '/img/partners/microsoft-light.png', 
    ],
    [
        'img' => '/img/partners/trivago-light.png', 
    ],
    [
        'img' => '/img/partners/visa-light.png', 
    ],
    [
        'img' => '/img/partners/columbia-light.png', 
    ]
];
?>

<?php foreach ($partners as $item): ?>
<div class="col-xl-2 col-lg-3 col-md-4 col-sm-4 col-4">
    <div class="explor-thumb">
        <img src="<?php echo $static_url, $item['img']; ?>" class="img-fluid" alt="">
    </div>
</div>
<?php endforeach; ?>