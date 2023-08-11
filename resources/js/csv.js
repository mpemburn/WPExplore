$(document).ready(function ($) {
    class CsvBuilder {
        constructor() {
            this.csvType = $('#csv_type');
            this.database = $('#database');
            this.downloadButton = $('#download_btn');
            this.dateRange = $('#date_range');
            this.startDate = $('input[name="start_date"]')
            this.endDate = $('input[name="end_date"]')
            this.useDateRange = false;

            this.setEndDate();
            this.addListeners();
        }

        setStartDate(dbName) {
            let self = this;
            this.setHeaders();
            $.ajax({
                type: "GET",
                dataType: 'json',
                url: "/min_date?database=" + dbName,
                processData: false,
                success: function (data) {
                    self.startDate.val('');
                    if (data.minDate) {
                        self.startDate.val(data.minDate);
                    }
                    console.log(data);
                },
                error: function (msg) {
                    console.log(msg);
                }
            });
        }

        setEndDate() {
            let todayDate = $('input[name="today_date"]').val();
            this.endDate.val(todayDate);
        }

        addListeners() {
            let self = this;

            this.database.on('change', function () {
                let dbName = $(this).val();
                self.setStartDate(dbName);
            });

            this.csvType.on('change', function () {
                self.startDate.val('');
                self.endDate.val('');
                self.dateRange.hide()
                self.useDateRange = false;
                if ($(this).val().search('DateRange') !== -1) {
                    let dbName = self.database.val();
                    self.setStartDate(dbName);
                    self.setEndDate();
                    self.dateRange.show();
                    self.useDateRange = true;
                }
            });

            this.downloadButton.on('click', function (evt) {
                evt.preventDefault();
                if (! self.isValidInput()) {
                    return;
                }
                let formData = $('#download_form').serialize();

                $.ajax({
                    type: "POST",
                    dataType: 'json',
                    url: "/do_download",
                    data: formData,
                    processData: false,
                    success: function (data) {
                      console.log(data);
                    },
                    error: function (msg) {
                        console.log(msg);
                    }
                });
            });

            this.download.on('keyup', function (evt) {
                let hasText = $(this).val() !== '';
                self.downloadButton.prop('disabled', ! hasText);
            });
        }

        isValidInput() {
            let inputs = [
                'csv_type',
                'database',
                'start_date',
                'end_date'
            ];

            inputs.forEach(selector => {
                let label = $('label[for="' + selector + '"]');
                console.log(label.html());
            });

            return false;
        }

        setHeaders() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
        }
    }

    new CsvBuilder();
});
