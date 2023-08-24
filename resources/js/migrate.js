$(document).ready(function ($) {
    class Migrate {
        constructor() {
            this.databaseFrom = $('#database_from');
            this.databaseTo = $('#database_to');
            this.subsitesFrom = $('#subsites_from');
            this.subsitesTo = $('#subsites_to');
            this.fromData = [];
            this.toData = [];
            this.maxSelected = null;
            this.maxError = $('#max_error');
            this.migrateButton = $('#migrate_btn');
            this.filter = $('#filter');
            this.loading = $('#loading');

            this.maxError.hide();
            this.addListeners();
        }

        addListeners() {
            let self = this;
            this.databaseFrom.on('change', function () {
                let dbName = $(this).val();
                if (dbName) {
                    self.retrieveSubsites(dbName, 'from');
                }
            });
            this.databaseTo.on('change', function () {
                let dbName = $(this).val();
                if (dbName) {
                    self.retrieveSubsites(dbName, 'to');
                }
            });
            this.filter.on('keyup', function (evt) {
                let value = $(this).val();
                $("#subsites_from > option").each(function() {
                    let siteUrl = this.text.replace(/[\d \[\]]+/, '');
                    let pathname = new URL(siteUrl).pathname;
                    $(this).removeClass('d-none');
                    if (pathname.indexOf(value) === -1) {
                        $(this).addClass('d-none');
                    }
                });
            });
            this.subsitesFrom.change(function(event) {
                if ($(this).val().length > 5) {
                    $(this).val(self.maxSelected);
                    self.maxError.show().fadeOut(4000);
                } else {
                    self.maxSelected = $(this).val();
                }
            });
            this.migrateButton.on('click', function () {
                let data = $.param({
                    databaseFrom: self.databaseFrom.val(),
                    databaseTo: self.databaseTo.val()
                });

                let selectedValues = $("#subsites_from :selected").map(function(i, el) {
                    return $(el).val();
                }).get();

                self.loading.removeClass('d-none');
                self.ajaxSetup();
                $.ajax({
                    type: "POST",
                    dataType: 'json',
                    url: "/do_migration?" + data + '&from=' + selectedValues.join(','),
                    processData: false,
                    success: function (data) {
                        console.log(data);
                        self.loading.addClass('d-none');
                    },
                    error: function (msg) {
                        console.log(msg);
                        self.loading.addClass('d-none');
                    }
                });
            });
        }

        retrieveSubsites(dbName, direction) {
            let self = this;

            this.ajaxSetup()
            $.ajax({
                type: "GET",
                dataType: 'json',
                url: "/subsites?database=" + dbName,
                processData: false,
                success: function (data) {
                    if (data.subsites) {
                        if (direction === 'from') {
                            self.fromData = data.subsites;
                        } else {
                            self.toData = data.subsites;
                        }
                        self.fillSelects();
                        self.filter.keyup();
                    }
                    console.log(data);
                },
                error: function (msg) {
                    console.log(msg);
                }
            });
        }

        fillSelects() {
            let disableButton = true;

            if (this.toData.length > 0 && this.fromData.length > 0) {
                this.toData.forEach(item => {
                    let siteUrl = new URL(item.siteurl).pathname;
                    // Find any subsite that ends with the same pathname
                    let index = this.fromData.findIndex((obj) => {
                        return obj.siteurl.endsWith(siteUrl);
                    });
                    // Remove item from array
                    if (index !== -1) {
                        this.fromData.splice(index, 1);
                    }
                });
                disableButton = false;
            }
            this.subsitesSelect = this.subsitesFrom;
            this.populateSubsites(this.fromData);
            this.subsitesSelect = this.subsitesTo;
            this.populateSubsites(this.toData);
            this.migrateButton.prop('disabled', disableButton);
        }

        populateSubsites(subsites) {
            let self = this;

            this.subsitesSelect.empty();
            subsites.forEach(row => {
                let displayId = '[' + row.blog_id + '] ';
                let url = row.siteurl;
                self.subsitesSelect.append('<option value="' + row.blog_id + '">' + displayId + url + '</option>')
            });
        }

        ajaxSetup() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
        }
    }

    new Migrate();
});
