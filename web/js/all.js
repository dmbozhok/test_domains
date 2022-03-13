$(document).ready(function () {
    $('.update-status').on('click', function () {
        const $this = $(this);
        $.ajax({
            url: $this.data('href'),
            method: 'post',
            data: {
                id: $this.data('id')
            },
            success: function (data) {
                if (data.success) {
                    location.reload();
                } else {
                    $this.parent().append(data.error);
                }
            }
        })
    });
});