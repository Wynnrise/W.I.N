<?php
$wheres = [
    [
        'id' => 'b1', 
        'name' => "where", 
        'title' => "Atlanta", 
        'type' => "radio", 
        'checke' => "", 
    ],
    [
        'id' => 'b2', 
        'name' => "where", 
        'title' => "Austin", 
        'type' => "radio", 
        'checke' => "", 
    ],
    [
        'id' => 'b3', 
        'name' => "where", 
        'title' => "Boston", 
        'type' => "radio", 
        'checke' => "", 
    ],
    [
        'id' => 'b4', 
        'name' => "where", 
        'title' => "Chicago", 
        'type' => "radio", 
        'checke' => "checked", 
    ],
    [
        'id' => 'b5', 
        'name' => "where", 
        'title' => "Dallas", 
        'type' => "radio", 
        'checke' => "", 
    ],
    [
        'id' => 'b6', 
        'name' => "where", 
        'title' => "Denver", 
        'type' => "radio", 
        'checke' => "", 
    ],
    [
        'id' => 'b7', 
        'name' => "where", 
        'title' => "Houston", 
        'type' => "radio", 
        'checke' => "", 
    ],
    [
        'id' => 'b8', 
        'name' => "where", 
        'title' => "Jacksonville", 
        'type' => "radio", 
        'checke' => "", 
    ],
    [
        'id' => 'b9', 
        'name' => "where", 
        'title' => "Los Angeles", 
        'type' => "radio", 
        'checke' => "", 
    ]
];
?>

<?php foreach ($wheres as $item): ?>
<li class="form-check">
    <input id="<?php echo $item['id']; ?>" class="form-check-input" name="<?php echo $item['name']; ?>" type="<?php echo $item['type']; ?>" <?php echo $item['checke']; ?>>
    <label for="<?php echo $item['id']; ?>" class="form-check-label"><?php echo $item['title']; ?></label>
</li>
<?php endforeach; ?>