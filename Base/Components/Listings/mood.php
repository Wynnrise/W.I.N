<?php
$moods = [
    [
        'id' => 'f1', 
        'name' => "moods", 
        'title' => "Any Mood", 
        'type' => "radio", 
        'checke' => "", 
    ],
    [
        'id' => 'f2', 
        'name' => "moods", 
        'title' => "Professional", 
        'type' => "radio", 
        'checke' => "", 
    ],
    [
        'id' => 'f3', 
        'name' => "moods", 
        'title' => "Essentials", 
        'type' => "radio", 
        'checke' => "", 
    ],
    [
        'id' => 'f4', 
        'name' => "moods", 
        'title' => "Unique", 
        'type' => "radio", 
        'checke' => "checked", 
    ],
    [
        'id' => 'f5', 
        'name' => "moods", 
        'title' => "Lively", 
        'type' => "radio", 
        'checke' => "", 
    ],
    [
        'id' => 'f6', 
        'name' => "moods", 
        'title' => "Luxe", 
        'type' => "radio", 
        'checke' => "", 
    ],
    [
        'id' => 'f7', 
        'name' => "moods", 
        'title' => "Luxe", 
        'type' => "radio", 
        'checke' => "", 
    ]
];
?>

<?php foreach ($moods as $item): ?>
<li class="form-check">
    <input id="<?php echo $item['id']; ?>" class="form-check-input" name="<?php echo $item['name']; ?>" type="<?php echo $item['type']; ?>" <?php echo $item['checke']; ?>>
    <label for="<?php echo $item['id']; ?>" class="form-check-label"><?php echo $item['title']; ?></label>
</li>
<?php endforeach; ?>