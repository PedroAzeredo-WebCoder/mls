<!DOCTYPE html>
<html lang="pt-BR" data-lt-installed="true">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta id="viewport" name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, viewport-fit=cover" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="copyright" content="© 2022 [%author%]" />
    <meta name="robots" content="all" />
    <meta name="robots" content="max-image-preview:standard" />
    <meta name="revisit-after" content="7 days" />
    <meta name="description" content="[%description%]">
    <meta name="author" content="[%author%]">
    <meta name="theme-color" />
    <meta name="msapplication-navbutton-color" />
    <meta name="msapplication-TileColor" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black" />
    <meta name="msapplication-TileImage" content="[%icon%]">

    <link href="[%icon%]" rel="[%icon%]">

    <!-- <link rel="stylesheet" type="text/css" href="./app-assets/vendors/css/vendors.min.css"> -->
    <link rel="stylesheet" type="text/css" href="./app-assets/css/bootstrap.css">
    <link rel="stylesheet" type="text/css" href="./app-assets/css/bootstrap-extended.min.css">
    <link rel="stylesheet" type="text/css" href="./app-assets/css/app.css">

    <link rel="stylesheet" type="text/css" href="./app-assets/css/components.css">

    <link rel="stylesheet" type="text/css" href="./app-assets/css/themes/dark-layout.css">
    <link rel="stylesheet" type="text/css" href="./app-assets/css/core/menu/menu-types/vertical-menu.css">

    <link rel="stylesheet" type="text/css" href="https://unpkg.com/@phosphor-icons/web@2.0.3/src/bold/style.css" />
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.css" />
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/filepond/dist/filepond.min.css" />

    <link rel="icon" href="[%icon%]" sizes="32x32">
    <link rel="apple-touch-icon" href="[%icon%]">

    <title>[%title%]</title>
    [%css%]
</head>

<body class="vertical-layout vertical-menu-modern navbar-floating footer-static" data-open="click" data-menu="vertical-menu-modern" data-col="">
    <!-- BEGIN: Header-->
    [%include_topbar%]
    <!-- END: Header-->


    <!-- BEGIN: Main Menu-->
    <div class="main-menu menu-fixed menu-light menu-accordion menu-shadow" data-aos="fade-right" data-aos-duration="3000">
        <div class="navbar-heaer">
            <ul class="nav navbar-nav flex-row">
                <li class="nav-item mx-auto d-flex align-items-center">
                    <a class="navbar-brand" href="index.php">
                        <h2 class="brand-text my-3">
                            <img src="./app-assets/images/logo.svg" class="card-img-top" alt="Logo [%title%]" title="[%title%]">
                        </h2>
                    </a>
                </li>
                <!-- <li class="nav-item nav-toggle"><a class="nav-link modern-nav-toggle pe-0" data-bs-toggle="collapse"><i class="d-block d-xl-none text-primary toggle-icon font-medium-4" data-feather="x"></i><i class="d-none d-xl-block collapse-toggle-icon font-medium-4  text-primary" data-feather="disc" data-ticon="disc"></i></a></li> -->
            </ul>
        </div>
        <div class="shadow-bottom"></div>
        [%include_sidebar%]
    </div>
    <!-- END: Main Menu-->

    <!-- BEGIN: Content-->
    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper p-0 mt-2">
            <div class="content-header row">
                <div class="content-header-left col-md-9 col-12 mb-2">
                    <div class="row breadcrumbs-top">
                        <div class="col-12">
                            <h2 class="content-header-title float-start mb-0">[%title_page%]</h2>
                            <div class="breadcrumb-wrapper">
                                [%breadcrumb%]
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-body">
                <!-- BEGIN: Cards -->
                [%include_content%]
                <!--/ END: Cards -->
            </div>
        </div>
    </div>
    <!-- END: Content-->

    <div class="sidenav-overlay"></div>
    <div class="drag-target"></div>

    <!-- Footer -->
    <footer class="footer footer-static footer-light">
        <p class="clearfix mb-0 d-flex justify-content-between">
        <div class="float-md-start d-block d-md-inline-block mt-25">
            &copy; 2023 [%title%]. Todos os direitos reservados.
        </div>
        <div class="float-md-end d-block d-md-inline-block mt-25">
            Desenvolvido por Web Coder &reg;
        </div>
        </p>
    </footer>
    <!-- Footer -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns"></script>

    <script src="./app-assets/vendors/js/vendors.min.js"></script>
    <script src="./app-assets/js/core/app-menu.js"></script>
    <script src="./app-assets/js/core/app.js"></script>
    <script src="./app-assets/js/app.js"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/@phosphor-icons/web@2.0.3"></script>

    <!-- Babel polyfill, contains Promise -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/babel-core/5.6.15/browser-polyfill.min.js"></script>


    <!-- Get FilePond polyfills from the CDN -->
    <script src="https://unpkg.com/filepond-polyfill/dist/filepond-polyfill.js"></script>


    <script src="https://unpkg.com/filepond-plugin-file-encode/dist/filepond-plugin-file-encode.min.js"></script>
    <script src="https://unpkg.com/filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.min.js"></script>
    <script src="https://unpkg.com/filepond-plugin-image-exif-orientation/dist/filepond-plugin-image-exif-orientation.min.js"></script>
    <script src="https://unpkg.com/filepond-plugin-image-crop/dist/filepond-plugin-image-crop.min.js"></script>
    <script src="https://unpkg.com/filepond-plugin-image-resize/dist/filepond-plugin-image-resize.min.js"></script>
    <script src="https://unpkg.com/filepond-plugin-image-transform/dist/filepond-plugin-image-transform.min.js"></script>
    <script src="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.js"></script>
    <script src="https://unpkg.com/filepond-plugin-image-edit/dist/filepond-plugin-image-edit.js"></script>
    <script src="https://unpkg.com/filepond/dist/filepond.min.js"></script>


    [%js%]
    [%sweetalert%]
</body>

</html>