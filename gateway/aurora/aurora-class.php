<?php
    $theme_meta = [
        'Theme Name'        => 'Aurora Theme',
        'Theme URI'         => 'https://github.com/fattain-naime/aurora-theme-for-PipraPay',
        'Description'       => 'A compact, card-focused checkout for PipraPay merchants.',
        'Version'           => '1.0.0',
        'Author'            => 'Fattain Naime',
        'Author URI'        => 'https://iamnaime.info.bd',
        'License'           => 'GPL-3.0+',
        'License URI'       => 'http://www.gnu.org/licenses/gpl-3.0.txt',
        'Text Domain'       => 'piprapay-aurora',
        'Domain Path'       => '/languages',
        'Requires at least' => '1.0.0',
        'Requires PHP'      => '8.1'
    ];
    
    $funcFile = __DIR__ . '/functions.php';
    if (file_exists($funcFile)) {
        require_once $funcFile;
    }
?>
