<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="no-js material-style">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport" >
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <base href="/"/>
    <link rel="icon" href="{{ asset('media/img/favicon/favicon.ico') }}" type="image/x-icon"/>
    <link rel="shortcut icon" href="{{ asset('media/img/favicon/favicon.ico') }}" type="image/x-icon"/>
    <title><?php echo $title ?> | <?php echo strtoupper($siteTitle) ?></title>
    <link href="{{ asset('media/css/reset.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('media/css/icons.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('media/css/fonts.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('media/plugins/select2/select2.css') }}" rel="stylesheet">
    <link href="{{ asset('media/css/admin/message.css') }}" rel="stylesheet">
    <link href="{{ asset('media/css/bootstrap-5/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('media/css/theme/bootstrap-material.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('media/css/theme/theme-material.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('media/css/theme/app-material.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('media/css/theme/colors-material.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('media/css/admin/login.bs4.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ asset('media/css/admin/main.bs4.css') }}" rel="stylesheet" type="text/css"/>
    <script src="{{ asset('media/js/modernizr.custom.js') }}"></script>
</head>
<body class="external-page sb-l-c sb-r-c">
    <div id="main">
        <div id="content_wrapper" class="login-page">
            <div class="login-logo m-t-30 p-b-5">&nbsp;</div>
            <div class="login-box card account-container">
                <div class="card-body ">
                    <div class="text-center w-75 m-auto">
                        <h4 class="text-dark-50 font-weight-bold" style="margin:10px 0 3px 0;font-size:18px">Sign In</h4>
                        <p class="text-muted "><?php echo __('Please enter your account to signin '); ?></p>
                    </div>
                    <form method="post" action="" name="form-login" id="form-login" class="pt-4">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group {{ $errors->has('tahun') ? ' has-error' : '' }}">
                                    <label for="tahun">Tahun</label>
                                    <input class="form-control {{ $errors->has('tahun') ? ' is-invalid' : '' }}" type="text" id="tahun" value="<?php echo date('Y') ?>" placeholder="" name="tahun" autocomplete="off" tabIndex="1" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tahun">Divisi</label>
                                    <select name="divisi" id="divisi" class="select2 form-control" tabIndex="2">
                                        <option value="" disabled selected>-- Pilih --</option>
                                        <?php 
                                            if($divisi != null) :
                                                foreach($divisi as $row) :
                                        ?>
                                            <option value="<?php echo $row["nama"] ?>"><?php echo $row["nama"] ?></option>
                                        <?php 
                                                endforeach;
                                            endif;
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group {{ $errors->has('username') ? ' has-error' : '' }}">
                                    <label for="username">Username</label>
                                    <input class="form-control {{ $errors->has('username') ? ' is-invalid' : '' }}" type="text" id="username" placeholder="" name="username" autocomplete="off" tabIndex="3" value="{{ old('username') }}">
                                    @if ($errors->has('username'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('username') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group {{ $errors->has('password') ? ' has-error' : '' }}">
                                    <a href="" class="text-muted float-right btn-forgot-password"><small>Lupa password?</small></a>
                                    <label for="password">Password</label>
                                    <div class="input-group input-group-merge">
                                        <input type="password" id="password" name="password" class="form-control {{ $errors->has('password') ? ' is-invalid' : '' }}" placeholder="" autocomplete="off" tabIndex="4" value="{{ old('password') }}">
                                    </div>
                                    @if ($errors->has('password'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('password') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="form-group mb-3">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="remember" value="" name="remember">
                                <label class="custom-control-label" for="remember"><?php echo __('Ingat saya') ?></label>
                            </div>
                        </div>
                        <div class="form-group mb-0 text-right">
                            <button class="btn btn-primary btn-block btn-sign-in button" type="submit">{{ __('Sign In') }}</button>
                        </div>
                        <div class="mt20 mt-2">You don't have an account? &nbsp; <a href="#" class="btn-sign-up button">Sign up</a></div>
                    </form>
                </div> <!-- end card-body -->
            </div>
        </div>
    </div>

    <div id="modal_forgot_password" class="modal fade" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-sm">
            <div class="modal-content animated bounceInDown">
                <div class="modal-header">
                    <h4><?php echo __('Lupa Password?'); ?></h4>
                </div>
                <div class="modal-body">
                    <p><?php echo __('Silahkan menghubungi team IT untuk mendapatkan password baru.');?></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary mt-2" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <div id="modal_sign_up" class="modal fade" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-sm">
            <div class="modal-content animated bounceInDown">
                <div class="modal-header">
                    <h4><?php echo __('Sign Up'); ?></h4>
                </div>
                <div class="modal-body">
                    <p><?php echo __('Silahkan menghubungi team IT untuk mendapatkan user.');?></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary mt-2" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <script src="{{ asset('media/js/jquery-3.5.1.min.js') }}"></script>
    <script src="{{ asset('media/js/bootstrap-5/popper.js') }}"></script>
    <script src="{{ asset('media/js/bootstrap-5/bootstrap.min.js') }}"></script>
    <script src="{{ asset('media/js/jquery-ui.custom.js') }}"></script>
    <script src="{{ asset('media/plugins/select2/select2.full.js') }}"></script>
    <script src="{{ asset('media/js/admin/setup.js') }}"></script>
    <script src="{{ asset('media/js/admin/message.js') }}"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            //
            jQuery(".btn-forgot-password").click(function (e) {
                e.preventDefault();
                jQuery('#modal_forgot_password').modal('show');
                return false;
            });
            jQuery(".btn-sign-up").click(function (e) {
                e.preventDefault();
                jQuery('#modal_sign_up').modal('show');
                return false;
            });

            //handle login
            var doc = jQuery('html').addClass('js-login'),
                container = jQuery('#container'),
                formLogin = jQuery('#form-login'),
                signIn = jQuery('.btn-sign-in'),
                // If layout is centered
                centered;
            /*
            * AJAX login
            * This function will handle the login process through AJAX
            */
            formLogin.submit(function (event)
            {
                // Values
                var username = jQuery.trim(jQuery('#username').val()),
                    pass = jQuery.trim(jQuery('#password').val()),
                    captcha = jQuery.trim(jQuery('#captcha').val());

                signIn.attr('disabled', 'disabled');

                // Check inputs
                if (username.length === 0) {
                    // Remove previous messages
                    formLogin.clearMessages();
                    signIn.removeAttr('disabled');
                    // Display message
                    displayError('Username harap diisi.');
                    return false;
                } else if (pass.length === 0) {
                    // Remove previous messages
                    formLogin.clearMessages();
                    signIn.removeAttr('disabled');
                    // Display message
                    displayError('Password harap diisi.');
                    return false;
                } else {
                    // Remove previous messages
                    formLogin.clearMessages();

                    event.preventDefault();

                    // Show progress
                    displayLoading('Checking Data...');

                    /*
                    * This is where you may do your AJAX call, for instance:
                    */
                    jQuery.ajax({
                        url: "{{ route('admin.login_process') }}",
                        dataType: 'json',
                        type: 'POST',
                        data: $("#form-login").serialize(),
                        success: function (response) {
                            var err_code = response.errCode;
                            var err_msg = response.errMsg;
                            if (err_code == 0) {
                                var dt = response.data;
                                var url = "{{ $url }}";
                                var next_url = "{{ $next_url }}";
                                document.location.href = (next_url != null && next_url != '' ? next_url : url);
                            } else {
                                formLogin.clearMessages();
                                signIn.removeAttr('disabled');
                                displayError(err_msg);
                            }
                        },
                        error: function (xhr, txtStatus, error) {
                            formLogin.clearMessages();
                            signIn.removeAttr('disabled');
                            displayError('Error while contacting server, please try again');
                        }
                    });
                    return false;
                }
            });

            // Handle resizing (mostly for debugging)
            function handleLoginResize()
            {
                // Detect mode
                centered = (container.css('position') === 'absolute');

                // Set min-height for mobile layout
                if (!centered)
                {
                    container.css('margin-top', '');
                } else
                {
                    if (parseInt(container.css('margin-top'), 10) === 0)
                    {
                        centerForm(false);
                    }
                }
            }
            ;

            // Register and first call
            $(window).bind('normalized-resize', handleLoginResize);
            handleLoginResize();

            /*
            * Center function
            * @param boolean animate whether or not to animate the position change
            * @param string|element|array any jQuery selector, DOM element or set of DOM elements which should be ignored
            * @return void
            */
            function centerForm(animate, ignore)
            {
                // If layout is centered
                if (centered)
                {
                    var siblings = formLogin.siblings(),
                            finalSize = formLogin.outerHeight();

                    // Ignored elements
                    if (ignore)
                    {
                        siblings = siblings.not(ignore);
                    }

                    // Get other elements height
                    siblings.each(function (i)
                    {
                        finalSize += $(this).outerHeight(true);
                    });

                    // Setup
                    container[animate ? 'animate' : 'css']({marginTop: -Math.round(finalSize / 2) + 'px'});
                }
            }
            ;

            // Initial vertical adjust
            centerForm(false);

            /**
             * Function to display error messages
             * @param string message the error to display
             */
            function displayError(message)
            {
                // Show message
                var message = formLogin.message(message, {
                    append: false,
                    arrow: 'none',
                    classes: ['red-gradient'],
                    animate: false					// We'll do animation later, we need to know the message height first
                });

                // Vertical centering (where we need the message height)
                centerForm(true, 'fast');

                // Watch for closing and show with effect
                message.bind('endfade', function (event)
                {
                    // This will be called once the message has faded away and is removed
                    centerForm(true, message.get(0));

                }).hide().slideDown('fast');
            }

            /**
             * Function to display loading messages
             * @param string message the message to display
             */
            function displayLoading(message)
            {
                // Show message
                var message = formLogin.message('<strong>' + message + '</strong>', {
                    append: false,
                    arrow: 'none',
                    classes: ['blue-gradient', 'align-center'],
                    stripes: true,
                    darkStripes: false,
                    closable: false,
                    animate: false					// We'll do animation later, we need to know the message height first
                });

                // Vertical centering (where we need the message height)
                centerForm(true, 'fast');

                // Watch for closing and show with effect
                message.bind('endfade', function (event)
                {
                    // This will be called once the message has faded away and is removed
                    centerForm(true, message.get(0));

                }).hide().slideDown('fast');
            }

        });
    </script>
</body>
</html>
