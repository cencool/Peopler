$('#imgModal').on('show.bs.modal', function(e) {
    $('#modal-image').attr('src', '/attachment/send-file?fileId=' + e.relatedTarget.name);
});
$('.delete').on('click', function(e) {
    if (!confirm(deleteMessage)) {
        e.preventDefault();
    }
});