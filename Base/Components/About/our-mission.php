<?php
$missions = [
    [
        'icon' => 'fa-solid fa-basket-shopping text-primary', 
        'title' => 'Contact Sales', 
        'contact' => 'sales@rikadahelp.co.uk', 
        'contact1' => '+01 215 245 6258', 
        'style' => '', 
    ],
    [
        'icon' => 'fa-solid fa-user-tie text-primary', 
        'title' => 'Contact Sales', 
        'contact' => 'sales@rikadahelp.co.uk', 
        'contact1' => '+01 215 245 6258', 
        'style' => '', 
    ],
    [
        'icon' => 'fa-solid fa-comments text-primary', 
        'title' => 'Start Live Chat', 
        'contact' => '+01 215 245 6258', 
        'contact1' => 'Live Chat', 
        'style' => 'live-chat', 
    ]
];
?>

<?php foreach ($missions as $item): ?>
<div class="col-lg-4 col-md-4 col-sm-12">
    <div class="contact-box">
        <i class="<?php echo $item['icon']; ?>"></i>
        <h4><?php echo $item['title']; ?></h4>
        <p><?php echo $item['contact']; ?></p>
        <span class="<?php echo $item['style']; ?>"><?php echo $item['contact1']; ?></span>
    </div>
</div>
<?php endforeach; ?>