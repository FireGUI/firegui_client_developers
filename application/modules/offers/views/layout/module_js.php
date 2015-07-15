<!-- BEGIN Module Related Javascript -->
<script>
    $(document).ready(function() {
        var table = $('#js_product_table');
        var body = $('tbody', table);
        var rows = $('tr', body);
        var increment = $('#js_add_product', table);


        var firstRow = rows.filter(':first');
        var counter = rows.size();

        increment.on('click', function() {
            var newRow = firstRow.clone();

            /* Line manipulation begin */
            newRow.removeClass('hidden');
            $('input, select, textarea', newRow).each(function() {
                var control = $(this);
                var name = control.attr('data-name');
                control.attr('name', 'products[' + counter + '][' + name + ']').removeAttr('data-name');
            });
            
            $('.js_table_select2', newRow).select2({
                placeholder: "Select a product",
                allowClear: true
            });
            /* Line manipulation end */

            counter++;
            newRow.appendTo(body);
        }).trigger('click');


        table.on('click', '.js_remove_product', function() {
            $(this).parents('tr').remove();
        });




    });
</script>

<script>
    $(document).ready(function() {
        $('.js_select2').each(function() {
            var select = $(this);
            var placeholder = select.attr('data-placeholder');
            select.select2({
                placeholder: placeholder? placeholder: '',
                allowClear: true
            });
        });
    });
</script>

<script>
    $(document).ready(function() {
        $('#js_dtable').dataTable({
            aoColumns: [null, null, null, null, null, null, null, null, {bSortable: false}]
        });
        $('#js_dtable_wrapper .dataTables_filter input').addClass("form-control input-small"); // modify table search input
        $('#js_dtable_wrapper .dataTables_length select').addClass("form-control input-xsmall"); // modify table per page dropdown
    });
</script>
<!-- END Module Related Javascript -->