@php
$containerFooter = !empty($containerNav) ? $containerNav : 'container-fluid';
@endphp

<!-- Footer-->
<footer class="content-footer footer bg-footer-theme">
    <div class="{{ $containerFooter }}">
        <div class="footer-container d-flex align-items-center justify-content-between py-4 flex-md-row flex-column">
            <div class="text-body">
                ©
                <script>
                document.write(new Date().getFullYear())
                </script> 
                ELI Voyages Connect - Powered with ❤️ by 
                <a href="https://elitech-solutions.com" target="_blank" class="footer-link">Elitech Solutions</a>
            </div>
            <div class="d-none d-lg-inline-block">
                <a href="javascript:void(0)" class="footer-link me-4" target="_blank">Support</a>
                <a href="javascript:void(0)" target="_blank" class="footer-link me-4">Documentation</a>
            </div>
        </div>
    </div>
</footer>
<!--/ Footer-->