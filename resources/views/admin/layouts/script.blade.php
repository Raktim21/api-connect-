    <script src="{{ asset('admin_dashboard/assets/js/jquery-3.6.0.min.js') }}"></script>
    <!-- Bootstrap js-->
    <script src="{{ asset('admin_dashboard/assets/js/bootstrap/bootstrap.bundle.min.js') }}"></script>
    <!-- feather icon js-->
    <script src="{{ asset('admin_dashboard/assets/js/icons/feather-icon/feather.min.js') }}"></script>
    <script src="{{ asset('admin_dashboard/assets/js/icons/feather-icon/feather-icon.js') }}"></script>
    <!-- scrollbar js-->
    <script src="{{ asset('admin_dashboard/assets/js/scrollbar/simplebar.js') }}"></script>
    <script src="{{ asset('admin_dashboard/assets/js/scrollbar/custom.js') }}"></script>
    <!-- Plugins JS start-->
    <script src="{{ asset('admin_dashboard/assets/js/sidebar-menu.js') }}"></script>
    {{-- <script src="{{ asset('admin_dashboard/assets/js/sweet-alert/sweetalert.min.js') }}"></script> --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script src="{{ asset('admin_dashboard/assets/js/prism/prism.min.js') }}"></script>
    <script src="{{ asset('admin_dashboard/assets/js/custom-card/custom-card.js') }}"></script>

    <script src="{{ asset('admin_dashboard/assets/js/typeahead/handlebars.js') }}"></script>
    {{-- <script src="{{ asset('admin_dashboard/assets/js/typeahead/typeahead.bundle.js') }}"></script> --}}
    {{-- <script src="{{ asset('admin_dashboard/assets/js/typeahead/typeahead.custom.js') }}"></script> --}}
    <!-- Plugins JS Ends-->
    <!-- Theme js-->
    <script src="{{ asset('admin_dashboard/assets/js/script.js') }}"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js" integrity="sha512-VEd+nq25CkR676O+pLBnDW09R7VQX9Mdiij052gVCp5yVH3jGtH70Ho/UUv4mJDsEdTvqRCFZg0NKGiojGnUCw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>


    <script>
        toastr.options = {
            "closeButton": true,
            "debug": false,
            "newestOnTop": false,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "preventDuplicates": true,
            "onclick": null,
            "showDuration": "500",
            "hideDuration": "1000",
            "timeOut": "4000",
            "extendedTimeOut": "1000",
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut"
        }
    </script>
    
    @if ($errors->any())
        @foreach ($errors->all() as $error)
            <script>
                toastr.error('{{ $error }}');
            </script>
        @endforeach
    @endif
    
    @if(session()->get('error'))
        <script>
            toastr.error('{{ session()->get('error') }}');
        </script>
    @endif

    @if(session()->get('success'))
        <script>
            toastr.success('{{ session()->get('success') }}');
        </script>
    @endif


    @yield('js')