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
                  <li class="active">
                     <a href="{{ route('dashboard') }}">
                        <span class="shape1"></span>
                        <span class="shape2"></span>
                        <i class="ti ti-layout-grid fs-16 me-2"></i>
                        <span>Dashboard</span>
                     </a>
                  </li>
               </ul>
            </li>
         </ul>
      </div>
   </div>
</div>
<!-- /Sidebar -->