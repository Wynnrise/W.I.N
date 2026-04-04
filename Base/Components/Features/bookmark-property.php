<?php
$propertys = [
    [
        'img' => '/img/p-2.jpg', 
        'title' => 'Serene Uptown', 
        'location' => '6 Bishop Ave. Perkasie, PA', 
        'price' => '$900 / monthly', 
        'btn' => 'Delete', 
    ],
    [
        'img' => '/img/p-3.jpg', 
        'title' => 'Oak Tree Villas', 
        'location' => '71 Lower River Dr. Bronx, NY', 
        'price' => '$535,000', 
        'btn' => 'Delete', 
    ],
    [
        'img' => '/img/p-4.jpg', 
        'title' => 'Selway Villas', 
        'location' => '33 William St. Northbrook, IL', 
        'price' => '$420,000', 
        'btn' => 'Delete', 
    ],
    [
        'img' => '/img/p-5.jpg', 
        'title' => 'Town Manchester', 
        'location' => '7843 Durham Avenue, MD', 
        'price' => '$420,000', 
        'btn' => 'Delete', 
    ]
];
?>

<?php foreach ($propertys as $item): ?>
<!-- Item #1 -->
<tr>
    <td class="property-container">
        <img src="<?php echo $static_url, $item['img']; ?>" alt="">
        <div class="title">
            <h4><a href="#"><?php echo $item['title']; ?></a></h4>
            <span><?php echo $item['location']; ?> </span>
            <span class="table-property-price"><?php echo $item['price']; ?></span>
        </div>
    </td>
    <td class="action">
        <a href="#" class="delete"><i class="fa-solid fa-trash-can"></i> <?php echo $item['btn']; ?></a>
    </td>
</tr>
<?php endforeach; ?>