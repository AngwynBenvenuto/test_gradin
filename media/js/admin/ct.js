//functions
//enter skip input form
function keyEnter(field, event) {
    var keyCode = event.keyCode ? event.keyCode : event.which ? event.which : event.charCode;
    if (keyCode == 13) {
        var i;
        for (i = 0; i < field.form.elements.length; i++)
            if (field == field.form.elements[i])
                break;
            i = (i + 1) % field.form.elements.length;
            field.form.elements[i].focus();
            return false;
    }  
    else
        return true;
}
//excel
function exportExcelMultiSheet(options = {}) {
    const workbook = new ExcelJS.Workbook();
    const sheets = [];
    for (let index = 0; index < options.jumlah; index++) {
        var sh = workbook.addWorksheet("Sheet" + (index + 1));
        options.data.forEach((item, index) => {
            const row = sh.getRow(index + 1);
            row.values = item;
        });

        if (options.columns) {
            options.columns.forEach((item) => {
                sh.getColumn(item.name).width = item.value;
            });
        }
        sheets.push(sh);
    }

    if (options.beforeExport) {
        options.beforeExport(workbook, sheets);
    }
    workbook.xlsx
        .writeBuffer({
            base64: true,
        })
        .then(function (xls64) {
            // build anchor tag and attach file (works in chrome)
            var a = document.createElement("a");
            var data = new Blob([xls64], {
                type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
            });

            var url = URL.createObjectURL(data);
            a.href = url;
            a.download = options.fileName
                ? options.fileName + ".xlsx"
                : "export.xlsx";
            document.body.appendChild(a);
            a.click();
            setTimeout(function () {
                document.body.removeChild(a);
                window.URL.revokeObjectURL(url);
            }, 0);
        })
        .catch(function (error) {
            console.log(error.message);
        });
}
//format tanggal
function formatTanggal(tanggal, datepicker = false) {
    if (tanggal == '' || tanggal == null)
        return;
        
    var tanggal_item, hasil;
    if (datepicker == false) {
        tanggal_item = tanggal.split("/");
        hasil = tanggal_item[2] + "-" + tanggal_item[0] + "-" + tanggal_item[1];
    } else {
        tanggal_item = tanggal.split("-");
        hasil = tanggal_item[1] + "/" + tanggal_item[2] + "/" + tanggal_item[0];
    }
    return hasil;
}
//format number
function formatCurrency2(input, blur, prefix) {
    // appends $ to value, validates decimal side
    // and puts cursor back in right position.
    // get input value
    let input_val = input.val();
    let input_val_raw = parseFloat(
        input_val.replace(prefix, "").replace(/\./g, "").replace(/\,/g, ".")
    );
    if (isNaN(input_val_raw)) {
        input_val_raw = 0;
    }
    input.attr("data-value", input_val_raw);
    if (input_val === "") {
        return;
    }
    let original_len = input_val.length;
    let caret_pos = input.prop("selectionStart");
    if (input_val.indexOf(",") >= 0) {
        // get position of first decimal
        // this prevents multiple decimals from
        // being entered
        let decimal_pos = input_val.indexOf(",");
        let left_side = input_val.substring(0, decimal_pos);
        let right_side = input_val.substring(decimal_pos);
        left_side = formatNumber2(left_side);
        right_side = formatNumber2(right_side).replace(/\./g, "");
        right_side = right_side.substring(0, 4);
        input_val = prefix + left_side;
        if (right_side !== "00") {
            input_val += "," + right_side;
        }
    } else {
        // no decimal entered
        // add commas to number
        // remove all non-digits
        input_val = formatNumber2(input_val);
        input_val = prefix + input_val;
    }
    input.val(input_val);
    let updated_len = input_val.length;
    caret_pos = updated_len - original_len + caret_pos;
    input[0].setSelectionRange(caret_pos, caret_pos);
}
function formatNumber2(n) {
    // format number 1000000 to 1,234,567
    return n.replace(/[^0-9-]/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}
function formatCurrency(angka, options = {}) {
    angka = parseFloat(angka + "");
    options = Object.assign(
        { withoutCurrency: false, withoutTrailingZero: false },
        options
    );
    let formatter = new Intl.NumberFormat("id-ID", {
        // style: options.withoutCurrency ? "decimal" : "currency",
        style: "decimal",
        currency: "IDR",
        currencyDisplay: "code",
        minimumFractionDigits: 0,
    });

    let result = formatter.format(angka);
    if (!options.withoutCurrency && result !== "NaN") {
        result = "Rp " + result;
    }
    return result !== "NaN" ? result : "";
}
function formatThousand(angka) {
    return formatCurrency(angka, { withoutCurrency: true });
}
function formatRupiah(angka, prefix) {
    var number_string = angka.replace(/[^,\d]/g, "").toString(),
        split = number_string.split(","),
        sisa = split[0].length % 3,
        rupiah = split[0].substr(0, sisa),
        ribuan = split[0].substr(sisa).match(/\d{3}/gi);
    if (ribuan) {
        separator = sisa ? "." : "";
        rupiah += separator + ribuan.join(".");
    }

    rupiah = split[1] != undefined ? rupiah + "," + split[1] : rupiah;
    return prefix == undefined ? rupiah : rupiah ? prefix + rupiah : "";
}
function formatDesimal(angka, prefix) {
    var jum = angka.toString();
    if (jum == "0") jum = "000";
    var jum1 = jum.substring(0, jum.length - 2);
    var jum2 = jum.substring(jum.length - 2, jum.length);
    return formatRupiah(jum1, prefix) + "," + jum2;
}
function convertToAngka(rupiah) {
    return parseInt(rupiah.replace(/,.*|[^0-9]/g, ''), 10);
}
function getRandomString(length) {
    var result = '',
        chars = '!-().0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    for (var i = length; i > 0; --i)
        result += chars[Math.round(Math.random() * (chars.length - 1))];
    return result;
};
function getRandomSmlAlphabet(length) {
    var result = '',
        chars = 'abcdefghijklmnopqrstuvwxyz';
    for (var i = length; i > 0; --i)
        result += chars[Math.round(Math.random() * (chars.length - 1))];
    return result;
};


//
jQuery.fn.extend({
    slideRightShow: function () {
        return this.each(function () {
            $(this).show('slide', { direction: 'right' }, 1000);
        });
    },
    slideLeftHide: function () {
        return this.each(function () {
            $(this).hide('slide', { direction: 'left' }, 1000);
        });
    },
    slideRightHide: function () {
        return this.each(function () {
            $(this).hide('slide', { direction: 'right' }, 1000);
        });
    },
    slideLeftShow: function () {
        return this.each(function () {
            $(this).show('slide', { direction: 'left' }, 1000);
        });
    }
});

//
var ct = function () { }
if (!window.ct) {
    window.ct = new ct();
}
(function ($, window, document, undefined) {
    $.ct = {
        _filesadded: "",
        openWindowUrl: function(theURL) { //v2.0
            window.open(theURL, "CETAK", "menubar=1,resizable=1,width=450,height=550");
        },
        showModal: function (message, options) {
            var settings = $.extend({
                // These are the defaults.
                haveHeader: false,
                haveFooter: false,
                headerText: '',
                footerAction: {}
            }, options);

            var modalContainer = jQuery('<div>').addClass('modal');
            var modalDialog = jQuery('<div>').addClass('modal-dialog');
            var modalContent = jQuery('<div>').addClass('modal-content');
            modalDialog.append(modalContent);
            modalContainer.append(modalDialog);
            var modalHeader = jQuery('<div>').addClass('modal-header');
            var modalBody = jQuery('<div>').addClass('modal-body');
            var modalFooter = jQuery('<div>').addClass('modal-footer');
            modalBody.append(message);
            if (settings.haveHeader) {
                modalHeader.html(settings.headerText);
                modalContent.append(modalHeader);
            }
            modalContent.append(modalBody);
            if (settings.haveFooter) {
                modalContent.append(modalFooter);
            }

            $('body').append(modalContainer);
            var modalOptions = {
                complete: function () {
                    modalContainer.remove();
                }
            };
            modalContainer.modal(modalOptions);
            modalContainer.modal();
        },
        blockElement: function (selector) {
            selector.blockUI({'message': 'Loading..'});
        },
        unblockElement: function (selector) {
            selector.unblockUI();
        },
        message: function (title, message, options, callback) {
            selector = typeof options.selector !== 'undefined' ? options.selector : '.container';
            type = typeof options.type !== 'undefined' ? options.type : 'error';
            type_alert = typeof options.type_alert !== 'undefined' ? options.type_alert : 'bootbox';
            if(type_alert == 'sweetalert') {
            //     Swal.fire({
            //         title: title,
            //         text: message,
            //         icon: type
            //     });
            }
            if (type_alert == 'bootbox') {
                if (typeof callback == 'undefined') {
                    bootbox.alert(message);
                } else {
                    bootbox.alert(message, callback);
                }
            }
            if (type_alert == 'notify') {
                obj = $('<div>');
                selector.prepend(obj);
                obj.addClass('notifications');
                obj.addClass('top-right');
                obj.notify({
                    'message': { text: message },
                    'type': type
                }).show();
            }
            
            $("html, body").animate({ scrollTop: 0 }, "slow");
            return false;
        },
        _loadjscss: function (filename, filetype, callback) {
            if (filetype == "js") { //if filename is a external JavaScript file
                var fileref = document.createElement('script')
                fileref.setAttribute("type", "text/javascript")
                fileref.setAttribute("src", filename)
            } else if (filetype == "css") { //if filename is an external CSS file
                var fileref = document.createElement("link")
                fileref.setAttribute("rel", "stylesheet")
                fileref.setAttribute("type", "text/css")
                fileref.setAttribute("href", filename)
            }
            if (typeof fileref != "undefined") {
                fileref.onload = $.pg._handle_response_callback(callback);
                if (typeof (callback) === 'function') {
                    fileref.onreadystatechange = function () {
                        if (this.readyState == 'complete') {
                            $.pg._handle_response_callback(callback);
                        }
                    }
                }
                document.getElementsByTagName("head")[0].appendChild(fileref);
            }
        },
        _removejscss: function (filename, filetype) {
            var targetelement = (filetype == "js") ? "script" : (filetype == "css") ? "link" : "none"; //determine element type to create nodelist from
            var targetattr = (filetype == "js") ? "src" : (filetype == "css") ? "href" : "none"; //determine corresponding attribute to test for
            var allsuspects = document.getElementsByTagName(targetelement);
            for (var i = allsuspects.length; i >= 0; i--) { //search backwards within nodelist for matching elements to remove
                if (allsuspects[i] && allsuspects[i].getAttribute(targetattr) != null && allsuspects[i].getAttribute(targetattr).indexOf(filename) != -1) {
                    allsuspects[i].parentNode.removeChild(allsuspects[i]) //remove element by calling parentNode.removeChild()
                }
            }
        },
        _handle_response: function (data, callback) {
            if (data.css_require && data.css_require.length > 0) {
                for (var i = 0; i < data.css_require.length; i++) {
                    $.pg.require(data.css_require[i], 'css');
                }
            }
            require(data.js_require, callback); 
                return;
            $.pg._filesloaded = 0;
            $.pg._filesneeded = 0;
            if (data.css_require && data.css_require.length > 0)
                $.pg._filesneeded += data.css_require.length;
            if (data.js_require && data.js_require.length > 0)
                $.pg._filesneeded += data.js_require.length;
            if (data.css_require && data.css_require.length > 0) {
                for (var i = 0; i < data.css_require.length; i++) {
                    $.pg.require(data.css_require[i], 'css', callback);
                }
            }
            if (data.js_require && data.js_require.length > 0) {
                for (var i = 0; i < data.js_require.length; i++) {
                    $.pg.require(data.js_require[i], 'js', callback);
                }
            }
            if ($.pg._filesloaded == $.pg._filesneeded) {
                callback();
            }
        },
        _handle_response_callback: function (callback) {
            $.pg._filesloaded++;
            if ($.pg._filesloaded == $.pg._filesneeded) {
                callback();
            }
        },
        require: function (filename, filetype, callback) {
            if ($.pg._filesadded.indexOf("[" + filename + "]") == -1) {
                $.pg._loadjscss(filename, filetype, callback);
                $.pg._filesadded += "[" + filename + "]" //List of files added in the form "[filename1],[filename2],etc"
            } else {
                $.pg._filesloaded++;
                if ($.pg._filesloaded == $.pg._filesneeded) {
                    callback();
                }
            }
        },
        darkmode: function (arg) {
            const STORAGE_KEY = 'theme';
            const ThemeConfig = {
                load() {
                    return localStorage.getItem(STORAGE_KEY);
                },
                save(theme) {
                    theme
                        ? localStorage.setItem(STORAGE_KEY, theme)
                        : localStorage.removeItem(STORAGE_KEY);
                },
                detect() {
                    return window.matchMedia('(prefers-color-scheme: dark)').matches
                        ? 'dark'
                        : 'light';
                },
                get() {
                    return this.load() || this.detect();
                },
                set(theme) {
                    this.save(theme);
                    this.apply(theme);
                },
                toggle() {
                    const next = this.get() === 'dark' ? 'light' : 'dark';
                    this.set(next);
                    return next;
                },
                apply(theme) {
                    document.documentElement.setAttribute('data-bs-theme', theme);
                    $('[data-theme-toggle] i')
                        .removeClass('ri-moon-line ri-sun-line')
                        .addClass(theme === 'dark'
                            ? 'ri-sun-line'
                            : 'ri-moon-line'
                        );
                },
            };
            if (!arg) {
                ThemeConfig.apply(ThemeConfig.get());
                return ThemeConfig.get();
            }
            if (arg === 'dark' || arg === 'light') {
                ThemeConfig.set(arg);
                return arg;
            }
            return ThemeConfig.toggle();
        },
        fullscreen: function (selector, element) {
            if (!$('body').hasClass("full-screen")) {
                $('body').addClass("full-screen");
                selector.children('i').removeClass('ri-fullscreen-line');
                selector.children('i').addClass('ri-fullscreen-exit-fill');
                if (element.requestFullscreen) {
                    element.requestFullscreen();
                } else if (element.mozRequestFullScreen) {
                    element.mozRequestFullScreen();
                } else if (element.webkitRequestFullscreen) {
                    element.webkitRequestFullscreen();
                } else if (element.msRequestFullscreen) {
                    element.msRequestFullscreen();
                }
            } else {
                $('body').removeClass("full-screen");
                selector.children('i').removeClass('ri-fullscreen-exit-fill');
                selector.children('i').addClass('ri-fullscreen-line');
                if (
                    document.fullscreenElement ||
                    document.webkitFullscreenElement ||
                    document.mozFullScreenElement
                ) {
                    if (document.exitFullscreen) {
                        document.exitFullscreen();
                    } else if (document.mozCancelFullScreen) {
                        document.mozCancelFullScreen();
                    } else if (document.webkitExitFullscreen) {
                        document.webkitExitFullscreen();
                    }
                }
            }
        },
        appTable: function (selector, options) {
            const $table = $(selector);
            var displayLength = AppHelper.settings.displayLength * 1;
            if (isNaN(displayLength) || !displayLength) {
                displayLength = 10;
            }
            var responsive = false;
            if (AppHelper.settings.disableResponsiveDataTable === "1") {
                responsive = false;
            } else if ((AppHelper.settings.disableResponsiveDataTableForMobile !== "1") && (window.outerWidth < 800)) {
                responsive = true;
            }
            const settings = $.extend(true, {
                source: null,
                columns: [],
                order: [[0, "asc"]],
                serverSide: false,
                searching: true,
                filters: [],
                excel: false,
                pdf: false,
                stateSave: true, //save user state
                exportExcelUrl: '',
                exportPdfUrl: '',
                displayLength: displayLength, //default rows per page
                responsive: responsive
            }, options);

            if (!settings.source) {
                console.error('appTable: source is required');
                return;
            }
            const $wrapper = $(`
                <div class="app-table-wrapper">
                    <div class="table-responsive app-table-scroll">
                    </div>
                </div>
            `);
            $table.before($wrapper);
            $wrapper.find('.table-responsive').append($table);
            
            const $filterContainer = $(`
                <div class="app-filter-container d-none">
                    <div class="row app-filter-body g-2"></div>
                </div>
            `);
            $table.before($filterContainer);

            //
            function buildColumns(columns) {
                let dtColumns = [];
                columns.forEach(col => {
                    // ACTION COLUMN
                    if (col.type === 'actions') {
                        dtColumns.push({
                            data: null,
                            orderable: false,
                            searchable: false,
                            title: 'Action',
                            render: function (data, type, row) {
                                const items = col.actions.map((a, i) => {
                                    const cls = a.class ? ` ${a.class}` : '';
                                    let dataAttrs = '';
                                    if (typeof a.data === 'function') {
                                        const obj = a.data(row) || {};
                                        Object.keys(obj).forEach(k => {
                                            dataAttrs += ` data-${k}="${String(obj[k]).replace(/"/g, '&quot;')}"`;
                                        });
                                    }
                                    return `
                                        <a href="javascript:;" class="dropdown-item${cls}"
                                            data-action-index="${i}" 
                                            ${dataAttrs}>
                                            ${a.icon ? a.icon + ' ' : ''}${a.label}
                                        </a>
                                    `;
                                }).join('');
                                return `
                                    <div class="btn-group btn-group-toggle ml-auto">
                                        <button class="btn btn-secondary dropdown-toggle"
                                            data-bs-toggle="dropdown">
                                            Action
                                        </button>
                                        <div class='dropdown-menu'>
                                            ${items}
                                        </div>
                                    </div>
                                `;
                            }
                        });
                        return;
                    }

                    // NORMAL COLUMN
                    const hasData = typeof col.data === 'string' && col.data.length;
                    dtColumns.push({
                        data: hasData ? col.data : null,
                        orderable: hasData,
                        searchable: hasData,
                        defaultContent: '',
                        render: function (val, type, row) {
                            if (!hasData && Array.isArray(col.combine)) {
                                const values = {};
                                col.combine.forEach(k => values[k] = row[k]);
                                return col.formatter
                                    ? col.formatter(values, row, type)
                                    : Object.values(values).filter(Boolean).join(' ');
                            }
                            if (col.formatter) {
                                return col.formatter(val, row, type);
                            }
                            return val ?? '';
                        }
                    });
                });

                return dtColumns;
            }
            
            const dt = $table.DataTable({
                ajax: {
                    url: settings.source,
                    dataSrc: 'data',
                    data: function (d) {
                        // kirim filter ke server
                        $('.app-filter-container .filter-input').each(function () {
                            if (this.value) {
                                d[$(this).data('filter')] = this.value;
                            }
                        });
                    }
                },
                serverSide: settings.serverSide,
                processing: true,
                searching: settings.searching,       // live search
                paging: true,
                info: false,
                lengthChange: false,
                pageLength: settings.displayLength,
                lengthMenu: [10, 20, 50, 100],
                ordering: true,
                order: settings.order,
                stateSave: settings.stateSave,
                responsive: settings.responsive,
                language: {
                    paginate: {
                        previous: '<i class="fa fa-angle-left"></i>',
                        next: '<i class="fa fa-angle-right"></i>'
                    }
                },
                buttons: [
                    {
                        extend: 'colvis',
                        text: '<i class="fa fa-columns "></i>',
                        className: 'btn btn-light btn-column-visibility',
                        columns: function (idx, data, node) {
                            const colDef = settings.columns[idx];
                            if (!colDef) 
                                return false;
                            if (colDef.type === 'actions') 
                                return false;
                            if (colDef.className && colDef.className.includes('no-vis')) 
                                return false;
                            return true;
                        }
                    }
                ],
                dom: `
                    <"row mb-1"
                        <"col-md-6 app-left-toolbar"B>
                        <"col-md-6 app-right-toolbar"f>
                    >
                    <"row"<"col-12"tr>>
                    <"row mt-2"
                        <"col-md-3 app-footer"
                            <"text-left app-length">
                            <"text-left app-info">
                        >
                        <"col-md-9 text-center app-pagination"p>
                    >
                `,
                columns: buildColumns(settings.columns),
                drawCallback: function () {
                    const api = this.api();
                    const info = api.page.info();
                    //
                    const start = info.recordsTotal === 0 ? 0 : info.start + 1;
                    const end = info.end;
                    const total = info.recordsTotal;
                    $('.app-info').html(`${start}–${end} / ${total}`);
                    //
                    const $paginate = $(api.table().container()).find('.dataTables_paginate');
                    if (info.pages <= 1) {
                        $paginate
                            .find('.paginate_button')
                            .not('.previous, .next')
                            .hide();
                        $paginate.find('.previous, .next').addClass('disabled');
                    } else {
                        $paginate
                            .find('.paginate_button')
                            .show();
                    }
                },
                initComplete: function () {
                    const api = this.api();
                    //
                    const $dtWrapper = $(api.table().container());
                    $dtWrapper
                        .find('.row.mb-1')
                        .after($filterContainer);
                    //
                    const $filter = $(api.table().container()).find('.dataTables_filter');
                    const $searchWrapper = $('<div class="app-right-search filter-item-box"></div>');
                    $searchWrapper.append($filter);
                    $('.app-right-toolbar').append($searchWrapper);
                }
            });

            $('.app-pagination').append($table.parent().find('.dataTables_paginate'));

            const $length = $(`
                <select class="form-select form-select-sm" style="width:auto">
                    <option value="10">10</option>
                    <option value="20">20</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            `);
            $('.app-length').empty().append($length);
            $length.val(dt.page.len());
            $length.on('change', function () {
                dt.page.len(this.value).draw();
            });
            
            //
            if (settings.excel || settings.pdf) {
                const $exportGroup = $(`
                    <div class="dt-buttons btn-group flex-wrap ms-2">
                        ${settings.excel ? `
                            <button type="button"
                                    class="btn btn-light btn-export-excel">
                                <i class="fa fa-file-excel-o"></i> Excel
                            </button>` : ''}
                        ${settings.pdf ? `
                            <button type="button"
                                    class="btn btn-light btn-export-pdf">
                                <i class="fa fa-file-pdf-o"></i> PDF
                            </button>` : ''}
                    </div>
                `);
                const $rightActions = $('<div class="app-right-actions filter-item-box"></div>');
                $rightActions.append($exportGroup);
                $('.app-right-toolbar').append($rightActions);
                $exportGroup.on('click', '.btn-export-excel', function () {
                    if (!settings.exportExcelUrl) 
                        return;
                    window.location.href = settings.exportExcelUrl;
                });
                $exportGroup.on('click', '.btn-export-pdf', function () {
                    if (!settings.exportPdfUrl) 
                        return;
                    window.location.href = settings.exportPdfUrl;
                });
            }

            let $filterBtn = null;
            if (settings.filters.length) {
                $filterBtn = $(`
                    <button class="btn btn-light btn-filter-toggle">Filter</button>
                `);
                settings.filters.forEach(f => {
                    let field = '';
                    if (f.type === 'select') {
                        field = `
                            <select class="form-select filter-input"
                                    data-filter="${f.name}">
                                <option value="">All</option>
                                ${Object.entries(f.options || {}).map(
                                    ([k, v]) => `<option value="${k}">${v}</option>`
                                ).join('')}
                            </select>
                        `;
                    }
                    if (f.type === 'date') {
                        field = `
                            <input type="date"
                                    class="form-control filter-input"
                                    data-filter="${f.name}">
                        `;
                    }
                    if (f.type === 'text') {
                        field = `
                            <input type="text"
                                    class="form-control filter-input" placeholder="${f.label}"
                                    data-filter="${f.name}">
                        `;
                    }
                    $filterContainer.find('.app-filter-body').append(`
                        <div class="col-md-2">
                            ${field}
                        </div>
                    `);
                });
                $filterContainer.find('.app-filter-body').append(`
                    <div class="app-filter-action col-md-auto mt-2">
                        <button class="btn btn-success btn-apply-filter"
                                title="Apply filter"><i class="fa fa-check"></i></button>
                        <button class="btn btn-default btn-close-filter"
                                title="Close & reset"><i class="fa fa-times"></i></button>
                    </div>
                `);
                setTimeout(() => {
                    $filterContainer.find('select').select2({
                        width: '100%',
                        dropdownParent: $filterContainer
                    });
                }, 0);
                $filterBtn.on('click', function () {
                    $filterContainer.toggleClass('d-none');
                    $filterBtn.addClass('d-none');
                });
                $('.app-left-toolbar')
                    .css('position', 'relative')
                    .append($filterBtn);
            }
            //
            $filterContainer.on('click', '.btn-apply-filter', function () {
                dt.ajax.reload();
            });
            $filterContainer.on('click', '.btn-close-filter', function () {
                $filterContainer.find('.filter-input').each(function () {
                    $(this).val('');
                    // reset select2 safely
                    if ($(this).is('select')) {
                        $(this).trigger('change.select2');
                    }
                });
                dt.ajax.reload();   // reset server filter
                $filterContainer.addClass('d-none');
                $filterBtn.removeClass('d-none');
            });
            return {
                reload: () => dt.ajax.reload(),
                datatable: dt
            };
        },
        appNavTab: function (options) {
            const config = $.extend({
                container: null,              // wrapper
                tabSelector: '[data-tab]',    // tab button selector
                targetAttr: 'bsTarget',       // data attribute name
                paneSelector: null,           // selector semua pane
                activeTabClass: 'active',
                activePaneClass: 'show active',
                spinner: `
                    <div class="d-flex justify-content-center py-5">
                        <div class="spinner-border text-primary"></div>
                    </div>
                `,
                preload: true,
                observerMargin: '150px'
            }, options);

            if (!config.container || !config.paneSelector) return;

            const $container = $(config.container);
            const $panes = $(config.paneSelector);

            const cache = {};
            const preloadQueue = [];
            let isLoading = false;

            function activateTab($btn) {
                $container.find(config.tabSelector).removeClass(config.activeTabClass);
                $btn.addClass(config.activeTabClass);
            }
            function activatePane(target) {
                $panes.removeClass(config.activePaneClass);
                $(target).addClass(config.activePaneClass);
            }
            function load(url, target, showLoading = true, done = null) {
                if (cache[target] || isLoading) return;

                isLoading = true;

                if (showLoading) {
                    $(target).html(config.spinner);
                }

                $.ajax({
                    url,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    success(html) {
                        cache[target] = html;
                        if (showLoading) $(target).html(html);
                    },
                    complete() {
                        isLoading = false;
                        done && done();
                    }
                });
            }
            function runQueue() {
                if (isLoading || !preloadQueue.length) return;
                const job = preloadQueue.shift();
                load(job.url, job.target, false, runQueue);
            }
            // CLICK TAB
            $container.on('click', config.tabSelector, function (e) {
                e.preventDefault();

                const $btn = $(this);
                const url = $btn.attr('href');
                const target = $btn.data(config.targetAttr);

                activateTab($btn);
                activatePane(target);

                cache[target]
                    ? $(target).html(cache[target])
                    : load(url, target, true);
            });
            // AUTO LOAD ACTIVE TAB
            const $active = $container
                .find(config.tabSelector + '.' + config.activeTabClass)
                .first();
            if ($active.length) {
                const target = $active.data(config.targetAttr);
                activatePane(target);
                load($active.attr('href'), target, true);
            }
            if (config.preload && 'IntersectionObserver' in window) {

                const observer = new IntersectionObserver(entries => {
                    entries.forEach(entry => {
                        if (!entry.isIntersecting) return;

                        const btn = entry.target;
                        const $btn = $(btn);
                        const target = $btn.data(config.targetAttr);
                        const url = $btn.attr('href');

                        if (!cache[target]) {
                            preloadQueue.push({ url, target });
                            runQueue();
                        }

                        observer.unobserve(btn);
                    });
                }, { rootMargin: config.observerMargin });

                $container.find(config.tabSelector).each(function () {
                    observer.observe(this);
                });
            }
        },
        appFileUpload: function (options) {
            const o = $.extend({
                container: null || '.js-fileupload',
                fileInput: null,
                previewText: null || '.js-fileupload-filename',
                chooseTrigger: null,
                removeTrigger: null,
                progressTarget: null || '.progress-bar',
                hiddenInput: null || '.js-fileupload-result',
                url: null || '',
                maxSizeMB: null || 5,
                allowedExt: [],
                onDone: function () { },
                onError: function (msg) {
                    $.ct?.message ? $.ct.message('error', msg) : alert(msg);
                }
            }, options);
            
            const maxSize = o.maxSizeMB * 1024 * 1024;
            const $c = $(o.container);

            const $file = $c.find(o.fileInput);
            const $hidden = $c.find(o.hiddenInput);
            const $preview = $c.find(o.previewText);

            // open dialog
            $c.on('click', o.chooseTrigger, () => $file.trigger('click'));

            // remove
            $c.on('click', o.removeTrigger, function () {
                $preview.text('');
                $hidden.val('').trigger('change');
                $c.find(o.progressTarget).css('width', '0%');
            });

            // change file
            $file.on('change', function (e) {
                const file = e.target.files[0];
                if (!file) return;

                const ext = file.name.split('.').pop().toLowerCase();

                if (o.allowedExt.length && !o.allowedExt.includes(ext)) {
                    o.onError(`File harus: ${o.allowedExt.join(', ')}`);
                    return;
                }

                if (maxSize && file.size > maxSize) {
                    o.onError(`Ukuran > ${o.maxSizeMB} MB`);
                    return;
                }

                $preview.text(file.name);

                // create progress if missing
                if (!$c.find(o.progressTarget).length) {
                    $preview.after(`
                        <div class="progress mt-1">
                            <div class="progress-bar"></div>
                        </div>
                    `);
                }

                const $bar = $c.find(o.progressTarget).css('width', '0%');

                const fd = new FormData();
                fd.append('file', file);

                var req = $.ajax({
                    url: o.url,
                    type: 'POST',
                    data: fd,
                    contentType: false,
                    processData: false,
                    xhr: function () {
                        const xhr = new XMLHttpRequest();
                        xhr.upload.onprogress = e => {
                            if (e.lengthComputable) {
                                $bar.css('width', Math.round(e.loaded / e.total * 100) + '%');
                            }
                        };
                        return xhr;
                    }
                })
                req.done(function(res) {
                    $bar.css('width', '100%');
                    $hidden.val(res?.file ?? file.name).trigger('change');
                    o.onDone(res, $c);
                })
                req.fail(function() {
                    o.onError('Upload gagal');
                    $bar.css('width', '0%');
                });

                $file.val('');
            });
        },
        appImageUpload: function (opt) {
            let cropper = null;
            let activeInput = null;
            let activeFile = null;
            let activePreview = null;
            let activeCfg = null;
            const inputSelector = opt.inputSelector;
            const wrapperSelector = opt.wrapperSelector;
            const previewSelector = opt.previewSelector;
            const modalSelector = opt.modalSelector || '#modal-cropper-js';
            const imageSelector = opt.imageSelector || '.cropper-image-container img';
            const cropBtnSelector = opt.cropBtnSelector || '.btn-crop';
            const MAX_SIZE = opt.maxSize || 2097152;
            const CONFIG = opt.config || {};
            const DEFAULT = { w: 300, h: 300, ratio: 1 };

            const modal = $(modalSelector);

            $(document).on('change', inputSelector, function () {
                const file = this.files[0];
                if (!file) return;
                if (!file.type.match('image.*')) {
                    alert('Please input correct image type');
                    this.value = '';
                    return;
                }

                if (file.size > MAX_SIZE) {
                    alert('Max file size exceeded');
                    this.value = '';
                    return;
                }

                const wrapper = $(this).closest(wrapperSelector);
                const preview = wrapper.find(previewSelector);
                if (!preview.length) return;

                activeInput = this;
                activePreview = preview;
                activeFile = file;
                activeCfg = CONFIG[this.name] || DEFAULT;

                const reader = new FileReader();
                reader.onloadend = function (e) {
                    const image = modal.find(imageSelector);
                    image.attr('src', e.target.result);

                    modal.modal({
                        backdrop: 'static',
                        keyboard: false
                    });

                    modal.modal('show');

                    modal.off('shown.bs.modal').on('shown.bs.modal', function () {
                        // if (cropper) {
                        //     cropper.destroy();
                        //     cropper = null;
                        // }
                        const isFavicon =
                            activeCfg.w === activeCfg.h &&
                            activeCfg.ratio === 1;
                        cropper = image.cropper({
                            aspectRatio: activeCfg.ratio,
                            viewMode: 1,
                            dragMode: 'move',
                            zoomOnWheel: false,
                            background: false,
                            autoCropArea: isFavicon ? 0.6 : 0.85
                        });
                    });

                    modal.find(cropBtnSelector)
                        .on('click', function (e) {
                            e.stopPropagation();
                            $(this).blur();
                            if(!image) return;
                            const canvas = image.cropper('getCroppedCanvas', {
                                width: parseFloat(activeCfg.w),
                                height: parseFloat(activeCfg.h)
                            });
                            const mime = activeFile.type.includes('jpeg')
                                ? 'image/jpeg'
                                : 'image/png';
                            canvas.toBlob(blob => {
                                const dt = new DataTransfer();
                                dt.items.add(new File([blob], activeFile.name, { type: mime }));
                                activeInput.files = dt.files;
                                activePreview.attr('src', canvas.toDataURL(mime));
                                // cropper.destroy();
                                // cropper = null;
                            }, mime);
                            modal.modal('hide');
                        });
                };

                reader.readAsDataURL(file);
            });
        },
        appValidate: function (selector, options = {}) {
            const settings = $.extend(true, {
                errorClass: 'is-invalid',
                errorElement: 'div',
                errorMessageClass: 'ct-invalid-feedback',
                blockClass: 'ct-app-block',
                disableSubmit: true,
                ajax: false,
                validateOnKeyup: true,
                focusFirstError: true
            }, options);

            if (!$.validator) {
                console.error('jQuery Validate belum di-load');
                return;
            }

            // REGISTER FILE RULE (SAFE)
            $.validator.addMethod('file', function (value, element) {
                if (!element.files || !element.files.length) return true;

                const $el = $(element);
                const file = element.files[0];

                const maxSize = parseInt($el.data('file-max') || 2048, 10); // KB
                const extAllow = ($el.data('file-ext') || '').split(',');

                const ext = file.name.split('.').pop().toLowerCase();
                const sizeKB = file.size / 1024;

                if (extAllow[0] && !extAllow.includes(ext)) return false;
                if (sizeKB > maxSize) return false;

                return true;
            }, 'File tidak valid');

            const $forms = (selector instanceof $) ? selector : $(selector);

            return $forms.each(function () {

                const $form = $(this);
                const rules = {};
                const messages = {};
                const $submitBtn = $form.find('[type="submit"], [data-submit-btn]');

                // PARSE RULES
                $form.find('[data-validate]').each(function () {

                    const $field = $(this);
                    const name = $field.attr('name');
                    if (!name) return;

                    rules[name] = {};
                    messages[name] = {};

                    $field.data('validate').toString().split('|').forEach(rule => {

                        let [type, param] = rule.split(':');

                        switch (type) {

                            case 'equalTo':
                                rules[name][type] = `[name="${param}"]`;
                                break;

                            case 'file':
                                rules[name][type] = true;
                                break;

                            case 'minlength':
                            case 'maxlength':
                            case 'min':
                            case 'max':
                                rules[name][type] = Number(param);
                                break;

                            default:
                                if (param !== undefined) {
                                    rules[name][type] = param;
                                } else {
                                    rules[name][type] = true;
                                }
                        }
                    });
                });

                // INIT VALIDATE
                $form.validate({
                    rules,
                    messages,

                    onkeyup: settings.validateOnKeyup
                        ? function (el) { $(el).valid(); }
                        : false,

                    onfocusout: settings.validateOnKeyup
                        ? function (el) { $(el).valid(); }
                        : false,

                    onchange: settings.validateOnKeyup
                        ? function (el) { $(el).valid(); }
                        : false,

                    errorClass: settings.errorClass,
                    errorElement: settings.errorElement,

                    highlight(el) {
                        $(el).addClass(settings.errorClass);
                    },

                    unhighlight(el) {
                        $(el).removeClass(settings.errorClass);
                    },

                    errorPlacement(error, element) {
                        error
                            .addClass(settings.errorMessageClass)
                            .insertAfter(element);
                    },

                    invalidHandler(e, validator) {
                        if (settings.focusFirstError && validator.errorList.length) {
                            const first = validator.errorList[0].element;
                            $('html, body').animate({
                                scrollTop: $(first).offset().top - 120
                            }, 300, function () {
                                $(first).focus();
                            });
                        }
                    },

                    submitHandler(form) {
                        blockForm();

                        if (settings.ajax) {
                            $form.trigger('app:submit', [form, unblockForm]);
                        } else {
                            form.submit();
                        }
                    }
                });

                function blockForm() {
                    if (settings.disableSubmit) {
                        $submitBtn
                            .prop('disabled', true)
                            .data('text', $submitBtn.html())
                            .html('Processing...');
                    }
                    $form.addClass(settings.blockClass);
                }

                function unblockForm() {
                    if (settings.disableSubmit) {
                        $submitBtn
                            .prop('disabled', false)
                            .html($submitBtn.data('text'));
                    }
                    $form.removeClass(settings.blockClass);
                }

                $form.on('app:unblock', unblockForm);
            });
        },
        appThemePicker: function (wrapperSelector, options = {}) {
            const settings = $.extend({
                cssVarBg: '--theme-bg-color',
                cssVarText: '--theme-color',
                cssVarTextHover: '--theme-hover-color',
                radioSelector: 'input[type=radio][name=theme_color]',
                colorPickerSelector: 'input[type=color][name=theme_color_custom]',
                previewTextSelector: 'input[type=text]'
            }, options);
            const $wrapper = $(wrapperSelector);
            function getContrastColor(hex) {
                hex = hex.replace('#', '');
                const r = parseInt(hex.substr(0, 2), 16);
                const g = parseInt(hex.substr(2, 2), 16);
                const b = parseInt(hex.substr(4, 2), 16);
                // relative luminance
                const luminance = (0.299 * r + 0.587 * g + 0.114 * b);
                return luminance > 186 ? '#000000' : '#ffffff';
            }
            function adjustColor(hex, percent) {
                hex = hex.replace('#', '');
                let r = parseInt(hex.substring(0, 2), 16);
                let g = parseInt(hex.substring(2, 4), 16);
                let b = parseInt(hex.substring(4, 6), 16);
                r = Math.min(255, Math.max(0, r + (r * percent / 100)));
                g = Math.min(255, Math.max(0, g + (g * percent / 100)));
                b = Math.min(255, Math.max(0, b + (b * percent / 100)));
                return '#' +
                    r.toString(16).padStart(2, '0') +
                    g.toString(16).padStart(2, '0') +
                    b.toString(16).padStart(2, '0');
            }
            function applyColor(color) {
                if (color.toLowerCase() === '#ffffff'||color.toLowerCase()==='#fff') {
                    resetTheme();
                } else {
                    const textColor = getContrastColor(color);
                    const hoverColor = getContrastColor(color) === '#ffffff'
                        ? adjustColor(color, -12)
                        : adjustColor(color, 12);
                    document.documentElement.style.setProperty(settings.cssVarBg, color);
                    document.documentElement.style.setProperty(settings.cssVarText, textColor);
                    document.documentElement.style.setProperty(settings.cssVarTextHover, hoverColor);
                    $wrapper.find(settings.previewTextSelector).val(color);
                }
            }
            function resetTheme() {
                const root = document.documentElement;
                root.style.removeProperty(settings.cssVarBg);
                root.style.removeProperty(settings.cssVarText);
                root.style.removeProperty(settings.cssVarTextHover);
            }
            // radio click
            $wrapper.on('change', settings.radioSelector, function () {
                const color = $(this).val();
                applyColor(color);
            });
            // custom color picker
            $wrapper.on('input change', settings.colorPickerSelector, function () {
                const color = $(this).val();
                applyColor(color);
                // uncheck radio
                $wrapper.find(settings.radioSelector).prop('checked', false);
            });
        }
    }
    
})(this.jQuery, window, document);

