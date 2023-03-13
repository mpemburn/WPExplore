$(document).ready(function () {
    let link = $('[data-line-num]');
    link.on('click', function () {
        let lineNum = $(this).data('line-num');
        let logId = $('[data-log]').data('log');
        let self = $(this);

        $.ajax({
            url: 'api/parser',
            type: 'POST',
            dataType: 'json',
            data: {
                lineNum: lineNum,
                logId: logId
            },
            success: function (response) {
                $('div[data-line-num="' + response.lineNum + '"').toggleClass('done', response.done)
            },
            error: function (response) {
                alert(response);
            }
        });
    });
});
