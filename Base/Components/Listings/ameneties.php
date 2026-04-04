<?php
$ameneties = [
    [
        'id' => 'g1', 
        'name' => "ADA", 
        'title' => "ADA Compliant", 
        'type' => "checkbox", 
        'checke' => "checked", 
    ],
    [
        'id' => 'g2', 
        'name' => "Parking", 
        'title' => "Parking Options", 
        'type' => "checkbox", 
        'checke' => "", 
    ],
    [
        'id' => 'g3', 
        'name' => "Coffee", 
        'title' => "Coffee Provided", 
        'type' => "checkbox", 
        'checke' => "", 
    ],
    [
        'id' => 'g4', 
        'name' => "Mother", 
        'title' => "Mother's Room", 
        'type' => "checkbox", 
        'checke' => "", 
    ],
    [
        'id' => 'g5', 
        'name' => "Outdoor", 
        'title' => "Outdoor Space", 
        'type' => "checkbox", 
        'checke' => "", 
    ],
    [
        'id' => 'g6', 
        'name' => "Pet", 
        'title' => "Pet Friendly", 
        'type' => "checkbox", 
        'checke' => "", 
    ],
    [
        'id' => 'g7', 
        'name' => "Beauty", 
        'title' => "Beauty & Message", 
        'type' => "checkbox", 
        'checke' => "", 
    ],
    [
        'id' => 'g8', 
        'name' => "Bike", 
        'title' => "Bike Parking", 
        'type' => "checkbox", 
        'checke' => "", 
    ],
    [
        'id' => 'g9', 
        'name' => "Phone", 
        'title' => "Phone Line", 
        'type' => "checkbox", 
        'checke' => "", 
    ],
    [
        'id' => 'g10', 
        'name' => "Private", 
        'title' => "Private Areas", 
        'type' => "checkbox", 
        'checke' => "", 
    ],
    [
        'id' => 'g11', 
        'name' => "Free", 
        'title' => "Free WiFi", 
        'type' => "checkbox", 
        'checke' => "", 
    ],
    [
        'id' => 'g12', 
        'name' => "Swiming", 
        'title' => "Swiming Pool", 
        'type' => "checkbox", 
        'checke' => "", 
    ]
];
?>

<?php foreach ($ameneties as $item): ?>
<li class="form-check">
    <input id="<?php echo $item['id']; ?>" class="form-check-input" name="<?php echo $item['name']; ?>" type="<?php echo $item['type']; ?>" <?php echo $item['checke']; ?>>
    <label for="<?php echo $item['id']; ?>" class="form-check-label"><?php echo $item['title']; ?></label>
</li>
<?php endforeach; ?>