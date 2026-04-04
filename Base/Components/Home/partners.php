<?php
$partners = [
    [
        'img' => '/img/partners/booking.png', 
    ],
    [
        'img' => '/img/partners/columbia.png', 
    ],
    [
        'img' => '/img/partners/Payoneer.png', 
    ],
    [
        'img' => '/img/partners/Paypal.png', 
    ],
    [
        'img' => '/img/partners/razorpay.png', 
    ],
    [
        'img' => '/img/partners/microsoft.png', 
    ],
    [
        'img' => '/img/partners/trivago.png', 
    ],
    [
        'img' => '/img/partners/visa.png', 
    ],
    [
        'img' => '/img/partners/columbia.png', 
    ]
];
?>

<?php foreach ($partners as $item): ?>
<div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-6">
    <div class="explor-thumb">
        <img src="<?php echo $static_url, $item['img']; ?>" class="img-fluid" alt="">
    </div>
</div>
<?php endforeach; ?>