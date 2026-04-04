<?php
$touchs = [
    [
        'icon' => 'fa-solid fa-house', 
        'title' => 'Reach Us', 
        'name' => '1200 W 73rd Ave #100,', 
        'name1' => 'Vancouver, BC V6P 6G5,', 
        'name2' => 'Canada', 
    ],
    [
        'icon' => 'fa-solid fa-envelope-circle-check', 
        'title' => 'Drop A Mail', 
        'name' => 'info@wynston.ca', 
        'name1' => 'sold@tamwynn.ca', 
        'name2' => '', 
    ],
    [
        'icon' => 'fa-solid fa-phone-volume', 
        'title' => 'Call Us', 
        'name' => '(604) 388-1196', 
        'name2' => '', 
    ],
];
?>

<?php foreach ($touchs as $item): ?>
<div class="cn-info-detail mt-4">
    <div class="cn-info-icon">
        <i class="<?php echo $item['icon']; ?>"></i>
    </div>
    <div class="cn-info-content">
        <h4 class="cn-info-title"><?php echo $item['title']; ?></h4>
        <?php echo $item['name']; ?><br><?php echo $item['name1']; ?><br><?php echo $item['name2']; ?>
    </div>
</div>
<?php endforeach; ?>