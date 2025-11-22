@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <script>
        // Fungsi select all
        function selectAll(selector) {
            $(selector).find('option').prop('selected', true);
            $(selector).trigger('change');
        }

        // Fungsi clear all
        function clearAll(selector) {
            $(selector).val(null).trigger('change');
        }

        $(document).ready(function() {
            
            // 1. Init Single Select
            function initSingle() {
                $('.select2-single').select2({
                    theme: "bootstrap-5",
                    width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
                    placeholder: $( this ).data( 'placeholder' ),
                    allowClear: true
                });
            }

            // 2. Init Multi Select
            function initMulti() {
                $('.select2-multi').select2({
                    theme: "bootstrap-5",
                    width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
                    placeholder: $( this ).data( 'placeholder' ),
                    closeOnSelect: false,
                    allowClear: true
                });
            }

            initSingle();
            initMulti();

            window.toggleInputs = function() {
                const role = $('#role').val();

                if (role === 'audit') {
                    $('#multi-branch-group').removeClass('d-none');
                    $('#single-branch-group').addClass('d-none');
                    setTimeout(() => {
                        initMulti();
                    }, 50);
                } else {
                    $('#single-branch-group').removeClass('d-none');
                    $('#multi-branch-group').addClass('d-none');
                }

                if (role === 'leader') {
                    $('#multi-division-group').removeClass('d-none');
                    $('#single-division-group').addClass('d-none');
                    setTimeout(() => {
                        initMulti();
                    }, 50);
                } else {
                    $('#single-division-group').removeClass('d-none');
                    $('#multi-division-group').addClass('d-none');
                }
            };

            toggleInputs();
        });
    </script>
@endpush
