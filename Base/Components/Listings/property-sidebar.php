<!-- property Sidebar -->
<div class="col-lg-4 col-md-12 col-sm-12">
    <div class="simple-sidebar sm-sidebar" id="filter_search"  style="left:0;">							
    
        <div class="search-sidebar_header">
            <h4 class="ssh_heading">Close Filter</h4>
            <button onclick="closeFilterSearch()" class="w3-bar-item w3-button w3-large"><i class="fa-regular fa-circle-xmark fs-5 text-muted-2"></i></button>
        </div>
        
        <!-- Find New Property -->
        <div class="sidebar-widgets">
            
            <div class="search-inner p-0">
                
                <div class="filter-search-box">
                    <div class="form-group">
                        <div class="position-relative">
                            <input type="text" class="form-control rounded-3 ps-5" placeholder="Search by space name…">
                            <div class="position-absolute top-50 start-0 translate-middle-y ms-2">
                                <span class="svg-icon text-primary svg-icon-2hx">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <rect opacity="0.5" x="17.0365" y="15.1223" width="8.15546" height="2" rx="1" transform="rotate(45 17.0365 15.1223)" fill="currentColor"/>
                                        <path d="M11 19C6.55556 19 3 15.4444 3 11C3 6.55556 6.55556 3 11 3C15.4444 3 19 6.55556 19 11C19 15.4444 15.4444 19 11 19ZM11 5C7.53333 5 5 7.53333 5 11C5 14.4667 7.53333 17 11 17C14.4667 17 17 14.4667 17 11C17 7.53333 14.4667 5 11 5Z" fill="currentColor"/>
                                    </svg>
                                </span>	
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="position-relative d-flex flex-xl-row flex-column align-items-center">
                    <div class="verifyd-prt-block flex-fill full-width my-1 me-1">
                        <div class="d-flex align-items-center justify-content-center justify-content-between border rounded-3 px-2 py-3">
                            <div class="eliok-cliops d-flex align-items-center">
                                <span class="svg-icon text-success svg-icon-2hx">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path opacity="0.3" d="M20.5543 4.37824L12.1798 2.02473C12.0626 1.99176 11.9376 1.99176 11.8203 2.02473L3.44572 4.37824C3.18118 4.45258 3 4.6807 3 4.93945V13.569C3 14.6914 3.48509 15.8404 4.4417 16.984C5.17231 17.8575 6.18314 18.7345 7.446 19.5909C9.56752 21.0295 11.6566 21.912 11.7445 21.9488C11.8258 21.9829 11.9129 22 12.0001 22C12.0872 22 12.1744 21.983 12.2557 21.9488C12.3435 21.912 14.4326 21.0295 16.5541 19.5909C17.8169 18.7345 18.8277 17.8575 19.5584 16.984C20.515 15.8404 21 14.6914 21 13.569V4.93945C21 4.6807 20.8189 4.45258 20.5543 4.37824Z" fill="currentColor"/>
                                        <path d="M10.5606 11.3042L9.57283 10.3018C9.28174 10.0065 8.80522 10.0065 8.51412 10.3018C8.22897 10.5912 8.22897 11.0559 8.51412 11.3452L10.4182 13.2773C10.8099 13.6747 11.451 13.6747 11.8427 13.2773L15.4859 9.58051C15.771 9.29117 15.771 8.82648 15.4859 8.53714C15.1948 8.24176 14.7183 8.24176 14.4272 8.53714L11.7002 11.3042C11.3869 11.6221 10.874 11.6221 10.5606 11.3042Z" fill="currentColor"/>
                                    </svg>
                                </span><span class="text-muted-2 fw-medium ms-1">Verified</span>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="flexSwitchCheckChecked" checked>
                                <label class="form-check-label" for="flexSwitchCheckChecked"></label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="super-agt-block flex-fill full-width my-1 ms-1">
                        <div class="d-flex align-items-center justify-content-center justify-content-between border rounded-3 px-2 py-3">
                            <div class="eliok-cliops d-flex align-items-center">
                                <span class="svg-icon text-warning svg-icon-2hx">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24px" height="24px" viewBox="0 0 24 24">
                                        <path d="M10.0813 3.7242C10.8849 2.16438 13.1151 2.16438 13.9187 3.7242V3.7242C14.4016 4.66147 15.4909 5.1127 16.4951 4.79139V4.79139C18.1663 4.25668 19.7433 5.83365 19.2086 7.50485V7.50485C18.8873 8.50905 19.3385 9.59842 20.2758 10.0813V10.0813C21.8356 10.8849 21.8356 13.1151 20.2758 13.9187V13.9187C19.3385 14.4016 18.8873 15.491 19.2086 16.4951V16.4951C19.7433 18.1663 18.1663 19.7433 16.4951 19.2086V19.2086C15.491 18.8873 14.4016 19.3385 13.9187 20.2758V20.2758C13.1151 21.8356 10.8849 21.8356 10.0813 20.2758V20.2758C9.59842 19.3385 8.50905 18.8873 7.50485 19.2086V19.2086C5.83365 19.7433 4.25668 18.1663 4.79139 16.4951V16.4951C5.1127 15.491 4.66147 14.4016 3.7242 13.9187V13.9187C2.16438 13.1151 2.16438 10.8849 3.7242 10.0813V10.0813C4.66147 9.59842 5.1127 8.50905 4.79139 7.50485V7.50485C4.25668 5.83365 5.83365 4.25668 7.50485 4.79139V4.79139C8.50905 5.1127 9.59842 4.66147 10.0813 3.7242V3.7242Z" fill="currentColor"/>
                                        <path d="M14.8563 9.1903C15.0606 8.94984 15.3771 8.9385 15.6175 9.14289C15.858 9.34728 15.8229 9.66433 15.6185 9.9048L11.863 14.6558C11.6554 14.9001 11.2876 14.9258 11.048 14.7128L8.47656 12.4271C8.24068 12.2174 8.21944 11.8563 8.42911 11.6204C8.63877 11.3845 8.99996 11.3633 9.23583 11.5729L11.3706 13.4705L14.8563 9.1903Z" fill="white"/>
                                    </svg>
                                </span><span class="text-muted-2 fw-medium ms-1">SuperAgent</span>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="flexSwitchCheckChecked" checked>
                                <label class="form-check-label" for="flexSwitchCheckChecked"></label>
                            </div>
                        </div>
                        
                    </div>
                </div>
                
                <div class="filter_wraps">
                    
                    <!-- Single Search -->
                    <div class="single_search_boxed">
                        <div class="widget-boxed-header">
                            <h4>
                                <a href="#where" data-bs-toggle="collapse" aria-expanded="false" role="button" class="collapsed">Where<span class="selected">Chicago</span></a>
                            </h4>
                            
                        </div>
                        <div class="widget-boxed-body collapse" id="where" data-parent="#where">
                            <div class="side-list no-border">
                                <!-- Single Filter Card -->
                                <div class="single_filter_card">
                                    <div class="card-body pt-0">
                                        <div class="inner_widget_link">
                                            <ul class="no-ul-list filter-list">
                                                
                                                <!-- where code  -->
                                                <?php
                                                    include "$base_dir/Components/Listings/where.php";
                                                ?>

                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Single Search -->
                    <div class="single_search_boxed">
                        <div class="widget-boxed-header">
                            <h4>
                                <a href="#fptype" data-bs-toggle="collapse" aria-expanded="false" role="button" class="collapsed">Property Types<span class="selected">Apartment</span></a>
                            </h4>
                            
                        </div>
                        <div class="widget-boxed-body collapse" id="fptype" data-parent="#fptype">
                            <div class="side-list no-border">
                                <!-- Single Filter Card -->
                                <div class="single_filter_card">
                                    <div class="card-body pt-0">
                                        <div class="inner_widget_link">
                                            <ul class="no-ul-list filter-list">
                                                
                                                <!-- property-types code  -->
                                                <?php
                                                    include "$base_dir/Components/Listings/property-types.php";
                                                ?>

                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Single Search -->
                    <div class="single_search_boxed">
                        <div class="widget-boxed-header">
                            <h4>
                                <a href="#fbedrooms" data-bs-toggle="collapse" aria-expanded="false" role="button" class="collapsed">Bedrooms<span class="selected">2 Beds</span></a>
                            </h4>
                            
                        </div>
                        <div class="widget-boxed-body collapse" id="fbedrooms" data-parent="#fbedrooms">
                            <div class="side-list no-border">
                                <!-- Single Filter Card -->
                                <div class="single_filter_card">
                                    <div class="card-body pt-0">
                                        <div class="inner_widget_link">
                                            <ul class="no-ul-list filter-list">
                                                
                                                <!-- bedrooms code  -->
                                                <?php
                                                    include "$base_dir/Components/Listings/bedrooms.php";
                                                ?>

                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Single Search -->
                    <div class="single_search_boxed">
                        <div class="widget-boxed-header">
                            <h4>
                                <a href="#fprice" data-bs-toggle="collapse" aria-expanded="false" role="button" class="collapsed">Price Range<span class="selected">$10,000 - $15,000</span></a>
                            </h4>
                            
                        </div>
                        <div class="widget-boxed-body collapse" id="fprice" data-parent="#fprice">
                            <div class="side-list no-border">
                                <!-- Single Filter Card -->
                                <div class="single_filter_card">
                                    <div class="card-body pt-0">
                                        <div class="inner_widget_link">
                                            <ul class="no-ul-list filter-list">
                                                
                                                <!-- price-range code  -->
                                                <?php
                                                    include "$base_dir/Components/Listings/price-range.php";
                                                ?>

                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Single Search -->
                    <div class="single_search_boxed">
                        <div class="widget-boxed-header">
                            <h4>
                                <a href="#mood" data-bs-toggle="collapse" aria-expanded="false" role="button" class="collapsed">Mood<span class="selected">Any Mood</span></a>
                            </h4>
                            
                        </div>
                        <div class="widget-boxed-body collapse" id="mood" data-parent="#mood">
                            <div class="side-list no-border">
                                <!-- Single Filter Card -->
                                <div class="single_filter_card">
                                    <div class="card-body pt-0">
                                        <div class="inner_widget_link">
                                            <ul class="no-ul-list filter-list">
                                                
                                                <!-- mood code  -->
                                                <?php
                                                    include "$base_dir/Components/Listings/mood.php";
                                                ?>

                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Single Search -->
                    <div class="single_search_boxed">
                        <div class="widget-boxed-header">
                            <h4>
                                <a href="#ameneties" data-bs-toggle="collapse" aria-expanded="false" role="button" class="collapsed">Ameneties<span class="selected">ADA Compliant</span></a>
                            </h4>
                            
                        </div>
                        <div class="widget-boxed-body collapse" id="ameneties" data-parent="#ameneties">
                            <div class="side-list no-border">
                                <!-- Single Filter Card -->
                                <div class="single_filter_card">
                                    <div class="card-body pt-0">
                                        <div class="inner_widget_link">
                                            <ul class="no-ul-list filter-list">
                                                
                                                <!-- ameneties code  -->
                                                <?php
                                                    include "$base_dir/Components/Listings/ameneties.php";
                                                ?>

                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                </div>
                
                <div class="form-group filter_button">
                    <button type="submit" class="btn btn btn-primary rounded full-width">22 Results Show</button>
                </div>
            </div>							
        </div>
    </div>
    <!-- Sidebar End -->

</div>