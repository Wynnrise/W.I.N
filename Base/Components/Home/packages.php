<?php
$packages = [
    [
        'price' => "49", 
        'title' => "Basic Package", 
        'btn' => "Choose Plan", 
        'style' => "pricing-header bg-dark", 
        'style1' => "pr-title text-info", 
        'style2' => "btn btn-light-primary rounded full-width", 
    ],
    [
        'price' => "99", 
        'title' => "PLATINUM PACKAGE", 
        'btn' => "Choose Plan", 
        'style' => "pricing-header bg-primary", 
        'style1' => "pr-title text-light", 
        'style2' => "btn btn-dark rounded full-width", 
    ],
    [
        'price' => "199", 
        'title' => "STANDARD PACKAGE", 
        'btn' => "Choose Plan", 
        'style' => "pricing-header bg-dark", 
        'style1' => "pr-title text-info", 
        'style2' => "btn btn-light-primary rounded full-width", 
    ]
];
?>

<?php foreach ($packages as $item): ?>
<!-- Single Package -->
<div class="col-xl-4 col-lg-4 col-md-4">
    <div class="pricing-wrap py-3 px-3">
        
        <div class="<?php echo $item['style']; ?>">
            <h4 class="pr-value text-light"><sup class="text-light opacity-75">$</sup><?php echo $item['price']; ?></h4>
            <h4 class="<?php echo $item['style1']; ?>"><?php echo $item['title']; ?></h4>
        </div>
        <div class="pricing-body px-2">
            <ul class="p-0">
                <li><span class="text-success me-2"><i class="fa-solid fa-circle-check"></i></span>5+ Listings</li>
                <li><span class="text-success me-2"><i class="fa-solid fa-circle-check"></i></span>Contact With Agent</li>
                <li><span class="text-success me-2"><i class="fa-solid fa-circle-check"></i></span>3 Month Validity</li>
                <li><span class="text-success me-2"><i class="fa-solid fa-circle-check"></i></span>7x24 Fully Support</li>
                <li><span class="text-success me-2"><i class="fa-solid fa-circle-check"></i></span>50GB Space</li>
            </ul>
        </div>
        <div class="pricing-bottom mt-5 mb-1 px-2">
            <a href="#" class="<?php echo $item['style2']; ?>"><?php echo $item['btn']; ?></a>
        </div>
        
    </div>
</div>
<?php endforeach; ?>