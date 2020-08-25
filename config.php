<?php
// Config
$config = [
    "version" => "1.2.7",
    "debug_mode" => false,
    "site_name" => "MyChan",
    // Dolphin will be angry if you change this value!
    "software_name" => "BelugaChan",
    "access_point" => "/",
    // Must be in megabytes!
    "max_upload_size" => 10,
    "templates_dir" => "templates",
    "max_posts_on_front_page" => 10,
    "file_dir" => "files/",
    "file_mimetypes" => [
        "image/jpeg",
        "image/jpg",
        "image/png",
        "video/mp4",
        "video/webm",
        "text/plain",
        "image/gif",
        "text/pdf"
    ],
    "max_file_count" => 9,
    /* Database config */
    // Allowed types are sqlite3 and mysql, and "" (online mode only)
    "database_type" => "online",
    /* Set only if SQLite is being used! */
    "database_file" => "database.sqlite3",
    // If MySQL is being used, input your connection config:
    // MySQL database host, e.g. localhost
    "database_host" => "localhost",
    // MySQL database name
    "database_name" => "mydatabase",
    // MySQL database user
    "database_user" => "dolphinchan",
    // MySQL database user password
    "database_password" => "Rptz",
    // Board themes; none = no theme
    "themes" => ["none", "dolphin"],
    // Default board theme
    "default_theme" => "none",
    // Default poster name
    "default_poster_name" => "Dolphin",
    // Enable captcha?
    "captcha_enabled" => false,
    // Enable account registration?
    "account_registration_enabled" => true,
    // Blacklisted usernames
    "blacklisted_usernames" => ["Administrator"],
    // Board creation open?
    "board_creation_open" => true,
    // Allow custom CSS?
    "custom_css_allowed" => true,
    // Custom CSS file header; #board changes to "test" for instance
    "custom_css_header" => "/* Board CSS /#board/ */",
    // Max boards per account
    "max_boards_per_account" => 99,
    // How many posts to show on the front page?
    "recent_post_limit" => 5,
    // Salt (needed for IPs
    "salt" => '$6$rounds=5000$CHANGE_MY_SALT$',
    // IPs to not ban; Tor should be 127.0.0.1
    "unbannable_ips" => [],
    // For country flags:
    "allow_country_flags" => true,
    "geoip_database" => "GeoIP2-Country.mmdb",
    // Show top boards?
    "top_boards_enabled" => true,
    // Enable thumbnails? Needs php-imagemagick
    "enable_thumbnails" => true,
    // External thumbnail processor; be careful!
    "external_thumbnail_processor" => false,
    // Image file formats
    "image_formats" => ["jpg", "jpeg", "png", "gif", "webp"],
    // Logging configuration
    // Enable logging?
    "enable_logging" => true,
    // Folder to store the logs in
    "logs_folder" => "logs/",
    // User roles; do not touch
    "user_roles" => ["admin", "gvol", "user"],
    // Start text filters; do not use as normal filters because they are not meant to replace words
    "text_filters" => [
        // Green text; &gt; = ">"
        "&gt;" => '<span class="green_text">%s</span>',
        // Orange text; &lt; = "<"
        "&lt;" => '<span class="orange_text">%s</span>',
    ],
    // Should be used for hard-coded filters like ==example== and **test**
    "global_style_filters" => [
        // Big red text (== test ==)
        '/==(.+)==/Ui' => '<span class="big_text">\1</span>',
        // Spoiler text (**test**)
        '/\*\*(.+)\*\*/Ui' => '<span class="spoiler_text">\1</span>',
        // Bold text ('''test''')
        '/\'\'\'(.+)\'\'\'/Ui' => '<span class="bold_text">\1</span>',
        // Underlined text (__test__)
        '/__(.+)__/Ui' => '<span class="underline_text">\1</span>',
        // Spin text (!spin test spin!)
        '/\!spin(.+)spin\!/Ui' => '<span class="spin_text">\1</span>',

    ],
    // Enable captcha wall?
    "captcha_wall" => true,
    // Maximum amount of preview reply posts to show when browsing through the board (normal view; no catalog)
    "maximum_preview_replies" => 2,
    // Do you want to cache? If so, allowed values are "redis", "apcu", false (if you don't want to cache)
    "cache_store" => false,
    // Redis config if enabled
    // Redis host
    "redis_host" => false,
    // Redis port
    "redis_port" => 6379,
    // CDN config
    "enable_cdn" => false,
    // CDN URL
    "cdn_url" => "https://cdn.mychan.com/",
    // Enable PPH?
    "enable_pph" => true,
    // Auto-updating of replies
    "auto_updating" => true,
    // Method? "websockets" are supported
    "auto_updating_method" => "websockets",
    // WebSocket config
    // WebSocket port
    "websocket_port" => 5,
    // Domain/IP for WebSocket server
    "websocket_host" => "10.1.0.1",
    // MZQ connection; localhost only
    "mzq_port" => 5555,
    // External config
    "external_websocket_dsn" => "ws://10.1.0.1:5",
    // Webring config
    // Enable webring support?
    "webring_enabled" => true,
    // Webring cache dir
    "webring_cache_dir" => "webring/",
    // Webring sites to follow
    "webring_follow_sites" => [
        "https://chan.clab.li/webring.json"
    ],
    // Webring sites to be known
    "webring_known_sites" => [
        "https://someotherchan.example/webring.json"
    ],
    // Webring sites to blacklist
    "webring_blacklist_sites" => [
        "https://badchan.example/webring.json"
    ],
    // Website URL
    "website_url" => "http://mychan.example/",
    // Webring output file; you usually don't need to change this!
    "webring_filename" => "webring.json",
    // Proxy for webring; example is http://localhost:9050
    "webring_proxy" => "",
    // Reverse proxy config; will use HTTP_X_FORWARDED_FOR
    "enable_reverse_proxy" => false,
    // Pagination
    // How many threads do you want per page?
    "threads_per_page" => 5,
    // How many emotes per page in manage emotes?
    "emotes_per_page" => 1,
    // Emote filter
    "emotes_enabled" => true,
    // Template for emotes
    "emote_template" => '<img src="%s" alt="%s" title="%s" class="emote">',
    // Cron config
    "webcron" => true,
    // Online mode?
    "online_mode" => true,
    // API key
    "api_key" => "my_api_key",
    // Online server
    "online_server" => "https://beluga.dolphinch.xyz/api"
];

