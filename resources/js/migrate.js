$(document).ready(function ($) {
    class Migrate {
        constructor() {
            this.databaseFrom = $('#database_from');
            this.databaseTo = $('#database_to');
            this.subsitesFrom = $('#subsites_from');
            this.subsitesTo = $('#subsites_to');
            this.selectedSubsite;
            this.addListeners();
        }

        addListeners() {
            let self = this;
            this.databaseFrom.on('change', function () {
                let dbName = $(this).val();
                if (dbName) {
                    self.subsitesSelect = self.subsitesFrom;
                    self.retrieveSubsites(dbName);
                }
            });
            this.databaseTo.on('change', function () {
                let dbName = $(this).val();
                if (dbName) {
                    self.subsitesSelect = self.subsitesTo;
                    self.retrieveSubsites(dbName);
                }
            });
        }

        retrieveSubsites(dbName) {
            let self = this;

            this.ajaxSetup()
            $.ajax({
                type: "GET",
                dataType: 'json',
                url: "/subsites?database=" + dbName,
                processData: false,
                success: function (data) {
                    self.populateSubsites(data);
                    console.log(data);
                },
                error: function (msg) {
                    console.log(msg);
                }
            });
        }

        populateSubsites(data) {
            let self = this;

            if (data.subsites) {
                this.subsitesSelect.empty();
                data.subsites.forEach(row => {
                    self.subsitesSelect.append('<option value="' + row.blog_id + '">' + row.siteurl + '</option>')
                });
                this.subsitesSelect.removeClass('d-none');
            }

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
