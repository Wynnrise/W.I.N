<?php
$bedrooms = [
    [
        'id' => 'a1', 
        'name' => "bed", 
        'title' => "01 Bedrooms", 
        'type' => "radio", 
        'checke' => "", 
    ],
    [
        'id' => 'a2', 
        'name' => "bed", 
        'title' => "02 Bedrooms", 
        'type' => "radio", 
        'checke' => "", 
    ],
    [
        'id' => 'a3', 
        'name' => "bed", 
        'title' => "03 Bedrooms", 
        'type' => "radio", 
        'checke' => "", 
    ],
    [
        'id' => 'a4', 
        'name' => "bed", 
        'title' => "04 Bedrooms", 
        'type' => "radio", 
        'checke' => "checked", 
    ],
    [
        'id' => 'a5', 
        'name' => "bed", 
        'title' => "05 Bedrooms", 
        'type' => "radio", 
        'checke' => "", 
    ],
    [
        'id' => 'a6', 
        'name' => "bed", 
        'title' => "06+ Bedrooms", 
        'type' => "radio", 
        'checke' => "", 
    ]
];
?>

<?php foreach ($bedrooms as $item): ?>
<li class="form-check">
    <input id="<?php echo $item['id']; ?>" class="form-check-input" name="<?php echo $item['name']; ?>" type="<?php echo $item['type']; ?>" <?php echo $item['checke']; ?>>
    <label for="<?php echo $item['id']; ?>" class="form-check-label"><?php echo $item['title']; ?></label>
</li>
<?php endforeach; ?>