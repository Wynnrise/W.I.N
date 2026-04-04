<?php
$ranges = [
    [
        'id' => 'e1', 
        'name' => "prices", 
        'title' => "Less Then $10,000", 
        'type' => "radio", 
        'checke' => "", 
    ],
    [
        'id' => 'e2', 
        'name' => "prices", 
        'title' => "$10,000 - $15,000", 
        'type' => "radio", 
        'checke' => "", 
    ],
    [
        'id' => 'e3', 
        'name' => "prices", 
        'title' => "$12,000 - $25,000", 
        'type' => "radio", 
        'checke' => "", 
    ],
    [
        'id' => 'e4', 
        'name' => "prices", 
        'title' => "$30,000 - $35,000", 
        'type' => "radio", 
        'checke' => "checked", 
    ],
    [
        'id' => 'e5', 
        'name' => "prices", 
        'title' => "$40,000 - $45,000", 
        'type' => "radio", 
        'checke' => "", 
    ],
    [
        'id' => 'e6', 
        'name' => "prices", 
        'title' => "$50,000 - $55,000", 
        'type' => "radio", 
        'checke' => "", 
    ],
    [
        'id' => 'e7', 
        'name' => "prices", 
        'title' => "$60,000 - $65,000", 
        'type' => "radio", 
        'checke' => "", 
    ]
];
?>

<?php foreach ($ranges as $item): ?>
<li class="form-check">
    <input id="<?php echo $item['id']; ?>" class="form-check-input" name="<?php echo $item['name']; ?>" type="<?php echo $item['type']; ?>" <?php echo $item['checke']; ?>>
    <label for="<?php echo $item['id']; ?>" class="form-check-label"><?php echo $item['title']; ?></label>
</li>
<?php endforeach; ?>