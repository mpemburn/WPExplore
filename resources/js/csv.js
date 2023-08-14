$(document).ready(function ($) {
    class CsvBuilder {
        constructor() {
            this.csvType = $('#csv_type');
            this.database = $('#database');
            this.databaseLabel = $('#database option:selected');
            this.downloadButton = $('#download_btn');
            this.dateRange = $('#date_range');
            this.startDate = $('input[name="start_date"]')
            this.endDate = $('input[name="end_date"]')
            this.filename = $('input[name="filename"]')
            this.filenameDefault = $('input[name="filename_default"]')
            this.error = $('#error');
            this.errorMessage = '';
            this.emptyFields = [];

            this.setEndDate();
            this.addListeners();
        }

        setStartDate(dbName) {
            let self = this;
            self.ajaxSetup()
            $.ajax({
                type: "GET",
                dataType: 'json',
                url: "/min_date?database=" + dbName,
                processData: false,
                success: function (data) {
                    self.startDate.val('');
                    if (data.minDate) {
                        self.startDate.val(data.minDate);
                        self.isValidInput();
                        self.setFileName();
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

        setFileName() {
            let filename = '';
            let useDateRange = this.dateRange.is(':visible');
            let dbName =  $('#database option:selected').text();
            let typeName = useDateRange ? 'blogs_from_' : this.csvType.val();
            let startDate = this.startDate.val();
            let endDate = this.endDate.val();

            if (dbName !== 'Select' && typeName !== '') {
                filename = dbName.replace(/\./g, '_') + '_' + typeName;
            }

            if (useDateRange) {
                filename += startDate + '_to_' + endDate;
            }

            this.filenameDefault.val(filename);
        }

        addListeners() {
            let self = this;

            this.database.on('change', function () {
                let dbName = $(this).val();
                self.error.html('');
                self.setStartDate(dbName);
            });

            this.csvType.on('change', function () {
                self.error.html('');
                self.startDate.val('');
                self.endDate.val('');
                self.dateRange.hide()
                self.useDateRange = false;
                if ($(this).val().search('date_range') !== -1) {
                    let dbName = self.database.val();
                    self.setStartDate(dbName);
                    self.setEndDate();
                    self.dateRange.show();
                    self.useDateRange = true;
                }
                self.setFileName();
            });

            this.startDate.on('change', function () {
                self.setFileName();
            });

            this.endDate.on('change', function () {
                self.setFileName();
            });

            this.downloadButton.on('click', function (evt) {
                evt.preventDefault();
                if (! self.isValidInput()) {
                    return;
                }
                let formData = $('#download_form').serialize();

                self.ajaxSetup()
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
        }

        isValidInput() {
            let self = this;

            this.error.html('');
            this.emptyFields = [];
            $('input, select').each(function () {
                let isVisible = $(this).is(":visible");
                let isRequired = $(this).is(":required");
                let name = $(this).attr('name');
                let value = $(this).val();
                let label = $('label[for="' + name + '"]').html();
                if (isRequired && isVisible && value === '') {
                    self.emptyFields.push(label.replace(':', ''));
                }
            });

            if (this.emptyFields.length > 0 ) {
                this.errorMessage = 'These fields cannot be empty: ' + this.emptyFields.join(', ');
                this.error.html(this.errorMessage);

                return false;
            }

            return true;
        }

        ajaxSetup() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
        }
    }

    new CsvBuilder();
});
