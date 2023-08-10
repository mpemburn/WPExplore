$(document).ready(function ($) {
    class Searcher {
        constructor() {
            this.found = $('#found');
            this.results = $('#results');
            this.loading = $('#loading');
            this.searchButton = $('#search_btn');
            this.search = $('[name="text"]');
            this.search.focus();

            this.addListeners();
        }

        addListeners() {
            let self = this;

            this.searchButton.on('click', function (evt) {
                evt.preventDefault();
                let formData = $('#search_form').serialize();

                self.loading.removeClass('d-none');
                self.found.html('');
                self.results.html('');

                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                $.ajax({
                    type: "POST",
                    dataType: 'json',
                    url: "/do_search",
                    data: formData,
                    processData: false,
                    success: function (data) {
                        self.loading.addClass('d-none');
                        self.found.html('<strong>Total Found: ' + data.found + '</strong>');
                        if (data.found > 0) {
                            self.results.html(data.html);
                        }
                        console.log(data);
                    },
                    error: function (msg) {
                        self.loading.addClass('d-none');
                        console.log(msg);
                    }
                });
            });

            this.search.on('keyup', function (evt) {
                let hasText = $(this).val() !== '';
                self.searchButton.prop('disabled', ! hasText);
            });
        }
    }

    new Searcher();
});
