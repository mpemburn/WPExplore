$(document).ready(function ($) {
    class HealthChecker {
        constructor() {
            let self = this;
            setInterval(function () {
                self.poll();
            }, 3000);

        }

        poll() {
            $.ajax({
                type: "GET",
                dataType: 'json',
                url: "/health?" + 'url=https://www.clarku.edu',
                processData: false,
                success: function (data) {
                    console.log(data);
                },
                error: function (msg) {
                    console.log(msg);
                }
            });

        }
    }

    new HealthChecker();
});
