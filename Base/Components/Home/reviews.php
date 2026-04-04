<?php
$reviews = [
    [
        'img' => '/img/user-3.jpg', 
        'name' => "Adam Williams", 
        'title' => "CEO Of Microwoft", 
        'desc' => "Cicero famously orated against his political opponent Lucius Sergius Catilina. Occasionally the first Oration against Catiline is taken specimens.", 
        'style' => "quotes bg-primary", 
    ],
    [
        'img' => '/img/user-8.jpg', 
        'name' => "Retha Deowalim", 
        'title' => "CEO Of Apple", 
        'desc' => "Cicero famously orated against his political opponent Lucius Sergius Catilina. Occasionally the first Oration against Catiline is taken specimens.", 
        'style' => "quotes bg-success", 
    ],
    [
        'img' => '/img/user-4.jpg', 
        'name' => "Sam J. Wasim", 
        'title' => "Pio Founder", 
        'desc' => "Cicero famously orated against his political opponent Lucius Sergius Catilina. Occasionally the first Oration against Catiline is taken specimens.", 
        'style' => "quotes bg-purple", 
    ],
    [
        'img' => '/img/user-5.jpg', 
        'name' => "Usan Gulwarm", 
        'title' => "CEO Of Facewarm", 
        'desc' => "Cicero famously orated against his political opponent Lucius Sergius Catilina. Occasionally the first Oration against Catiline is taken specimens.", 
        'style' => "quotes bg-seegreen", 
    ],
    [
        'img' => '/img/user-6.jpg', 
        'name' => "Shilpa Shethy", 
        'title' => "CEO Of Zapple", 
        'desc' => "Cicero famously orated against his political opponent Lucius Sergius Catilina. Occasionally the first Oration against Catiline is taken specimens.", 
        'style' => "quotes bg-danger", 
    ],
];
?>

<?php foreach ($reviews as $item): ?>
<!-- Single Item -->
<div class="item">
    <div class="item-box">
        <div class="smart-tes-author">
            <div class="st-author-box">
                <div class="st-author-thumb">
                    <div class="<?php echo $item['style']; ?>"><i class="fa-solid fa-quote-left"></i></div>
                    <img src="<?php echo $static_url, $item['img']; ?>" class="img-fluid" alt="" />
                </div>
            </div>
        </div>
        
        <div class="smart-tes-content">
            <p><?php echo $item['desc']; ?></p>
        </div>
        
        <div class="st-author-info">
            <h4 class="st-author-title"><?php echo $item['name']; ?></h4>
            <span class="st-author-subtitle"><?php echo $item['title']; ?></span>
        </div>
    </div>
</div>
<?php endforeach; ?>