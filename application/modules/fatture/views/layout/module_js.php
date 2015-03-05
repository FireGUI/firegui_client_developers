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
                placeholder: "Seleziona prodotto",
                allowClear: true
            });
            
            /* Line manipulation end */

            counter++;
            newRow.appendTo(body);
        }).trigger('click');


        table.on('click', '.js_remove_product', function() {
            $(this).parents('tr').remove();
        });
        
        $('#offerproducttable .js_remove_product').on('click', function() {
            $(this).parents('tr').remove();
        });
        
        
        $('#js_product_table').on('change', '.js_product_select_offers_ndr', function() {
            var iProductID = $(this).val();
            var jqThis = $(this);
            $.ajax(base_url+'offers_ndr/db_ajax/get_product_accessories/'+iProductID, {
                dataType: 'json',
                success: function(jsonData) {
                    var jqContainer = $('.js_accessories_container_offers_ndr', jqThis.parents('td').filter(':first'));
                    jqContainer.html('');
                    
                    
                    var jqTable = $('<table></table>').addClass('table');
                    jqContainer.append(jqTable);
                    
                    $.each(jsonData, function(k, field) {
                        var jqTr = $('<tr></tr>');
                        var jqCheckQty = $('<input></input>').attr('name', 'product_accessory['+iProductID+']['+k+'][sel_qty]');
                        
                        if(field.prodotti_accessori_quantita === 't') {
                            // Text
                            jqCheckQty.attr('type', 'text').attr('placeholder', 'Q.ty').addClass('form-control');
                        } else {
                            // Checkbox
                            jqCheckQty.val(1).attr('type', 'checkbox').addClass('checkbox');
                        }
                        
                        
                        
                        $('<td></td>').append(
                                $('<input></input>')
                                    .val(field.prodotti_accessori_id).attr('type', 'hidden')
                                    .attr('name', 'product_accessory['+iProductID+']['+k+'][id]')
                        ).append(jqCheckQty).appendTo(jqTr);
                        $('<td></td>').text(field.prodotti_accessori_descrizione).appendTo(jqTr);
                        $('<td></td>').append(
                            $('<input></input>').val(field.prodotti_accessori_sigla).attr('type', 'text').attr('name', 'product_accessory['+iProductID+']['+k+'][code]').addClass('form-control').attr('placeholder', 'Sigla')
                        ).appendTo(jqTr);
                        $('<td></td>').append(
                            $('<input></input>').val(field.prodotti_accessori_prezzo==='0'? '': parseFloat(field.prodotti_accessori_prezzo).toFixed(2)).attr('type', 'text').attr('name', 'product_accessory['+iProductID+']['+k+'][price]').addClass('form-control').attr('placeholder', 'Prezzo')
                        ).appendTo(jqTr);
                        
                        jqTable.append(jqTr);
                    });
                }
            });
            
            
            var jqCustomer = $('input.js_offer_customer').val();
            $.ajax(base_url+'offers_ndr/db_ajax/get_product_prices/'+iProductID+'/'+jqCustomer, {
                dataType: 'json',
                success: function(jsonData) {
                    var jqPrice = $('.js_product_price_offers_ndr', jqThis.parents('tr').filter(':first'));
                    jqPrice.val(parseFloat(jsonData.prodotti_prezzo).toFixed(2));
                }
            });
        });
        
        
        // Selezione mandante filtra prodotti per quella mandante
        $('.js_mandante').on('change', function() {
        
            // Rimuovi classi non hidden
            $('#js_product_table tbody tr:not(.hidden)').remove();
            
            $.ajax(base_url+'offers_ndr/db_ajax/prodotti_mandante/'+$(this).val(), {
                dataType: 'json',
                success: function(data) {
                    $('.js_product_select_offers_ndr').html('<option></option>');
                    $.each(data, function(k, v) {
                        var option = $('<option></option>');
                        option.val(k).text(v);
                        option.appendTo($('.js_product_select_offers_ndr'));
                    });
                    increment.click();
                }
            });
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
            aoColumns: [null, null, null, null, null, null, null, null, {bSortable: false}],
            aaSorting: [[0, 'desc']]
        });
        $('#js_dtable_wrapper .dataTables_filter input').addClass("form-control input-small"); // modify table search input
        $('#js_dtable_wrapper .dataTables_length select').addClass("form-control input-xsmall"); // modify table per page dropdown
    });
</script>
<!-- END Module Related Javascript -->