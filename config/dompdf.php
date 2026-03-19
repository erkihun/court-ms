<?php

$abyssinicaFont = resource_path('fonts/AbyssinicaSIL-Regular.ttf');
$nyalaFont = resource_path('fonts/Nyala.ttf');
$notoRegularFont = resource_path('fonts/NotoSansEthiopic-Regular.ttf');
$notoBoldFont = resource_path('fonts/NotoSansEthiopic-Bold.ttf');

return [

    /*
    |--------------------------------------------------------------------------
    | Settings
    |--------------------------------------------------------------------------
    |
    | Base settings for barryvdh/laravel-dompdf.
    |
    */

    'show_warnings' => false,   // Throw an Exception on warnings from dompdf
    'public_path'   => null,    // Override the public path if needed

    /*
     * Dejavu Sans font is missing glyphs for converted entities, turn it off if you need to show € and £.
     */
    'convert_entities' => true,

    /*
    |--------------------------------------------------------------------------
    | Fonts (Laravel layer)
    |--------------------------------------------------------------------------
    */

    // Where dompdf copies fonts and caches metrics
    'font_dir'   => storage_path('fonts/'),
    'font_cache' => storage_path('fonts/'),

    // Custom fonts
    'font_data'  => [
        'notoethiopic' => array_filter([
            'R' => is_file($notoRegularFont) ? $notoRegularFont : null,
            'B' => is_file($notoBoldFont) ? $notoBoldFont : (is_file($notoRegularFont) ? $notoRegularFont : null),
        ]),
        'nyala' => array_filter([
            'R' => is_file($nyalaFont) ? $nyalaFont : null,
        ]),
        'abyssinica' => array_filter([
            'R' => is_file($abyssinicaFont) ? $abyssinicaFont : null,
        ]),
    ],

    /*
    |--------------------------------------------------------------------------
    | Dompdf native options
    |--------------------------------------------------------------------------
    */

    'options' => [

        // dompdf font dir & cache (mirrors the top-level config, safe)
        'font_dir'   => storage_path('fonts'),
        'font_cache' => storage_path('fonts'),

        /**
         * ==== IMPORTANT ====
         * dompdf's "chroot": Prevents dompdf from accessing system files or other
         * files on the webserver. All local files opened by dompdf must be in a
         * subdirectory of this directory.
         */
        'chroot' => realpath(base_path()),

        /**
         * Protocol whitelist
         */
        'allowed_protocols' => [
            'data://'  => ['rules' => []],
            'file://'  => ['rules' => []],
            'http://'  => ['rules' => []],
            'https://' => ['rules' => []],
        ],

        /**
         * Operational artifact (log files, temporary files) path validation
         */
        'artifactPathValidation' => null,

        /**
         * @var string|null
         */
        'log_output_file' => null,

        /**
         * Whether to enable font subsetting or not.
         */
        'enable_font_subsetting' => false,

        /**
         * The PDF rendering backend to use
         */
        'pdf_backend' => 'CPDF',

        /**
         * html target media view which should be rendered into pdf.
         */
        'default_media_type' => 'screen',

        /**
         * The default paper size.
         */
        'default_paper_size' => 'a4',

        /**
         * The default paper orientation.
         */
        'default_paper_orientation' => 'portrait',

        /**
         * The default font family
         *
         * IMPORTANT: set this to your Amharic-capable font key.
         */
        'default_font' => is_file($abyssinicaFont) ? 'abyssinica' : (is_file($nyalaFont) ? 'nyala' : 'notoethiopic'),

        /**
         * Image DPI setting
         */
        'dpi' => 96,

        /**
         * Enable embedded PHP (in <script type="text/php">)
         */
        'enable_php' => false,

        /**
         * Enable inline JavaScript (PDF JS, not browser JS)
         */
        'enable_javascript' => true,

        /**
         * Enable remote file access
         */
        'enable_remote' => true,

        /**
         * List of allowed remote hosts (if enable_remote = true)
         */
        'allowed_remote_hosts' => null,

        /**
         * Ratio applied to font height (line-height)
         */
        'font_height_ratio' => 1.1,

        /**
         * Use the HTML5 Lib parser
         */
        'enable_html5_parser' => true,
    ],

];
