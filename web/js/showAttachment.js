$('#imgModal').on('show.bs.modal', function(e) {
    $('#modal-image').attr('src', '/person/send-file?fileId=' + e.relatedTarget.id);
});