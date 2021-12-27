$('#imgModal').on('show.bs.modal', function(e) {
    $('#modal-image').attr('src', '/attachment/send-file?fileId=' + e.relatedTarget.name);
});