<?php
$agents = [
    [
        'id' => 1,
        'img' => '/img/ag-7.png', 
        'title' => 'Green villa', 
        'title1' => '117 Properties', 
        'tag' => '5 Agents', 
        'btn' => 'View Agency', 
    ],
    [
        'id' => 2,
        'img' => '/img/ag-7.png', 
        'title' => 'Modern house', 
        'title1' => '76 Properties', 
        'tag' => '4 Agents', 
        'btn' => 'View Agency', 
    ],
    [
        'id' => 3,
        'img' => '/img/ag-5.png', 
        'title' => 'Royal Estate', 
        'title1' => '53 Properties', 
        'tag' => '3 Agents', 
        'btn' => 'View Agency', 
    ],
    [
        'id' => 4,
        'img' => '/img/ag-4.png', 
        'title' => 'Royal Green', 
        'title1' => '14 Properties', 
        'tag' => '4 Agents', 
        'btn' => 'View Agency', 
    ],
    [
        'id' => 5,
        'img' => '/img/ag-1.png', 
        'title' => 'Muskaan Estate', 
        'title1' => '63 Properties', 
        'tag' => '10 Agents', 
        'btn' => 'View Agency', 
    ],
    [
        'id' => 6,
        'img' => '/img/ag-2.png', 
        'title' => 'Red House', 
        'title1' => '18 Properties', 
        'tag' => '9 Agents', 
        'btn' => 'View Agency', 
    ],
    [
        'id' => 7,
        'img' => '/img/ag-3.png', 
        'title' => 'Green villa1', 
        'title1' => '69 Properties', 
        'tag' => '8 Agents', 
        'btn' => 'View Agency', 
    ],
    [
        'id' => 8,
        'img' => '/img/ag-4.png', 
        'title' => 'Estate City', 
        'title1' => '42 Properties', 
        'tag' => '7 Agents', 
        'btn' => 'View Agency', 
    ],
    [
        'id' => 9,
        'img' => '/img/ag-5.png', 
        'title' => 'House Design', 
        'title1' => '31 Properties', 
        'tag' => '3 Agents', 
        'btn' => 'View Agency', 
    ],
    [
        'id' => 10,
        'img' => '/img/ag-6.png', 
        'title' => 'Jumil Estate', 
        'title1' => '25 Properties', 
        'tag' => '2 Agents', 
        'btn' => 'View Agency', 
    ],
    [
        'id' => 11,
        'img' => '/img/ag-7.png', 
        'title' => 'House Stock', 
        'title1' => '18 Properties', 
        'tag' => '5 Agents', 
        'btn' => 'View Agency', 
    ],
    [
        'id' => 12,
        'img' => '/img/ag-8.png', 
        'title' => 'Ran Estate', 
        'title1' => '63 Properties', 
        'tag' => '6 Agents', 
        'btn' => 'View Agency', 
    ]
];
?>

<?php foreach ($agents as $item): ?>
<!-- Single Agent -->
<div class="col-xl-3 col-lg-4 col-md-6 col-sm-12">
    <div class="agents-grid card rounded-3 shadow">
        
        <div class="agents-grid-wrap">
            <div class="fr-grid-thumb mx-auto text-center mt-5 mb-3">
                <a href="agency-page.php?title=<?php echo urlencode(str_replace(' ', '-', strtolower($item['title']))); ?>" class="d-inline-flex p-1 rounded border">
                    <img src="<?php echo $static_url, $item['img']; ?>" class="img-fluid rounded" width="130" alt="" />
                </a>
            </div>
            <div class="fr-grid-deatil text-center">
                <div class="fr-grid-deatil-flex">
                    <div class="mb-1"><span class="label text-light bg-success"><?php echo $item['tag']; ?></span></div>
                    <h5 class="fr-can-name mb-1 mt-2"><a href="agency-page.php?title=<?php echo urlencode(str_replace(' ', '-', strtolower($item['title']))); ?>"><?php echo $item['title']; ?></a></h5>
                    <span class="agent-property text-muted-2"><?php echo $item['title1']; ?></span>
                </div>
            </div>
        </div>
        
        <div class="fr-grid-info d-flex align-items-center justify-content-between px-4 py-4">
            <a href="agency-page.php?title=<?php echo urlencode(str_replace(' ', '-', strtolower($item['title']))); ?>" class="btn btn-light-primary full-width"><?php echo $item['btn']; ?><i class="fa-solid fa-arrow-right-long ms-2"></i></a>
        </div>
        
    </div>
</div>
<?php endforeach; ?>