<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Livewire Class Namespace
    |--------------------------------------------------------------------------
    |
    | This value sets the root namespace for Livewire component classes in
    | your application. This value affects component auto-discovery and
    | any Livewire file helper commands, like `artisan make:livewire`.
    |
    */

    'class_namespace' => 'App\\Livewire',

    /*
    |--------------------------------------------------------------------------
    | Livewire View Path
    |--------------------------------------------------------------------------
    |
    | This value sets the path where Livewire component views are stored.
    | This affects file manipulation helper commands like `artisan make:livewire`.
    |
    */

    'view_path' => resource_path('views/livewire'),

    /*
    |--------------------------------------------------------------------------
    | Layout
    |--------------------------------------------------------------------------
    |
    | The default layout view that will be used when rendering a component via
    | Route::get('/some-endpoint', SomeComponent::class);. In this case the
    | the view returned by SomeComponent will be wrapped in "layouts.app"
    |
    */

    'layout' => 'components.layouts.app',

    /*
    |--------------------------------------------------------------------------
    | Lazy Loading Placeholder
    |--------------------------------------------------------------------------
    |
    | Livewire allows you to lazy load components that would otherwise slow
    | down the initial page load. Every component can have a placeholder.
    | This configuration defines the default placeholder view to show.
    |
    */

    'lazy_placeholder' => null,

    /*
    |--------------------------------------------------------------------------
    | Temporary File Uploads
    |--------------------------------------------------------------------------
    |
    | Livewire handles file uploads by storing uploads in a temporary directory
    | before the file is validated and stored permanently. All file uploads
    | are directed to a global endpoint for temporary storage. The config
    | items below are used for customizing the handling of that endpoint.
    |
    */

    'temporary_file_upload' => [
        'disk' => env('LIVEWIRE_TEMPORARY_FILE_UPLOAD_DISK', 'local'),
        'rules' => ['required', 'file', 'max:102400'], // 100MB Max
        'directory' => env('LIVEWIRE_TEMPORARY_FILE_UPLOAD_DIRECTORY', 'livewire-tmp'),
        'middleware' => null,
        'preview_mimes' => [
            'png', 'gif', 'bmp', 'svg', 'wav', 'mp4',
            'mov', 'avi', 'wmv', 'mp3', 'm4a',
            'jpg', 'jpeg', 'mpga', 'webp', 'wma',
        ],
        'max_upload_time' => 5, // minutes
    ],

    /*
    |--------------------------------------------------------------------------
    | Render On Redirect
    |--------------------------------------------------------------------------
    |
    | This value determines if Livewire will render before it's redirected or
    | not. Setting this to "false" (default) will mean the render method is
    | skipped when redirecting. And "true" will mean the component is
    | rendered before redirecting. Browsers bfcache can store a potentially
    | stale view if render is skipped on redirect.
    |
    */

    'render_on_redirect' => false,

    /*
    |--------------------------------------------------------------------------
    | Eloquent Model Binding
    |--------------------------------------------------------------------------
    |
    | Previous versions of Livewire always bound Eloquent models to route
    | parameters. This has been improved since then. This configuration
    | item is being kept for backwards compatibility for older users.
    |
    */

    'legacy_model_binding' => false,

    /*
    |--------------------------------------------------------------------------
    | Auto-inject Frontend Assets
    |--------------------------------------------------------------------------
    |
    | By default, Livewire automatically injects its JavaScript and CSS into
    | the page's <head> and <body>. You can disable this behavior here and
    | include them yourself. This is useful if you want to customize the
    | assets or if you want to use a CDN to serve them.
    |
    */

    'inject_assets' => true,

    /*
    |--------------------------------------------------------------------------
    | Inject Morph Markers
    |--------------------------------------------------------------------------
    |
    | Livewire uses HTML comments to track and identify elements across
    | requests. This configuration item allows you to turn off the
    | comments if you need to (for SEO, debugging, etc.).
    |
    */

    'inject_morph_markers' => true,

    /*
    |--------------------------------------------------------------------------
    | Navigate (SPA mode)
    |--------------------------------------------------------------------------
    |
    | By default, Livewire will prevent standard page loads and instead use
    | its own "SPA" mode; where the page is refreshed without a page-load.
    | You can customize the behavior of navigate() here.
    |
    */

    'navigate' => [
        'show_progress_bar' => true,
        'progress_bar_color' => '#2299dd',
    ],

    /*
    |--------------------------------------------------------------------------
    | HTML Morph Markers
    |--------------------------------------------------------------------------
    |
    | Livewire can track and identify elements across requests using HTML
    | comments. You can customize the format of these comments here.
    |
    */

    'back_button_cache' => true,

];

