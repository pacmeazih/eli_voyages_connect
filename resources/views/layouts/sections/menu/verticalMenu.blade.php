<!-- Menu -->
<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
  <div class="app-brand demo">
    <a href="{{url('/')}}" class="app-brand-link">
      <span class="app-brand-logo demo">
        @include('_partials.macros')
      </span>
      <span class="app-brand-text demo menu-text fw-bold ms-2">{{config('variables.templateName')}}</span>
    </a>

    <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
      <i class="icon-base ri ri-menu-line align-middle"></i>
    </a>
  </div>

  <div class="menu-inner-shadow"></div>

  <ul class="menu-inner py-1">
    <!-- Dashboards -->
    <li class="menu-item {{ request()->is('/') ? 'active' : '' }}">
      <a href="{{url('/')}}" class="menu-link">
        <i class="menu-icon tf-icons ri ri-home-line"></i>
        <div>Dashboard</div>
      </a>
    </li>

    <!-- Client Management -->
    <li class="menu-header small text-uppercase">
      <span class="menu-header-text">Client Management</span>
    </li>
    <li class="menu-item">
      <a href="javascript:void(0);" class="menu-link menu-toggle">
        <i class="menu-icon tf-icons ri ri-user-line"></i>
        <div>Clients</div>
      </a>
      <ul class="menu-sub">
        <li class="menu-item">
          <a href="#" class="menu-link">
            <div>All Clients</div>
          </a>
        </li>
        <li class="menu-item">
          <a href="#" class="menu-link">
            <div>Add New Client</div>
          </a>
        </li>
      </ul>
    </li>

    <!-- Travel Management -->
    <li class="menu-header small text-uppercase">
      <span class="menu-header-text">Travel Management</span>
    </li>
    <li class="menu-item">
      <a href="javascript:void(0);" class="menu-link menu-toggle">
        <i class="menu-icon tf-icons ri ri-flight-takeoff-line"></i>
        <div>Travel Packages</div>
      </a>
      <ul class="menu-sub">
        <li class="menu-item">
          <a href="#" class="menu-link">
            <div>All Packages</div>
          </a>
        </li>
        <li class="menu-item">
          <a href="#" class="menu-link">
            <div>Create Package</div>
          </a>
        </li>
      </ul>
    </li>

    <!-- Bookings -->
    <li class="menu-item">
      <a href="javascript:void(0);" class="menu-link menu-toggle">
        <i class="menu-icon tf-icons ri ri-calendar-check-line"></i>
        <div>Bookings</div>
      </a>
      <ul class="menu-sub">
        <li class="menu-item">
          <a href="#" class="menu-link">
            <div>Active Bookings</div>
          </a>
        </li>
        <li class="menu-item">
          <a href="#" class="menu-link">
            <div>Booking History</div>
          </a>
        </li>
      </ul>
    </li>

    <!-- Account Settings -->
    <li class="menu-header small text-uppercase">
      <span class="menu-header-text">Account</span>
    </li>
    <li class="menu-item">
      <a href="{{ route('pages-account-settings-account') }}" class="menu-link">
        <i class="menu-icon tf-icons ri ri-settings-4-line"></i>
        <div>Settings</div>
      </a>
    </li>
  </ul>
</aside>
<!-- / Menu -->