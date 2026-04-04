<?php
$packages = [
    [
        'icon' => 'fa-solid fa-location-dot', 
        'number' => '607', 
        'title' => 'Listings Included', 
        'style' => 'dashboard-stat widget-1', 
    ],
    [
        'icon' => 'ti ti-pie-chart', 
        'number' => '102', 
        'title' => 'Listings Remaining', 
        'style' => 'dashboard-stat widget-2', 
    ],
    [
        'icon' => 'ti ti-user', 
        'number' => '70', 
        'title' => 'Featured Included', 
        'style' => 'dashboard-stat widget-3', 
    ],
    [
        'icon' => 'fa-solid fa-location-dot', 
        'number' => '30', 
        'title' => 'Featured Remaining', 
        'style' => 'dashboard-stat widget-4', 
    ],
    [
        'icon' => 'ti ti-pie-chart', 
        'number' => 'Unlimited', 
        'title' => 'Images / per listing', 
        'style' => 'dashboard-stat widget-5', 
    ],
    [
        'icon' => 'ti ti-user', 
        'number' => '2025-02-26', 
        'title' => 'Ends On', 
        'style' => 'dashboard-stat widget-6', 
    ]
];
?>

<?php foreach ($packages as $item): ?>
<div class="col-lg-4 col-md-6 col-sm-12">
    <div class="<?php echo $item['style']; ?>">
        <div class="dashboard-stat-content"><h4><?php echo $item['number']; ?></h4> <span><?php echo $item['title']; ?></span></div>
        <div class="dashboard-stat-icon"><i class="<?php echo $item['icon']; ?>"></i></div>
    </div>	
</div>
<?php endforeach; ?>