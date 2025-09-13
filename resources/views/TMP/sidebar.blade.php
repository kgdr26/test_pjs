<aside class="left-sidebar with-vertical">
    <div>
        <!-- Sidebar scroll-->
        <nav class="sidebar-nav scroll-sidebar" data-simplebar>
            <!-----------Profile------------------>
            <div class="user-profile position-relative" style="background: url(https://bootstrapdemos.wrappixel.com/materialpro/dist/assets/images/backgrounds/user-info.jpg) no-repeat;">
                <!-- User profile image -->
                <div class="profile-img">
                    <img src="{{asset('assets/img/profile/kgdr-img-1.png')}}" alt="user" class="w-100 rounded-circle overflow-hidden" />
                </div>
                <!-- User profile text-->
                <div class="profile-text hide-menu pt-1 dropdown">
                    <a href="javascript:void(0)" class="dropdown-toggle u-dropdown w-100 text-white d-block position-relative" id="dropdownMenuLink" data-bs-toggle="dropdown" aria-expanded="false">Muhamad Badrudduja</a>
                    <div class="dropdown-menu animated flipInY" aria-labelledby="dropdownMenuLink">
                        <a class="dropdown-item d-flex gap-2" href="">
                            <i data-feather="user" class="feather-sm text-info "></i>
                            My Profile
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item d-flex gap-2" href="#">
                            <i data-feather="log-out" class="feather-sm text-danger "></i>
                            Logout
                        </a>
                    </div>
                </div>
            </div>
            <!-----------Profile End------------------>

            <ul id="sidebarnav">
                <li class="nav-small-cap">
                    <iconify-icon icon="solar:menu-dots-bold" class="nav-small-cap-icon fs-4"></iconify-icon>
                    <span class="hide-menu">Home</span>
                </li>
                
                <li class="sidebar-item">
                    <a class="sidebar-link" href="{{route('home')}}" id="get-url">
                        <iconify-icon icon="solar:screencast-2-linear" class="aside-icon"></iconify-icon>
                        <span class="hide-menu">Home</span>
                    </a>
                </li>


            </ul>
        </nav>
    </div>
</aside>