$routes = [
    $config["access_point"] => "home",
    $config["access_point"] . "forms/create_thread" => "forms/create_thread",
    $config["access_point"] . "forms/reply_thread" => "forms/reply_thread",
    $config["access_point"] . "error" => "error",
    $config["access_point"] . "login" => "login",
    $config["access_point"] . "forms/login" => "forms/login_process",
    $config["access_point"] . "dashboard" => "dashboard",
    $config["access_point"] . "manage_board" => "manage_board",
    $config["access_point"] . "forms/update_board" => "forms/update_board",
    $config["access_point"] . "delete_post" => "delete_post",
    $config["access_point"] . "forms/delete_post" => "forms/deletepost",
    $config["access_point"] . "register" => "register",
    $config["access_point"] . "forms/register" => "forms/register",
    $config["access_point"] . "forms/create_board" => "forms/create_board",
    $config["access_point"] . "forms/delete_board" => "forms/delete_board",
    $config["access_point"] . "forms/ban_user" => "forms/ban_user",
    $config["access_point"] . "ban_user" => "ban_user",
    $config["access_point"] . "forms/add_vol" => "forms/add_vol",
    $config["access_point"] . "forms/remove_vol" => "forms/remove_vol",
    $config["access_point"] . "forms/change_password" => "forms/change_password",
    $config["access_point"] . "logout" => "logout",
    $config["access_point"] . "forms/delete_thread" => "forms/delete_thread",
    $config["access_point"] . "forms/lift_ban" => "forms/lift_ban",
    $config["access_point"] . "boards" => "boards",
    $config["access_point"] . "manage_bans" => "manage_bans",
    $config["access_point"] . "forms/appeal_ban" => "forms/appeal_ban",
    $config["access_point"] . "manage_banners" => "manage_banners",
    $config["access_point"] . "forms/add_banner" => "forms/add_banner",
    $config["access_point"] . "forms/delete_banner" => "forms/delete_banner",
    $config["access_point"] . "delete_thread" => "forms/delete_thread",
    $config["access_point"] . "manage_config" => "manage_config",
    $config["access_point"] . "forms/update_config" => "forms/update_config",
    $config["access_point"] . "manage_boards" => "manage_boards",
    $config["access_point"] . "api" => "api",
    $config["access_point"] . "log" => "log",
    $config["access_point"] . "log_viewer" => "log_viewer",
    $config["access_point"] . "manage_users" => "manage_users",
    $config["access_point"] . "forms/update_user_role" => "forms/update_user_role",
    $config["access_point"] . "forms/thread_actions" => "forms/thread_actions",
    $config["access_point"] . "forms/delete_user" => "forms/delete_user",
    $config["access_point"] . "about" => "about",
    $config["access_point"] . "system_status" => "system_status",
    $config["access_point"] . "help" => "help",
    $config["access_point"] . "manage_global_filters" => "manage_global_filters",
    $config["access_point"] . "forms/delete_filter" => "forms/delete_filter",
    $config["access_point"] . "forms/add_filter" => "forms/add_filter",
    $config["access_point"] . "manage_board_filters" => "manage_board_filters",
    $config["access_point"] . "report" => "report",
    $config["access_point"] . "send_alert" => "send_alert",
    $config["access_point"] . "forms/send_alert" => "forms/send_alert",
    $config["access_point"] . "manage_emotes" => "manage_emotes",
    $config["access_point"] . "forms/add_emote" => "forms/add_emote",
    $config["access_point"] . "forms/delete_emote" => "forms/delete_emote",

];
