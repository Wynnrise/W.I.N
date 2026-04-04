<?php
$process = [
    [
        'icon' => 'fa-solid fa-unlock-keyhole text-primary', 
        'title' => 'Fully Secure & 24x7 Dedicated Support', 
        'desc' => 'If you are an individual client, or just a business startup looking for good backlinks for your website.', 
    ],
    [
        'icon' => 'fa-brands fa-twitter text-primary', 
        'title' => 'Manage your Social & Busness Account Carefully', 
        'desc' => 'If you are an individual client, or just a business startup looking for good backlinks for your website.', 
    ],
    [
        'icon' => 'fa-solid fa-layer-group text-primary', 
        'title' => 'We are Very Hard Worker and loving', 
        'desc' => 'If you are an individual client, or just a business startup looking for good backlinks for your website.', 
    ]
];
?>

<?php foreach ($process as $item): ?>
<div class="icon-mi-left">
    <i class="<?php echo $item['icon']; ?>"></i>
    <div class="icon-mi-left-content">
        <h4><?php echo $item['title']; ?></h4>
        <p><?php echo $item['desc']; ?></p>
    </div>
</div>
<?php endforeach; ?>