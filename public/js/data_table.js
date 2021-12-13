  
    
    $(document).ready(function() {
    $('#table-backoffice').DataTable({
        language: {

            url: '/js/dataTables.french.json'
        },
        "aoColumnDefs": [
            { 'bSortable': false, 'aTargets': [ 2,3,5,6 ] }
        ]
    });

    $('#table-category').DataTable({
        language: {

            url: '/js/dataTables.french.json'
        },
        "aoColumnDefs": [
            { 'bSortable': false, 'aTargets': [ 1,3 ] }
        ]
    });

});

