<?php
// $base_dir = __DIR__ . '/Base';
$static_url = '/assets';

// Define the content for the navlink block
ob_start();
?>

<!DOCTYPE html>
<html lang="zxx">
	<head>
		<meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
		
        <title>Wynston - Real Estate</title>	
        <link rel="icon" href="<?php echo $static_url; ?>/img/favicon.png" type="image/gif" sizes="18x18">
		
        <!-- Custom CSS -->
        <link href="<?php echo $static_url; ?>/css/styles.css" rel="stylesheet">
		
		<!-- Custom Color Option -->
		<link href="<?php echo $static_url; ?>/css/colors.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/@mdi/font/css/materialdesignicons.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://unpkg.com/@icon/themify-icons/themify-icons.css">
		
    </head>
	
    <body class="blue-skin">

        <div id="preloader"><div class="preloader"><span></span><span></span></div></div>

        <div id="main-wrapper">

            <?php
                // Define an associative array for mapping page values to navbar files
                $navbarFiles = [
                    'nav' => 'navbar.php',
                    'nav2' => 'navbar2.php',
                    'nav3' => 'navbar3.php',
                    'nav4' => 'navbar4.php',
                    'nav5' => 'navbar5.php',
                    'nav6' => 'navbar6.php',
                    
                    
                ];

                // Check if the page exists in the array, and include the corresponding file
                if (array_key_exists($page, $navbarFiles)) {
                    include $navbarFiles[$page];
                } else {
                    include 'no-header.php';
                }
            ?>

            <!-- Main Content -->
            <main>
                <?php echo $hero_content ?? '<!-- Default hero content here -->'; ?>
            </main>

            <?php
            // Define an associative array for mapping fpage values to footer files
            $footerFiles = [
                'foot' => 'footer.php',
                'foot1' => 'footer1.php',
            ];

            // Check if $fpage is set and exists in the array, then include the corresponding file
            if (isset($fpage) && array_key_exists($fpage, $footerFiles)) {
                include $footerFiles[$fpage];
            } else {
                // Do nothing if $fpage is not set or not valid
            }
            ?>

            <!-- Back to top -->
            <a id="back2Top" class="top-scroll bg-primary" title="Back to top" href="#"><i class="ti ti-arrow-up"></i></a>
            <!-- Back to top -->

        </div>

        <!-- ============================================================== -->
		<!-- All Jquery -->
		<!-- ============================================================== -->
		<script src="<?php echo $static_url; ?>/js/jquery.min.js"></script>
		<script src="<?php echo $static_url; ?>/js/popper.min.js"></script>
		<script src="<?php echo $static_url; ?>/js/bootstrap.min.js"></script>
		<script src="<?php echo $static_url; ?>/js/rangeslider.js"></script>
		<script src="<?php echo $static_url; ?>/js/select2.min.js"></script>
		<script src="<?php echo $static_url; ?>/js/jquery.magnific-popup.min.js"></script>
		<script src="<?php echo $static_url; ?>/js/slick.js"></script>
		<script src="<?php echo $static_url; ?>/js/slider-bg.js"></script>
		<script src="<?php echo $static_url; ?>/js/lightbox.js"></script> 
		<script src="<?php echo $static_url; ?>/js/imagesloaded.js"></script>
		 
		<script src="<?php echo $static_url; ?>/js/custom.js"></script>

        <script src="<?php echo $static_url; ?>/js/dropzone.js"></script>
        <script src="<?php echo $static_url; ?>/js/contact.js"></script>
		<!-- New Js -->

        <!-- Date Booking Script -->
		<script src="<?php echo $static_url; ?>/js/moment.min.js"></script>
		<script src="<?php echo $static_url; ?>/js/daterangepicker.js"></script>

        <!-- Map -->
		<script src="https://maps.google.com/maps/api/js?key="></script>
		<script src="<?php echo $static_url; ?>/js/map_infobox.js"></script>
		<script src="<?php echo $static_url; ?>/js/markerclusterer.js"></script> 
		<script src="<?php echo $static_url; ?>/js/map.js"></script>

        <script>
			// Check In & Check Out Daterange Script
			$(function() {
			  $('input[name="checkout"]').daterangepicker({
				singleDatePicker: true,
			  });
				$('input[name="checkout"]').val('');
				$('input[name="checkout"]').attr("placeholder","Check Out");
			});
			$(function() {
			  $('input[name="checkin"]').daterangepicker({
				singleDatePicker: true,
				
			  });
				$('input[name="checkin"]').val('');
				$('input[name="checkin"]').attr("placeholder","Check In");
			});
		</script>

        <script>
            try {
                const counter = document.querySelectorAll('.counter-value');
                const speed = 2500; // The lower the slower

                counter.forEach(counter_value => {
                    const updateCount = () => {
                        const target = +counter_value.getAttribute('data-target');
                        const count = +counter_value.innerText;

                        // Lower inc to slow and higher to slow
                        var inc = target / speed;

                        if (inc < 1) {
                            inc = 1;
                        }

                        // Check if target is reached
                        if (count < target) {
                            // Add inc to count and output in counter_value
                            counter_value.innerText = (count + inc).toFixed(0);
                            // Call function every ms
                            setTimeout(updateCount, 1);
                        } else {
                            counter_value.innerText = target;
                        }
                    };

                    updateCount();
                });
            } catch (err) {

            }
        </script>

        <script>
			function openFilterSearch() {
				document.getElementById("filter_search").style.display = "block";
			}
			function closeFilterSearch() {
				document.getElementById("filter_search").style.display = "none";
			}
		</script>

        <script>
			  $(document).ready(function(){
				$("#showbutton").click(function(){
				$("#showing").slideToggle("slow");
			  });
			  });
		</script>

        <script>
            window.onscroll = function () {
                scrollFunction();
            };

            function scrollFunction() {
                var mybutton = document.getElementById("back-to-top");
                if(mybutton!=null){
                    if (document.body.scrollTop > 500 || document.documentElement.scrollTop > 500) {
                        mybutton.classList.add("block");
                        mybutton.classList.remove("hidden");
                    } else {
                        mybutton.classList.add("hidden");
                        mybutton.classList.remove("block");
                    }
                }
            }

            function topFunction() {
                document.body.scrollTop = 0;
                document.documentElement.scrollTop = 0;
            }
        </script>
		

	</body>
</html>