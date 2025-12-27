<!-- Sidebar -->
<div class="sidebar" id="sidebar">
   <!-- Logo -->
   <div class="sidebar-logo active">
      <a href="{{ route('dashboard') }}" class="logo logo-normal">
         <img src="{{asset('backend/assets/back-img/logo.png')}}" alt="Img">
      </a>
      <a href="{{ route('dashboard') }}" class="logo logo-white">
         <img src="{{asset('backend/assets/back-img/logo.png')}}" alt="Img">
      </a>
      <a href="{{ route('dashboard')}}" class="logo-small">
         <img src="{{asset('backend/assets/back-img/fav.png')}}" alt="Img">
      </a>
   </div>
   <div class="sidebar-inner slimscroll">
      <div id="sidebar-menu" class="sidebar-menu">
         <ul>
            <li class="submenu-open">
               <ul>
                  <li class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                     <a href="{{ route('dashboard') }}" class="firsta">
                        @if(request()->routeIs('dashboard'))
                              <span class="shape1"></span>
                              <span class="shape2"></span>
                        @endif
                        <i class="ti ti-layout-grid fs-16 me-2"></i>
                        <span>Dashboard</span>
                     </a>
                  </li>
                  <li class="submenu {{ request()->routeIs('manage-banner.*') ? 'active' : '' }}">
                     <a href="javascript:void(0);" class="firsta">
                        @if(request()->routeIs('manage-banner.*'))
                              <span class="shape1"></span>
                              <span class="shape2"></span>
                        @endif
                        <i class="ti ti-brand-appgallery fs-16 me-2"></i>
                        <span>Manage Banner</span>
                        <span class="menu-arrow"></span>
                     </a>
                     <ul style="{{ request()->routeIs('manage-banner.*') ? 'display:block;' : '' }}">
                        <li><a href="{{ route('manage-banner.index') }}">Banner</a></li>
                     </ul>
                  </li>
                  <li class="submenu {{ request()->routeIs('manage-album.*', 'manage-gallery.*') ? 'active' : '' }}">
                     <a href="javascript:void(0);" class="firsta">
                        @if(request()->routeIs('manage-album.*', 'manage-gallery.*'))
                           <span class="shape1"></span>
                           <span class="shape2"></span>
                        @endif
                        <i class="ti ti-brand-appgallery fs-16 me-2"></i>
                        <span>Manage Gallery</span>
                        <span class="menu-arrow"></span>
                     </a>
                     <ul style="{{ request()->routeIs('manage-album.*', 'manage-gallery.*') ? 'display:block;' : '' }}">
                        <li><a href="{{ route('manage-album.index') }}">Album</a></li>
                        <li><a href="{{ route('manage-gallery.index') }}">Gallery</a></li>
                     </ul>
                  </li>      
                  <li class="submenu {{ request()->routeIs('manage-near-by-place.*') ? 'active' : '' }}">
                     <a href="javascript:void(0);" class="firsta">
                        @if(request()->routeIs('manage-location.*')) 
                           <span class="shape1"></span>
                           <span class="shape2"></span>
                        @endif
                        <i class="ti ti-brand-appgallery fs-16 me-2"></i>
                        <span>Manage Near By Place</span>
                        <span class="menu-arrow"></span>
                     </a>
                     <ul style="{{ request()->routeIs('manage-near-by-place.*') ? 'display:block;' : '' }}">
                        <li><a href="{{ route('manage-near-by-place.index') }}">Near by Place</a></li>
                     </ul>
                  </li>  
                  
                  <li class="submenu {{ request()->routeIs('manage-flyers.*') ? 'active' : '' }}">
                     <a href="javascript:void(0);" class="firsta">
                        @if(request()->routeIs('manage-flyers.*')) 
                           <span class="shape1"></span>
                           <span class="shape2"></span>
                        @endif
                        <i class="ti ti-brand-appgallery fs-16 me-2"></i>
                        <span>Manage Flyers</span>
                        <span class="menu-arrow"></span>
                     </a>
                     <ul style="{{ request()->routeIs('manage-flyers.*') ? 'display:block;' : '' }}">
                        <li><a href="{{ route('manage-flyers.index') }}">Flyers</a></li>
                     </ul>
                  </li>  
               </ul>
            </li>
         </ul>
      </div>
   </div>
</div>
<!-- /Sidebar -->