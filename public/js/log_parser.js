$(document).ready(function () {
    let link = $('[data-line-num]');
    link.on('click', function () {
        let lineNum = $(this).data('line-num');
        let logId = $('[data-log]').data('log');

        $.ajax({
            url: 'api/parser',
            type: 'POST',
            dataType: 'json',
            data: {
                lineNum: lineNum,
                logId: logId
            },
            success: function (response) {
                console.log(response);
                location.reload()
            },
            error: function (response) {
                alert(response);
            }
        });
    });
});
