$(document).ready(function() {
    //
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    //
    $('.number, .phone, .postal, .only-number').keypress(function (evt) {
        var charCode = (evt.which) ? evt.which : event.keyCode
        if ((charCode < 48 || charCode > 57))
            return false;
        return true;
    });

    //
    var btn_toggle = '#toggle-fullscreen';
    $(btn_toggle).click(function () {
        $.ct.fullscreen($(this), document.documentElement);
    });
    $(document).on(
        'fullscreenchange webkitfullscreenchange mozfullscreenchange',
        function () {
            const isFullscreen =
                document.fullscreenElement ||
                document.webkitFullscreenElement ||
                document.mozFullScreenElement;
            $('body').toggleClass('full-screen', !!isFullscreen);
            const $icon = $(btn_toggle).children('i');
            if(!isFullscreen) {
                $icon.removeClass('ri-fullscreen-exit-fill');
                $icon.addClass('ri-fullscreen-line');
            } else if(isFullscreen) {
                $icon.removeClass('ri-fullscreen-line');
                $icon.addClass('ri-fullscreen-exit-fill');
            }
        }
    );

    //dark mode
    $.ct.darkmode();
    $("[data-theme-toggle]").click(function () {
        $.ct.darkmode(this);
    });
    
    //
    $(document).on("keyup", "input[data-input-type='thousand'], input[data-input-type='currency']", function (e) {
        formatCurrency2($(this), null, "Rp ");
    });
    $(document).on("blur", "input[data-input-type='thousand'], input[data-input-type='currency']", function (e) {
        formatCurrency2($(this), "blur", "Rp ");
    });
    
    //
    var selector = '.select2';
    var options = {};
    $(selector).each(function () {
        const $el = $(this);
        $el.select2($.extend(true, {
            dropdownPosition: 'below',
            dropdownParent: $el.parent()
        }, options));
    });
    $(document)
        .off('select2:close.initSelect2', selector)
        .on('select2:close.initSelect2', selector, function (e) {
            const evt = 'scroll.select2';
            $(e.target).parents().off(evt);
            $(window).off(evt);
        });
    //select2 multi
    $(".select2-multiple").select2({
        placeholder: "Select All",
        tags: true,
        tokenSeparators: [',', ' ']
    });

    //
    $(".date").datepicker({
        format: 'yyyy-mm-dd',
        orientation: "bottom left",
        autoClose: true,
        todayHighlight: true,
    });
    $(".time").timepicker({
        showMeridian: false,
        placement: 'bottom',
        align: 'left',
        autoclose: true,
        'default': 'now'
    });

    //
    var start_date = moment().subtract(29, 'days');
    var end_date = moment();
    $(".daterange").daterangepicker({
        locale: {
            format: 'DD/MM/YYYY'
        },
        startDate: start_date,
        endDate: end_date,
        ranges: {
            'Today': [moment(), moment()],
            'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'Last 7 Days': [moment().subtract(6, 'days'), moment()],
            'Last 30 Days': [moment().subtract(29, 'days'), moment()],
            'This Month': [moment().startOf('month'), moment().endOf('month')],
            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        }
    });

    //
    $(".summernote").summernote({
        height: "200px"
    });

    //
    $(window).scroll(function () {
        var scrolled = $(window).scrollTop();
        if (scrolled > 200) $('.ct-gtop').fadeIn('slow');
        if (scrolled < 200) $('.ct-gtop').fadeOut('slow');
    });
    $('.ct-gtop').click(function () {
        $("html, body").animate({ scrollTop: "0" }, 500);
    });
});