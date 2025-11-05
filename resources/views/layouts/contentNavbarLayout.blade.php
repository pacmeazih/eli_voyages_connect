@extends('layouts/commonMaster')

@section('layoutContent')
<div class="layout-wrapper layout-content-navbar {{ $isMenu ?? '' ? '' : 'layout-without-menu' }}">
    <div class="layout-container">

        @if (isset($isMenu) && $isMenu)
        @include('layouts/sections/menu/verticalMenu')
        @endif

        <!-- Layout page -->
        <div class="layout-page">

            <!-- BEGIN: Navbar-->
            @if (isset($isNavbar) && $isNavbar)
            @include('layouts/sections/navbar/navbar')
            @endif
            <!-- END: Navbar-->

            <!-- Content wrapper -->
            <div class="content-wrapper">
                <!-- Content -->
                @if (isset($isFlex) && $isFlex)
                <div class="{{ $container ?? 'container-xxl' }} d-flex align-items-stretch flex-grow-1 p-0">
                @else
                <div class="{{ $container ?? 'container-xxl' }} flex-grow-1 container-p-y">
                @endif

                    @yield('content')

                </div>
                <!-- / Content -->

                <!-- Footer -->
                @include('layouts/sections/footer/footer')
                <!-- / Footer -->

                <div class="content-backdrop fade"></div>
            </div>
            <!--/ Content wrapper -->
        </div>
        <!--/ Layout page -->
    </div>

    @if (isset($isMenu) && $isMenu)
    <!-- Overlay -->
    <div class="layout-overlay layout-menu-toggle"></div>
    @endif
    
    <!-- Drag Target Area To SlideIn Menu On Small Screens -->
    <div class="drag-target"></div>
</div>
@endsection