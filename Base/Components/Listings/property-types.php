<?php
$types = [
    [
        'id' => 'c1', 
        'name' => "ptype", 
        'title' => "House", 
        'type' => "radio", 
        'checke' => "", 
    ],
    [
        'id' => 'c2', 
        'name' => "ptype", 
        'title' => "Office Desk", 
        'type' => "radio", 
        'checke' => "", 
    ],
    [
        'id' => 'c3', 
        'name' => "ptype", 
        'title' => "Villa", 
        'type' => "radio", 
        'checke' => "", 
    ],
    [
        'id' => 'c4', 
        'name' => "ptype", 
        'title' => "Apartment", 
        'type' => "radio", 
        'checke' => "checked", 
    ],
    [
        'id' => 'c5', 
        'name' => "ptype", 
        'title' => "Condo", 
        'type' => "radio", 
        'checke' => "", 
    ],
    [
        'id' => 'c6', 
        'name' => "ptype", 
        'title' => "Denver", 
        'type' => "radio", 
        'checke' => "", 
    ],
    [
        'id' => 'c7', 
        'name' => "ptype", 
        'title' => "Studio", 
        'type' => "radio", 
        'checke' => "", 
    ]
];
?>

<?php foreach ($types as $item): ?>
<li class="form-check">
    <input id="<?php echo $item['id']; ?>" class="form-check-input" name="<?php echo $item['name']; ?>" type="<?php echo $item['type']; ?>" <?php echo $item['checke']; ?>>
    <label for="<?php echo $item['id']; ?>" class="form-check-label"><?php echo $item['title']; ?></label>
</li>
<?php endforeach; ?>